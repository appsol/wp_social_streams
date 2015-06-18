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
        $user = $this->getTemporaryData($requestUrl);
        if (!$user || $purgeCache) {
            try {
                if ($user = $this->service->requestJSON($this->service->getBaseApiUri() . $requestUrl)) {
                    $this->deleteLastMessage();
                    $this->storeTemporaryData($requestUrl, $url);
                }
            } catch (ExpiredTokenException $e) {
                $this->setLastMessage($e->getMessage(), $e->getCode());
            } catch (\Exception $e) {
              // Some other error occurred
                $this->setLastMessage($e->getMessage(), $e->getCode());
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
        $userId = $userId? : 'me';
        $requestUrl = $userId . '/friends';
        $count = $this->getTemporaryData($requestUrl);
        if (!$count || $purgeCache) {
            try {
                if ($result = $this->service->requestJSON($this->service->getBaseApiUri() . $requestUrl)) {
                    $this->deleteLastMessage();
                    $count = $result['summary']['total_count'];
                    $this->storeTemporaryData($requestUrl, $count);
                }
            } catch (ExpiredTokenException $e) {
                $this->setLastMessage($e->getMessage(), $e->getCode());
            } catch (\Exception $e) {
              // Some other error occurred
                $this->setLastMessage($e->getMessage(), $e->getCode());
            }
        }
        return $count;
    }
}
