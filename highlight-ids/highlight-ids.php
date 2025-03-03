<?php

/**
 * Plugin Name: Highlight IDs in Target Containers
 * Description: Adds a button to the admin bar to outline elements with an ID inside user-defined containers.
 * Version: 1.2
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Add settings menu
function highlight_ids_add_settings_page() {
    add_options_page(
        'Highlight IDs Settings',
        'Highlight IDs',
        'manage_options',
        'highlight-ids-settings',
        'highlight_ids_settings_page'
    );
}
add_action('admin_menu', 'highlight_ids_add_settings_page');

// Settings page content
function highlight_ids_settings_page() {
?>
    <div class="wrap">
        <h1>Highlight IDs Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('highlight-ids-settings-group');
            do_settings_sections('highlight-ids-settings');
            submit_button();
            ?>
        </form>
    </div>
<?php
}

// Register settings
function highlight_ids_register_settings() {
    register_setting('highlight-ids-settings-group', 'highlight_ids_target_classes');

    add_settings_section(
        'highlight-ids-main-section',
        'Configuration',
        function () {
            echo '<p>Enter the CSS classes for the target containers (comma-separated, e.g., <code>.container, .example-box</code>)</p>';
        },
        'highlight-ids-settings'
    );

    add_settings_field(
        'highlight-ids-container-classes',
        'Target Container Classes',
        function () {
            $value = get_option('highlight_ids_target_classes', '.example-container');
            echo '<input type="text" name="highlight_ids_target_classes" value="' . esc_attr($value) . '" class="regular-text">';
        },
        'highlight-ids-settings',
        'highlight-ids-main-section'
    );
}
add_action('admin_init', 'highlight_ids_register_settings');

// Add button to Admin Bar
function highlight_ids_admin_bar_button($wp_admin_bar) {
    if (!is_admin_bar_showing()) return;

    $args = array(
        'id'    => 'highlight_ids',
        'title' => 'Highlight IDs',
        'href'  => '#',
        'meta'  => array('class' => 'highlight-ids-button')
    );
    $wp_admin_bar->add_node($args);
}
add_action('admin_bar_menu', 'highlight_ids_admin_bar_button', 100);

// Enqueue external JS & CSS
function highlight_ids_enqueue_assets() {
    if (is_admin()) return; // Only run on frontend

    $plugin_url = plugin_dir_url(__FILE__);
    $target_classes = esc_js(get_option('highlight_ids_target_classes', '.example-container'));

    wp_enqueue_script('highlight-ids-script', $plugin_url . 'assets/highlight-ids.js', array('jquery'), null, true);
    wp_localize_script('highlight-ids-script', 'highlightIDs', array('targetClasses' => $target_classes));

    wp_enqueue_style('highlight-ids-style', $plugin_url . 'assets/highlight-ids.css');
}
add_action('wp_enqueue_scripts', 'highlight_ids_enqueue_assets');
