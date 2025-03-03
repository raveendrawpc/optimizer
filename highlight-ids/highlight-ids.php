<?php

/**
 * Plugin Name: Highlight IDs in Target Containers
 * Description: Adds a button to the admin bar to outline elements with an ID inside user-defined containers.
 * Version: 1.1
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

// Inject JavaScript & CSS into the footer
function highlight_ids_inline_script() {
    $target_classes = get_option('highlight_ids_target_classes', '.example-container');
    $target_classes = esc_js($target_classes); // Ensure safe output for JS
?>
    <script>
        jQuery(document).ready(function($) {
            $(document).on('click', '#wp-admin-bar-highlight_ids a', function(e) {
                e.preventDefault();
                $('<?php echo $target_classes; ?> [id]').each(function() {
                    $(this).toggleClass('highlight-outline');
                });
            });
        });
    </script>
    <style>
        .highlight-outline {
            outline: 1px dotted red !important;
            position: relative;
        }

        .highlight-outline::after {
            content: attr(id);
            position: absolute;
            top: -20px;
            left: 0;
            background: rgba(255, 0, 0, 0.8);
            color: white;
            font-family: Monaco, monospace;
            font-size: 12px;
            padding: 0px 7px;
            border-radius: 3px;
            white-space: nowrap;
            z-index: 9999;
        }
    </style>
<?php
}
add_action('wp_footer', 'highlight_ids_inline_script', 100);
