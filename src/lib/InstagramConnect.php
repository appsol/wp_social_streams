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
     * See SocialApiInterface
     * {@inheritdoc}
     **/
    public function getNiceName()
    {
        return 'Instagram';
    }

    /**
     * See SocialApiInterface
     * {@inheritdoc}
     **/
    public function getFollowerName($plural = false)
    {
        return $plural? 'followers' : 'follower';
    }

    /**
     * Get a user object
     * Returns the authenticated user if no user ID supplied
     *
     * @param string userId
     * @return User
     * @author Stuart Laverick
     **/
    public function getUser($userId = '')
    {
        $userId = $userId? : 'self';
        try {
            if ($user = $this->service->requestJSON($this->service->getBaseApiUri() . 'users/' . $userId)) {
                $this->deleteLastMessage();
                return $user['data'];
            }
        } catch (ExpiredTokenException $e) {
            $this->setLastMessage($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
          // Some other error occurred
            $this->setLastMessage($e->getMessage(), $e->getCode());
        }

        return false;
    }

    /**
     * See SocialApiInterface
     * {@inheritdoc}
     **/
    public function getFollowerCount($userId = '', $purgeCache = false)
    {
        $count = $this->getTemporaryData('follower_count_' . $userId);
        if (!$count || $purgeCache) {
            if ($user = $this->getUser($userId)) {
                $this->log($user);
                $count = $user['counts']['followed_by'];
                $this->storeTemporaryData('follower_count_' . $userId, $count);
            }
        }
        return $count;
    }
}
