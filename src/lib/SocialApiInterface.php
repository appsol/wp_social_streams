<?php
/**
 * SocialStreams\Lib\SocialApiInterface
 *
 * @package wp_social_streams
 * @author Stuart Laverick
 */
namespace SocialStreams;

defined('ABSPATH') or die( 'No script kiddies please!' );

interface SocialApiInterface
{
    public function getAuthenticationUrl();

    public function authenticateFromRedirect();

    public function hasSession();
}
