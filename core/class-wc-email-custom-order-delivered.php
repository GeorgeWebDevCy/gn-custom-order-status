<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( class_exists( 'WC_Email' ) ) {
    /**
     * Custom Order Delivered Email
     *
     * @class       WC_Email_Custom_Order_Delivered
     * @extends     WC_Email
     */
    class WC_Email_Custom_Order_Delivered extends WC_Email {
        /**
         * Constructor.
         */
        public function __construct() {
            // Set ID, this simply needs to be a unique name
            $this->id = 'custom_order_delivered';

            // Set title
            $this->title = 'Custom Order Delivered';

            // Set description
            $this->description = 'This is a custom order delivered email sent to customers.';

            // Set default heading and subject
            $this->heading = 'Order Delivered';
            $this->subject = 'Your order has been delivered';

            // Call parent constructor
            parent::__construct();

            // Other hooks/actions can be added here
        }

        /**
         * Trigger function.
         */
        public function trigger( $order_id ) {
            // Set the recipient email
            $this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );

            // Get the order object
            $this->object = wc_get_order( $order_id );

            // Validate recipient
            if ( ! $this->is_valid_email( $this->recipient ) ) {
                return;
            }

            // Replace variables in the subject/headings
            $this->find[] = '{order_date}';
            $this->replace[] = date_i18n( wc_date_format(), strtotime( $this->object->get_date_created() ) );

            $this->find[] = '{order_number}';
            $this->replace[] = $this->object->get_order_number();

            // Some more replacement variables can be added here

            // Call parent trigger to send the email
            parent::trigger( $order_id );
        }
    }
}
