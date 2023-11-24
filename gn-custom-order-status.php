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
 * Description:   Add custom order status to woocommerce
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
define( 'GNCUSTOMOR_NAME',			'GN Custom Order Status' );

// Plugin version
define( 'GNCUSTOMOR_VERSION',		'1.0.0' );

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
