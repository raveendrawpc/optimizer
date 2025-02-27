<?php

function truncate_action_scheduler_tables() {
    // Check if the query string parameter is present
    if (isset($_GET['truncate_scheduler']) && $_GET['truncate_scheduler'] === 'true') {
        global $wpdb;

        // Security: Only allow admins to run this
        if (!current_user_can('manage_options')) {
            exit('Unauthorized access');
        }

        // Truncate tables
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}actionscheduler_actions");
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}actionscheduler_logs");

        // Display success message
        wp_die("Action Scheduler tables have been truncated!");
        exit;
    }
}

// Hook into WordPress
add_action('init', 'truncate_action_scheduler_tables');
