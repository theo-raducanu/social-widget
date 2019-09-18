<?php
/**
 * Plugin Name: Social Icons Widget
 * Plugin URI: #
 * Description: Custom widget to display social media icons.
 * Author: Exibit
 * Author URI: #
 * Version: 1.0.0
 * License: GPLv2 or later
 * Text Domain: social-icons-widget-exibit
 * Domain Path: /languages
 */

require_once plugin_dir_path( __FILE__ ) . 'class.exibit-social-icons-widget.php';
require_once plugin_dir_path( __FILE__ ) . 'exibit-helper.php';


/**
* Load textdomain
 */
function exibit_social_icons_widget_load_textdomain() {
    load_plugin_textdomain( 'exibit-social-icons-widget', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'exibit_social_icons_widget_load_textdomain' );

/**
* Register the widget
 */
add_action( 'widgets_init', 'exibit_social_icons_widget_register' );
function exibit_social_icons_widget_register() {
	register_widget( 'exibit_Social_Icons_Widget' );
}