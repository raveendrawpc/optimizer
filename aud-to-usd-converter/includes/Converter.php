<?php

namespace Rabindra\AUDToUSD;

use WP_Error;

class Converter {

    private $api_key;
    private $transient_name = 'aud_to_usd_rate';
    private $cache_expiration = 86400;  // 1 day cache expiration

    public function __construct($api_key) {
        $this->api_key = $api_key;
    }

    /**
     * Fetch exchange rate from API or cache, unless a manual rate is set.
     */
    public function get_exchange_rate() {
        // Check if a manual conversion rate is set
        $manual_rate = get_option('aud_to_usd_manual_conversion_rate', '');
        if ($manual_rate) {
            return (float) $manual_rate; // Return the manual rate if available
        }

        // Attempt to get the cached exchange rate or fetch it if not cached
        $rate = $this->get_cached_exchange_rate();

        // Return the rate as a float
        return (float) $rate;
    }

    public function get_cached_exchange_rate() {
        // Attempt to get the cached exchange rate
        $rate = get_transient($this->transient_name);

        // If no cached rate is found, fetch a new rate from the API
        if ($rate === false) {
            $rate = $this->fetch_exchange_rate_from_api();

            // Handle any errors from the API request
            if (is_wp_error($rate)) {
                return $rate;
            }

            // Cache the fetched rate for future use
            set_transient($this->transient_name, $rate, $this->cache_expiration);
        }

        return $rate;
    }

    /**
     * Fetch the AUD to USD conversion rate from the API.
     */
    private function fetch_exchange_rate_from_api() {
        $url = "https://v6.exchangerate-api.com/v6/{$this->api_key}/latest/AUD";
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            return new WP_Error('api_error', 'Failed to fetch exchange rate.');
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!isset($data['conversion_rates']['USD'])) {
            return new WP_Error('invalid_response', 'Invalid API response.');
        }

        return floatval($data['conversion_rates']['USD']);
    }

    /**
     * Convert AUD to USD with percentage deduction if specified.
     */
    public function convert_aud_to_usd($price, $product) {
        // Return early if price is empty
        if (empty($price)) {
            return $price;
        }

        // Check if product should be excluded from conversion
        if ($this->is_product_excluded($product)) {
            return $price; // Return original price if excluded
        }

        // Fetch the conversion rate
        $rate = $this->get_exchange_rate();
        if (is_wp_error($rate)) {
            return $price; // Return original price if there's an error fetching the rate
        }

        // Ensure price is a float for proper calculations
        $price = floatval($price);

        // Apply percentage deduction if specified
        $price = $this->apply_percentage_deduction($price);

        // Convert AUD to USD and return the new price
        return $price * $rate;
    }

    /**
     * Check if the product should be excluded from conversion.
     */
    private function is_product_excluded($product) {
        return $this->is_product_excluded_by_id($product) ||
            $this->is_product_excluded_by_meta($product) ||
            $this->is_product_excluded_by_category($product);
    }

    /**
     * Check if the product is excluded by its ID.
     */
    private function is_product_excluded_by_id($product) {
        $excluded_ids = get_option('aud_to_usd_exclude_product_ids', '');
        if (!empty($excluded_ids)) {
            $excluded_ids = array_map('trim', explode(',', $excluded_ids));
            return in_array($product->get_id(), $excluded_ids);
        }
        return false;
    }

    /**
     * Check if the product is excluded by meta key-value.
     */
    private function is_product_excluded_by_meta($product) {
        $meta_key = get_option('aud_to_usd_exclude_meta_key', '');
        $meta_value = get_option('aud_to_usd_exclude_meta_value', '');
        return !empty($meta_key) && !empty($meta_value) && get_post_meta($product->get_id(), $meta_key, true) == $meta_value;
    }

    /**
     * Check if the product is excluded by categories.
     */
    private function is_product_excluded_by_category($product) {
        $exclude_categories = get_option('aud_to_usd_exclude_categories', '');
        if (!empty($exclude_categories)) {
            $exclude_categories = array_map('trim', explode(',', $exclude_categories));
            $product_categories = wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'slugs']);
            return !empty(array_intersect($exclude_categories, $product_categories));
        }
        return false;
    }

    /**
     * Apply percentage deduction if specified in settings.
     */
    private function apply_percentage_deduction($price) {
        $deduction_percentage = get_option('aud_to_usd_percentage_deduction', 0);
        if (!empty($deduction_percentage)) {
            $deduction_factor = 1 - (floatval($deduction_percentage) / 100);
            $price *= $deduction_factor;
        }
        return $price;
    }
}
