<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       
 * @since      1.0.0
 *
 * @package    Plui
 * @subpackage Plui/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Plui
 * @subpackage Plui/admin
 * @author     Lalit Rastogi <rastogi.lalit12@gmail.com>
 */
class Plui_Admin {

    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Hook into WordPress
        add_action('admin_menu', array($this, 'plui_add_settings_menu'));
        add_action('admin_init', array($this, 'plui_register_settings'));
        add_action('add_meta_boxes', array($this, 'plui_add_meta_box'));
        add_action('save_post', array($this, 'plui_save_post_meta'));
        add_filter('the_content', array($this, 'plui_display_last_update_info'));
        add_action('wp_head', array($this, 'plui_add_schema_markup'));
        add_action('init', array($this, 'plui_register_shortcode'));
    }

    public function enqueue_styles() {
        if ( !is_admin() ) { 
            // Enqueue styles if needed
        }
    }

    public function enqueue_scripts() {
        if ( !is_admin() ) { 
            // Enqueue scripts if needed
        }
    }

    // Add menu item under "Settings"
    public function plui_add_settings_menu() {
        add_options_page(
            'Post Last Update Info Settings',
            'Post Last Update Info',
            'manage_options',
            'plui',
            array($this, 'plui_settings_page')
        );
    }

    // Register settings
    public function plui_register_settings() {
        register_setting('plui_settings_group', 'plui_enabled', 'plui_sanitize_enabled');
        register_setting('plui_settings_group', 'plui_custom_text', 'plui_sanitize_custom_text');
        register_setting('plui_settings_group', 'plui_display_position', 'plui_sanitize_display_position');
        register_setting('plui_settings_group', 'plui_disable_on_single_pages', 'plui_sanitize_disable_on_single_pages');
    }

    // Define sanitization functions
    public function plui_sanitize_enabled($input) {
        return !empty($input) ? 1 : 0; // Ensure it's a boolean (0 or 1)
    }

    public function plui_sanitize_custom_text($input) {
        return sanitize_text_field($input); // Sanitize text input
    }

    public function plui_sanitize_display_position($input) {
        return in_array($input, ['above', 'below']) ? $input : 'above'; // Allow only 'above' or 'below'
    }

    public function plui_sanitize_disable_on_single_pages($input) {
        return !empty($input) ? 1 : 0; // Ensure it's a boolean (0 or 1)
    }


    // Display the settings page
    public function plui_settings_page() {
        $plui_enabled = get_option('plui_enabled', 0);
        $plui_custom_text = get_option('plui_custom_text', 'Last updated on');
        $plui_display_position = get_option('plui_display_position', 'below');
        // Removed: $disabled_archives = get_option('plui_disable_archives', []);
        $plui_disable_on_single_pages = get_option('plui_disable_on_single_pages', 0);

        ?>
        <div class="wrap">
            <h1>Post Last Update Info Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('plui_settings_group'); ?>
                <?php do_settings_sections('plui_settings_group'); ?>
                
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Enable Last Update Info</th>
                        <td><input type="checkbox" name="plui_enabled" value="1" <?php checked(1, $plui_enabled, true); ?> /></td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row">Custom Text</th>
                        <td><input type="text" name="plui_custom_text" value="<?php echo esc_attr($plui_custom_text); ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Display Position</th>
                        <td>
                            <select name="plui_display_position">
                                <option value="above" <?php selected($plui_display_position, 'above'); ?>>Above Content</option>
                                <option value="below" <?php selected($plui_display_position, 'below'); ?>>Below Content</option>
                            </select>
                        </td>
                    </tr>

                 

                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function plui_add_meta_box() {
        add_meta_box(
            'plui_meta_box', 
            'Post Last Update Info', 
            array($this, 'plui_meta_box_callback'), 
            'post', 
            'side'
        );
    }

    public function plui_meta_box_callback($post) {
        wp_nonce_field('plui_save_meta_box_data', 'plui_meta_box_nonce');

        $value = get_post_meta($post->ID, '_plui_disable_last_update', true);
        ?>
        <label for="plui_disable_last_update">
            <input type="checkbox" id="plui_disable_last_update" name="plui_disable_last_update" value="1" <?php checked($value, '1'); ?> />
            Disable Last Update Info for this post
        </label>
        <?php
    }

    public function plui_save_post_meta($post_id) {
        if (!isset($_POST['plui_meta_box_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['plui_meta_box_nonce'])), 'plui_save_meta_box_data')) {
            return $post_id;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        if (isset($_POST['plui_disable_last_update'])) {
            update_post_meta($post_id, '_plui_disable_last_update', '1');
        } else {
            delete_post_meta($post_id, '_plui_disable_last_update');
        }
    }

    public function plui_display_last_update_info($content) {
        $plui_enabled = get_option('plui_enabled', 0);
        $plui_disable_on_single_pages = get_option('plui_disable_on_single_pages', 0);
        
        if (!$plui_enabled) {
            return $content;
        }

        if (is_single() && $plui_disable_on_single_pages) {
            return $content;
        }

        if (is_singular() && !is_page() && get_post_meta(get_the_ID(), '_plui_disable_last_update', true)) {
            return $content;
        }

        $last_updated = get_the_modified_time('F j, Y');
        $custom_text = get_option('plui_custom_text', 'Last updated on');
        $display_position = get_option('plui_display_position', 'below');

        $last_update_info = '<p>' . esc_html($custom_text) . ' ' . esc_html($last_updated) . '</p>';

        if ($display_position === 'above') {
            return $last_update_info . $content;
        } else {
            return $content . $last_update_info;
        }
    }

  public function plui_add_schema_markup() {
    if (is_single() || is_page()) {
        $schema_data = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'dateModified' => get_the_modified_time('c'),
        ];

        echo '<script type="application/ld+json">' . wp_json_encode($schema_data) . '</script>';
    }
}


    public function plui_register_shortcode() {
        add_shortcode('plui-post-last-updated-info', array($this, 'plui_shortcode_handler'));
    }

    public function plui_shortcode_handler($atts) {
        $custom_text = get_option('plui_custom_text', 'Last updated on');
        $last_updated = get_the_modified_time('F j, Y');
        return '<p>' . esc_html($custom_text) . ' ' . esc_html($last_updated) . '</p>';
    }
}
