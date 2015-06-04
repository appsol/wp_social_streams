<?php
/**
 * SocialStreams\Lib\SocialApiConnect
 *
 * @package wp_social_streams
 * @author Stuart Laverick
 */
namespace SocialStreams;

defined('ABSPATH') or die( 'No script kiddies please!' );

abstract class SocialApiConnect
{

    /**
     * Transient Prefix
     *
     * @var string
     **/
    protected $transientPrefix = 'social_streams';

    /**
     * Default lifetime for session transients (4 weeks)
     *
     * @var integer
     **/
    protected $transientLifetime = 2419200;

    /**
     * Stores the OAuth token for the required time
     *
     * @return bool success
     * @author Stuart Laverick
     **/
    protected function storeToken($name, $token, $expires = null)
    {
        $expires? : $this->transientLifetime;
        return set_transient(substr($this->transientPrefix . md5($name), 0, 40), $token, $expires);
    }

    /**
     * Stores the OAuth token for the required time
     *
     * @return string OAuth token
     * @author Stuart Laverick
     **/
    protected function getToken($name)
    {
        return get_transient(substr($this->transientPrefix . md5($name), 0, 40));
    }
}
