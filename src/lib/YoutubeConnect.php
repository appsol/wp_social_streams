<?php
/**
 * SocialStreams\YoutubeConnect
 *
 * @package wp_social_streams
 * @author Stuart Laverick
 */

namespace SocialStreams;

use Google_Client;
use Google_Service_YouTube;

defined('ABSPATH') or die( 'No script kiddies please!' );

class YoutubeConnect extends SocialApiConnect implements SocialApiInterface
{

    /**
     * Constructor
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function __construct($appId)
    {
        $this->apiName = 'youtube';
        $this->appId = $appId;
    }

    /**
     * Return the authentication URL
     *
     * @return string Url
     * @author Stuart Laverick
     **/
    public function getAuthenticationUrl()
    {
        
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
     * Create a valid session from the parameters passed back
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
        try {
            $client = new Google_Client();
            $client->setApplicationName('Social Streams');
            $client->setDeveloperKey($this->appId);
            $this->session = new \Google_Service_YouTube($client);
            return true;
        }
        catch(\Exception $e) {
            $this->setLastMessage($e->getMessage());
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
    public function getUser($userId = 'YT')
    {
        try {
          $user = $this->session->channels->listChannels('snippet', ['forUsername' => $userId]);
        } catch (\Exception $e) {
          // Some other error occurred
            $this->setLastMessage($e->getMessage());
        }
        if ($user = array_pop($user['items'])) {
            $this->deleteLastMessage();

            return $user['snippet']['title'];
        }
        return false;
    }
}
