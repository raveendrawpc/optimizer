<?php

function fix_autoload_options() {
    // Check if the query string parameter is present
    if (isset($_GET['fix_autoload']) && $_GET['fix_autoload'] === 'true') {
        global $wpdb;

        // Security: Only allow admins to run this
        if (!current_user_can('manage_options')) {
            exit('Unauthorized access');
        }

        // SQL Query: Set autoload to 'no' except for required options
        $wpdb->query("
            UPDATE {$wpdb->options} 
            SET autoload = 'no' 
            WHERE autoload = 'yes' 
            AND option_name NOT IN (
                'siteurl', 
                'home', 
                'blogname', 
                'blogdescription', 
                'admin_email', 
                'default_category', 
                'users_can_register', 
                'start_of_week', 
                'wp_user_roles',
                'permalink_structure', 
                'upload_path', 
                'upload_url_path', 
                'timezone_string',
                'gmt_offset'
            )
        ");

        // Optimize the table
        $wpdb->query("OPTIMIZE TABLE {$wpdb->options}");

        // Display success message
        wp_die("Autoloaded options have been updated!");
        exit; // Stop further execution
    }
}

// Hook into WordPress
add_action('init', 'fix_autoload_options');
