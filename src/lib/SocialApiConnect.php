<?php
/**
 * SocialStreams\SocialApiConnect
 *
 * @package wp_social_streams
 * @author Stuart Laverick
 */
namespace AppSol\SocialStreams;

use OAuth\Common\Token\TokenInterface;
use OAuth\Common\Consumer\Credentials;
use OAuth\ServiceFactory;
use OAuth\Common\Http\Url;

defined('ABSPATH') or die( 'No script kiddies please!' );

abstract class SocialApiConnect
{

    /**
     * Social Network App ID
     *
     * @var string
     **/
    protected $appId;

    /**
     * Social Network App Secret
     *
     * @var string
     **/
    protected $appSecret;

    /**
     * OAuth Access Token
     *
     * @var string
     **/
    protected $accessToken;

    /**
     * OAuth Access Token Secret
     *
     * @var string
     **/
    protected $accessTokenSecret;

    /**
     * Current Page Url
     *
     * @var string
     **/
    protected $pageUrl;

    /**
     * OAuth authenticated session object
     *
     * @var Object
     **/
    protected $service;

    /**
     * The name used to identify the Social Network
     *
     * @var string
     **/
    protected $apiName;

    /**
     * Transient Prefix
     *
     * @var string
     **/
    protected $transientPrefix = 'social_streams';

    /**
     * Default lifetime for session transients (4 weeks)
     *
     * @var integer
     **/
    protected $transientLifetime = WEEK_IN_SECONDS;

    /**
     * Last error to be returned from the API
     *
     * @var object
     **/
    protected $lastMessage;

    /**
     * Constructor
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function __construct($appId, $appSecret)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->pageUrl  = admin_url('options-general.php?page=' . $_GET["page"]);
    }

    /**
     * See SocialApiInterface
     * {@inheritdoc}
     **/
    public function getNetworkName()
    {
        return $this->apiName;
    }

    /**
     * Return the authentication URL
     *
     * @return string Url
     * @author Stuart Laverick
     **/
    public function getAuthenticationUrl()
    {
        try {
            return $this->service->getAuthorizationUri();
        } catch (\Exception $e) {
            $this->setLastMessage($e->getMessage(), $e->getCode());
        }
        return false;
    }

    /**
     * Returns a URL that will allow the OAuth token to be deleted
     *
     * @return string URL
     * @author Stuart Laverick
     **/
    public function getDisconnectUrl()
    {
        return $this->pageUrl . '&disconnect=' . $this->apiName;
    }

    /**
     * Stores temporary data for the required time
     *
     * @return bool success
     * @author Stuart Laverick
     **/
    protected function storeTemporaryData($key, $value, $expires = null)
    {
        $expires? : $this->transientLifetime;
        return set_transient(md5($this->transientPrefix . $this->apiName . $key), $value, $expires);
    }

    /**
     * Gets the data relevant to the Restful query either from a local cache
     * or from the API if no local cache exists
     *
     * @return mixed
     * @author Stuart Laverick
     **/
    public function getData($requestUrl, $purgeCache = false)
    {
        $this->log($requestUrl);
        $result = $this->getTemporaryData($requestUrl);
        if (!$result || $purgeCache) {
            if ($result = $this->queryApi($requestUrl)) {
                $this->storeTemporaryData($requestUrl, $result);
            }
        }
        return $result;
    }

    /**
     * Retrieves stored temporary data
     *
     * @return string OAuth token
     * @author Stuart Laverick
     **/
    protected function getTemporaryData($key)
    {
        return get_transient(md5($this->transientPrefix . $this->apiName . $key));
    }

    /**
     * Delete stored temporary data
     *
     * @return Bool success
     * @author Stuart Laverick
     **/
    protected function deleteTemporaryData($key)
    {
        return delete_transient(md5($this->transientPrefix . $this->apiName . $key));
    }

    /**
     * Set the last message property
     *
     * @return Bool
     * @author Stuart Laverick
     **/
    protected function setLastMessage($message, $code = 0)
    {
        $this->lastMessage = ['message' => $message, 'code' => $code];
        return $this->storeTemporaryData('last_message', $this->lastMessage, DAY_IN_SECONDS);
    }

    /**
     * Get the last message property
     *
     * @return Array|Bool
     * @author Stuart Laverick
     **/
    public function getLastMessage()
    {
        return $this->lastMessage? : $this->getTemporaryData('last_message');
    }

    /**
     * Removes the last message property
     *
     * @return Bool success
     * @author Stuart Laverick
     **/
    protected function deleteLastMessage()
    {
        $this->lastMessage = null;
        return $this->deleteTemporaryData('last_message');
    }

    /**
     * Set up the API service library with account parameters
     *
     * @return bool
     * @author Stuart Laverick
     **/
    protected function initialiseService(Array $scopes = [], $baseApiUrl = '')
    {
        $credentials = new Credentials(
            $this->appId,
            $this->appSecret,
            $this->pageUrl . '&callback=' . $this->apiName
        );

        $storage = new TransientTokenStore();

        $serviceFactory = new ServiceFactory();

        $serviceFactory->setHttpTransporter('Curl', [
                'ignoreErrors' => true,
                'maxRedirects' => 5,
                'timeout' => 5,
                'verifyPeer' => false
            ]);
        $baseApiUrl = $baseApiUrl? new Url($baseApiUrl) : null;
        $apiVersion = null;

        try {
            if ($this->service = $serviceFactory->createService(
                $this->apiName,
                $credentials,
                $storage,
                $scopes,
                $baseApiUrl,
                $apiVersion
            )) {
                return true;
            }
        } catch (\Exception $e) {
            $this->setLastMessage($e->getMessage(), $e->getCode());
        }

        return false;
    }

    /**
     * Checks for a locally stored valid access token for the service
     *
     * @return bool
     * @author Stuart Laverick
     **/
    public function hasValidAccessToken()
    {
        if ($this->service) {
            try {
                    $token = $this->service->getAccessToken();
                    return ! $token->isExpired();
            } catch (\OAuth\Common\Storage\Exception\TokenNotFoundException $e) {
                $this->setLastMessage($e->getMessage());
                return false;
            }
        }
        return false;
    }

    /**
     * Tries to set the session property with an authenticated session
     * using a stored OAuth token. Returns true on success false on failure.
     *
     * @return bool authenticated session is available
     * @author Stuart Laverick
     **/
    public function hasSession()
    {
        $hasSession = $this->hasValidAccessToken();
        if (!$hasSession) {
            $hasSession = $this->createSession();
        }
        if (isset($_GET['disconnect']) && $_GET['disconnect'] == $this->apiName) {
            $this->deleteSession();
            $hasSession = false;
        }
        return $hasSession;
    }

    /**
     * Attempts to get a new OAuth token from the service
     *
     * @return bool success
     * @author Stuart Laverick
     **/
    public function createSession()
    {
        if ($this->service->isGlobalRequestArgumentsPassed()
            && isset($_GET['callback'])
            && $_GET['callback'] == $this->apiName) {
            $token = $this->getAccessTokenFromAuthorizationCode();
        } elseif ($this->service->getStorage()->hasAccessToken($this->apiName)) {
            $token = $this->getAccessTokenWithRefreshToken($this->getToken());
        }

        if (! $this->hasValidAccessToken()) {
            return false;
        }

        $this->getUser('', true);
        $this->deleteLastMessage();
        return true;
    }

    /**
     * See SocialApiInterface
     * {@inheritdoc}
     **/
    public function getToken()
    {
        return $this->service->getAccessToken();
    }

    /**
     * Retrieves the Access Token from a passed Authorisation Code
     *
     * @return bool
     * @author Stuart Laverick
     **/
    protected function getAccessTokenFromAuthorizationCode()
    {
        try {
            $token = $this->service->retrieveAccessTokenByGlobReqArgs();
        } catch (\Exception $e) {
            $this->setLastMessage($e->getMessage(), $e->getCode());
            return false;
        }
        return $token;
    }

    /**
     * Refreshes the Access Token from a stored Refresh Token
     *
     * @return void
     * @author 
     **/
    protected function getAccessTokenWithRefreshToken(TokenInterface $token)
    {
        $class = get_class($this->service);
        if ($class::OAUTH_VERSION == 1) {
            return false;
        }
        try {
            $token = $this->service->refreshAccessToken($token);
        } catch (\OAuth\OAuth2\Service\Exception\MissingRefreshTokenException $e) {
            return false;
        } catch (\Exception $e) {
            $this->setLastMessage($e->getMessage(), $e->getCode());
            return false;
        }
        return $token;
    }

    /**
     * Removes the current OAuth token so ending the session
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function deleteSession()
    {
        $this->service->getStorage()->clearToken($this->apiName);
    }

    /**
     * Quieries the Restful API endpoint and returns the result
     *
     * @return mixed
     * @author Stuart Laverick
     **/
    protected function queryApi($requestUrl)
    {
        try {
            $url = $this->service->getBaseApiUri() . $requestUrl;
        } catch (\Exception $e) {
            $url = $requestUrl;
        }
        try {
            if ($result = $this->service->request($url)) {
                $this->log($result);
                if ($result = json_decode($result, true)) {
                    $this->deleteLastMessage();
                    return $result;
                }
                $this->log(json_last_error_msg());
                $this->setLastMessage(json_last_error_msg());
            }
        } catch (\OAuth\Common\Token\Exception\ExpiredTokenException $e) {
            // Token expired so try to refresh and have another go
            $this->log($e->getMessage());
            $this->setLastMessage($e->getMessage(), $e->getCode());
            if ($this->getAccessTokenWithRefreshToken($this->service->getAccessToken())) {
                return $this->queryApi($requestUrl);
            }
        } catch (\Exception $e) {
          // Some other error occurred
            $this->log($e->getMessage());
            $this->setLastMessage($e->getMessage(), $e->getCode());
        }
        return false;
    }

    /**
     * Writes to the error log
     *
     * @param mixed $message
     * @param bool $backtrace
     * @return void
     * @author Stuart Laverick
     */
    public static function log($message = '', $backtrace = false)
    {
        if (WP_DEBUG === true) {
            $trace = debug_backtrace();
            $caller = $trace[1];
            error_log(isset($caller['class']) ? $caller['class'] . '::' . $caller['function'] : $caller['function']);
            if ($message) {
                error_log(is_array($message) || is_object($message) ? print_r($message, true) : $message);
            }
            if ($backtrace) {
                error_log(print_r($backtrace, true));
            }
        }
    }
}
