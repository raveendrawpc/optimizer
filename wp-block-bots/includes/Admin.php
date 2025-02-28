<?php

namespace WPBlockBots;

class Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }

    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'settings_page_wp-block-bots') {
            return;
        }
        wp_enqueue_script('wp-block-bots-admin', plugin_dir_url(__FILE__) . '../assets/admin.js', [], '1.0', true);
    }

    public function add_settings_page() {
        add_options_page(
            'Block Bots',
            'Block Bots',
            'manage_options',
            'wp-block-bots',
            [$this, 'settings_page_html']
        );
    }

    public function register_settings() {
        register_setting(
            'wp_block_bots_group',
            'blocked_bots',
            [
                'sanitize_callback' => [$this, 'sanitize_bot_list']
            ]
        );
        register_setting(
            'wp_block_bots_group',
            'blocked_ips',
            [
                'sanitize_callback' => [$this, 'sanitize_ip_list']
            ]
        );
        register_setting(
            'wp_block_bots_group',
            'custom_headers',
            [
                'sanitize_callback' => [$this, 'sanitize_custom_headers']
            ]
        );
    }

    // Sanitize bot names (Remove special characters)
    public function sanitize_bot_list($input) {
        $botArray = explode(',', $input);
        $cleanBots = array_map(function ($bot) {
            return preg_replace('/[^a-zA-Z0-9\-]/', '', trim($bot));
        }, $botArray);

        return implode(',', array_filter($cleanBots));
    }

    public function sanitize_ip_list($input) {
        // Sanitize IP list - allow only valid IPs separated by commas
        $ips = explode(',', $input);
        $cleanIps = array_map('trim', $ips);
        return implode(',', array_filter($cleanIps, function ($ip) {
            return filter_var($ip, FILTER_VALIDATE_IP);
        }));
    }

    public function sanitize_custom_headers($input) {
        $headers = explode(',', $input); // Split into individual key-value pairs
        $cleanHeaders = [];
        foreach ($headers as $header) {
            $parts = explode(':', $header, 2); // Split key and value
            if (count($parts) == 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                $cleanHeaders[] = $key . ':' . $value; // Store the header as a string
            }
        }
        return implode(',', $cleanHeaders); // Return as a comma-separated string
    }

    public function settings_page_html() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        require_once plugin_dir_path(__FILE__) . '../views/settings-page.php';
    }
}
