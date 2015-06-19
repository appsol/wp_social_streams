<?php
/**
 * SocialStreams\SocialApiInterface
 *
 * @package wp_social_streams
 * @author Stuart Laverick
 */
namespace SocialStreams;

defined('ABSPATH') or die( 'No script kiddies please!' );

interface SocialApiInterface
{
    /**
     * Returns the url safe name for the network
     *
     * @return string
     * @author Stuart Laverick
     **/
    public function getNetworkName();

    /**
     * Returns the human readable name for the network
     *
     * @return string
     * @author Stuart Laverick
     **/
    public function getNiceName();

    /**
     * Returns the name given to followers in the network
     *
     * @return string
     * @author Stuart Laverick
     **/
    public function getFollowerName($plural);

    /**
     * Gets the data relevant to the Restful query either from a local cache
     * or from the API if no local cache exists
     *
     * @return mixed
     * @author Stuart Laverick
     **/
    public function getData($requestUrl, $purgeCache);

    /**
     * Return the authentication URL
     *
     * @return string Url
     * @author Stuart Laverick
     **/
    public function getAuthenticationUrl();

    /**
     * Returns a URL that will allow the OAuth token to be deleted
     *
     * @return string URL
     * @author Stuart Laverick
     **/
    public function getDisconnectUrl();

    /**
     * Detects if an authenticated OAuth session is available for the service
     *
     * @return bool
     * @author Stuart Laverick
     **/
    public function hasSession();

    /**
     * Attempts to get a new OAuth token from the service
     *
     * @return bool success
     * @author Stuart Laverick
     **/
    public function createSession();

    /**
     * Removes the current OAuth token so ending the session
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function deleteSession();

    /**
     * Get the last message property
     *
     * @return Array|Bool
     * @author Stuart Laverick
     **/
    public function getLastMessage();

    /**
     * Get a user object
     * Returns the authenticated user if no user ID supplied
     *
     * @param string $userId
     * @return User
     * @author Stuart Laverick
     **/
    public function getUser($userId);

    /**
     * Returns the number of connected nodes within the graph that
     * show social influence, e.g.
     * Facebook: friends
     * Facebook Page: likes
     * Twitter: followers
     * YouTube: subscribers
     * LinkedIn: connections
     * Instagram: followers
     *
     * @param string $userId
     * @param Bool $purgeCache
     * @return int
     * @author Stuart Laverick
     **/
    public function getFollowerCount($userId, $purgeCache);

    /**
     * Get the public link to the entity on the social network
     *
     * @return string
     * @author Stuart Laverick
     **/
    public function getProfileUrl($userId, $purgeCache);
}
