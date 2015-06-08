<?php
/**
 * SocialStreams\FacebookConnect
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
     * Constructor
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function __construct($appId, $appSecret)
    {
        $this->apiName = 'facebook';
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->pageUrl  = 'options-general.php?page=' . $_GET["page"] . '&redirect=' . $this->apiName;
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
     * Returns a URL that will allow the OAuth token to be deleted
     *
     * @return string URL
     * @author Stuart Laverick
     **/
    public function getDisconnectUrl()
    {
        return admin_url('options-general.php?page=' . $_GET["page"] . '&disconnect=' . $this->apiName);
    }

    /**
     * Create a valid session from the parameters passed back from Facebook
     *
     * @return void
     * @author Stuart Laverick
     **/
    private function authenticateFromRedirect()
    {
        $helper = new FacebookRedirectLoginHelper(admin_url($this->pageUrl));
        try {
          $this->session = $helper->getSessionFromRedirect();
        } catch (FacebookRequestException $e) {
        // When Facebook returns an error
            $this->setLastMessage($e->getMessage(), $ex->getCode());
        } catch (\Exception $e) {
        // When validation fails or other local issues
            $this->setLastMessage($e->getMessage());
        }
        if ($this->session) {
        // Logged in
            // print_r($session->getSessionInfo());
            $this->storeTemporaryData('token', $this->session->getToken());
            return true;
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
        if ($token = $this->getTemporaryData('token')) {
            $this->session = new FacebookSession($token);
            return true;
        } elseif (isset($_GET['redirect']) && $_GET['redirect'] == $this->apiName) {
            return $this->authenticateFromRedirect();
        }
        return false;
    }

    /**
     * Get a user object
     * Returns the authenticated user if no user ID supplied
     *
     * @param string userId
     * @return User
     * @author Stuart Laverick
     **/
    public function getUser($userId = 'me')
    {
        try {
          $user = (new FacebookRequest(
            $this->session, 'GET', '/' . $userId
          ))->execute()->getGraphObject(GraphUser::className());
        } catch (FacebookRequestException $e) {
          // The Graph API returned an error
            $this->setLastMessage($e->getMessage());
        } catch (\Exception $e) {
          // Some other error occurred
            $this->setLastMessage($e->getMessage());
        }
        if ($user) {
            $this->deleteLastMessage();
            return $user->getName();
        }
        return false;
    }
}