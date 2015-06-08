<?php
/**
 * SocialStreams\LinkedinConnect
 *
 * @package wp_social_streams
 * @author Stuart Laverick
 */

namespace SocialStreams;

use OAuth\OAuth2\Service\Linkedin;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use OAuth\ServiceFactory;

defined('ABSPATH') or die( 'No script kiddies please!' );

class LinkedinConnect extends SocialApiConnect implements SocialApiInterface
{

    /**
     * Constructor
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function __construct($appId, $appSecret)
    {
        $this->apiName = 'linkedin';
        $this->appId = $appId;
        $this->appSecret = $appSecret;

        $credentials = new Credentials(
            $this->appId,
            $this->appSecret,
            $this->pageUrl
        );

        $storage = new TransientTokenStore();

        $serviceFactory = new ServiceFactory();

        $this->session = $serviceFactory->createService('linkedin', $credentials, $storage, ['r_basicprofile']);
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
    public function getUser($userId = '~')
    {
        try {
            $user = $this->session->requestJSON('/people/' . $userId . '?format=json');;
        } catch (ExpiredTokenException $e) {
            $this->setLastMessage($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
          // Some other error occurred
            $this->setLastMessage($e->getMessage(), $e->getCode());
        }
        if ($user) {
            $this->deleteLastMessage();
            return $user['firstName'] . ' ' . $user['lastName'];
        }
        return false;
    }
}
