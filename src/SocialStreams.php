<?php
/**
 * Plugin Name: WP Social Streams
 * Plugin URI: http://www.appropriatesolutions.co.uk/
 * Description: Displays metrics and posts from social media
 * Version: 2.1.1
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
namespace AppSol\SocialStreams;

defined('ABSPATH') or die( 'No script kiddies please!' );

require_once dirname(__FILE__) . '/vendor/autoload.php';

use AppSol\SocialStreams\ConnectionFactory;
use AppSol\SocialStreams\SocialStreamsOptions;
use AppSol\SocialStreams\SocialStreamsCountsWidget;

// require_once 'SocialStreamsCountsWidget.php';
// require_once 'SocialStreamsOptions.php';

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
        add_action("init", [$this, 'registerShortcodes']);
        add_action("widgets_init", [$this, 'registerWidget']);

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
                if ($connection->getUser()) {
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
    public function registerWidget()
    {
        register_widget('\AppSol\SocialStreams\SocialStreamsCountsWidget');
    }

    /**
     * Regiter shortcodes that are made available
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function registerShortcodes()
    {
        add_shortcode('social_count', [$this, 'countShortcodeHandler']);
        // add_shortcode('social_total', [$this, 'totalShortcodeHandler']);
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
            // wp_enqueue_script('jcarousel', 'https://cdnjs.cloudflare.com/ajax/libs/jcarousel/0.3.3/jquery.jcarousel.min.js', ['jquery'], '0.3.3', true);
            // wp_enqueue_script('wp-video-playlists', plugin_dir_url(__FILE__) . 'assets/js/main.js', ['jcarousel'], '0.3.0', true);
        }
    }

    /**
     * Handler for Social Count shortcode calls
     *
     * Options:
     * network - the id of a network (facebook, twitter, etc)
     * entity - a username on the network or blank for the authenticated user
     *
     * @return string HTML of the player
     * @author Stuart Laverick
     **/
    public function countShortcodeHandler($attributes)
    {
        extract(shortcode_atts([
            'network' => '',
            'entity' => null
        ], $attributes, 'social_count'));

        if ($network && isset($this->activeNetworks[$network])) {
            $count = $this->activeNetworks[$network]->getFollowerCount($entity);
              $url = $this->activeNetworks[$network]->getProfileUrl($entity);
          return '<a class="' . $network . '-count" href="' . $url . '"><span class="network-name">' . $this->activeNetworks[$network]->getNiceName() . '</span> '
          . '<span class="follower-count">' . $count . '</span> '
          . '<span class="follower-name">' . $this->activeNetworks[$network]->getFollowerName(true) . '</span></a>';
        }

        return false;
    }

    /**
     * undocumented function
     *
     * @return void
     * @author 
     **/
    public function totalShortcodeHandler($attributes)
    {
        $defaults = [];
        foreach ($this->activeNetworks as $network) {
            $defaults[$network->getNetworkName()] = '';
        }
        $atts = shortcode_atts($defaults, $attributes, 'social_total');
        $totalCount = 0;
        foreach ($this->activeNetworks as $network) {
            if ($atts[$network->getNetworkName()]) {
                $totalCount+= (int) $network->getFollowerCount($atts[$network->getNetworkName()]);
            }
        }
        return __('Total Followers:') . '<span class="follower-count">' . $totalCount . '</span> ';
    }
}

$socialStreams = SocialStreams::getInstance();
