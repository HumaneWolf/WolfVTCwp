<?php
defined( 'ABSPATH' ) or die( 'No direct access! Bad user!');

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

        // Title
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
            <a href="' . admin_url() . '"><input type="button" class="button-primary" style="width:100%;margin-bottom:5px" value="Dashboard"></a>
            <a href="' . site_url() . '"><input type="button" class="button-primary" style="width:100%;margin-bottom:5px" value="Jobs"></a>
            ';
            if (get_option('wolftvc_divisionsenabled') != FALSE) {
                echo '<a href="' . site_url() . '"><input type="button" class="button-primary" style="width:100%;margin-bottom:5px" value="Divisons"></a>';
            }
            echo '
            <a href="' . admin_url('options-general.php?page=wolfvtc') . '"><input type="button" class="button-primary" style="width:100%;margin-bottom:5px" value="VTC Admin"></a>
            <a href="' . wp_logout_url( get_permalink()) . '"><input type="button" class="button-primary" style="width:100%" value="Log out"></a>
            ';
        } else { //Else display this
            $loginargs = array(
            'echo'           => true,
            'redirect' => ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
            'form_id'        => 'loginform',
            'label_username' => __( 'Username' ),
            'label_password' => __( 'Password' ),
            'label_remember' => __( 'Remember Me' ),
            'label_log_in'   => __( 'Log In' ),
            'id_username'    => 'user_login',
            'id_password'    => 'user_pass',
            'id_remember'    => 'rememberme',
            'id_submit'      => 'wp-submit',
            'remember'       => true,
            'value_username' => '',
            'value_remember' => true,
            );
            wp_login_form($loginargs);

            echo '<a href="' . wp_registration_url( get_permalink()) . '"><input type="button" class="button-primary" style="width:100%" value="Register"></a>';
        }
        echo '</div>';
        echo $after_widget;
    }
}

// register widget
add_action('widgets_init', create_function('', 'return register_widget("wolfvtc_widget");'));