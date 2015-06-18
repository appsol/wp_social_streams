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
    $ss = SocialStreams::getInstance();
    $defaults = [
      'title' => __('Social Counts', 'wp_social_streams'),
      'home_only' => ''
    ];
    foreach ($ss->activeNetworks as $network) {
      $defaults[$network->getNetworkName() . '_count'] = '';
      $defaults[$network->getNetworkName() . '_entity_id'] = '';
    }
    $instance = wp_parse_args((array) $instance, $defaults);
    ?>
    <p><label for="<?php echo $this->get_field_id("title"); ?>"><?php _e('Title'); ?>:</label>
      <input id="<?php echo $this->get_field_id("title"); ?>"
      name="<?php echo $this->get_field_name("title"); ?>"
      value="<?php echo $instance['title'] ?>" class="widefat" /></p>
    <?php if (isset($instance['message'])): ?>
        <p class="message"><?php echo $instance['message']; ?></p>
    <?php endif; ?>
    <p class="description"><?php _e('Add Entity IDs to show counts from specific users or pages. If left blank the default logged in user will be used.'); ?></p>
    <?php foreach ($ss->activeNetworks as $network):
            $countFieldId = $network->getNetworkName() . '_count';
            $entityFieldId = $network->getNetworkName() . '_entity_id';
            $countFieldName = 'Show ' . $network->getNiceName() . ' Count';
            $entityFieldName = $network->getNiceName() . ' Entity ID';
            $count = $network->getFollowerCount(); ?>
    <p>
      <input class="checkbox" id="<?php echo $this->get_field_id($countFieldId); ?>" name="<?php echo $this->get_field_name($countFieldId); ?>" type="checkbox" value="yes" <?php if (esc_attr($instance[$countFieldId]) == 'yes') echo 'checked="checked"'; ?> />
      <label for="<?php echo $this->get_field_id($countFieldId); ?>"><?php _e($countFieldName); ?><?php if($count) echo ' (' . $count . ')'; ?></label>
      <input id="<?php echo $this->get_field_id($entityFieldId); ?>"
      name="<?php echo $this->get_field_name($entityFieldId); ?>"
      value="<?php echo $instance[$entityFieldId] ?>"
      placeholder="<?php _e($entityFieldName); ?>" class="widefat" />
    </p>
    <?php endforeach; ?>
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

      $instance = $old_instance;
      $instance['message'] = '';

      $instance['title'] = $new_instance['title'];
        // Only show this on the home page?
      $instance['home_only'] = isset($new_instance['home_only'])? 'yes' : 'no';
      // Update for Active Networks
      foreach ($ss->activeNetworks as $network) {
        $network->getFollowerCount($instance[$network->getNetworkName() . '_entity_id'], true);
        $instance[$network->getNetworkName() . '_count'] = isset($new_instance[$network->getNetworkName() . '_count'])? 'yes' : 'no';
        $instance[$network->getNetworkName() . '_entity_id'] = $new_instance[$network->getNetworkName() . '_entity_id'];
      }
      // $instance['twitter_count'] = isset($new_instance['twitter_count'])? 'yes' : 'no';
      // $instance['youtube_count'] = isset($new_instance['youtube_count'])? 'yes' : 'no';
      // $instance['instagram_count'] = isset($new_instance['instagram_count'])? 'yes' : 'no';
      // $instance['linkedin_count'] = isset($new_instance['linkedin_count'])? 'yes' : 'no';

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

      $output = ['<dl>'];
      foreach ($ss->activeNetworks as $network) {
        if ($instance[$network->getNetworkName() . '_count'] === 'yes') {
          $output[] = '<dt class="network-name ' . $network->getNetworkName() . '">'
                . $network->getNiceName() . '</dt>';
          $output[] = '<dd>' . $network->getFollowerCount($instance[$network->getNetworkName() . '_entity_id']) . '<span class="follower-name">'
                . $network->getFollowerName(true) . '</span></dd>';
        }
      }
      $output[] = '</dl>';
      echo $before_widget;
      if ($title) {
        echo $before_title . $title . $after_title;
      }
      echo $output;
      echo $after_widget;
      return true;
    }

    /**
     * Returns the follower count for the default entity (user, page, etc)
     *
     * @return int Follower Count
     * @author Stuart Laverick
     **/
    private function getDefaultEntityFollowerCount($service, $update = false)
    {
      if($connection = $this->getConnection($service)) {
        return $connection->getFollowerCount('', $update);
      }
    }

    /**
     * Returns a connection object for the spcified social network
     *
     * @return SocialApiInterface object
     * @author Stuart Laverick
     **/
    private function getConnection($service)
    {
      $connectionFactory = new ConnectionFactory();
      $connection = $connectionFactory->createConnection($service);
      if ($connection->hasSession()) {
        return $connection;
      }
      return false;
    }
  }
