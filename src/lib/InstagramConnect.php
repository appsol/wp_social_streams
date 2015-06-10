<?php
/**
 * SocialStreams\InstagramConnect
 *
 * @package wp_social_streams
 * @author Stuart Laverick
 */

namespace SocialStreams;

use OAuth\OAuth2\Service\Instagram;

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

        parent::__construct($appId, $appSecret);

        $this->initialiseService([Instagram::SCOPE_BASIC, Instagram::SCOPE_LIKES]);
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
        try {
            if ($user = $this->service->requestJSON($this->service->getBaseApiUri() . 'users/' . $userId)) {
                $this->deleteLastMessage();
                return $user['data']['full_name'];
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
