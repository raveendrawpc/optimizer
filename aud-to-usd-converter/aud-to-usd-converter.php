<?php

/**
 * Plugin Name: AUD to USD Converter with Settings
 * Plugin URI: https://example.com/aud-to-usd-converter
 * Description: Convert AUD prices to USD with exclusion options and percentage deduction.
 * Version: 1.4
 * Author: Rabindra Pantha
 * Author URI: https://example.com
 * License: GPL2
 */

if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/vendor/autoload.php';

// Default API Key (fallback if not set in settings)
$api_key = get_option('aud_to_usd_api_key', '');
$converter = new \Rabindra\AUDToUSD\Converter($api_key);

add_filter('woocommerce_product_get_price', function ($price, $product) use ($converter) {
    return $converter->convert_aud_to_usd($price, $product);
}, 10, 2);

add_filter('woocommerce_product_get_regular_price', function ($price, $product) use ($converter) {
    return $converter->convert_aud_to_usd($price, $product);
}, 10, 2);

add_filter('woocommerce_product_get_sale_price', function ($price, $product) use ($converter) {
    return $converter->convert_aud_to_usd($price, $product);
}, 10, 2);

add_filter('woocommerce_product_variation_get_price', function ($price, $product) use ($converter) {
    return $converter->convert_aud_to_usd($price, $product);
}, 10, 2);

add_filter('woocommerce_product_variation_get_regular_price', function ($price, $product) use ($converter) {
    return $converter->convert_aud_to_usd($price, $product);
}, 10, 2);

// Apply conversion for variations' prices
add_filter('woocommerce_variation_prices_price', function ($price, $variation, $product) use ($converter) {
    return $converter->convert_aud_to_usd($price, $variation);
}, 10, 3);

add_filter('woocommerce_variation_prices_regular_price', function ($price, $variation, $product) use ($converter) {
    return $converter->convert_aud_to_usd($price, $variation);
}, 10, 3);

// Optionally apply conversion for sale price of variations
add_filter('woocommerce_variation_prices_sale_price', function ($price, $variation, $product) use ($converter) {
    return $converter->convert_aud_to_usd($price, $variation);
}, 10, 3);

add_filter('woocommerce_currency', function ($currency) {
    return 'USD';
});

add_action('admin_menu', 'aud_to_usd_add_settings_page');
function aud_to_usd_add_settings_page() {
    add_options_page(
        'AUD to USD Converter Settings',
        'AUD to USD Converter',
        'manage_options',
        'aud-to-usd-converter-settings',
        'aud_to_usd_render_settings_page'
    );
}

add_action('admin_init', 'aud_to_usd_register_settings');
function aud_to_usd_register_settings() {
    register_setting('aud_to_usd_settings_group', 'aud_to_usd_api_key');
    register_setting('aud_to_usd_settings_group', 'aud_to_usd_manual_conversion_rate');
    register_setting('aud_to_usd_settings_group', 'aud_to_usd_percentage_deduction');
    register_setting('aud_to_usd_settings_group', 'aud_to_usd_exclude_categories');
    register_setting('aud_to_usd_settings_group', 'aud_to_usd_exclude_meta_key');
    register_setting('aud_to_usd_settings_group', 'aud_to_usd_exclude_meta_value');
    register_setting('aud_to_usd_settings_group', 'aud_to_usd_exclude_product_ids');

    add_settings_section('aud_to_usd_section', 'Conversion Settings', null, 'aud-to-usd-converter-settings');

    add_settings_field('aud_to_usd_api_key', 'API Key', 'aud_to_usd_api_key_callback', 'aud-to-usd-converter-settings', 'aud_to_usd_section');
    add_settings_field('aud_to_usd_manual_conversion_rate', 'Manual Conversion Rate (AUD to USD)', 'aud_to_usd_manual_conversion_rate_callback', 'aud-to-usd-converter-settings', 'aud_to_usd_section');
    add_settings_field('aud_to_usd_percentage_deduction', 'Percentage Deduction', 'aud_to_usd_percentage_deduction_callback', 'aud-to-usd-converter-settings', 'aud_to_usd_section');
    add_settings_field('aud_to_usd_exclude_categories', 'Exclude Categories', 'aud_to_usd_exclude_categories_callback', 'aud-to-usd-converter-settings', 'aud_to_usd_section');
    add_settings_field('aud_to_usd_exclude_meta_key', 'Exclude Meta Key', 'aud_to_usd_exclude_meta_key_callback', 'aud-to-usd-converter-settings', 'aud_to_usd_section');
    add_settings_field('aud_to_usd_exclude_meta_value', 'Exclude Meta Value', 'aud_to_usd_exclude_meta_value_callback', 'aud-to-usd-converter-settings', 'aud_to_usd_section');
    add_settings_field('aud_to_usd_exclude_product_ids', 'Excluded Product IDs', 'aud_to_usd_exclude_product_ids_callback', 'aud-to-usd-converter-settings', 'aud_to_usd_section');
}

function aud_to_usd_render_settings_page() {
?>
    <div class="wrap">
        <h1>AUD to USD Converter Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('aud_to_usd_settings_group');
            do_settings_sections('aud-to-usd-converter-settings');
            submit_button();
            ?>
        </form>
    </div>
<?php
}

function aud_to_usd_api_key_callback() {
    $value = get_option('aud_to_usd_api_key', '');
    echo '<input type="text" name="aud_to_usd_api_key" value="' . esc_attr($value) . '" class="regular-text" />';
    echo '<p class="description">Please enter your API key for <a target="_blank" href="https://www.exchangerate-api.com/">https://www.exchangerate-api.com/</a> to retrieve the current AUD to USD exchange rate.</p>';
}

function aud_to_usd_manual_conversion_rate_callback() {
    // Get the manual conversion rate from the settings (if available)
    $value = get_option('aud_to_usd_manual_conversion_rate', '');

    // Fetch the latest exchange rate from the API
    $api_key = get_option('aud_to_usd_api_key', ''); // Get API key from settings
    $converter = new \Rabindra\AUDToUSD\Converter($api_key);
    $rate = $converter->get_cached_exchange_rate();

    // Check for errors in the rate
    if (is_wp_error($rate)) {
        $rate_display = 'Error fetching API rate';
    } else {
        $rate_display = number_format($rate, 4); // Format the rate to 4 decimal places
    }

    // Display the manual conversion rate input field
    echo '<input type="text" name="aud_to_usd_manual_conversion_rate" value="' . esc_attr($value) . '" class="regular-text" />';
    echo '<p class="description">Optional: Enter a custom AUD to USD conversion rate to override the API rate.</p>';

    // Display the current API rate fetched from the API
    echo '<p class="description">Current API Rate: <strong>' . esc_html($rate_display) . ' USD</strong></p>';
}

function aud_to_usd_percentage_deduction_callback() {
    $value = get_option('aud_to_usd_percentage_deduction', '');
    echo '<input type="number" step="0.01" name="aud_to_usd_percentage_deduction" value="' . esc_attr($value) . '" class="regular-text" /> %';
    echo '<p class="description">Specify a percentage (e.g., 10 for 10%) to reduce from the AUD price before converting to USD.</p>';
}

function aud_to_usd_exclude_categories_callback() {
    $value = get_option('aud_to_usd_exclude_categories', '');
    echo '<input type="text" name="aud_to_usd_exclude_categories" value="' . esc_attr($value) . '" class="regular-text" />';
    echo '<p class="description">Enter comma-separated category slugs to exclude from conversion.</p>';
}

function aud_to_usd_exclude_meta_key_callback() {
    $value = get_option('aud_to_usd_exclude_meta_key', '');
    echo '<input type="text" name="aud_to_usd_exclude_meta_key" value="' . esc_attr($value) . '" class="regular-text" />';
    echo '<p class="description">Specify a custom meta key to exclude products with a matching meta value from conversion.</p>';
}

function aud_to_usd_exclude_meta_value_callback() {
    $value = get_option('aud_to_usd_exclude_meta_value', '');
    echo '<input type="text" name="aud_to_usd_exclude_meta_value" value="' . esc_attr($value) . '" class="regular-text" />';
    echo '<p class="description">Specify the meta value to exclude products with the given meta key.</p>';
}

function aud_to_usd_exclude_product_ids_callback() {
    $value = get_option('aud_to_usd_exclude_product_ids', '');
    echo '<input type="text" name="aud_to_usd_exclude_product_ids" value="' . esc_attr($value) . '" class="regular-text" />';
    echo '<p class="description">Enter comma-separated product IDs to exclude from conversion.</p>';
}
