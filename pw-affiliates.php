<?php
/**
 * Plugin Name: PW WooCommerce Affiliates
 * Plugin URI: https://www.pimwick.com/affiliates/
 * Description: Affiliate tracking for your WooCommerce store.
 * Version: 2.0
 * Author: Pimwick, LLC
 * Author URI: https://www.pimwick.com
 * Text Domain: pw-woocommerce-affiliates
 * Domain Path: /languages
 * WC requires at least: 4.0
 * WC tested up to: 9.1
 * Requires Plugins: woocommerce
*/

/*
Copyright (C) Pimwick, LLC

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
define( 'PWWA_VERSION', '2.0' );

defined( 'ABSPATH' ) or exit;

if ( !function_exists( 'pimwick_define' ) ) :
function pimwick_define( $constant_name, $default_value ) {
    defined( $constant_name ) or define( $constant_name, $default_value );
}
endif;

pimwick_define( 'PWWA_REQUIRES_PRIVILEGE', 'manage_woocommerce' );
pimwick_define( 'PWWA_WC_VERSION_MINIMUM', '4.0' );
pimwick_define( 'PWWA_PLUGIN_FILE', __FILE__ );
pimwick_define( 'PWWA_PLUGIN_ROOT', plugin_dir_path( PWWA_PLUGIN_FILE ) );
pimwick_define( 'PWWA_FONT_AWESOME_VERSION', '5.1.1' );
pimwick_define( 'PWWA_RANDOM_CODE_LENGTH', '4' );
pimwick_define( 'PWWA_RANDOM_CODE_CHARSET', 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789' );
pimwick_define( 'PWWA_SESSION_KEY', 'pw-affiliates-code' );

if ( ! class_exists( 'PW_Affiliates' ) ) :

final class PW_Affiliates {

    public $program_name;
    public $default_commission;
    public $url_fields;
    public $affiliate_endpoint;

    function __construct() {
        global $wpdb;

        $wpdb->pimwick_affiliate = $wpdb->prefix . 'pimwick_affiliate';

        $this->program_name = get_option( 'pw_affiliates_program_name', __( 'Affiliate Program', 'pw-woocommerce-affiliates' ) );
        if ( empty( $this->program_name ) ) { $this->program_name = __( 'Affiliate Program', 'pw-woocommerce-affiliates' ); }

        $this->default_commission = get_option( 'pw_affiliates_default_commission', '0' );
        if ( empty( $this->default_commission ) ) { $this->default_commission = '0'; }

        $this->url_fields = get_option( 'pwwa_url_fields', 'affiliate' );
        if ( empty( $this->url_fields ) ) { $this->url_fields = 'affiliate'; }

        $this->affiliate_endpoint = get_option( 'pwwa_affiliate_endpoint', 'affiliate-report' );
        if ( empty( $this->affiliate_endpoint ) ) { $this->affiliate_endpoint = 'affiliate-report'; }

        add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
        add_action( 'woocommerce_init', array( $this, 'woocommerce_init' ) );
        add_action( 'init', array( $this, 'add_endpoints' ) );

        require_once( 'includes/class-pw-affiliate.php' );
        require_once( 'includes/pwwa-functions.php' );

        // WooCommerce High Performance Order Storage (HPOS) compatibility declaration.
        add_action( 'before_woocommerce_init', function() {
            if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
            }
        } );
    }

    function plugins_loaded() {
        load_plugin_textdomain( 'pw-woocommerce-affiliates', false, basename( dirname( __FILE__ ) ) . '/languages' );

        foreach ( array_map( 'trim', explode( ',', $this->url_fields ) ) as $url_field ) {
            if ( isset( $_GET[ $url_field ] ) ) {
                $code = sanitize_text_field( $_GET[ $url_field ] );
                if ( $this->add_affiliate_code_to_session( $code ) ) {
                    break;
                }
            }
        }
    }

    function woocommerce_init() {
        if ( is_admin() ) {
            require_once( 'admin/admin.php' );

            add_filter( 'woocommerce_order_item_display_meta_value', array( $this, 'woocommerce_order_item_display_meta_value' ), 10, 3 );

        } else {
            add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
            add_filter( 'woocommerce_account_menu_items', array( $this, 'woocommerce_account_menu_items' ) );
            add_action( 'woocommerce_account_' . $this->affiliate_endpoint . '_endpoint', array( $this, 'affiliate_report' ) );
            add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', array( $this, 'woocommerce_order_data_store_cpt_get_orders_query' ), 10, 2 );
            add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'woocommerce_checkout_update_order_meta' ), 10, 2 );
            add_action( 'woocommerce_cart_totals_before_order_total', array( $this, 'woocommerce_cart_totals_before_order_total' ) );
            add_action( 'woocommerce_review_order_before_order_total', array( $this, 'woocommerce_review_order_before_order_total' ) );
        }

        add_filter( 'woocommerce_attribute_label', array( $this, 'woocommerce_attribute_label' ), 10, 3 );
    }

    function add_endpoints() {
        add_rewrite_endpoint( $this->affiliate_endpoint, EP_ROOT | EP_PAGES );
    }

    public static function flush_rewrite_rules() {
        global $pw_affiliates;

        $pw_affiliates->add_endpoints();
        flush_rewrite_rules();
    }

    function wc_min_version( $version ) {
        return version_compare( WC()->version, $version, ">=" );
    }

    function relative_url( $url ) {
        return plugins_url( $url, PWWA_PLUGIN_FILE );
    }

    function insert_after( $items, $new_items, $after ) {
        // Search for the item position and +1 since is after the selected item key.
        $position = array_search( $after, array_keys( $items ) ) + 1;

        // Insert the new item.
        $array = array_slice( $items, 0, $position, true );
        $array += $new_items;
        $array += array_slice( $items, $position, count( $items ) - $position, true );

        return $array;
    }

    function wp_enqueue_scripts() {
        global $wp;

        if ( ! empty( $wp->query_vars ) && isset( $wp->query_vars['pagename'] ) && isset( $wp->query_vars[ $this->affiliate_endpoint ] ) ) {
            $myaccount_page = get_post( wc_get_page_id( 'myaccount' ) );
            if ( $wp->query_vars['pagename'] === $myaccount_page->post_name ) {
                wp_enqueue_script( 'pw-affiliates', $this->relative_url( '/assets/js/pw-affiliates.js' ), array( 'jquery' ), PWWA_VERSION );
                wp_localize_script( 'pw-affiliates', 'pwwa', array(
                    'i18n' => array(
                        'loading' => __( 'Loading...', 'pw-woocommerce-affiliates' ),
                        'linkCopied' => __( 'Link copied to clipboard', 'pw-woocommerce-affiliates' ),
                    )
                ) );

                if ( boolval( get_option( 'pw_affiliates_use_builtin_jquery_styles', '1' ) ) ) {
                    wp_register_style( 'jquery-ui-style', $this->relative_url( '/assets/css/jquery-ui-style.min.css', __FILE__ ), array(), PWWA_VERSION );
                    wp_enqueue_style( 'jquery-ui-style' );
                }

                wp_enqueue_script( 'jquery-ui-datepicker' );
            }
        }
    }

    function woocommerce_account_menu_items( $items ) {
        if ( pwwa_current_user_affiliate_code() !== false ) {
            $new_menu = array();
            $new_menu[ $this->affiliate_endpoint ] = $this->program_name;

            $items = $this->insert_after( $items, $new_menu, 'dashboard' );
        }

        return $items;
    }

    function affiliate_report() {
        $user = wp_get_current_user();

        wc_get_template( 'pw-affiliates-report.php', '', '', PWWA_PLUGIN_ROOT . 'templates/woocommerce/' );
    }

    function woocommerce_order_data_store_cpt_get_orders_query( $query, $query_vars ) {
        if ( ! empty( $query_vars['_pw_affiliate_code'] ) ) {
            $query['meta_query'][] = array(
                'key' => '_pw_affiliate_code',
                'value' => esc_attr( $query_vars['_pw_affiliate_code'] ),
            );
        }

        return $query;
    }

    function add_affiliate_code_to_session( $code ) {
        $affiliate = pwwa_get_active_affiliate( $code );
        if ( $affiliate ) {
            $cookie_days = absint( get_option( 'pw_affiliates_cookie_days', '30' ) );

            if ( $cookie_days == 0 ) {
                $cookie_expiration = 0;
            } else {
                $expires = $cookie_days * 60 * 60 * 24;
                $cookie_expiration = time() + $expires;
            }

            setcookie( PWWA_SESSION_KEY, $affiliate->get_code(), $cookie_expiration, '/' );
            $_COOKIE[ PWWA_SESSION_KEY ] = $affiliate->get_code();
            return true;
        }

        return false;
    }

    function get_affiliate_code_from_session() {
        if ( isset( $_COOKIE[ PWWA_SESSION_KEY ] ) ) {
            return $_COOKIE[ PWWA_SESSION_KEY ];
        } else {
            return null;
        }
    }

    function woocommerce_checkout_update_order_meta( $order_id, $data ) {
        $code = $this->get_affiliate_code_from_session();
        if ( empty( $code ) ) {
            return;
        }

        $affiliate = pwwa_get_active_affiliate( $code );
        if ( $affiliate ) {
            $order = new WC_Order( $order_id );

            $pre_tax = boolval( get_option( 'pw_affiliates_commission_before_tax', '1' ) );
            $total_commission = 0;

            foreach ( $order->get_items( 'line_item' ) as $line_item ) {
                $product = $line_item->get_product();
                if ( !empty( $product ) ) {
                    $rate = pwwa_get_product_commission( $product, $affiliate );
                    if ( $rate > 0 ) {
                        $line_item_total = $line_item->get_total();
                        if ( ! $pre_tax ) {
                            $line_item_total += $line_item->get_total_tax();
                        }

                        $line_item_commission = round( ( $line_item_total * ( $rate / 100 ) ), 4 );
                        $total_commission += $line_item_commission;

                        $line_item->update_meta_data( '_pw_affiliate_commission', $line_item_commission );
                        $line_item->save();
                    }
                }
            }

            $commission = round( $total_commission, wc_get_price_decimals() );

            $order->update_meta_data( '_pw_affiliate_code', $affiliate->get_code() );
            $order->update_meta_data( '_pw_affiliate_commission', $commission );
            $order->add_order_note( sprintf( __( 'Affiliate %s. Commission %s', 'pw-woocommerce-affiliates' ), $affiliate->get_code(), $commission ) );

            $order->save();
        }
    }

    function woocommerce_cart_totals_before_order_total() {
        if ( 'yes' === get_option( 'pw_affiliates_show_code_in_cart', 'no' ) ) {
            global $pw_affiliate_code;
            $pw_affiliate_code = $this->get_affiliate_code_from_session();

            wc_get_template( 'cart/pw-affiliates-cart-totals-before-order-total.php', '', '', PWWA_PLUGIN_ROOT . 'templates/woocommerce/' );
        }
    }

    function woocommerce_review_order_before_order_total() {
        if ( 'yes' === get_option( 'pw_affiliates_show_code_in_checkout', 'no' ) ) {
            global $pw_affiliate_code;
            $pw_affiliate_code = $this->get_affiliate_code_from_session();

            wc_get_template( 'checkout/pw-affiliates-review-order-before-order-total.php', '', '', PWWA_PLUGIN_ROOT . 'templates/woocommerce/' );
        }
    }

    function woocommerce_attribute_label( $label, $name, $product ) {
        switch ( $label ) {
            case '_pw_affiliate_code':
                return __( 'Affiliate code', 'pw-woocommerce-affiliates' );
            break;

            case '_pw_affiliate_commission':
                return __( 'Affiliate commission', 'pw-woocommerce-affiliates' );
            break;

            default:
                return $label;
        }
    }

    function woocommerce_order_item_display_meta_value( $display_value, $meta, $order_item ) {
        if ( $meta->key == '_pw_affiliate_commission' ) {
            $display_value = wc_price( $display_value );
        }

        return $display_value;
    }
}

global $pw_affiliates;
$pw_affiliates = new PW_Affiliates();

register_activation_hook( __FILE__, array( 'PW_Affiliates', 'flush_rewrite_rules' ) );
register_deactivation_hook( __FILE__, array( 'PW_Affiliates', 'flush_rewrite_rules' ) );

endif;
