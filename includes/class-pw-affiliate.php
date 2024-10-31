<?php

/*
Copyright (C) 2016-2017 Pimwick, LLC

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'PW_Affiliate' ) ) :

class PW_Affiliate {

    /*
     *
     * Properties
     *
     */
    public function get_id() { return $this->pimwick_affiliate_id; }
    private $pimwick_affiliate_id;

    public function get_code() { return $this->code; }
    private $code;

    public function get_name() { return $this->name; }
    private $name;

    public function get_user_id() { return $this->user_id; }
    private $user_id;

    public function get_commission() { return $this->commission; }
    private $commission;

    public function get_active() { return $this->active; }
    private $active;

    public function get_create_date() { return $this->create_date; }
    private $create_date;

    public function get_error_message() { return $this->error_message; }
    private $error_message;

    function __construct( $code ) {
        global $wpdb;
        global $pw_affiliates;

        $code = sanitize_text_field( $code );
        if ( !empty( $code ) ) {

            $result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->pimwick_affiliate}` WHERE `code` = %s", $code ) );
            if ( $result !== null ) {
                $this->pimwick_affiliate_id     = $result->pimwick_affiliate_id;
                $this->code                     = $result->code;
                $this->name                     = $result->name;
                $this->user_id                  = $result->user_id;
                $this->commission               = $pw_affiliates->default_commission;
                $this->active                   = boolval( $result->active );
                $this->create_date              = $result->create_date;
            } else {
                $this->error_message = __( 'Affiliate does not exist.', 'pw-woocommerce-affiliates' );
            }
        } else {
            $this->error_message = __( 'Enter a code.', 'pw-woocommerce-affiliates' );
        }
    }



    /*
     *
     * Public methods.
     *
     */
    public function get_url() {
        return pwwa_affiliate_url( $this->code );
    }

    public function get_orders( $begin_date, $end_date ) {
        $range = $begin_date . '...' . $end_date;

        if ( !isset( $this->orders[ $range ] ) ) {
            $orders = wc_get_orders( array(
                '_pw_affiliate_code' => $this->get_code(),
                'date_created' => $range,
                'status' => 'completed',
                'limit' => -1,
            ) );

            $this->orders[ $range ] = apply_filters( 'pwwa_affiliate_orders', $orders );
        }

        return $this->orders[ $range ];
    }
    private $orders = array();

    public function get_total_commission( $begin_date, $end_date ) {
        $range = $begin_date . '...' . $end_date;

        if ( !isset( $this->total_commission[ $range ] ) ) {
            $total_commission = 0;
            $orders = $this->get_orders( $begin_date, $end_date );

            foreach ( $orders as $order ) {
                foreach ( $order->get_items( 'line_item' ) as $line_item ) {
                    if ( is_numeric( $line_item->get_meta( '_pw_affiliate_commission' ) ) ) {
                        $total_commission += $line_item->get_meta( '_pw_affiliate_commission' );
                    }
                }
            }

            $this->total_commission[ $range ] = apply_filters( 'pwwa_affiliate_orders', $total_commission );
        }

        return $this->total_commission[ $range ];
    }
    private $total_commission = array();

    public function delete() {
        global $wpdb;

        $wpdb->delete( $wpdb->pimwick_affiliate, array( 'pimwick_affiliate_id' => $this->get_id() ), array( '%d' ) );
    }



    /*
     *
     * Static Methods
     *
     */

    public static function plugin_activate() {
        global $wpdb;

        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        $wpdb->query( "
            CREATE TABLE IF NOT EXISTS `{$wpdb->pimwick_affiliate}` (
                `pimwick_affiliate_id` INT NOT NULL AUTO_INCREMENT,
                `code` TEXT NOT NULL,
                `name` TEXT NOT NULL,
                `user_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `commission` DECIMAL( 7, 4 ) NOT NULL DEFAULT 0,
                `active` TINYINT(1) NOT NULL DEFAULT 1,
                `create_user_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `create_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`pimwick_affiliate_id`),
                INDEX `ix_pimwick_affiliate_id` (`pimwick_affiliate_id`),
                UNIQUE `idx_code` ( `code` (128) )
            );
        " );
        if ( $wpdb->last_error != '' ) {
            wp_die( $wpdb->last_error );
        }
    }



    /*
     *
     * Private methods
     *
     */
    private function update_property( $property, $value ) {
        global $wpdb;

        if ( property_exists( $this, $property ) ) {
            if ( $this->{$property} != $value ) {
                $result = $wpdb->update( $wpdb->pimwick_affiliate, array ( $property => $value ), array( 'pimwick_affiliate_id' => $this->get_id() ) );

                if ( $result !== false ) {
                    $this->{$property} = $value;

                    return true;
                } else {
                    wp_die( $wpdb->last_error );
                }
            }

        } else {
            wp_die( sprintf( __( 'Property %s does not exist on %s', 'pw-woocommerce-affiliates' ), $property, get_class() ) );
        }
    }
}

register_activation_hook( PWWA_PLUGIN_FILE, array( 'PW_Affiliate', 'plugin_activate' ) );

endif;

?>