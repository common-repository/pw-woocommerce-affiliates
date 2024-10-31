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

if ( ! class_exists( 'PW_Affiliates_Admin' ) ) :

final class PW_Affiliates_Admin {

    public $settings;

    function __construct() {
        global $pw_affiliates;

        $this->settings = array(
            array(
                'title'     => __( 'PW Affiliates', 'pw-woocommerce-affiliates' ),
                'type'      => 'title',
                'desc'      => '',
                'id'        => 'pw_affiliates_options',
            ),
            array(
                'title'     => __( 'Affiliate Program Name', 'pw-woocommerce-affiliates' ),
                'desc'      => __( 'Shown to affiliates when they log in to check their reports.', 'pw-woocommerce-affiliates' ),
                'id'        => 'pw_affiliates_program_name',
                'default'   => __( 'Affiliate Program', 'pw-woocommerce-affiliates' ),
                'type'      => 'text',
            ),
            array(
                'title'     => __( 'Pre-Tax Commission', 'pw-woocommerce-affiliates' ),
                'desc'      => __( 'Should the commission be based on the pre-tax product price?', 'pw-woocommerce-affiliates' ),
                'id'        => 'pw_affiliates_commission_before_tax',
                'default'   => '1',
                'type'      => 'checkbox',
            ),
            array(
                'title'     => __( 'Affiliate Code URL Field', 'pw-woocommerce-affiliates' ),
                'desc'      => __( 'For example, ' . apply_filters( 'pw_affiliates_shop_page_url', get_permalink( wc_get_page_id( 'shop' ) ) ) . '?affiliate=123 for the value "affiliate". Multiple options can be separated by a comma.', 'pw-woocommerce-affiliates' ),
                'id'        => 'pwwa_url_fields',
                'default'   => 'affiliate',
                'type'      => 'text',
            ),
            array(
                'title'     => __( 'Affiliate Endpoint', 'pw-woocommerce-affiliates' ),
                'desc'      => sprintf( __( 'The URL for the report shown to affiliate users. Default: %s', 'pw-woocommerce-affiliates' ), trailingslashit( get_permalink( get_option('woocommerce_myaccount_page_id') ) ) . 'affiliate-report/' ),
                'id'        => 'pwwa_affiliate_endpoint',
                'default'   => 'affiliate-report',
                'type'      => 'text',
            ),
            array(
                'title'     => __( 'Cookie Lifetime', 'pw-woocommerce-affiliates' ),
                'desc'      => sprintf( __( 'The number of days the Affiliate tracking cookie is stored for visitors. If set to 0, the cookie will expire at the end of the session (when the browser closes). Default: %s', 'pw-woocommerce-affiliates' ), '30' ),
                'id'        => 'pw_affiliates_cookie_days',
                'default'   => '30',
                'type'      => 'number',
            ),
            array(
                'title'     => __( 'Show Code In Cart', 'pw-woocommerce-affiliates' ),
                'desc'      => __( 'Check this box to show the Affiliate Code on the Cart page. If no code is applied, nothing will be shown. Default: unchecked.', 'pw-woocommerce-affiliates' ),
                'id'        => 'pw_affiliates_show_code_in_cart',
                'default'   => 'no',
                'type'      => 'checkbox',
            ),
            array(
                'title'     => __( 'Show Code In Checkout', 'pw-woocommerce-affiliates' ),
                'desc'      => __( 'Check this box to show the Affiliate Code on the Checkout page. If no code is applied, nothing will be shown. Default: unchecked.', 'pw-woocommerce-affiliates' ),
                'id'        => 'pw_affiliates_show_code_in_checkout',
                'default'   => 'no',
                'type'      => 'checkbox',
            ),
            array(
                'type'      => 'sectionend',
                'id'        => 'pw_affiliates_options',
            ),
        );

        // Show an alert on the backend if we don't have the minimum required version.
        if ( !$pw_affiliates->wc_min_version( PWWA_WC_VERSION_MINIMUM ) ) {
            add_action( 'admin_notices', array( $this, 'woocommerce_version_error' ) );
            return;
        }

        add_action( 'admin_menu', array( $this, 'admin_menu' ), 11, 1 );
        add_filter( 'custom_menu_order', array( $this, 'custom_menu_order' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_action( 'restrict_manage_posts', array( $this, 'filter_orders_by_affiliate'), 20 );
        add_filter( 'request', array( $this, 'filter_orders_by_affiliate_query' ) );
        add_action( 'admin_init', array( $this, 'send_exported_file' ) );

        add_action( 'wp_ajax_pwwa-affiliates-report', array( $this, 'ajax_affiliates_report' ) );
        add_action( 'wp_ajax_pwwa-export-report', array( $this, 'ajax_export_report' ) );
        add_action( 'wp_ajax_pwwa-create-affiliate', array( $this, 'ajax_create_affiliate' ) );
        add_action( 'wp_ajax_pwwa-edit-affiliate', array( $this, 'ajax_edit_affiliate' ) );
        add_action( 'wp_ajax_pwwa-delete-affiliate', array( $this, 'ajax_delete_affiliate' ) );
        add_action( 'wp_ajax_pwwa-save-commissions', array( $this, 'ajax_save_commissions' ) );
        add_action( 'wp_ajax_pwwa-save-settings', array( $this, 'ajax_save_settings' ) );
    }

    function woocommerce_version_error() {
        ?>
        <div class="error notice">
            <p><?php printf( __( 'PW WooCommerce Affiliates requires WooCommerce version %s or later.', 'pw-woocommerce-affiliates' ), PWWA_WC_VERSION_MINIMUM ); ?></p>
        </div>
        <?php
    }

    function admin_menu() {
        global $pw_affiliates;

        if ( empty ( $GLOBALS['admin_page_hooks']['pimwick'] ) ) {
            add_menu_page(
                __( 'PW Affiliates', 'pw-woocommerce-affiliates' ),
                __( 'Pimwick Plugins', 'pw-woocommerce-affiliates' ),
                PWWA_REQUIRES_PRIVILEGE,
                'pimwick',
                array( $this, 'index' ),
                $pw_affiliates->relative_url( '/admin/assets/images/pimwick-icon-120x120.png' ),
                6
            );

            add_submenu_page(
                'pimwick',
                __( 'PW Affiliates', 'pw-woocommerce-affiliates' ),
                __( 'Pimwick Plugins', 'pw-woocommerce-affiliates' ),
                PWWA_REQUIRES_PRIVILEGE,
                'pimwick',
                array( $this, 'index' )
            );

            remove_submenu_page( 'pimwick', 'pimwick' );
        }

        add_submenu_page(
            'pimwick',
            __( 'PW Affiliates', 'pw-woocommerce-affiliates' ),
            __( 'PW Affiliates', 'pw-woocommerce-affiliates' ),
            PWWA_REQUIRES_PRIVILEGE,
            'pw-affiliates',
            array( $this, 'index' )
        );
    }

    function custom_menu_order( $menu_order ) {
        global $submenu;

        if ( isset( $submenu['pimwick'] ) ) {
            usort( $submenu['pimwick'], array( $this, 'sort_menu_by_title' ) );
        }

        return $menu_order;
    }

    function sort_menu_by_title( $a, $b ) {
        if ( $a[2] == 'pimwick-plugins' ) {
            return 1;
        } else if ( $b[2] == 'pimwick-plugins' ) {
            return -1;
        } else {
            return strnatcasecmp( $a[0], $b[0] );
        }
    }

    function other_plugins_page() {
        global $pimwick_more_handled;

        if ( !$pimwick_more_handled ) {
            $pimwick_more_handled = true;
            require( 'ui/more.php' );
        }
    }

    function index() {
        require( 'ui/index.php' );
    }

    function admin_enqueue_scripts( $hook ) {
        global $wp_scripts;
        global $pw_affiliates;

        wp_register_style( 'pw-affiliates-icon', $pw_affiliates->relative_url( '/admin/assets/css/icon-style.css' ), array( 'admin-menu' ), PWWA_VERSION );
        wp_enqueue_style( 'pw-affiliates-icon' );

        if ( !empty( $hook ) && substr( $hook, -strlen( 'pw-affiliates' ) ) === 'pw-affiliates' ) {
            wp_register_style( 'pw-affiliates-admin', $pw_affiliates->relative_url( '/admin/assets/css/pw-affiliates-admin.css' ), array(), PWWA_VERSION );
            wp_enqueue_style( 'pw-affiliates-admin' );

            wp_enqueue_script( 'pw-affiliates-admin', $pw_affiliates->relative_url( '/admin/assets/js/pw-affiliates-admin.js' ), array( 'jquery' ), PWWA_VERSION );
            wp_localize_script( 'pw-affiliates-admin', 'pwwa', array(
                'ordersUrl' => admin_url( 'edit.php?post_type=shop_order' ),
                'exportUrl' => admin_url( 'admin.php?page=pw-affiliates' ),
                'i18n' => array(
                    'loading' => __( 'Loading...', 'pw-woocommerce-affiliates' ),
                    'saving' => __( 'Saving...', 'pw-woocommerce-affiliates' ),
                    'exporting' => __( 'Exporting...', 'pw-woocommerce-affiliates' ),
                    'linkCopied' => __( 'Link copied to clipboard', 'pw-woocommerce-affiliates' ),
                    'confirmDelete' => __( 'Are you sure you want to delete this affiliate?', 'pw-woocommerce-affiliates' ),
                ),
                'nonces' => array(
                    'affiliatesReport' => wp_create_nonce( 'pw-affiliates-affiliates-report' ),
                    'exportReport' => wp_create_nonce( 'pw-affiliates-export-report' ),
                    'createAffiliate' => wp_create_nonce( 'pw-affiliates-create-affiliate' ),
                    'editAffiliate' => wp_create_nonce( 'pw-affiliates-edit-affiliate' ),
                    'deleteAffiliate' => wp_create_nonce( 'pw-affiliates-delete-affiliate' ),
                    'saveCommissions' => wp_create_nonce( 'pw-affiliates-save-commissions' ),
                    'saveSettings' => wp_create_nonce( 'pw-affiliates-save-settings' ),
                )
            ) );

            wp_register_script( 'fontawesome', $pw_affiliates->relative_url( '/admin/assets/js/fontawesome.min.js' ), array(), PWWA_FONT_AWESOME_VERSION );
            wp_enqueue_script( 'fontawesome' );

            wp_register_script( 'fontawesome-solid', $pw_affiliates->relative_url( '/admin/assets/js/fontawesome-solid.min.js' ), array( 'fontawesome' ), PWWA_FONT_AWESOME_VERSION );
            wp_enqueue_script( 'fontawesome-solid' );

            wp_register_style( 'jquery-ui-style', $pw_affiliates->relative_url( '/assets/css/jquery-ui-style.min.css', __FILE__ ), array(), PWWA_VERSION );
            wp_enqueue_style( 'jquery-ui-style' );

            wp_enqueue_script( 'jquery-ui-datepicker' );
        }
    }

    function filter_orders_by_affiliate() {
        global $typenow;

        if ( 'shop_order' === $typenow ) {

            $affiliates = pwwa_affiliates_list();
            ?>
            <select name="pw_affiliate" id="dropdown_shop_order_pw_affiliate_code">
                <option value="">
                    <?php esc_html_e( 'All Affiliates', 'pw-woocommerce-affiliates' ); ?>
                </option>

                <?php
                    foreach ( $affiliates as $affiliate ) {
                        $code = $affiliate->code;
                        ?>
                        <option value="<?php echo esc_attr( $code ); ?>" <?php echo esc_attr( isset( $_GET['pw_affiliate'] ) ? selected( $code, $_GET['pw_affiliate'], false ) : '' ); ?>>
                            <?php echo esc_html( sprintf( '%s (%s)', $affiliate->name, $code ) ); ?>
                        </option>
                        <?php
                    }
                ?>
            </select>
            <?php
        }
    }

    function filter_orders_by_affiliate_query( $vars ) {
        global $typenow;

        if ( 'shop_order' === $typenow && isset( $_GET['pw_affiliate'] ) && !empty( $_GET['pw_affiliate'] ) ) {
            $vars['meta_key']   = '_pw_affiliate_code';
            $vars['meta_value'] = wc_clean( $_GET['pw_affiliate'] );
        }
        return $vars;
    }

    function ajax_affiliates_report() {

        check_ajax_referer( 'pw-affiliates-affiliates-report', 'security' );

        ob_start();
        $affiliates = $this->affiliates_report();
        require( 'ui/affiliates-table.php' );
        $html = ob_get_clean();

        wp_send_json_success( array( 'html' => $html ) );
    }

    function affiliates_report() {
        global $pwwa_sort;
        global $pwwa_sort_order;

        $active = true;
        $limit = 1000;

        $form = array();
        parse_str( $_REQUEST['form'], $form );

        $affiliate_id = absint( $form['affiliate_id'] );
        $begin_date = wc_clean( $form['begin_date'] );
        $end_date = wc_clean( $form['end_date'] );

        $pwwa_sort = 'revenue';
        if ( !empty( $form['sort'] ) ) {
            $pwwa_sort = wc_clean( $form['sort'] );
        }

        $pwwa_sort_order = 'desc';
        if ( !empty( $form['sort_order'] ) ) {
            $pwwa_sort_order = wc_clean( $form['sort_order'] );
        }

        $affiliates = pwwa_affiliates_report( $affiliate_id, $begin_date, $end_date, $pwwa_sort, $pwwa_sort_order, $active, $limit );

        return apply_filters( 'pwwa_affiliates_report', $affiliates );
    }

    function ajax_export_report() {

        check_ajax_referer( 'pw-affiliates-export-report', 'security' );

        $form = array();
        parse_str( $_REQUEST['form'], $form );

        $output_filename = wp_tempnam();
        $csv_file = fopen( $output_filename, 'w' );

        $report_type = 'affiliates';
        $data = $this->affiliates_report();
        $columns = pwwa_affiliates_report_columns();

        // Output the header row.
        if ( !empty( $columns ) ) {
            fputcsv( $csv_file, wp_list_pluck( $columns, 'label' ) );
        }

        foreach ( $data as &$row ) {
            foreach( $row as $key => &$value ) {
                if ( isset( $columns[ $key ] ) ) {
                    $value = trim( preg_replace( '/\s+/', ' ', $value ) );
                } else {
                    unset( $row->$key );
                }
            }

            fputcsv( $csv_file, (array) $row );
        }

        fclose( $csv_file );

        wp_send_json_success(
            array(
                'report_type' => $report_type,
                'output_filename' => $output_filename
            )
        );
    }

    function send_exported_file() {
        if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'pwwa_export' && isset( $_REQUEST['report_type'] ) && isset( $_REQUEST['filename'] ) ) {
            if ( !current_user_can( PWWA_REQUIRES_PRIVILEGE ) ) { wp_die( 'Unauthorized.' ); }

            $filename = wc_clean( $_REQUEST['filename'] );
            $download_filename = ucfirst( wc_clean( $_REQUEST['report_type'] ) ) . '.csv';

            $extension = pathinfo( $filename, PATHINFO_EXTENSION );
            if ( strtolower( $extension ) != 'tmp' ) {
                wp_die( 'Invalid filename.' );
            }

            header( 'Content-Type: application/octet-stream' );
            header( 'Content-Disposition: attachment; filename="' . $download_filename . '"' );
            header( 'Content-Description: File Transfer' );
            header( 'Expires: 0' );
            header( 'Cache-Control: must-revalidate' );
            header( 'Pragma: public' );
            header( 'Content-Length: ' . filesize( $filename ) );
            readfile( $filename );
            unlink( $filename );
            exit;
        }
    }

    function ajax_create_affiliate() {

        check_ajax_referer( 'pw-affiliates-create-affiliate', 'security' );

        $form = array();
        parse_str( $_REQUEST['form'], $form );

        $code = wc_clean( $form['code'] );
        $name = wc_clean( $form['name'] );
        $user_id = intval( $form['user_id'] );

        $name = wc_clean( $name );
        if ( empty( $name ) ) {
            $result = __( 'Name cannot be empty.', 'pw-woocommerce-affiliates' );
        } else {
            if ( !empty( $code ) ) {
                $result = pwwa_add_affiliate( $code, $name, $user_id );
            } else {
                $result = pwwa_create_affiliate( $name, $user_id );
            }
        }

        if ( is_a( $result, 'PW_Affiliate' ) ) {
            wp_send_json_success( array( 'message' => sprintf( __( 'Added new affiliate: %s', 'pw-woocommerce-affiliates' ), $name ) ) );
        } else {
            wp_send_json_error( array( 'message' => $result ) );
        }
    }

    function ajax_edit_affiliate() {

        check_ajax_referer( 'pw-affiliates-edit-affiliate', 'security' );

        $form = array();
        parse_str( $_REQUEST['form'], $form );

        $affiliate_id = absint( $form['affiliate_id'] );
        $code = wc_clean( $form['code'] );
        $name = wc_clean( $form['name'] );
        $user_id = intval( $form['user_id'] );

        $name = wc_clean( $name );
        if ( empty( $name ) ) {
            $result = __( 'Name cannot be empty.', 'pw-woocommerce-affiliates' );

        } else if ( empty( $code ) ) {
            $result = __( 'Code cannot be empty.', 'pw-woocommerce-affiliates' );

        } else if ( empty( $affiliate_id ) ) {
            $result = __( 'Affiliate ID cannot be empty.', 'pw-woocommerce-affiliates' );

        } else {
            $result = pwwa_edit_affiliate( $affiliate_id, $code, $name, $user_id );
        }

        if ( is_a( $result, 'PW_Affiliate' ) ) {
            wp_send_json_success( array( 'message' => sprintf( __( 'Saved affiliate: %s', 'pw-woocommerce-affiliates' ), $name ) ) );
        } else {
            wp_send_json_error( array( 'message' => $result ) );
        }
    }

    function ajax_delete_affiliate() {

        check_ajax_referer( 'pw-affiliates-delete-affiliate', 'security' );

        $affiliate_id = absint( $_POST['affiliate_id'] );

        $affiliate = pwwa_get_affiliate( $affiliate_id );

        if ( $affiliate !== false ) {
            $affiliate->delete();
            wp_send_json_success();
        } else {
            wp_send_json_error( array( 'message' => __( 'Could not locate affiliate by ID', 'pw-woocommerce-affiliates' ) . ' ' . $affiliate_id ) );
        }
    }

    function ajax_save_commissions() {
        global $pw_affiliates;

        check_ajax_referer( 'pw-affiliates-save-commissions', 'security' );

        $form = array();
        parse_str( $_REQUEST['form'], $form );

        $default_commission = preg_replace( "/[^0-9.]/", "", $form['default_commission'] );
        if ( !is_numeric( $default_commission ) ) {
            $default_commission = '0';
        }

        update_option( 'pw_affiliates_default_commission', $default_commission );

        wp_send_json_success( array( 'message' => __( 'Saved', 'pw-woocommerce-affiliates' ) ) );
    }

    function ajax_save_settings() {
        global $pw_affiliates;

        check_ajax_referer( 'pw-affiliates-save-settings', 'security' );

        $form = array();
        parse_str( $_REQUEST['form'], $form );

        WC_Admin_Settings::save_fields( $this->settings, $form );

        $html = '<span style="color: blue;">' . __( 'Settings saved.', 'pw-woocommerce-affiliates' ) . '</span>';

        wp_send_json_success( array( 'html' => $html ) );
    }
}

global $pw_affiliates_admin;
$pw_affiliates_admin = new PW_Affiliates_Admin();

endif;