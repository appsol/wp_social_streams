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
     * @param string userId
     * @return User
     * @author Stuart Laverick
     **/
    public function getUser();
}
