<?php

namespace RabindraPantha\ManuallyQuotedShipping;

/*
Plugin Name: Manually Quoted Shipping Manager
Description: A plugin to manage "Manually Quoted" shipping fees and send updated order emails.
Version: 1.0
Author: Rabindra Pantha
*/

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

use WC_Order;

class ManuallyQuotedShipping {

    public function __construct() {
        add_action('admin_menu', [$this, 'addMenuPage']);
        add_action('wp_ajax_update_shipping_fee', [$this, 'handleAjaxUpdateShippingFee']);
    }

    public function addMenuPage() {
        add_menu_page(
            'Manually Quoted Shipping',
            'Quoted Shipping',
            'manage_woocommerce',
            'manually-quoted-shipping',
            [$this, 'renderSettingsPage'],
            'dashicons-email-alt',
            56
        );
    }

    public function renderSettingsPage() {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        $orders = wc_get_orders([
            'limit' => -1,
            'status' => 'any'
        ]);

        echo '<div class="wrap"><h1>All Orders</h1>';
        echo '<table class="wp-list-table widefat striped">';
        echo '<thead><tr><th>Order ID</th><th>Customer</th><th>Total</th><th>Shipping Methods</th><th>Shipping Fee</th><th>Action</th></tr></thead><tbody>';

        foreach ($orders as $order) {
            $this->renderOrderRow($order);
        }

        echo '</tbody></table>';
        $this->enqueueScripts();
        echo '</div>';
    }

    private function enqueueScripts() {
        echo '<script>
            jQuery(document).ready(function($) {
                $(document).on("submit", ".update-shipping-form", function(event) {
                    event.preventDefault();
                    var form = $(this);
                    var data = form.serialize();
                    $.post(ajaxurl, data, function(response) {
                        alert(response.data.message);
                        if (response.success) {
                            location.reload();
                        }
                    });
                });
            });
        </script>';
    }

    public function handleAjaxUpdateShippingFee() {
        if (!current_user_can('manage_woocommerce') || !isset($_POST['order_id'])) {
            wp_send_json_error(['message' => 'Unauthorized or missing order ID.']);
        }

        $order_id = intval($_POST['order_id']);
        $shipping_fee = floatval($_POST['shipping_fee']);
        $order = wc_get_order($order_id);

        if ($order) {
            $this->updateOrderShippingFee($order, $shipping_fee);
            $this->createSeparateShippingFeeOrder($order_id, $shipping_fee);
            wc()->mailer()->emails['WC_Email_Customer_Invoice']->trigger($order_id);
            wp_send_json_success(['message' => 'Order updated and email sent.']);
        } else {
            wp_send_json_error(['message' => 'Failed to update order.']);
        }
    }

    private function updateOrderShippingFee(WC_Order $order, $shipping_fee) {
        $shipping_items = $order->get_items('shipping');
        if (!empty($shipping_items)) {
            foreach ($shipping_items as $item) {
                $item->set_total($shipping_fee);
                $item->save();
            }
        }

        $order->set_shipping_total($shipping_fee);
        $order->calculate_totals();
        $order->save();
    }

    private function createSeparateShippingFeeOrder($original_order_id, $shipping_fee) {
        $original_order = wc_get_order($original_order_id);
        $customer_id = $original_order->get_customer_id();
        $billing_address = $original_order->get_address('billing');
        $shipping_address = $original_order->get_address('shipping');

        $new_order = wc_create_order(['customer_id' => $customer_id]);
        $new_order->set_address($billing_address, 'billing');
        $new_order->set_address($shipping_address, 'shipping');

        $item = new \WC_Order_Item_Product();
        $item->set_product_id(51); // Assuming 51 is the product ID for the shipping fee
        $item->set_quantity(1);
        $item->set_subtotal($shipping_fee);
        $item->set_total($shipping_fee);
        $new_order->add_item($item);

        $new_order->calculate_totals();
        $new_order->update_status('pending');
        $new_order->save();
    }

    private function renderOrderRow(WC_Order $order) {
        $order_id = $order->get_id();
        $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
        $total = wc_price($order->get_total());
        $shipping_total = wc_price($order->get_shipping_total());
        $shipping_methods = $this->getShippingMethods($order);

        echo '<tr>';
        echo "<td>#{$order_id}</td>";
        echo "<td>{$customer_name}</td>";
        echo "<td>{$total}</td>";
        echo "<td>{$shipping_methods}</td>";
        echo "<td>{$shipping_total}</td>";
        echo '<td>';
        echo '<form method="POST" class="update-shipping-form">';
        echo '<input type="hidden" name="action" value="update_shipping_fee">';
        echo '<input type="hidden" name="order_id" value="' . $order_id . '">';
        echo '<input type="number" step="0.01" name="shipping_fee" placeholder="Enter shipping fee" required>';
        echo '<button type="submit" class="button">Update & Send Email</button>';
        echo '</form></td>';
        echo '</tr>';
    }

    private function getShippingMethods(WC_Order $order) {
        $shipping_items = $order->get_items('shipping');
        $methods = [];

        foreach ($shipping_items as $item) {
            $methods[] = $item->get_name();
        }

        return implode(', ', $methods);
    }
}

new ManuallyQuotedShipping();
