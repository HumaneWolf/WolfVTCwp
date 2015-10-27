<?php
defined('ABSPATH') or die('No direct access! Bad user!');

class wolfvtc_stats_widget extends WP_Widget {

    // CONSTRUCT
    function wolfvtc_stats_widget() {
        parent::WP_Widget(false, $name = __('VTC Stats', 'wolfvtc_stats_widget') );
    }

    // UPDATE FORM
    function form($instance) {  
        if( $instance) {
             $title = esc_attr($instance['title']);
        } else {
             $title = '';
        }
        ?>
        <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', 'wp_widget_plugin'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <?php
    }

    // UPDATE
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        // Fields
        $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }

    // DISPLAY IT
    function widget($args, $instance) {
        extract( $args );

        $title = apply_filters('widget_title', $instance['title']);
        echo $before_widget;
        echo '<div class="widget-text wp_widget_plugin_box">';

        // Widget title
        if ( $title ) {
            echo $before_title . $title . $after_title;
        } else {
            echo $before_title . "VTC Stats" . $after_title;
        }

        echo '<p><strong>Drivers:</strong> ' . wolfvtc_drivers() . '</p>';
        echo '<p><strong>Cargo delivered:</strong> ' . wolfvtc_jobs() . '</p>';
        echo '<p><strong>Kilometres driven:</strong> ' . wolfvtc_kmdriven() . ' Km</p>';
        if (get_option("wolfvtc_divisionsenabled") != 0) {
            echo '<p><strong>Divisions:</strong> ' . wolfvtc_divisions() . '</p>';
        }

        echo '</div>';
        echo $after_widget;
    }
}

// register widget
add_action('widgets_init', create_function('', 'return register_widget("wolfvtc_stats_widget");'));