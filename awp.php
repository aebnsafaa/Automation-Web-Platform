<?php
/*
    *Plugin Name: Wawp - Instant Order Notifications & OTP Verification for Woocommerce
    *Version: 2.0.14
    *Plugin URI: https://wawp.net/whatsapp-for-woocommerce/
    *Description: Notify your customers about orders and abandoned carts via "WhatsApp" or "WhatsApp Business", for woocommerce system and enable Login & register for user by otp Code for Wordpress
    *Author: wawp.net
    *Author URI: https://wawp.net
    *Text Domain: AWP
    *Domain Path: /languages
*/

ob_start();
register_activation_hook(__FILE__, function() {
    $output = ob_get_clean();
    if ($output) {
        file_put_contents(WP_CONTENT_DIR . '/wawp_activation.txt', $output);
    }
});


function awp_check_woocommerce_active() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'awp_woocommerce_inactive_notice');
        deactivate_plugins(plugin_basename(__FILE__));
    }
}
add_action('admin_init', 'awp_check_woocommerce_active');

// Admin notice if WooCommerce is not active
function awp_woocommerce_inactive_notice() {
    $install_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=woocommerce'), 'install-plugin_woocommerce');
    ?>
    <div class="notice notice-error">
        <p><?php _e('Wawp - Instant Order Notifications & OTP Verification for WooCommerce requires WooCommerce to be installed and active.', 'awp'); ?></p>
        <p><a href="<?php echo esc_url($install_url); ?>" class="button button-primary"><?php _e('Install WooCommerce Now', 'awp'); ?></a></p>
    </div>
    <?php
}

define( 'WWO_NAME', 'awp' );
define( 'WWO_VERSION', '1.0.0' );
define( 'WWO_URL', plugin_dir_url( __FILE__ ) );
define( 'WWO_PATH', plugin_dir_path( __FILE__ ) );
define( 'WWO_DOMAIN', 'awp');

require 'awp-wwo.php';
new WWO();

add_action('admin_head', function(){
    ?>
    <style>
        li#toplevel_page_awp img {
            width: 18px;
        }
    </style>
    <?php
});

require_once 'awp-main.php';
require_once 'awp-ui.php';
require_once 'awp-logger.php';

$nno = new awp_Main;

function awp_load_textdomain() {
    load_plugin_textdomain( 'awp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'awp_load_textdomain' );

