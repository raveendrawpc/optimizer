<?php

/**
 * Plugin Name: Hreflang Multisite
 * Description: Adds hreflang tags to the head for multisite configurations (for blog ID 1 and blog ID 3).
 * Version: 1.1
 * Author: Rabindra Pantha
 * License: GPL2
 */

// Define a unique prefix for the plugin based on its slug
define('HREFLANG_MULTISITE_PREFIX', 'hreflang_multisite_');

// Hook into the wp_head action to inject hreflang tags and canonical link
function hreflang_multisite_add_tags() {
    // Get the current blog ID
    $blog_id = get_current_blog_id();

    // Define the URLs for the global and AU versions
    $global_url = 'https://cruiser.naphix.com/';
    $au_url = 'https://cruiser-au.naphix.com/';

    // Get the current page URL for canonical tag
    $current_url = home_url(add_query_arg(null, null));

    // Check the current blog ID and add hreflang accordingly
    if ($blog_id == 1) { // cruiser.naphix.com (Blog ID 1)
        // Output the global site's hreflang tags
        echo '<link rel="alternate" hreflang="en" href="' . esc_url($current_url) . '" />' . "\n";
        echo '<link rel="alternate" hreflang="en-AU" href="' . esc_url($au_url) . '" />' . "\n";
    } elseif ($blog_id == 3) { // cruiser-au.naphix.com (Blog ID 3)
        // Output the AU site's hreflang tags
        echo '<link rel="alternate" hreflang="en-AU" href="' . esc_url($current_url) . '" />' . "\n";
        echo '<link rel="alternate" hreflang="en" href="' . esc_url($global_url) . '" />' . "\n";
    }

    // Add canonical link tag for the current page
    echo '<link rel="canonical" href="' . esc_url($current_url) . '" />' . "\n";
}

// Hook the function into the wp_head action using the unique prefix
add_action('wp_head', HREFLANG_MULTISITE_PREFIX . 'add_tags');
