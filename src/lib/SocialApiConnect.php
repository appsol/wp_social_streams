<?php
/**
 * SocialStreams\SocialApiConnect
 *
 * @package wp_social_streams
 * @author Stuart Laverick
 */
namespace SocialStreams;

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
     * Public accessible callback handler URL
     *
     * @var string
     **/
    protected $callbackUrl;

    /**
     * OAuth authenticated session object
     *
     * @var Object
     **/
    protected $session;

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
    public function __construct()
    {
        $this->pageUrl  = admin_url('options-general.php?page=' . $_GET["page"]);
        $this->callbackUrl  = plugins_url('callback.php', dirname(__FILE__));
    }

    /**
     * Return the authentication URL
     *
     * @return string Url
     * @author Stuart Laverick
     **/
    public function getAuthenticationUrl()
    {
        return $this->session->getAuthorizationUri();
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
     * Checks for a locally stored valid access token for the service
     *
     * @return bool
     * @author Stuart Laverick
     **/
    public function hasValidAccessToken()
    {
        if ($this->session && $this->session->getStorage()->hasAccessToken($this->apiName)) {
            $token = $this->session->getAccessToken();
            return $token->getEndOfLife() === TokenInterface::EOL_NEVER_EXPIRES
            || $token->getEndOfLife() === TokenInterface::EOL_UNKNOWN
            || time() < $token->getEndOfLife();
        }
        return false;
    }
}
