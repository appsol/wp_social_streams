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
     * See SocialApiInterface
     * {@inheritdoc}
     **/
    public function getNiceName()
    {
        return 'LinkedIn';
    }

    /**
     * See SocialApiInterface
     * {@inheritdoc}
     **/
    public function getFollowerName($plural = false)
    {
        return $plural? 'connections' : 'connection';
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
        $userId = $userId? : '~';
        $requestUrl = 'people/' . $userId . '?format=json';
        $user = $this->getData($requestUrl, $purgeCache);

        return $user;
    }

    /**
     * See SocialApiInterface
     * {@inheritdoc}
     **/
    public function getFollowerCount($userId = '', $purgeCache = false)
    {
        $userId = $userId? : '~';
        $requestUrl =  'people/' . $userId . ':(id,num-connections,num-connections-capped)?format=json';
        $count = false;
        if ($result = $this->getData($requestUrl, $purgeCache)) {
            $count = $result['num-connections'];
        }

        return $count;
    }

    /**
     * Get the public link to the entity on LinkedIn
     *
     * @return string
     * @author Stuart Laverick
     **/
    public function getProfileUrl($userId = '', $purgeCache = false)
    {
        if ($user = $this->getUser($userId, $purgeCache)) {
            return ;
        }

    }
}
