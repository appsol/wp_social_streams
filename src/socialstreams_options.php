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
                settings_fields('socialstreams_option_group');
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
            [$this, 'sanitize'] // Sanitize
        );

        add_settings_section(
            'socialstreams_general', // ID
            'General Options', // Title
            [$this, 'printGeneralInfo'], // Callback
            'socialstreams-setting-admin' // Page
        );

        add_settings_field(
            'load_css', // ID
            'Load Plugin CSS', // Title
            [$this, 'checkboxCallback'], // Callback
            'socialstreams-setting-admin', // Page
            'socialstreams_general', // Section
            ['name' => 'load_css']
        );

        add_settings_field(
            'load_js', // ID
            'Load Plugin Javascript', // Title
            [$this, 'checkboxCallback'], // Callback
            'socialstreams-setting-admin', // Page
            'socialstreams_general', // Section
            ['name' => 'load_js']
        );

        add_settings_section(
            'socialstreams_facebook', // ID
            'Facebook Options', // Title
            [$this, 'printFacebookApiInfo'], // Callback
            'socialstreams-setting-admin' // Page
        );

        add_settings_field(
            'socialstreams_facebook_app_id', // ID
            'Facebook App ID', // Title
            [$this, 'simpleKeyCallback'], // Callback
            'socialstreams-setting-admin', // Page
            'socialstreams_facebook', // Section
            ['name' => 'facebook_app_id']
        );

        add_settings_field(
            'socialstreams_facebook_app_secret', // ID
            'Facebook App Secret', // Title
            [$this, 'hiddenKeyCallback'], // Callback
            'socialstreams-setting-admin', // Page
            'socialstreams_facebook', // Section
            ['name' => 'facebook_app_secret']
        );

        add_settings_section(
            'socialstreams_twitter', // ID
            'Twitter Options', // Title
            [$this, 'printSocialMediaApiInfo'], // Callback
            'socialstreams-setting-admin' // Page
        );

        add_settings_field(
            'socialstreams_twitter_app_id', // ID
            'Twitter Consumer Key', // Title
            [$this, 'simpleKeyCallback'], // Callback
            'socialstreams-setting-admin', // Page
            'socialstreams_twitter', // Section
            ['name' => 'twitter_app_id']
        );

        add_settings_field(
            'socialstreams_twitter_app_secret', // ID
            'Twitter Consumer Secret', // Title
            [$this, 'hiddenKeyCallback'], // Callback
            'socialstreams-setting-admin', // Page
            'socialstreams_twitter', // Section
            ['name' => 'twitter_app_secret']
        );

        add_settings_field(
            'socialstreams_twitter_access_token', // ID
            'Twitter Access Token', // Title
            [$this, 'simpleKeyCallback'], // Callback
            'socialstreams-setting-admin', // Page
            'socialstreams_twitter', // Section
            ['name' => 'twitter_access_token']
        );

        add_settings_field(
            'socialstreams_twitter_access_token_secret', // ID
            'Twitter Access Token Secret', // Title
            [$this, 'hiddenKeyCallback'], // Callback
            'socialstreams-setting-admin', // Page
            'socialstreams_twitter', // Section
            ['name' => 'twitter_access_token_secret']
        );

        add_settings_section(
            'socialstreams_youtube', // ID
            'YouTube Options', // Title
            [$this, 'printSocialMediaApiInfo'], // Callback
            'socialstreams-setting-admin' // Page
        );

        add_settings_field(
            'socialstreams_youtube_simple_key', // ID
            'YouTube Simple API Key', // Title
            [$this, 'simpleKeyCallback'], // Callback
            'socialstreams-setting-admin', // Page
            'socialstreams_youtube', // Section
            ['name' => 'youtube_simple_key']
        );

        add_settings_section(
            'socialstreams_instagram', // ID
            'Instagram Options', // Title
            [$this, 'printSocialMediaApiInfo'], // Callback
            'socialstreams-setting-admin' // Page
        );

        add_settings_field(
            'socialstreams_instagram_app_id', // ID
            'Instagram Client ID', // Title
            [$this, 'simpleKeyCallback'], // Callback
            'socialstreams-setting-admin', // Page
            'socialstreams_instagram', // Section
            ['name' => 'instagram_app_id']
        );

        add_settings_field(
            'socialstreams_instagram_app_secret', // ID
            'Instagram Client Secret', // Title
            [$this, 'hiddenKeyCallback'], // Callback
            'socialstreams-setting-admin', // Page
            'socialstreams_instagram', // Section
            ['name' => 'instagram_app_secret']
        );

        add_settings_section(
            'socialstreams_linkedin', // ID
            'LinkedIn Options', // Title
            [$this, 'printSocialMediaApiInfo'], // Callback
            'socialstreams-setting-admin' // Page
        );

        add_settings_field(
            'socialstreams_linkedin_app_id', // ID
            'LinkedIn Client ID', // Title
            [$this, 'simpleKeyCallback'], // Callback
            'socialstreams-setting-admin', // Page
            'socialstreams_linkedin', // Section
            ['name' => 'linkedin_app_id']
        );

        add_settings_field(
            'socialstreams_linkedin_app_secret', // ID
            'LinkedIn Client Secret', // Title
            [$this, 'hiddenKeyCallback'], // Callback
            'socialstreams-setting-admin', // Page
            'socialstreams_linkedin', // Section
            ['name' => 'linkedin_app_secret']
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

        foreach ($input as $key => $value) {
            $new_value = sanitize_text_field(trim($value));
            if (!empty($new_value)) {
                $new_input[$key] = $new_value;
            }
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
     * Print the section text for the Facebook API section
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function printFacebookApiInfo()
    {
        print "Enter your API keys and Authentication details";
        if(!empty($this->options['facebook_app_id']) && !empty($this->options['facebook_app_secret'])) {
            $fbConnect = new FacebookConnect($this->options['facebook_app_id'], $this->options['facebook_app_secret']);
            if ($fbConnect->hasSession()) {
                print "Connected";
            } else {
                print '<a class="button button-secondary" href="' . $fbConnect->getAuthenticationUrl() . '">Connect</a>';
            }
        }
    }

    /**
     * Prints a input field for a simple API key
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function simpleKeyCallback($params)
    {
        extract($params);
        printf(
            '<input type="text" id="' . $name . '" name="socialstreams[' . $name . ']" value="%s" />',
            isset( $this->options[$name] ) ? esc_attr($this->options[$name]) : ''
        );
    }

    /**
     * Prints a password field for a simple API key
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function hiddenKeyCallback($params)
    {
        extract($params);
        printf(
            '<input type="password" id="' . $name . '" name="socialstreams[' . $name . ']" value="%s" />',
            isset( $this->options[$name] ) ? esc_attr($this->options[$name]) : ''
        );
    }

    /**
     * Prints a checkbox field
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function checkboxCallback($params)
    {
        extract($params);
        printf(
            '<input type="checkbox" id="' . $name . '" name="socialstreams[' . $name . ']" value="yes" %s/>',
            isset( $this->options[$name] ) ? 'checked ' : ''
        );
    }
}