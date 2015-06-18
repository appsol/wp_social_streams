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
    public function getUser($userId = '')
    {
        $userId = $userId? : '~';
        try {
            if ($user = $this->service->requestJSON($this->service->getBaseApiUri() . 'people/' . $userId . '?format=json')) {
                $this->deleteLastMessage();
                return $user;
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
        $userId = $userId? : '~';
        $requestUrl =  'people/' . $userId . ':(id,num-connections,num-connections-capped)?format=json';
        $count = $this->getTemporaryData($requestUrl);
        if (!$count || $purgeCache) {
            try {
                $this->log($requestUrl);
                if ($result = $this->service->requestJSON($this->service->getBaseApiUri() . $requestUrl)) {
                    $this->log($result);
                    $this->deleteLastMessage();
                    $count = $result['num-connections'];
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
