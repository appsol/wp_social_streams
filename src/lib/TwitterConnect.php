<?php
/**
 * SocialStreams\TwitterConnect
 *
 * @package wp_social_streams
 * @author Stuart Laverick
 */

namespace SocialStreams;

use Abraham\TwitterOAuth\TwitterOAuth;

defined('ABSPATH') or die( 'No script kiddies please!' );

class TwitterConnect extends SocialApiConnect implements SocialApiInterface
{

    /**
     * Constructor
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function __construct($appId, $appSecret, $accessToken, $accessTokenSecret)
    {
        $this->apiName = 'twitter';
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->accessToken = $accessToken;
        $this->accessTokenSecret = $accessTokenSecret;
        $this->pageUrl  = 'options-general.php?page=' . $_GET["page"] . '&redirect=' . $this->apiName;
    }

    /**
     * Return the authentication URL
     *
     * @return string Url
     * @author Stuart Laverick
     **/
    public function getAuthenticationUrl()
    {
        return $this->session->url('oauth/autherize', ['oauth_token' => $this->accessToken]);
    }

    /**
     * Returns a URL that will allow the OAuth token to be deleted
     *
     * @return string URL
     * @author Stuart Laverick
     **/
    public function getDisconnectUrl()
    {
        return admin_url('options-general.php?page=' . $_GET["page"] . '&disconnect=' . $this->apiName);
    }

    /**
     * Create a valid session from the parameters passed back from Facebook
     *
     * @return void
     * @author Stuart Laverick
     **/
    private function authenticateFromRedirect()
    {
        
    }

    /**
     * Tries to set the session property with an authenticated session
     * using a stored OAuth token. Returns true on success false on failure.
     *
     * @return bool authenticated session is available
     * @author Stuart Laverick
     **/
    public function hasSession()
    {
        $this->session = new TwitterOAuth(
            $this->appId,
            $this->appSecret,
            $this->accessToken,
            $this->accessTokenSecret
        );

        return true;
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
            $updates = $this->session->get(
                "statuses/user_timeline",
                [
                    "count" => 1,
                    "exclude_replies" => true
                ]
            );
            if ($this->session->getLastHttpCode() != 200) {
                throw new Exception($this->session->getLastBody(), $this->session->getLastHttpCode());
            }
        } catch (TwitterOAuthException $e) {
            $this->setLastMessage($e->getMessage());
        } catch (\Exception $e) {
          // Some other error occurred
            $this->setLastMessage($e->getMessage());
        }
        if ($updates[0]->user) {
            return $updates[0]->user->name;
        }
        return false;
    }
}
