<?php
/**
 * GN Custom Order Status
 *
 * @package       GNCUSTOMOR
 * @author        George Nicolaou
 * @license       gplv2
 * @version       1.0.1
 *
 * @wordpress-plugin
 * Plugin Name:   GN Custom Order Status
 * Plugin URI:    https://www.georgenicolaou.me/plugins/gn-custom-order-status
 * Description:   Add custom order status to woocommerce
 * Version:       1.0.1
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
define( 'GNCUSTOMOR_NAME',			'GN Custom Order Status' );

// Plugin version
define( 'GNCUSTOMOR_VERSION',		'1.0.1' );

// Plugin Root File
define( 'GNCUSTOMOR_PLUGIN_FILE',	__FILE__ );

// Plugin base
define( 'GNCUSTOMOR_PLUGIN_BASE',	plugin_basename( GNCUSTOMOR_PLUGIN_FILE ) );

// Plugin Folder Path
define( 'GNCUSTOMOR_PLUGIN_DIR',	plugin_dir_path( GNCUSTOMOR_PLUGIN_FILE ) );

// Plugin Folder URL
define( 'GNCUSTOMOR_PLUGIN_URL',	plugin_dir_url( GNCUSTOMOR_PLUGIN_FILE ) );

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
function GNCUSTOMOR() {
	return Gn_Custom_Order_Status::instance();
}

GNCUSTOMOR();
require 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/GeorgeWebDevCy/gn-custom-order-status',
	__FILE__,
	'gn-custom-order-status'
);

function gncy_register_custom_order_delivered_status() {
	register_post_status( 'wc-delivered', array(
		'label'                     => _x( 'Delivered', 'Order status', 'woocommerce' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Delivered <span class="count">(%s)</span>', 'Delivered <span class="count">(%s)</span>', 'woocommerce' )
	) );
}
add_action( 'init', 'gncy_register_custom_order_delivered_status' );

function gncy_add_custom_order_delivered_status_to_order_statuses( $order_statuses ) {
	$new_order_statuses = array();
	// add new order status after processing
	foreach ( $order_statuses as $key => $status ) {
		$new_order_statuses[ $key ] = $status;
		if ( 'wc-processing' === $key ) {
			$new_order_statuses['wc-delivered'] = __( 'Delivered', 'woocommerce' );
		}
	}
	return $new_order_statuses;
}

add_filter( 'wc_order_statuses', 'gncy_add_custom_order_delivered_status_to_order_statuses' );

//add custom order status to admin order list bulk actions
function gncy_add_custom_order_status_bulk_actions( $bulk_actions ) {
	$bulk_actions['mark_delivered'] = __( 'Mark Delivered', 'woocommerce' );
	return $bulk_actions;
}
add_filter( 'bulk_actions-edit-shop_order', 'gncy_add_custom_order_status_bulk_actions' );

//add custom order status to dropdown in order edit screen
function gncy_add_custom_order_status_to_dropdown( $order_statuses ) {
	$new_order_statuses = array();
	// add new order status after processing
	foreach ( $order_statuses as $key => $status ) {
		$new_order_statuses[ $key ] = $status;
		if ( 'wc-processing' === $key ) {
			$new_order_statuses['wc-delivered'] = __( 'Delivered', 'woocommerce' );
		}
	}
	return $new_order_statuses;
}
add_filter( 'wc_order_statuses', 'gncy_add_custom_order_status_to_dropdown' );
  
function gncy_change_woocommerce_strings_emails( $translated, $untranslated, $domain ) {
   if ( 'woocommerce' === $domain ) {   
      
	  $translated = str_ireplace( '<p>Hold onto your excitement because your order is officially in transit to you! 🎉</p>
	  <p>If your shipping includes a tracking number, find it below to follow your order\'s journey to your door.</p>
	  <p>Now, let\'s address the elephant in the room - delays. Sometimes, despite our best efforts, orders like to take unexpected detours. But rest assured, your order will arrive eventually. We promise it\'s not plotting a grand escape 😋</p>
	  <p>Kindly be aware that we operate multiple warehouses strategically located to improve efficiency and shipping times. Rest assured, your order will be dispatched from the most suitable warehouse at that specific moment.</p>
	  <p>Please don’t hesitate to reach out to us at support@planetofgadgets.com, and we’ll do our utmost to resolve any concerns promptly and ensure you have a positive shopping experience with us. Your happiness is our priority.</p>
	  <p>We greatly appreciate your trust in us. Your order is en route, and we can\'t wait for you to welcome it with open arms.</p>', 'test', $untranslated ); // EDIT

	  $translated = str_ireplace( 'We have finished processing your order.', 'Your order has been delivered', $untranslated ); // EDIT
   }

	  
   return $translated;
}




//send email notification when order status is changed to delivered with custom email subject and content using a custom email templated based on the completed email notification
function gncy_send_custom_email_notification( $order_id, $old_status, $new_status ) {
	if ( $new_status == 'delivered' ) {
		$order = wc_get_order( $order_id );
		$wc_emails = WC()->mailer()->get_emails(); // Get all WC_emails objects instances
		$wc_emails['WC_Email_Customer_Completed_Order']->heading = 'Your order is delivered'; // Changing the email heading
		$wc_emails['WC_Email_Customer_Completed_Order']->subject = 'Your order is delivered'; // Changing the email subject
		$wc_emails['WC_Email_Customer_Completed_Order']->settings['heading'] = 'Your order is delivered';
		$wc_emails['WC_Email_Customer_Completed_Order']->settings['subject'] = 'Your order is delivered';
		add_filter( 'gettext', 'gncy_change_woocommerce_strings_emails', 9999, 3 );
		$wc_emails['WC_Email_Customer_Completed_Order']->trigger( $order_id ); // Sending the email

		
}

}
add_action( 'woocommerce_order_status_changed', 'gncy_send_custom_email_notification', 10, 3 );

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('main');