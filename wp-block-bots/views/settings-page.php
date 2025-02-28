<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h2>Block Bots</h2>
    <form method="post" action="options.php">
        <?php
        settings_fields('wp_block_bots_group');
        do_settings_sections('wp_block_bots_group');
        wp_nonce_field('wp_block_bots_action', 'wp_block_bots_nonce');
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="blocked_bots">Enter bot names (comma separated):</label>
                </th>
                <td>
                    <textarea id="blocked_bots" name="blocked_bots" class="large-text"><?php echo esc_textarea(get_option('blocked_bots', '')); ?></textarea>
                    <p class="description">Example: Googlebot, Bingbot, BadBot</p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="blocked_ips">Enter IP addresses to block (comma separated):</label>
                </th>
                <td>
                    <textarea id="blocked_ips" name="blocked_ips" class="large-text"><?php echo esc_textarea(get_option('blocked_ips', '')); ?></textarea>
                    <p class="description">Example: 192.168.1.1, 203.0.113.45</p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="custom_headers">Enter custom headers (key:value pairs, comma separated):</label>
                </th>
                <td>
                    <textarea id="custom_headers" name="custom_headers" class="large-text"><?php echo esc_textarea(get_option('custom_headers', '')); ?></textarea>
                    <p class="description">Example: X-Frame-Options:DENY, X-Content-Type-Options:nosniff</p>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
