<?php
/**
 * SocialStreams\TwitterConnect
 *
 * @package wp_social_streams
 * @author Stuart Laverick
 */

namespace SocialStreams;

use OAuth\OAuth1\Service\Twitter;

defined('ABSPATH') or die( 'No script kiddies please!' );

class TwitterConnect extends SocialApiConnect implements SocialApiInterface
{

    /**
     * Constructor
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function __construct($appId, $appSecret)
    {
        $this->apiName = 'twitter';

        parent::__construct($appId, $appSecret);

        $this->initialiseService();
    }

    /**
     * See SocialApiInterface
     * {@inheritdoc}
     **/
    public function getNiceName()
    {
        return 'Twitter';
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
     * Additional check for OAuth v1 token
     * OAuth v1 token set during requestToken transaction so cannot
     * rely on it's exisitance to indicate authentication state.
     * On authentication a new access token will be set that is 
     * different to the request token
     *
     * @return bool
     * @author Stuart Laverick
     **/
    public function hasValidAccessToken()
    {
        if (parent::hasValidAccessToken()) {
            $token = $this->service->getAccessToken();
            $params = $token->getExtraParams();
            return isset($params['user_id']);
        }

        return false;
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
        $userId = $userId? : 'me';
        $requestUrl = $userId == 'me'? 'account/verify_credentials.json' : 'users/show.json?screen_name=' . $userId;
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
        if ($user = $this->getUser($userId, $purgeCache)) {
            $count = $user['followers_count'];
        }
        return $count;
    }

    /**
     * Get the public link to the entity on Twitter
     *
     * @return string
     * @author Stuart Laverick
     **/
    public function getProfileUrl($userId = '', $purgeCache = false)
    {
        if ($user = $this->getUser($userId, $purgeCache)) {
            return 'https://www.twitter.com/' . $user['screen_name'];
        }

    }
}
