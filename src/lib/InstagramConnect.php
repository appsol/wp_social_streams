<?php
/**
 * SocialStreams\InstagramConnect
 *
 * @package wp_social_streams
 * @author Stuart Laverick
 */

namespace AppSol\SocialStreams;

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
    public function getUser($userId = '', $purgeCache = false)
    {
        // If not the authenticated user then search for them by username
        $requestUrl = $userId? 'users/search?q=' . $userId . '&count=5' : 'users/self';
        $user = false;
        if ($result = $this->getData($requestUrl, $purgeCache)) {
            $user = $result['data'];
            // If not the authenticated user then a second request is required to get the default profile
            if ($userId) {
                foreach ($result['data'] as $u) {
                    if ($u['username'] == $userId) {
                        if ($result = $this->getData('users/' . $u['id'], $purgeCache)) {
                            $user = $result['data'];
                        }
                        break;
                    }
                }
            }
        }
        return $user;
    }

    /**
     * See SocialApiInterface
     * {@inheritdoc}
     **/
    public function getFollowerCount($userId = '', $purgeCache = false)
    {
        $count = false;
        if ($user = $this->getUser($userId, $purgeCache)) {
            $count = $user['counts']['followed_by'];
        }
        return $count;
    }

    /**
     * Get the public link to the entity on Instagram
     *
     * @return string
     * @author Stuart Laverick
     **/
    public function getProfileUrl($userId = '', $purgeCache = false)
    {
        if ($user = $this->getUser($userId, $purgeCache)) {
            return 'https://www.instagram.com/' . $user['username'];
        }

    }
}
