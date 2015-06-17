<?php
/**
 * SocialStreamsCounterWidget
 * 
 * @package wp_social_streams
 * @author Stuart Laverick
 */
namespace SocialStreams;

defined('ABSPATH') or die( 'No script kiddies please!' );

class SocialStreamsCounterWidget extends \WP_Widget
{

  /**
  * Constructor
  *
  * @return void
  * @author Stuart Laverick
  */
  function __construct()
  {
    parent::__construct(
        'social_streams_count',
        __('Social Counter', 'wp_social_streams'),
        ['description' => __('Shows the engagement counts from a range of Social Media networks.')]
    );
  }

  /**
   * Display the form for the widget settings
   *
   * @return void
   * @author Stuart Laverick
   **/
  function form($instance)
  {
        $instance = wp_parse_args((array) $instance, [
            'title' => __('Social Counts', 'wp_social_streams'),
            'facebook_count' => '',
            'twitter_count' => '',
            'youtube_count' => '',
            'instagram_count' => '',
            'linkedin_count' => '',
            'home_only' => '']);
        $thumbcount = $instance['thumbs'];
        ?>
        <p><label for="<?php echo $this->get_field_id("title"); ?>"><?php _e('Title'); ?>:</label>
            <input id="<?php echo $this->get_field_id("title"); ?>"
                   name="<?php echo $this->get_field_name("title"); ?>"
                   value="<?php echo $instance['title'] ?>" class="widefat" /></p>
        <?php if ($instance['message']): ?>
        <p class="message"><?php echo $instance['message']; ?></p>
        <?php endif; ?>
        <p>
            <input class="checkbox" id="<?php echo $this->get_field_id('facebook_count'); ?>" name="<?php echo $this->get_field_name('facebook_count'); ?>" type="checkbox" value="yes" <?php if (esc_attr($instance['facebook_count']) == 'yes') echo 'checked="checked"'; ?> />
            <label for="<?php echo $this->get_field_id('facebook_count'); ?>"><?php _e('Facebook Count'); ?></label>
        </p>
        <p>
            <input class="checkbox" id="<?php echo $this->get_field_id('twitter_count'); ?>" name="<?php echo $this->get_field_name('twitter_count'); ?>" type="checkbox" value="yes" <?php if (esc_attr($instance['twitter_count']) == 'yes') echo 'checked="checked"'; ?> />
            <label for="<?php echo $this->get_field_id('twitter_count'); ?>"><?php _e('Twitter Count'); ?></label>
        </p>
        <p>
            <input class="checkbox" id="<?php echo $this->get_field_id('youtube_count'); ?>" name="<?php echo $this->get_field_name('youtube_count'); ?>" type="checkbox" value="yes" <?php if (esc_attr($instance['youtube_count']) == 'yes') echo 'checked="checked"'; ?> />
            <label for="<?php echo $this->get_field_id('youtube_count'); ?>"><?php _e('YouTube Count'); ?></label>
        </p>
        <p>
            <input class="checkbox" id="<?php echo $this->get_field_id('instagram_count'); ?>" name="<?php echo $this->get_field_name('instagram_count'); ?>" type="checkbox" value="yes" <?php if (esc_attr($instance['instagram_count']) == 'yes') echo 'checked="checked"'; ?> />
            <label for="<?php echo $this->get_field_id('instagram_count'); ?>"><?php _e('Instagram Count'); ?></label>
        </p>
        <p>
            <input class="checkbox" id="<?php echo $this->get_field_id('linkedin_count'); ?>" name="<?php echo $this->get_field_name('linkedin_count'); ?>" type="checkbox" value="yes" <?php if (esc_attr($instance['linkedin_count']) == 'yes') echo 'checked="checked"'; ?> />
            <label for="<?php echo $this->get_field_id('linkedin_count'); ?>"><?php _e('LinkedIn Count'); ?></label>
        </p>
        <p>
            <input class="checkbox" id="<?php echo $this->get_field_id('home_only'); ?>" name="<?php echo $this->get_field_name('home_only'); ?>" type="checkbox" value="yes" <?php if (esc_attr($instance['home_only']) == 'yes') echo 'checked="checked"'; ?> />
            <label for="<?php echo $this->get_field_id('home_only'); ?>"><?php _e('Display on Home page only'); ?></label>
        </p>
        <?php
    }

    /**
     * Update the settings for this instance of the widget
     *
     * @return Array the updated settings array
     * @author Stuart Laverick
     **/
    function update($new_instance, $old_instance)
    {
        $ss = SocialStreams::getInstance();
        $ss->deleteWidgetTransients($old_instance);

        $instance = $old_instance;
        $instance['message'] = '';

        $instance['title'] = $new_instance['title'];
        // Only show this on the home page?
        $instance['home_only'] = isset($new_instance['home_only'])? 'yes' : 'no';
        $instance['facebook_count'] = isset($new_instance['facebook_count'])? 'yes' : 'no';
        $instance['twitter_count'] = isset($new_instance['twitter_count'])? 'yes' : 'no';
        $instance['youtube_count'] = isset($new_instance['youtube_count'])? 'yes' : 'no';
        $instance['instagram_count'] = isset($new_instance['instagram_count'])? 'yes' : 'no';
        $instance['linkedin_count'] = isset($new_instance['linkedin_count'])? 'yes' : 'no';

        return $instance;
    }

    /**
     * Display the widget
     *
     * @return void
     * @author Stuart Laverick
     **/
    function widget($args, $instance)
    {
        extract($args);
        // Only show on the Home page?
        if ($instance['home_only'] == 'yes' && !is_front_page()) {
            return;
        }
        $title = apply_filters('widget_title', $instance['title']);
        $ss = SocialStreams::getInstance();

        ob_start();

        $output = ob_get_contents();
        ob_end_clean();

        echo $before_widget;
        if ($title) {
            echo $before_title . $title . $after_title;
        }
        echo $output;
        echo $after_widget;
        return true;
    }

    /**
     * undocumented function
     *
     * @return void
     * @author 
     **/
    private function deleteMetricData()
    {
    }

    /**
     * undocumented function
     *
     * @return void
     * @author 
     **/
    private function updateMetricdata($service)
    {
      $connectionFactory = new ConnectionFactory();
      $connection = $connectionFactory->createConnection($service);
      
    }
}
