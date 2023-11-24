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

/**
 * Load the main class for the core functionality
 */
require_once GNCUSTOMOR_PLUGIN_DIR . 'core/class-gn-custom-order-status.php';

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
    gncy_log_to_file( 'Order Status Change: Old Status - ' . $old_status . ', New Status - ' . $new_status );

    if ( 'wc-order-delivered' === $new_status ) {
        $subject = 'Your Order is Delivered';
        $heading = 'Order Delivered';
        $message = 'Your order has been delivered. Thank you for shopping with us!';

        // You can customize the email template as needed, including the text before the order table.
        $email = WC()->mailer()->emails['WC_Email_Customer_Completed_Order'];
        $email_heading = $email->get_option( 'heading', $heading );
        $email_subject = $email->get_option( 'subject', $subject );

        // Get the default message
        $default_message = WC()->mailer()->wrap_message(
            $email_heading,
            $message . "\n\n" . '{order_table}',
            $email->get_option( 'email_type' ),
            $email->get_option( 'email_heading' )
        );

        // Try to send the email and log errors if any
        try {
            // Set the email content
            $email->heading = $email_heading;
            $email->subject = $email_subject;
            $email->message = $default_message;

            // Send the email
            $email->trigger( $order_id );

            // Log a message to the custom log file
            gncy_log_to_file( 'Order Delivered Email Triggered for Order ID: ' . $order_id );
        } catch ( Exception $e ) {
            // Log the error message
            gncy_log_to_file( 'Error sending Order Delivered Email for Order ID ' . $order_id . ': ' . $e->getMessage() );
        }
    }
}
add_action( 'woocommerce_order_status_changed', 'gncy_send_order_delivered_email', 10, 4 );
