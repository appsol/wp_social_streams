<?php
/**
 * SocialStreams\GoogleConnect
 *
 * @package wp_social_streams
 * @author Stuart Laverick
 */

namespace SocialStreams;

use OAuth\OAuth2\Service\Google;

defined('ABSPATH') or die( 'No script kiddies please!' );

class GoogleConnect extends SocialApiConnect implements SocialApiInterface
{

    /**
     * Constructor
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function __construct($appId, $appSecret)
    {
        $this->apiName = 'google';

        parent::__construct($appId, $appSecret);

        $this->initialiseService([Google::SCOPE_USERINFO_PROFILE, Google::SCOPE_YOUTUBE_READ_ONLY]);
    }

    /**
     * See SocialApiInterface
     * {@inheritdoc}
     **/
    public function getNiceName()
    {
        return 'YouTube';
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
        try {
          if ($user = $this->service->requestJSON('https://www.googleapis.com/oauth2/v1/userinfo')) {
            $this->deleteLastMessage();
            return $user;
          }
        } catch (ExpiredTokenException $e) {
            $this->setLastMessage($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
          // Some other error occurred
            $this->setLastMessage($e->getMessage());
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
                // $count = $user['followers_count'];
                // $this->storeTemporaryData('follower_count' . $userId, $count);
            }
        }
        return $count;
    }
}
