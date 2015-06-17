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
    public function getUser($userId = 'me')
    {
        try {
            if ($user = $this->service->requestJSON($this->service->getBaseApiUri() . 'account/verify_credentials.json')) {
                $this->deleteLastMessage();
                return $user['name'];
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
