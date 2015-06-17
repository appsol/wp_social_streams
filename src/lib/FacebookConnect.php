<?php
/**
 * SocialStreams\FacebookConnect
 *
 * @package wp_social_streams
 * @author Stuart Laverick
 */

namespace SocialStreams;

use OAuth\OAuth2\Service\Facebook;

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

        parent::__construct($appId, $appSecret);

        $this->initialiseService([Facebook::SCOPE_EMAIL]);
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
            if ($user = $this->service->requestJSON($this->service->getBaseApiUri() . $userId)) {
                $this->deleteLastMessage();
                return $user['name'];
            }
        } catch (ExpiredTokenException $e) {
            $this->setLastMessage($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
          // Some other error occurred
            $this->setLastMessage($e->getMessage(), $e->getCode());
        }
        return false;
    }
}
