<?php
/**
 * Plugin Name: WP Block Bots
 * Description: Block unwanted crawlers and bots via a settings page.
 * Version: 1.1
 * Author: Rabindra Pantha
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

require_once __DIR__ . '/vendor/autoload.php';

use WPBlockBots\Admin;
use WPBlockBots\BlockBots;

class WP_Block_Bots {
    public function __construct() {
        new Admin();
        new BlockBots();
    }
}

new WP_Block_Bots();
