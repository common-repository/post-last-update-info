<?php

/** 
 * @link              
 * @since             1.0.0
 * @package           Plui
 *
 * @wordpress-plugin
 * Plugin Name:       Post Last update info
 * Plugin URI:        
 * Description:       Post Last Update Info is a lightweight plugin that automatically displays the last modified date on posts, pages, and custom post types. 
 * Version:           1.0.0
 * Author:            Lalit Rastogi
 * Author URI:        
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       post-last-update-info
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUI_VERSION', '1.0.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plui-activator.php
 */
function plui_activate_plui() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-plui-activator.php';
	Plui_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plui-deactivator.php
 */
function plui_deactivate_plui() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-plui-deactivator.php';
	Plui_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'plui_activate_plui' );
register_deactivation_hook( __FILE__, 'plui_deactivate_plui' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-plui.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function plui_run_plui() {

	$plugin = new Plui();
	$plugin->run();

}
plui_run_plui();
