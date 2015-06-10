<?php
/**
 * SocialStreams\LinkedinConnect
 *
 * @package wp_social_streams
 * @author Stuart Laverick
 */

namespace SocialStreams;

use OAuth\OAuth2\Service\Linkedin;

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

        parent::__construct($appId, $appSecret);

        $this->initialiseService([Linkedin::SCOPE_R_BASICPROFILE]);
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
            if ($user = $this->service->requestJSON($this->service->getBaseApiUri() . 'people/' . $userId . '?format=json')) {
                $this->deleteLastMessage();
                return $user['firstName'] . ' ' . $user['lastName'];
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
