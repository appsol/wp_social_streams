<?php
/**
 * SocialStreams\Lib\FacebookConnect
 *
 * @package wp_social_streams
 * @author Stuart Laverick
 */

namespace SocialStreams;

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\GraphSessionInfo;

defined('ABSPATH') or die( 'No script kiddies please!' );

class FacebookConnect extends SocialApiConnect implements SocialApiInterface
{

    /**
     * Facebook App ID
     *
     * @var string
     **/
    private $appId;

    /**
     * Facebook App Secret
     *
     * @var string
     **/
    private $appSecret;

    /**
     * Current Page Url
     *
     * @var string
     **/
    private $pageUrl;

    /**
     * Facebook authenticated session object
     *
     * @var Facebook\FacebookSession
     **/
    private $session;

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
        $this->pageUrl  = 'options-general.php?page=' . $_GET["page"] . '&redirect=facebook';
        FacebookSession::setDefaultApplication($this->appId, $this->appSecret);
    }

    /**
     * Return the authentication URL
     *
     * @return string Url
     * @author Stuart Laverick
     **/
    public function getAuthenticationUrl()
    {
        $helper  = new FacebookRedirectLoginHelper(admin_url($this->pageUrl));

        return $helper->getLoginUrl();
    }

    /**
     * Create a valid session from the parameters passed back from Facebook
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function authenticateFromRedirect()
    {
        $helper = new FacebookRedirectLoginHelper(admin_url($this->pageUrl));
        try {
          $session = $helper->getSessionFromRedirect();
        } catch (FacebookRequestException $ex) {
        // When Facebook returns an error
            echo $ex->getMessage();
            //$errorCode = $ex->getCode();
        } catch (\Exception $ex) {
        // When validation fails or other local issues
            echo $ex->getMessage();
        }
        if ($session) {
        // Logged in
            print_r($session->getSessionInfo());
            $this->storeToken('facebook', $session->getToken());
        }
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
        if ($token = $this->getToken()) {
            $this->session = new FacebookSession($token);
            return true;
        } elseif (isset($_GET['redirect']) && $_GET['redirect'] == 'facebook') {
            $this->authenticateFromRedirect();
            return true;
        }
        return false;
    }
}
