<?php
if ( !defined( 'ABSPATH' ) || ! defined( 'AMSHOP_CONNECTER_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements helper functions for amShop Connecter for woocommerce
 *
 * @package AM Shop Connecter
 * @since   0.0.1
 * @author:      Kun You Shih
 */

global $amshop_connecter_db_version;

$amshop_connecter_db_version = '0.0.1';


if ( !function_exists( 'amshop_connecter_db_install' ) ) {
    function amshop_connecter_db_install() {
        global $wpdb;
        global $amshop_connecter_db_version;

        $installed_ver = get_option( "amshop_connecter_db_version" );

        $table_name = $wpdb->prefix . 'amshop_connecter_log';

        $charset_collate = $wpdb->get_charset_collate();

        if( ! $installed_ver ){
            $sql = "CREATE TABLE $table_name (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `order_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `order_numb` VARCHAR (255) NOT NULL,
            `buyer_name` VARCHAR (255) NOT NULL,
            `rid` VARCHAR (255) NOT NULL,
            `click_id` VARCHAR (255) NOT NULL,
            `product_sn` VARCHAR (255) NOT NULL,
            `product_descrip` VARCHAR (255) NOT NULL,
            `quanitity` int(11) NOT NULL,
            `unit_price` int(11) NOT NULL,
            `sale_amount` int(11) NOT NULL,
            `completed` datetime,
            `cancelled` datetime,
            PRIMARY KEY (id)
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

            add_option( 'amshop_connecter_db_version', $amshop_connecter_db_version );
        }

        if ( $installed_ver == '0.0.1') {
            $sql = "SELECT COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME='$table_name'";
            $cols = $wpdb->get_col($sql);

            if( is_array($cols) && !in_array('cancelled', $cols)){
                $sql = "ALTER TABLE $table_name ADD `cancelled` datetime";
                $wpdb->query( $sql);
            }
            update_option( 'amshop_connecter_db_version', $amshop_connecter_db_version );
        }
    }
}

if ( !function_exists( 'amshop_connecter_update_db_check' ) ) {
    function amshop_connecter_update_db_check() {
        global $amshop_connecter_db_version;

        if ( get_site_option( 'amshop_connecter_db_version' ) != $amshop_connecter_db_version ) {

            amshop_connecter_db_install();
        }
    }
}


if (!function_exists( 'amshop_log_insert' )) {
    function amshop_log_insert( $order_numb, $buyer_name, $rid, $click_id, 
                                $product_sn, $product_descrip, $quanitity, $unit_price, $sale_amount ) 
    {
        global $wpdb;
        $wpdb->query("SET @@session.time_zone='-04:00'");

        if (isset($order_numb) && isset($buyer_name) && isset($rid) 
            && isset($click_id) && isset($product_sn) && isset($product_descrip) && isset($quanitity)
            && isset($unit_price) && isset($sale_amount)) 
        {
            $table_name = $wpdb->prefix . 'amshop_connecter_log';

            $initial_query = "INSERT INTO $table_name ( order_numb, buyer_name, rid, click_id, 
                    product_sn, product_descrip, quanitity, unit_price, sale_amount ) VALUES ";
            $place_holders[] = "('%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d')";
            $initial_query .= implode(', ', $place_holders);

            $values = array( $order_numb, $buyer_name ,$rid, $click_id, $product_sn, 
                $product_descrip, $quanitity, $unit_price, $sale_amount );

            $wpdb->query( $wpdb->prepare( "$initial_query ", $values ) );
        }
    }
}

if (!function_exists( 'amshop_log_order_complited' )) {
    function amshop_log_order_complited( $order_numb, $complete ) 
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'amshop_connecter_log';
        $wpdb->update( $table_name, array('completed' => $complete), array('order_numb' => $order_numb) );
    }
}

if (!function_exists( 'amshop_log_order_get_rid' )) {
    function amshop_log_order_get_rid( $order_numb ) {
        global $wpdb;

        $items = $wpdb->get_row(
                $wpdb->prepare(
                        "SELECT rid
                        FROM {$wpdb->prefix}amshop_connecter_log
                        WHERE order_numb = %d",
                        $order_numb
                ), ARRAY_A
        );
        if (!empty($items)) {
            foreach ( $items as $item ) {
                if (!empty($item)) {
                    amshop_connecter_debug_message('rid: '.$item);
                    return $item;
                }
            }
        }
        return false;
    }
}

if (!function_exists( 'amshop_log_order_get_click_id' )) {
    function amshop_log_order_get_click_id( $order_numb ) {
        global $wpdb;

        $items = $wpdb->get_row(
                $wpdb->prepare(
                        "SELECT click_id
                        FROM {$wpdb->prefix}amshop_connecter_log
                        WHERE order_numb = %d",
                        $order_numb
                ), ARRAY_A
        );
        if (!empty($items)) {
            foreach ( $items as $item ) {
                if (!empty($item)) {
                    amshop_connecter_debug_message('click_id: '.$item);
                    return $item;
                }
            }
        }
        return false;
    }
}

if (!function_exists( 'amshop_log_get_order_date' )) {
    function amshop_log_get_order_date( $order_numb ) {
        global $wpdb;
        $items = $wpdb->get_row(
                $wpdb->prepare(
                        "SELECT order_date
                        FROM {$wpdb->prefix}amshop_connecter_log
                        WHERE order_numb = %d",
                        $order_numb
                ), ARRAY_A
        );
        if (!empty($items)) {
            foreach ( $items as $item ) {
                if (!empty($item)) {
                    amshop_connecter_debug_message('DATABASE :: order Date: '.$item);
                    return $item;
                }
            }
        }

        return false;
    }
}

if (!function_exists( 'amshop_log_is_completed' )) {
    function amshop_log_is_completed( $order_numb ) {
        global $wpdb;

        $items = $wpdb->get_row(
                $wpdb->prepare(
                        "SELECT completed
                        FROM {$wpdb->prefix}amshop_connecter_log
                        WHERE order_numb = %d",
                        $order_numb
                ), ARRAY_A
        );
        if (!empty($items)) {
            foreach ( $items as $item ) {
                if (!empty($item)) {
                    amshop_connecter_debug_message('is completed? : '.$item);
                    return $item;
                }
            }
        }
        return false;
    }
}

if (!function_exists( 'amshop_log_order_cancel' )) {
    function amshop_log_order_cancel( $order_numb, $cancel ) 
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'amshop_connecter_log';
        $wpdb->update( $table_name, array('cancelled' => $cancel), array('order_numb' => $order_numb) );
    }
}

if (!function_exists( 'amshop_log_is_cancelled' )) {
    function amshop_log_is_cancelled( $order_numb ) {
        global $wpdb;

        $items = $wpdb->get_row(
                $wpdb->prepare(
                        "SELECT cancelled
                        FROM {$wpdb->prefix}amshop_connecter_log
                        WHERE order_numb = %d",
                        $order_numb
                ), ARRAY_A
        );
        if (!empty($items)) {
            foreach ( $items as $item ) {
                if (!empty($item)) {
                    amshop_connecter_debug_message('is cancelled? : '.$item);
                    return $item;
                }
            }
        }
        return false;
    }

}




