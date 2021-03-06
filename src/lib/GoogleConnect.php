<?php
/**
 * SocialStreams\GoogleConnect
 *
 * @package wp_social_streams
 * @author Stuart Laverick
 */

namespace AppSol\SocialStreams;

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
    public function __construct($appId, $appSecret, $additionalScopes = [])
    {
        $this->apiName = 'google';
        $scope = [
            Google::SCOPE_USERINFO_PROFILE,
            Google::SCOPE_YOUTUBE_READ_ONLY
            ];

        parent::__construct($appId, $appSecret);

        $this->initialiseService($scope, 'https://www.googleapis.com/youtube/v3/');
    }

    /**
     * Set the access type for the Google OAuth endpoint
     *
     * @return string
     * @author Stuart Laverick
     **/
    public function getAuthenticationUrl()
    {
        $this->service->setAccessType('offline');
        return parent::getAuthenticationUrl();
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
    public function getUser($userId = '', $purgeCache = false)
    {
        $requestUrl = $userId?
            'channels?part=id,snippet,statistics&forUsername=' . $userId
            : 'https://www.googleapis.com/oauth2/v1/userinfo';
        $user = $this->getData($requestUrl, $purgeCache);
        return $user;
    }

    /**
     * See SocialApiInterface
     * {@inheritdoc}
     **/
    public function getFollowerCount($userId = '', $purgeCache = false)
    {
        $count = false;
        if ($userId) {
            if ($channels = $this->getUser($userId, $purgeCache)) {
                if($channel = array_pop($channels['items'])) {
                    $count = $channel['statistics']['subscriberCount'];
                }

            }

        } else {
            $requestUrl = 'subscriptions?part=id&mySubscribers=true';

            if ($result = $this->getData($requestUrl, $purgeCache)) {
                $count = $result['pageInfo']['totalResults'];
            }
        }

        return $count;
    }

    /**
     * Get the public link to the entity on YouTube
     *
     * @return string
     * @author Stuart Laverick
     **/
    public function getProfileUrl($userId = '', $purgeCache = false)
    {
        if ($userId) {
            return 'https://www.youtube.com/user/' . $userId;
        }

    }
}
