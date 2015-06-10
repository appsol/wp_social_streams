<?php
/**
 * SocialStreams\SocialApiConnect
 *
 * @package wp_social_streams
 * @author Stuart Laverick
 */
namespace SocialStreams;

use OAuth\Common\Token\TokenInterface;
use OAuth\Common\Consumer\Credentials;
use OAuth\ServiceFactory;

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
    protected $transientLifetime = 2419200;

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
     * Return the authentication URL
     *
     * @return string Url
     * @author Stuart Laverick
     **/
    public function getAuthenticationUrl()
    {
        return $this->service->getAuthorizationUri();
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
    protected function initialiseService(Array $scopes)
    {
        $credentials = new Credentials(
            $this->appId,
            $this->appSecret,
            $this->pageUrl . '&callback=' . $this->apiName
        );

        $storage = new TransientTokenStore();

        $serviceFactory = new ServiceFactory();

        if ($this->service = $serviceFactory->createService(
            $this->apiName,
            $credentials,
            $storage,
            $scopes,
            null,
            true
        )) {
            return true;
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
        if ($this->service && $this->service->getStorage()->hasAccessToken($this->apiName)) {
            $token = $this->service->getAccessToken();
            return $token->getEndOfLife() === TokenInterface::EOL_NEVER_EXPIRES
            || $token->getEndOfLife() === TokenInterface::EOL_UNKNOWN
            || time() < $token->getEndOfLife();
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

            try {
                $this->service->retrieveAccessTokenByGlobReqArgs();
                if ($this->hasValidAccessToken()) {
                    $this->deleteLastMessage();
                    return true;
                }
            } catch (\Exception $e) {
                $this->setLastMessage($e->getMessage(), $e->getCode());
            }
        }
        return false;
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
}
