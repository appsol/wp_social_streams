<?php
/**
 * SocialStreams\ConnectionFactory
 *
 * @package wp_social_streams
 * @author Stuart Laverick
 */

namespace SocialStreams;

defined('ABSPATH') or die( 'No script kiddies please!' );

class ConnectionFactory
{
    /**
     * Factory method to create Service Connect objects
     *
     * @return SocialApiInterface
     * @author Stuart Laverick
     **/
    public function createConnection($service)
    {
        $options = get_option('socialstreams');

        $class = "\\" . __NAMESPACE__ . "\\" . ucfirst($service) . 'Connect';
        if (!class_exists($class)) {
            throw new \Exception($class . " Service Class not available");
        }
        $connection = null;
        switch ($service) {
            case 'facebook':
            case 'instagram':
            case 'linkedin':
                if (isset($options[$service . '_app_id'])
                    && isset($options[$service . '_app_secret'])) {
                    $connection = new $class(
                        $options[$service . '_app_id'],
                        $options[$service . '_app_secret']
                    );
                }
                break;
            case 'twitter':
                if (isset($options[$service . '_app_id'])
                    && isset($options[$service . '_app_secret'])
                    && isset($options[$service . '_access_token'])
                    && isset($options[$service . '_access_token_secret'])
                    ) {
                    $connection = new $class(
                        $options[$service . '_app_id'],
                        $options[$service . '_app_secret'],
                        $options[$service . '_access_token'],
                        $options[$service . '_access_token_secret']
                    );
                }
                break;
            case 'youtube':
                if (isset($options[$service . '_simple_key'])) {
                    $connection = new $class($options[$service . '_simple_key']);
                }
                break;
            default:
                throw new \Exception("Attempting to create an unknown Service");
                break;
        }

        return $connection;
    }
}
