<?php
/**
 * SocialStreamsOptions
 * 
 * @package wp_social_streams
 * @author Stuart Laverick
 */
namespace AppSol\SocialStreams;

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
     * Factory object
     *
     * @var ConnectionFactory
     **/
    private $connectionFactory;

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
        $this->connectionFactory = new ConnectionFactory();
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
            [$this, 'printTwitterApiInfo'], // Callback
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

        add_settings_section(
            'socialstreams_google', // ID
            'Google Options', // Title
            [$this, 'printGoogleApiInfo'], // Callback
            'socialstreams-setting-admin' // Page
        );

        add_settings_field(
            'socialstreams_google_app_id', // ID
            'Google Client ID', // Title
            [$this, 'simpleKeyCallback'], // Callback
            'socialstreams-setting-admin', // Page
            'socialstreams_google', // Section
            ['name' => 'google_app_id']
        );

        add_settings_field(
            'socialstreams_google_app_secret', // ID
            'Google Client Secret', // Title
            [$this, 'hiddenKeyCallback'], // Callback
            'socialstreams-setting-admin', // Page
            'socialstreams_google', // Section
            ['name' => 'google_app_secret']
        );

        add_settings_section(
            'socialstreams_instagram', // ID
            'Instagram Options', // Title
            [$this, 'printInstagramApiInfo'], // Callback
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
            [$this, 'printLinkedinApiInfo'], // Callback
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
    public function printSocialMediaApiInfo(SocialApiInterface $connection, $userName = '')
    {
        if ($connection->hasSession() && $userName) {
            printf(
                '<p>%s %s <a class="button button-secondary" href="%s">%s</a></p>',
                __('Connected as', 'wp_social_streams'),
                $userName,
                $connection->getDisconnectUrl(),
                __('Disconnect', 'wp_social_streams')
            );
        } else {
            if ($msg = $connection->getLastMessage()) {
                printf(
                    '<div class="updated"><p>%s</p></div>',
                    esc_html__($msg['message'], 'wp_social_streams')
                );
            }
            printf(
                '<p><a class="button button-secondary" href="%s">%s</a></p>',
                $connection->getAuthenticationUrl(),
                __('Connect', 'wp_social_streams')
            );
        }
        try {
                $token = $connection->getToken();
                switch ($token->getEndOfLife()) {
                    case \OAuth\Common\Token\TokenInterface::EOL_UNKNOWN:
                        $eol = __('Unknown Token Lifespan', 'wp_social_streams');
                        break;
                    case \OAuth\Common\Token\TokenInterface::EOL_NEVER_EXPIRES:
                        $eol = __('Indefinite Token Lifespan', 'wp_social_streams');
                        break;
                    default:
                        $eol = date('Y-m-d H:m:s', $token->getEndOfLife());
                        break;
                }
                printf(
                    '<p class="description">%s</p>',
                    __('End of Life: ') . $eol
                );
        } catch (\OAuth\Common\Storage\Exception\TokenNotFoundException $e) {
        }
    }

    /**
     * Print the section text for the Facebook API section
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function printFacebookApiInfo()
    {
        if (!empty($this->options['facebook_app_id']) && !empty($this->options['facebook_app_secret'])) {
            $fbConnect = $this->connectionFactory->createConnection('facebook');
            $user = $fbConnect->getUser();
            $this->printSocialMediaApiInfo($fbConnect, isset($user['name'])? $user['name'] : '');
        } else {
            printf('<p>%s <a target="_blank" href="https://developers.facebook.com/apps/">%s</a></p>',
                __('Enter your Facebook API keys. See', 'wp_social_streams'),
                __('Facebook Developer Apps', 'wp_social_streams')
                );
        }
    }

    /**
     * Print the section text for the YouTube API section
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function printGoogleApiInfo()
    {
        if (!empty($this->options['google_app_id']) && !empty($this->options['google_app_secret'])) {
            $ggConnect = $this->connectionFactory->createConnection('google');
            $user = $ggConnect->getUser();
            $this->printSocialMediaApiInfo($ggConnect, isset($user['name'])? $user['name'] : '');
        } else {
            printf('<p>%s <a target="_blank" href="https://console.developers.google.com/project?authuser=0">%s</a></p>',
                __('Enter your Google API keys. These can be used for many Google services (e.g. YouTube, etc) See', 'wp_social_streams'),
                __('Google Developers Console', 'wp_social_streams')
                );
        }
    }

    /**
     * Print the section text for the Twitter API section
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function printTwitterApiInfo()
    {
        if (!empty($this->options['twitter_app_id']) && !empty($this->options['twitter_app_secret'])) {
            $twConnect = $this->connectionFactory->createConnection('twitter');
            $user = $twConnect->getUser();
            $this->printSocialMediaApiInfo($twConnect, isset($user['name'])? $user['name'] : '');
        } else {
            printf('<p>%s <a target="_blank" href="https://apps.twitter.com/">%s</a></p>',
                __('Enter your Twitter API keys. See', 'wp_social_streams'),
                __('Twitter Application Management', 'wp_social_streams')
                );
        }
    }

    /**
     * Print the section text for the Instagram API section
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function printInstagramApiInfo()
    {
        if (!empty($this->options['instagram_app_id']) && !empty($this->options['instagram_app_secret'])) {
            $igConnect = $this->connectionFactory->createConnection('instagram');
            $user = $igConnect->getUser();
            $this->printSocialMediaApiInfo($igConnect, isset($user['full_name'])? $user['full_name'] : '');
        } else {
            printf('<p>%s <a target="_blank" href="https://instagram.com/developer/clients/manage/">%s</a></p>',
                __('Enter your Instagram API keys. See', 'wp_social_streams'),
                __('Instagram Manage Clients', 'wp_social_streams')
                );
        }
    }

    /**
     * Print the section text for the LinkedIn API section
     *
     * @return void
     * @author Stuart Laverick
     **/
    public function printLinkedinApiInfo()
    {
        if (!empty($this->options['linkedin_app_id']) && !empty($this->options['linkedin_app_secret'])) {
                $liConnect = $this->connectionFactory->createConnection('linkedin');
                $user = $liConnect->getUser();
                $this->printSocialMediaApiInfo($liConnect, isset($user['firstName'])? $user['firstName'] . ' ' . $user['lastName'] : '');
        } else {
            printf('<p>%s <a target="_blank" href="https://www.linkedin.com/developer/apps">%s</a></p>',
                __('Enter your LinkedIn API keys. See', 'wp_social_streams'),
                __('LinkedIn My Applications', 'wp_social_streams')
                );
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