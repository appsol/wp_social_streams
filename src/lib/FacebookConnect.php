<?php
/**
 * SocialStreams\FacebookConnect
 *
 * @package wp_social_streams
 * @author Stuart Laverick
 */

namespace AppSol\SocialStreams;

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
     * See SocialApiInterface
     * {@inheritdoc}
     **/
    public function getNiceName()
    {
        return 'Facebook';
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
        $requestUrl = $userId? : 'me';
        $user = $this->getData($requestUrl, $purgeCache);

        return $user;
    }

    /**
     * See SocialApiInterface
     * {@inheritdoc}
     **/
    public function getFollowerCount($userId = '', $purgeCache = false)
    {
        $user = $this->getUser($userId);
        $count = false;
        // Is this a page or a user?
        if (isset($user['likes'])) {
            $count = $user['likes'];
        } else {
            $userId = $userId? : 'me';
            $requestUrl = $userId . '/friends';
            if ($result = $this->getData($requestUrl, $purgeCache)) {
                $count = $result['summary']['total_count'];
            }
        }

        return $count;
    }

    /**
     * Get the public link to the entity on facebook
     *
     * @return string
     * @author Stuart Laverick
     **/
    public function getProfileUrl($userId = '', $purgeCache = false)
    {
        $user = $this->getUser($userId);
        return $user['link'];
    }
}
