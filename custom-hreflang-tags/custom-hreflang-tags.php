<?php
/*
Plugin Name: Custom Hreflang Tags for Multisite
Description: Adds hreflang tags for international and Australian site versions in a WordPress multisite network.
Version: 1.0
Author: Rabindra Pantha
*/

function add_custom_hreflang_tags_multisite() {
    // Define blog IDs for the international and Australian sites
    $intl_blog_id = 1; // International site
    $aus_blog_id = 3;  // Australian site

    // Get the URLs for each site based on blog IDs
    $intl_site_details = get_blog_details($intl_blog_id);
    $aus_site_details = get_blog_details($aus_blog_id);

    // Check if the current site matches the international or Australian site
    if (get_current_blog_id() == $intl_blog_id) {
        // We are on the International site
        echo '<link rel="alternate" href="' . esc_url($intl_site_details->siteurl . $_SERVER['REQUEST_URI']) . '" hreflang="en" />' . "\n";
        echo '<link rel="alternate" href="' . esc_url($aus_site_details->siteurl . $_SERVER['REQUEST_URI']) . '" hreflang="en-AU" />' . "\n";
        echo '<link rel="alternate" href="' . esc_url($intl_site_details->siteurl . $_SERVER['REQUEST_URI']) . '" hreflang="x-default" />' . "\n";
    } elseif (get_current_blog_id() == $aus_blog_id) {
        // We are on the Australian site
        echo '<link rel="alternate" href="' . esc_url($intl_site_details->siteurl . $_SERVER['REQUEST_URI']) . '" hreflang="en" />' . "\n";
        echo '<link rel="alternate" href="' . esc_url($aus_site_details->siteurl . $_SERVER['REQUEST_URI']) . '" hreflang="en-AU" />' . "\n";
        echo '<link rel="alternate" href="' . esc_url($aus_site_details->siteurl . $_SERVER['REQUEST_URI']) . '" hreflang="x-default" />' . "\n";
    }
}

// Set the priority to 1 to add tags as early as possible in the head
add_action('wp_head', 'add_custom_hreflang_tags_multisite', 1);
