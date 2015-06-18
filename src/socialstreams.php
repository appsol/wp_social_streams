<?php
/**
 * Plugin Name: WP Social Streams
 * Plugin URI: http://www.appropriatesolutions.co.uk/
 * Description: Displays metrics and posts from social media
 * Version: 2.1.0
 * Author: Stuart Laverick
 * Author URI: http://www.appropriatesolutions.co.uk/
 * Text Domain: Optional. wp_social_streams
 * License: GPL2
 *
 * @package wp_social_streams
 */
/*  Copyright 2015  Stuart Laverick  (email : stuart@appropriatesolutions.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
namespace SocialStreams;

defined('ABSPATH') or die( 'No script kiddies please!' );

require_once 'vendor/autoload.php';
require_once 'socialstreams_counter_widget.php';
require_once 'socialstreams_options.php';

class SocialStreams
{
    /**
     * Available Social Networks
     **/
    const NETWORKS = 'facebook,twitter,google,instagram,linkedin';

    /**
     * Singleton class instance
     *
     * @var object SocialStreams
     **/
    private static $instance = null;

    /**
     * Holds the lst error message from the API or empty if none
     *
     * @var string
     **/
    public $lastError;

    /**
     * Social Networks which have saved connection parameters
     *
     * @var Array
     **/
    public $activeNetworks = [];

    /**
     * Constructor for SocialStreams
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function __construct()
    {
        add_action("widgets_init", [$this, 'register']);
        add_shortcode('social_streams', [$this, 'shortcodeHandler']);

        if (is_admin()) {
            $optionsPage = new SocialStreamsOptions();
        } else {
            add_action('wp_enqueue_scripts', [$this, 'actionEnqueueAssets']);
        }
        // Create the connection objects for the active networks
        $options = get_option('socialstreams');
        $networks = explode(',', SocialStreams::NETWORKS);
        $connectionFactory = new ConnectionFactory();
        foreach ($networks as $network) {
            if (!empty($options[$network . '_app_id']) && !empty($options[$network . '_app_secret'])) {
                $connection = $connectionFactory->createConnection($network);
                if ($connection->hasSession()) {
                    $this->activeNetworks[$network] = $connection;
                }
            }
        }
    }

    /**
     * Creates or returns an instance of this class
     *
     * @return A single instance of this class
     * @author Stuart Laverick
     **/
    public static function getInstance()
    {
        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Register the Widget
     * The sidebar occurs within the list of posts, allowing 
     * placement of adverts, promotions and similar
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function register()
    {
        register_widget('SocialStreams\SocialStreamsCounterWidget');
    }

    /**
     * Load any scripts and styles needed in the page
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function actionEnqueueAssets()
    {
        $options = get_option('socialstreams');
        if (!empty($options['load_css'])) {
            wp_register_style('wp-social-streams', plugin_dir_url(__FILE__) . 'assets/css/player.css');
            wp_enqueue_style('wp-social-streams');
        }

        if (!empty($options['load_js'])) {
            wp_enqueue_script('jcarousel', 'https://cdnjs.cloudflare.com/ajax/libs/jcarousel/0.3.3/jquery.jcarousel.min.js', ['jquery'], '0.3.3', true);
            wp_enqueue_script('wp-video-playlists', plugin_dir_url(__FILE__) . 'assets/js/main.js', ['jcarousel'], '0.3.0', true);
        }
    }

    /**
     * Handler for shortcode calls
     *
     * Options:
     *
     * @return string HTML of the player
     * @author Stuart Laverick
     **/
    public function shortcodeHandler($attributes)
    {
        extract(shortcode_atts(array(
            
                        ), $attributes));

        

        return false;
    }
}

$socialStreams = SocialStreams::getInstance();
