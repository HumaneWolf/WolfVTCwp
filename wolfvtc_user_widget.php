<?php
defined('ABSPATH') or die('No direct access! Bad user!');

class wolfvtc_widget extends WP_Widget {

    // CONSTRUCT
    function wolfvtc_widget() {
        parent::WP_Widget(false, $name = __('User Panel', 'wolfvtc_widget') );
    }

    // FORM THINGY
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

    // DISPLAY EEEET
    function widget($args, $instance) {
        extract( $args );

        $title = apply_filters('widget_title', $instance['title']);
        echo $before_widget;
        echo '<div class="widget-text wp_widget_plugin_box">';

        // Widget title
        if ( $title ) {
            echo $before_title . $title . $after_title;
        } else {
            echo $before_title . "User Panel" . $after_title;
        }

        //Is user logged in?
        if (is_user_logged_in()) { //If yes, do this
            echo '
            <a href="' . admin_url('options-general.php?page=wolfvtc') . '"><input type="button" class="button-primary" style="width:100%;margin-bottom:5px" value="Dashboard"></a>
            <a href="' . admin_url('options-general.php?page=wolfvtc&do=newjob') . '"><input type="button" class="button-primary" style="width:100%;margin-bottom:5px" value="Submit Job"></a>
            ';
            if (get_option('wolftvc_divisionsenabled') != FALSE) {
                echo '<a href="' . admin_url('options-general.php?page=wolfvtc&do=divs') . '"><input type="button" class="button-primary" style="width:100%;margin-bottom:5px" value="Divisons"></a>';
            }
            echo '
            <a href="' . admin_url('options-general.php?page=wolfvtcadmin') . '"><input type="button" class="button-primary" style="width:100%;margin-bottom:5px" value="VTC Admin"></a>
            <a href="' . wp_logout_url(get_permalink()) . '"><input type="button" class="button-primary" style="width:100%" value="Log out"></a>
            ';
        } else { //Else display this
            echo '<form method="post" action="' . wp_login_url() . '" name="loginform">
                <label>Username</label>
                <input class="input" type="text" name="log" style="width:100%">

                <label>Password</label>
                <input class="input" type="password" name="pwd" style="width:100%">

                <input type="checkbox" value="forever" checked="checked" name="rememberme">
                <label>Remember me</label>

                <input type="submit" class="button-primary" style="width:100%" value="Sign in">
            </form>
            ';

            echo '<a href="' . wp_registration_url(get_permalink()) . '"><input type="button" class="button-primary" style="width:100%;margin-top:2px" value="Register"></a>';
        }
        echo '</div>';
        echo $after_widget;
    }
}

// register widget
add_action('widgets_init', create_function('', 'return register_widget("wolfvtc_widget");'));