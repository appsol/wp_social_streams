<?php
/**
 * SocialStreamsOptions
 * 
 * @package wp_social_streams
 * @author Stuart Laverick
 */
namespace SocialStreams;

defined('ABSPATH') or die( 'No script kiddies please!' );

class SocialStreamsOptions
{
    /**
     * Holds the values to be used in the fields callbacks
     *
     * @var string
     **/
    private $options;

    /**
     * Constructor
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function __construct()
    {
        add_action('admin_menu', array( $this, 'addPluginPage' ));
        add_action('admin_init', array( $this, 'pageInit' ));
    }

    /**
     * Adds the Settings menu menu item
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function addPluginPage()
    {
        // This page will be under "Settings"
        add_options_page(
            'Social Streams Options',
            'Social Streams',
            'manage_options',
            'socialstreams-admin',
            array( $this, 'createAdminPage' )
        );
    }

    /**
     * Callback for options page
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function createAdminPage()
    {
        // Set class property
        $this->options = get_option('socialstreams');
        ?>
        <div class="wrap">
            <h2>Social Streams Settings</h2>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields('videoplaylists_option_group');
                do_settings_sections('socialstreams-setting-admin');
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function pageInit()
    {
        register_setting(
            'socialstreams_option_group', // Option group
            'socialstreams', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'socialstreams_general', // ID
            'General Options', // Title
            array( $this, 'printGeneralInfo' ), // Callback
            'socialstreams-setting-admin' // Page
        );

        add_settings_field(
            'load_css', // ID
            'Load Plugin CSS', // Title
            array( $this, 'loadCssCallback' ), // Callback
            'socialstreams-setting-admin', // Page
            'socialstreams_general' // Section
        );

        add_settings_field(
            'load_js', // ID
            'Load Plugin Javascript', // Title
            array( $this, 'loadJsCallback' ), // Callback
            'socialstreams-setting-admin', // Page
            'socialstreams_general' // Section
        );

        add_settings_section(
            'socialstreams_facebook', // ID
            'Facebook Options', // Title
            array( $this, 'printSocialMediaApiInfo' ), // Callback
            'socialstreams-setting-admin' // Page
        );

        add_settings_field(
            'socialstreams_facebook_app_id', // ID
            'Facebook App ID', // Title
            [$this, 'simpleKeyCallback'], // Callback
            'socialstreams-setting-admin', // Page
            'socialstreams_facebook', // Section
            []
        );

        add_settings_field(
            'socialstreams_facebook_app_secret', // ID
            'Facebook App Secret', // Title
            [$this, 'simpleKeyCallback'], // Callback
            'socialstreams-setting-admin', // Page
            'socialstreams_facebook', // Section
            []
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     * @return void
     * @author Stuart Laverick
     **/
    public function sanitize($input)
    {
        $new_input = array();

        if( isset( $input['youtube_simple_key'] ) ) {
            $new_input['youtube_simple_key'] = sanitize_text_field( $input['youtube_simple_key'] );
        }

        if( isset( $input['load_css'] ) && $input['load_css'] === 'yes' ) {
            $new_input['load_css'] = 'yes';
        }

        if( isset( $input['load_js'] ) && $input['load_js'] === 'yes' ) {
            $new_input['load_js'] = 'yes';
        }

        return $new_input;
    }

    /**
     * Print the section text for the General section
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function printGeneralInfo()
    {
        print "Set the plugin options";
    }

    /**
     * Print the section text for the Social Media API sections
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function printSocialMediaApiInfo()
    {
        print "Enter your API keys and Authentication details";
    }

    /**
     * Prints the input field for a simple API key
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function simpleKeyCallback($name)
    {
        printf(
            '<input type="text" id="' . $name . '" name="socialstreams[' . $name . ']" value="%s" />',
            isset( $this->options[$name] ) ? esc_attr( $this->options[$name]) : ''
        );
    }

    /**
     * Prints the checkbox field for load_css
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function loadCssCallback()
    {
        printf(
            '<input type="checkbox" id="load_css" name="socialstreams[load_css]" value="yes" %s/>',
            isset( $this->options['load_css'] ) ? 'checked ' : ''
        );
    }

    /**
     * Prints the checkbox field for load_js
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function loadJsCallback()
    {
        printf(
            '<input type="checkbox" id="load_js" name="socialstreams[load_js]" value="yes" %s/>',
            isset( $this->options['load_js'] ) ? 'checked ' : ''
        );
    }
}