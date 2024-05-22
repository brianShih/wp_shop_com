<?php

if ( ! defined( 'ABSPATH' ) || ! defined( 'AMSHOP_CONNECTER_VERSION' ) ) {
	exit; // Exit if accessed directly
}

/** 
 * Implements features of amShop Connecter for woocommerce
 *
 * @class   amshop_connecter_order_helper
 * @package amshopConnecter
 * @since   0.0.1
 * @author  Kun You Shih
 */
if ( ! class_exists( 'amshop_connecter_order_helper' ) ) {

	class amshop_connecter_order_helper {
		protected static $instance;
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function __construct() {
			//create order
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'order_created' ), 12 );

			//successful
			//add_action( 'woocommerce_payment_complete', array( $this, 'order_complete' ), 12 );
			//add_action( 'woocommerce_order_status_on-hold_to_completed', array( $this, 'order_complete' ), 12 );
			//add_action( 'woocommerce_order_status_processing', array( $this, 'order_complete' ), 12 );
			add_action( 'woocommerce_order_status_completed', array( $this, 'order_completed' ), 12 );
			//add_action( 'woocommerce_order_status_failed_to_processing', array( $this, 'order_complete' ), 12 );
			//add_action( 'woocommerce_order_status_failed_to_completed', array( $this, 'order_complete' ), 12 );
			
			//failure
			add_action( 'woocommerce_order_status_cancelled', array( $this, 'order_cancelled' ) );
			//add_action( 'woocommerce_order_status_cancelled', array( $this, 'order_cancel' ), 12 );
			//add_action( 'woocommerce_order_status_completed_to_cancelled', array( $this, 'order_cancel' ), 12 );
			//add_action( 'woocommerce_order_status_on-hold_to_cancelled', array( $this, 'order_cancel' ), 12 );
			//add_action( 'woocommerce_order_status_failed_to_cancelled', array( $this, 'order_cancel' ), 12 );
			//add_action( 'woocommerce_order_status_processing_to_cancelled', array( $this, 'order_cancel' ), 12 );
			//add_action( 'woocommerce_order_status_pending_to_cancelled', array( $this, 'order_cancel' ), 12 );
		}

		public function order_created ( $order_id ) {
			if( function_exists( 'amshop_log_insert' ) ) {
				$rid = $_COOKIE["RID"];
				$click_id = $_COOKIE["Click_ID"];
				$order = wc_get_order( $order_id );
				$customer_id = $order->get_user_id();
				if (!empty($customer_id)) {
					$user  = get_user_by( 'id', $customer_id );
					$username = $user->display_name;
				} else {
					$username = $order->get_billing_email();
				}
				$order_items = $order->get_items();

				if ( ! empty( $order_items ) ) {
					foreach ( $order_items as $order_item ) {
						$product_id  = ( $order_item['variation_id'] != 0 && $order_item['variation_id'] != '' ) ? $order_item['variation_id'] : $order_item['product_id'];
						$product = wc_get_product( $product_id );
						$qty = $order_item['qty'];
						amshop_log_insert( $order_id,  $username, $rid, 
                                $click_id, $product_id, $product->get_name(),
                                $qty, $order_item['line_subtotal'] / $qty, $order_item['line_subtotal']);
					}
				}
        	}
		}

		public function order_completed ( $order_id ) {
			if( function_exists( 'amshop_log_order_cancel' ) ) {
				if (isset( $order_id )) {
					if (function_exists( 'amshop_log_is_cancelled' )) {
						$is_cancelled = amshop_log_is_cancelled($order_id);
						if ( !empty($is_cancelled)) {
							amshop_log_order_cancel($order_id, NULL);
						}
					}
					date_default_timezone_set("America/Anguilla");
					$orderDate = amshop_log_get_order_date( $order_id );
	
					amshop_connecter_debug_message('COMPLETED : $order_date : '.$orderDate);
					$completed_date = urlencode($orderDate);

					if ( function_exists( 'amshop_connecter_api_success_send' )) {
						$order = wc_get_order( $order_id );
						$order_items = $order->get_items();
						$totals = 0;
						foreach ( $order_items as $order_item ) {
							$totals = $order_item['line_subtotal'] + $totals;
						}
						$commission_amount = ($totals * 0.12);//round($totals * 0.12, 2);
						//$commission = round( $commission_amount, 2 );
						$commission = sprintf("%1\$.2f", $commission_amount);
                        $rid = amshop_log_order_get_rid( $order_id );
                        $click_id = amshop_log_order_get_click_id( $order_id );
                        if (strlen($rid) > 0 && strlen($click_id) > 0) {
                        	amshop_connecter_debug_message('COMPLETED : $commission_amount : '.$commission);
                        	amshop_connecter_debug_message('COMPLETED : $totals :'.$totals);
                        	amshop_connecter_debug_message('COMPLETED : $rid : '.$rid. ' , $click_id : '.$click_id);
                        	amshop_connecter_api_success_send($totals, $commission, $order_id, $rid, $click_id, $completed_date);
						}
						//$Order_Amount, $Commission_Amount, $ORDER_ID, $RID, $Click_ID, $DateTime
					}
				}
			}
		}

		public function order_cancelled ( $order_id ) {
			if( function_exists( 'amshop_log_order_cancel' ) ) {
				if (isset( $order_id )) {
					if (function_exists( 'amshop_log_is_completed' )) {
						$is_completed = amshop_log_is_completed($order_id);
						if ( !empty(is_completed) ) {
							amshop_log_order_complited($order_id, NULL);
						}
					}
					date_default_timezone_set("America/Anguilla");

					$orderDate = amshop_log_get_order_date( $order_id );
					amshop_connecter_debug_message('CANCELLED : $order_date : '.$orderDate);
					$canceldate = urlencode($orderDate);
					
					if ( function_exists( 'amshop_connecter_api_cancel_send' )) {
						$order = wc_get_order( $order_id );
						$order_items = $order->get_items();
						$totals = 0;
						foreach ( $order_items as $order_item ) {
							$totals = $order_item['line_subtotal'] + $totals;
						}
						$commission_amount = ($totals * 0.12);//round($totals * 0.12, 2);
						//$commission = round( $commission_amount, 2 );
						$commission = sprintf("%1\$.2f", $commission_amount);
                        $rid = amshop_log_order_get_rid( $order_id );
                        $click_id = amshop_log_order_get_click_id( $order_id );
                        if (strlen($rid) > 0 && strlen($click_id) > 0) {
                        	amshop_connecter_debug_message('CANCELLED : $commission_amount : '.$commission);
                        	amshop_connecter_debug_message('CANCELLED : $totals :'.$totals);
                        	amshop_connecter_debug_message('CANCELLED : $rid : '.$rid. ' , $click_id : '.$click_id);
                        	amshop_connecter_api_cancel_send($totals, $commission, $order_id, $rid, $click_id, $canceldate);
                    	}

						//$Order_Amount, $Commission_Amount, $ORDER_ID, $RID, $Click_ID, $DateTime
					}
				}
			}
		}
	}

}


function amshop_connecter_order_helper() {
	return amshop_connecter_order_helper::get_instance();
}
