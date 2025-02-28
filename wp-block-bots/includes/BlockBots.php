<?php

namespace WPBlockBots;

class BlockBots {
    public function __construct() {
        add_action('send_headers', [$this, 'block_bots']);
        add_filter('robots_txt', [$this, 'modify_robots_txt'], 10, 2);
    }

    public function block_bots() {
        if (is_admin()) {
            return; // Do not block in WP Admin area.
        }

        $blockedBots = get_option('blocked_bots', '');
        $blockedIps = get_option('blocked_ips', '');
        $customHeaders = get_option('custom_headers', ''); // Get custom headers as a string

        // If no bots or IPs are set, don't block anything
        if (empty($blockedBots) && empty($blockedIps)) {
            return;
        }

        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $clientIP = $_SERVER['REMOTE_ADDR'] ?? '';

        // Block known bad bots by User-Agent
        $botList = array_map('trim', explode(',', $blockedBots));
        foreach ($botList as $bot) {
            if (stripos($userAgent, $bot) !== false) {
                header('HTTP/1.1 403 Forbidden');
                exit;
            }
        }

        // Block known bad IPs
        if (!empty($blockedIps)) {
            $ipList = array_map('trim', explode(',', $blockedIps));
            if (in_array($clientIP, $ipList)) {
                header('HTTP/1.1 403 Forbidden');
                exit;
            }
        }

        // Add custom headers if provided
        if ($customHeaders) {
            $headerList = explode(',', $customHeaders); // Split string into array
            foreach ($headerList as $header) {
                $headerParts = explode(':', $header, 2);
                if (count($headerParts) == 2) {
                    $key = trim($headerParts[0]);
                    $value = trim($headerParts[1]);
                    header("$key: $value"); // Add header
                }
            }
        }

        // Extra Security Headers
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
    }

    public function modify_robots_txt($output, $public) {
        $blockedBots = get_option('blocked_bots', '');
        if (!$blockedBots) return $output;

        $botList = array_map('trim', explode(',', $blockedBots));
        foreach ($botList as $bot) {
            $output .= "User-agent: $bot\nDisallow: /\n";
        }

        return $output;
    }
}
