<?php
/**
 * GN Custom Order Status
 *
 * @package       GNCUSTOMOR
 * @author        George Nicolaou
 * @license       gplv2
 * @version       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:   GN Custom Order Status
 * Plugin URI:    https://www.georgenicolaou.me/plugins/gn-custom-order-status
 * Description:   Add custom order status to WooCommerce
 * Version:       1.0.0
 * Author:        George Nicolaou
 * Author URI:    https://www.georgenicolaou.me/
 * Text Domain:   gn-custom-order-status
 * Domain Path:   /languages
 * License:       GPLv2
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with GN Custom Order Status. If not, see <https://www.gnu.org/licenses/gpl-2.0.html/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Plugin name
define( 'GNCUSTOMOR_NAME', 'GN Custom Order Status' );

// Plugin version
define( 'GNCUSTOMOR_VERSION', '1.0.0' );

// Plugin Root File
define( 'GNCUSTOMOR_PLUGIN_FILE', __FILE__ );

// Plugin base
define( 'GNCUSTOMOR_PLUGIN_BASE', plugin_basename( GNCUSTOMOR_PLUGIN_FILE ) );

// Plugin Folder Path
define( 'GNCUSTOMOR_PLUGIN_DIR', plugin_dir_path( GNCUSTOMOR_PLUGIN_FILE ) );

// Plugin Folder URL
define( 'GNCUSTOMOR_PLUGIN_URL', plugin_dir_url( GNCUSTOMOR_PLUGIN_FILE ) );

// Add a custom log file in your plugin directory
define( 'GNCUSTOMOR_LOG_FILE', GNCUSTOMOR_PLUGIN_DIR . 'debug-log.txt' );

// Function to log messages to the custom log file
function gncy_log_to_file( $message ) {
    file_put_contents( GNCUSTOMOR_LOG_FILE, date( 'Y-m-d H:i:s' ) . ' - ' . $message . PHP_EOL, FILE_APPEND );
}

function gncy_include_custom_email_class() {
    if ( class_exists( 'WC_Email' ) && ! class_exists( 'WC_Email_Custom_Order_Delivered' ) ) {
        // Try including the class file
        require_once GNCUSTOMOR_PLUGIN_DIR . 'core/class-wc-email-custom-order-delivered.php';

        // Add a filter to register the email class
        add_filter( 'woocommerce_email_classes', 'gncy_add_custom_email_class' );

        // If your email class is still not recognized, try delaying the registration
        add_action('init', 'gncy_add_custom_email_class_late', 9999);
    }
}


function gncy_add_custom_email_class( $email_classes ) {
    $email_classes['WC_Email_Custom_Order_Delivered'] = new WC_Email_Custom_Order_Delivered();
    return $email_classes;
}

// Late registration in case of issues
function gncy_add_custom_email_class_late() {
    add_filter( 'woocommerce_email_classes', 'gncy_add_custom_email_class' );
}

/**
 * Load the main class for the core functionality
 */
require_once GNCUSTOMOR_PLUGIN_DIR . 'core/class-gn-custom-order-status.php';

// Load the email class
require_once GNCUSTOMOR_PLUGIN_DIR . 'core/class-wc-email-custom-order-delivered.php';

/**
 * The main function to load the only instance
 * of our master class.
 *
 * @author  George Nicolaou
 * @since   1.0.0
 * @return  object|Gn_Custom_Order_Status
 */
function gncy_GNCUSTOMOR() {
    return Gn_Custom_Order_Status::instance();
}

gncy_GNCUSTOMOR();

require 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/GeorgeWebDevCy/gn-custom-order-status',
    __FILE__,
    'gn-custom-order-status'
);

// Set the branch that contains the stable release.
$myUpdateChecker->setBranch('main');

// Add custom order status "Order Delivered"
function gncy_add_custom_order_status() {
    register_post_status(
        'wc-order-delivered',
        array(
            'label'                     => _x( 'Order Delivered', 'WooCommerce Order status', 'woocommerce' ),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'Order Delivered <span class="count">(%s)</span>', 'Order Delivered <span class="count">(%s)</span>', 'woocommerce' )
        )
    );

    // Add the custom order status to the list of order statuses
    add_filter( 'wc_order_statuses', 'gncy_add_custom_order_statuses' );
}
add_action( 'init', 'gncy_add_custom_order_status' );

// Add custom order status to the list
function gncy_add_custom_order_statuses( $order_statuses ) {
    $order_statuses['wc-order-delivered'] = _x( 'Order Delivered', 'WooCommerce Order status', 'woocommerce' );
    return $order_statuses;
}

// Send email on order status change to "Order Delivered"
function gncy_send_order_delivered_email( $order_id, $old_status, $new_status, $order ) {
    $new_status = 'wc-' . $new_status;
    gncy_log_to_file( 'Order Status Change: Old Status - ' . $old_status . ', New Status - ' . $new_status );
    gncy_log_to_file( 'Order Details: ' . print_r( $order, true ) );

    if ( 'wc-order-delivered' === $new_status ) {
        $subject = 'Your Order is Delivered';
        $heading = 'Order Delivered';
        $message = 'Your order has been delivered. Thank you for shopping with us!';

        // Your custom template file
        $template = GNCUSTOMOR_PLUGIN_DIR . 'woocommerce/emails/custom-order-delivered.php';

        // Try to send the email and log errors if any
        try {
            // Ensure the email class is loaded
            gncy_include_custom_email_class();

            // You can use the WooCommerce email class to send the email
            $email = WC()->mailer()->get_emails()['WC_Email_Custom_Order_Delivered'];

            // Check if the email class exists
            if ($email) {
                // Set the email content
                $email->heading = $heading;
                $email->subject = $subject;

                // Load the custom template
                ob_start();
                include $template;
                $wrapped_message = ob_get_clean();

                // Set the email content
                $email->message = $wrapped_message;

                // Send the email
                $email->trigger( $order_id );

                // Log a message to the custom log file along with email content
                gncy_log_to_file( 'Order Delivered Email Triggered for Order ID: ' . $order_id . ', Email Content: ' . $wrapped_message );
            } else {
                // Log an error if the email class is not found
                gncy_log_to_file( 'Error: WC_Email_Custom_Order_Delivered class not found.' );
            }
        } catch ( Exception $e ) {
            // Log the error message
            gncy_log_to_file( 'Error sending Order Delivered Email for Order ID ' . $order_id . ': ' . $e->getMessage() );
        }
    }
}
add_action( 'woocommerce_order_status_changed', 'gncy_send_order_delivered_email', 10, 4 );
