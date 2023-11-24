<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'Gn_Custom_Order_Status' ) ) :

	/**
	 * Main Gn_Custom_Order_Status Class.
	 *
	 * @package		GNCUSTOMOR
	 * @subpackage	Classes/Gn_Custom_Order_Status
	 * @since		1.0.0
	 * @author		George Nicolaou
	 */
	final class Gn_Custom_Order_Status {

		/**
		 * The real instance
		 *
		 * @access	private
		 * @since	1.0.0
		 * @var		object|Gn_Custom_Order_Status
		 */
		private static $instance;

		/**
		 * GNCUSTOMOR helpers object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Gn_Custom_Order_Status_Helpers
		 */
		public $helpers;

		/**
		 * GNCUSTOMOR settings object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Gn_Custom_Order_Status_Settings
		 */
		public $settings;

		/**
		 * Throw error on object clone.
		 *
		 * Cloning instances of the class is forbidden.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to clone this class.', 'gn-custom-order-status' ), '1.0.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to unserialize this class.', 'gn-custom-order-status' ), '1.0.0' );
		}

		/**
		 * Main Gn_Custom_Order_Status Instance.
		 *
		 * Insures that only one instance of Gn_Custom_Order_Status exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @access		public
		 * @since		1.0.0
		 * @static
		 * @return		object|Gn_Custom_Order_Status	The one true Gn_Custom_Order_Status
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Gn_Custom_Order_Status ) ) {
				self::$instance					= new Gn_Custom_Order_Status;
				self::$instance->base_hooks();
				self::$instance->includes();
				self::$instance->helpers		= new Gn_Custom_Order_Status_Helpers();
				self::$instance->settings		= new Gn_Custom_Order_Status_Settings();

				//Fire the plugin logic
				new Gn_Custom_Order_Status_Run();

				/**
				 * Fire a custom action to allow dependencies
				 * after the successful plugin setup
				 */
				do_action( 'GNCUSTOMOR/plugin_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Include required files.
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  void
		 */
		private function includes() {
			require_once GNCUSTOMOR_PLUGIN_DIR . 'core/includes/classes/class-gn-custom-order-status-helpers.php';
			require_once GNCUSTOMOR_PLUGIN_DIR . 'core/includes/classes/class-gn-custom-order-status-settings.php';

			require_once GNCUSTOMOR_PLUGIN_DIR . 'core/includes/classes/class-gn-custom-order-status-run.php';
		}

		/**
		 * Add base hooks for the core functionality
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  void
		 */
		private function base_hooks() {
			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'gn-custom-order-status', FALSE, dirname( plugin_basename( GNCUSTOMOR_PLUGIN_FILE ) ) . '/languages/' );
		}

	}

endif; // End if class_exists check.