<?php
/**
 * SocialStreams\InstagramConnect
 *
 * @package wp_social_streams
 * @author Stuart Laverick
 */

namespace SocialStreams;

use OAuth\OAuth2\Service\Linkedin;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use OAuth\ServiceFactory;

// use MetzWeb\Instagram\Instagram;

defined('ABSPATH') or die( 'No script kiddies please!' );

class InstagramConnect extends SocialApiConnect implements SocialApiInterface
{

    /**
     * Constructor
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function __construct($appId, $appSecret)
    {
        $this->apiName = 'instagram';
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        parent::__construct();
        // $this->pageUrl  = 'options-general.php?page=' . $_GET["page"] . '&redirect=' . $this->apiName;
        // $this->session = new Instagram([
        //     'apiKey'      => $this->appId,
        //     'apiSecret'   => $this->appSecret,
        //     'apiCallback' => admin_url($this->pageUrl)
        // ]);
        $credentials = new Credentials(
            $this->appId,
            $this->appSecret,
            $this->callbackUrl
        );

        $storage = new TransientTokenStore();

        $serviceFactory = new ServiceFactory();

        $this->session = $serviceFactory->createService(
            'instagram',
            $credentials,
            $storage,
            ['basic', 'likes']
        );
    }

    /**
     * Return the authentication URL
     *
     * @return string Url
     * @author Stuart Laverick
     **/
    // public function getAuthenticationUrl()
    // {
    //     return $this->session->getLoginUrl(['basic', 'likes']);
    // }

    /**
     * Returns a URL that will allow the OAuth token to be deleted
     *
     * @return string URL
     * @author Stuart Laverick
     **/
    // public function getDisconnectUrl()
    // {
    //     return admin_url('options-general.php?page=' . $_GET["page"] . '&disconnect=' . $this->apiName);
    // }

    /**
     * Create a valid session from the parameters passed back
     *
     * @return void
     * @author Stuart Laverick
     **/
    // private function authenticateFromRedirect()
    // {
    //     try {
    //         if (!isset($_GET['code'])) {
    //             if (isset($_GET['error'])) {
    //                 $msg = 'Error Type: ' . $_GET['error'] .
    //                 ' Error Reason: ' . $_GET['error_reason'] .
    //                 ' Description: ' . urldecode($_GET['error_description']);
    //             }
    //             throw new \Exception($msg? : 'Incomplete OAuth response');
    //         }
    //         $code = $_GET['code'];
    //         $token = $this->session->getOAuthToken($code);
    //     } catch (\Exception $e) {
    //     // When validation fails or other local issues
    //         $this->setLastMessage($e->getMessage());
    //     }
    //     if ($token) {
    //     // Logged in
    //         $this->session->setAccessToken($token->access_token);
    //         $this->storeTemporaryData('token', $token->access_token);
    //         return true;
    //     }
    //     return false;
    // }

    /**
     * Tries to set the session property with an authenticated session
     * using a stored OAuth token. Returns true on success false on failure.
     *
     * @return bool authenticated session is available
     * @author Stuart Laverick
     **/
    public function hasSession()
    {
        // $hasSession = false;
        // if (isset($_GET['redirect']) && $_GET['redirect'] == $this->apiName) {
        //     $hasSession = $this->authenticateFromRedirect();
        // }
        // if (isset($_GET['disconnect']) && $_GET['disconnect'] == $this->apiName) {
        //     $this->deleteTemporaryData('token');
        // }
        // if ($token = $this->getTemporaryData('token')) {
        //     $this->session->setAccessToken($token);
        //     $hasSession = true;
        // }
        // return $hasSession;

        $hasSession = false;
        try {
            if ($this->session->isGlobalRequestArgumentsPassed()) {
                $this->session->retrieveAccessTokenByGlobReqArgs();
                $hasSession = true;
            }
            if (isset($_GET['redirect']) && $_GET['redirect'] == $this->apiName) {
                $this->session->redirectToAuthorizationUri();
            }
            if (isset($_GET['disconnect']) && $_GET['disconnect'] == $this->apiName) {
                $this->deleteTemporaryData('token');
            }
        } catch (\Exception $e) {
            $this->setLastMessage($e->getMessage(), $e->getCode());
        }

        return $hasSession;
    }

    /**
     * Get a user object
     * Returns the authenticated user if no user ID supplied
     *
     * @param string userId
     * @return User
     * @author Stuart Laverick
     **/
    public function getUser($userId = 'self')
    {
        // try {
        //     $user = $this->session->getUser($userId);
        //     if (isset($user->meta->error_type)) {
        //         throw new \Exception($user->meta->error_type . ': ' . $user->meta->error_message, $user->meta->code);
        //     }
        // } catch (\Exception $e) {
        //   // Some other error occurred
        //     $this->setLastMessage($e->getMessage(), $e->getCode());
        // }
        // if ($user->data) {
        //     $this->deleteLastMessage();
        //     return $user->data->full_name;
        // }
        // return false;
        try {
            $user = $this->session->requestJSON('/users/' . $userId);
        } catch (ExpiredTokenException $e) {
            $this->setLastMessage($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
          // Some other error occurred
            $this->setLastMessage($e->getMessage(), $e->getCode());
        }
        if ($user) {
            $this->deleteLastMessage();
            return $user['data']['full_name'];
        }
        return false;
    }
}
