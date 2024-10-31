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

if ( ! function_exists( 'pwwa_current_user_affiliate_code' ) ) {
    function pwwa_current_user_affiliate_code() {
        global $wpdb;

        $user = wp_get_current_user();

        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT
                a.code
            FROM
                {$wpdb->pimwick_affiliate} AS a
            WHERE
                a.user_id = %d
                AND a.active = 1
        ", $user->ID ) );

        if ( is_array( $results ) && count( $results ) > 0 ) {
            return $results[0]->code;
        } else {
            return false;
        }
    }
}

if ( ! function_exists( 'pwwa_affiliate_url' ) ) {
    function pwwa_affiliate_url( $code = '' ) {
        global $pw_affiliates;

        $shop_page_url = apply_filters( 'pw_affiliates_shop_page_url', get_permalink( wc_get_page_id( 'shop' ) ) );
        $url_fields = array_map( 'trim', explode( ',', $pw_affiliates->url_fields ) );
        $url_prefix = add_query_arg( $url_fields[0], '', $shop_page_url );

        if ( !empty( $code ) ) {
            return $url_prefix . '=' . $code;
        } else {
            return $url_prefix;
        }
    }
}

if ( ! function_exists( 'pwwa_affiliates_report_columns' ) ) {
    function pwwa_affiliates_report_columns() {
        $columns = array(
            'name'              => array( 'label' => __( 'Affiliate name', 'pw-woocommerce-affiliates' ), 'default_sort' => 'asc' ),
            'user'              => array( 'label' => __( 'User', 'pw-woocommerce-affiliates' ), 'default_sort' => 'asc' ),
            'code'              => array( 'label' => __( 'Affiliate code', 'pw-woocommerce-affiliates' ), 'default_sort' => 'asc' ),
            'url'               => array( 'label' => __( 'URL', 'pw-woocommerce-affiliates' ), 'default_sort' => 'asc' ),
            'order_count'       => array( 'label' => __( 'Order count', 'pw-woocommerce-affiliates' ), 'default_sort' => 'desc' ),
            'revenue'           => array( 'label' => __( 'Revenue', 'pw-woocommerce-affiliates' ), 'default_sort' => 'desc' ),
            'total_commission'  => array( 'label' => __( 'Commission', 'pw-woocommerce-affiliates' ), 'default_sort' => 'desc' ),
        );

        return apply_filters( 'pwwa_affiliates_report_columns', $columns );
    }
}

if ( ! function_exists( 'pwwa_affiliates_report' ) ) {
    function pwwa_affiliates_report( $affiliate_id, $begin_date, $end_date, $sort, $sort_order, $active, $limit ) {
        global $wpdb;
        global $pw_affiliates;

        $order_by = '';
        if ( !empty( $sort ) ) {
            switch ( $sort ) {
                case 'name':
                    $order_by = ' ORDER BY a.name';
                break;

                case 'user':
                    $order_by = ' ORDER BY user';
                break;

                case 'code':
                    $order_by = ' ORDER BY a.code';
                break;

                case 'url':
                    $order_by = ' ORDER BY a.code';
                break;

                case 'commission':
                    $order_by = ' ORDER BY a.commission';
                break;

                case 'order_count':
                    $order_by = ' ORDER BY order_count';
                break;

                case 'revenue':
                    $order_by = ' ORDER BY revenue';
                break;

                case 'total_commission':
                    $order_by = ' ORDER BY total_commission';
                break;
            }

            if ( !empty( $order_by ) && $sort_order == 'desc' ) {
                $order_by .= ' DESC';
            }

            if ( !empty( $order_by ) ) {
                $order_by .= ', a.name';
            }
        }

        $url_prefix = pwwa_affiliate_url();

        $total_select = "COALESCE(SUM(oim_line_total.meta_value), 0)";
        $join_tax = '';
        $pre_tax = get_option( 'pw_affiliates_commission_before_tax', 'yes' );
        if ( 'no' == $pre_tax ) {
            $total_select = "COALESCE(SUM(oim_line_total.meta_value + oim_line_tax.meta_value), 0)";
            $join_tax = "LEFT JOIN `{$wpdb->prefix}woocommerce_order_itemmeta` AS oim_line_tax ON (oim_line_tax.order_item_id = oi.order_item_id AND oim_line_tax.meta_key = '_line_tax')";
        }

        //
        // NOTE: If you add/change columns here, also update pwwa_affiliates_report_columns() above.
        //
        $query = $wpdb->prepare( "
            SELECT
                a.pimwick_affiliate_id,
                a.name,
                a.user_id,
                CONCAT(u.display_name, ' (', u.user_login, ')') AS user,
                a.code,
                CONCAT('$url_prefix=', a.code) AS url,
                a.active,
                a.create_user_id,
                a.create_date,
                COUNT(DISTINCT o.ID) AS order_count,
                COALESCE(SUM((
                    SELECT
                        $total_select
                    FROM
                        `{$wpdb->prefix}woocommerce_order_items` AS oi
                    LEFT JOIN
                        `{$wpdb->prefix}woocommerce_order_itemmeta` AS oim_line_total ON (oim_line_total.order_item_id = oi.order_item_id AND oim_line_total.meta_key = '_line_total')
                    $join_tax
                    WHERE
                        oi.order_id = o.ID
                        AND oi.order_item_type = 'line_item'
                )), 0) AS revenue,
                COALESCE(SUM(o_commission.meta_value), 0) AS total_commission
            FROM
                `{$wpdb->pimwick_affiliate}` AS a
            LEFT JOIN
                `{$wpdb->prefix}users` AS u ON (CONVERT(u.ID USING utf8) = CONVERT(a.user_id USING utf8))
            LEFT JOIN
                `{$wpdb->postmeta}` AS om_code ON (om_code.meta_key = '_pw_affiliate_code' AND CONVERT(om_code.meta_value USING utf8) = CONVERT(a.code USING utf8))
            LEFT JOIN
                `{$wpdb->posts}` AS o ON (o.ID = om_code.post_id AND o.post_status = 'wc-completed' AND o.post_date BETWEEN %s AND %s)
            LEFT JOIN
                `{$wpdb->postmeta}` AS o_commission ON (o_commission.post_id = o.ID AND o_commission.meta_key = '_pw_affiliate_commission')
            WHERE
                (%d = 0 or a.pimwick_affiliate_id = %d)
                AND a.active = %d
            GROUP BY
                a.pimwick_affiliate_id,
                a.code,
                a.name,
                a.commission,
                a.active,
                a.create_user_id,
                a.create_date
            $order_by
            LIMIT
                %d
            ",
            $begin_date,
            $end_date,
            $affiliate_id,
            $affiliate_id,
            $active,
            absint( $limit )
        );

        $affiliates = $wpdb->get_results( $query );

        if ( empty( $wpdb->last_error) && null !== $affiliates ) {
            return $affiliates;
        } else {
            return sprintf( __( 'Error while getting Affiliates from the database: %s', 'pw-woocommerce-affiliates' ), $wpdb->last_error );
        }
    }
}

if ( ! function_exists( 'pwwa_affiliates_list' ) ) {
    function pwwa_affiliates_list( $active = true ) {
        global $wpdb;

        $results = $wpdb->get_results( "
            SELECT
                a.pimwick_affiliate_id,
                a.name,
                a.code
            FROM
                `{$wpdb->pimwick_affiliate}` AS a
            WHERE
                a.active = true
            ORDER BY
                a.name,
                a.code
        " );

        return $results;
    }
}

if ( ! function_exists( 'pwwa_get_affiliate' ) ) {
    function pwwa_get_affiliate( $id ) {
        global $wpdb;

        if ( !empty( absint( $id ) ) ) {
            $result = $wpdb->get_row( $wpdb->prepare( "SELECT `code` FROM `{$wpdb->pimwick_affiliate}` WHERE pimwick_affiliate_id = %d", absint( $id ) ) );
            if ( null !== $result ) {
                return new PW_Affiliate( $result->code );
            }
        }

        return false;
    }
}

if ( ! function_exists( 'pwwa_get_active_affiliate' ) ) {
    function pwwa_get_active_affiliate( $code ) {
        $affiliate = new PW_Affiliate( $code );
        if ( empty( $affiliate->get_error_message() ) && $affiliate->get_active() ) {
            return $affiliate;
        } else {
            return false;
        }
    }
}

if ( ! function_exists( 'pwwa_add_affiliate' ) ) {
    function pwwa_add_affiliate( $code, $name, $user_id ) {
        global $wpdb;

        $code = wc_clean( $code );
        $code = preg_replace( '/[^\w]/', '', $code );
        if ( empty( $code ) ) {
            return __( 'Affiliate Code cannot be empty.', 'pw-woocommerce-affiliates' );
        }

        $name = wc_clean( $name );
        if ( empty( $name ) ) {
            return __( 'Name cannot be empty.', 'pw-woocommerce-affiliates' );
        }

        $user_id = intval( $user_id );
        if ( $user_id <= 0 ) {
            $user_id = null;
        }

        $result = $wpdb->insert( $wpdb->pimwick_affiliate, array ( 'code' => $code, 'name' => $name, 'user_id' => $user_id, 'create_user_id' => get_current_user_id() ) );

        if ( $result !== false ) {
            $affiliate = pwwa_get_affiliate( $wpdb->insert_id );

            return $affiliate;
        } else {
            return $wpdb->last_error;
        }
    }
}

if ( ! function_exists( 'pwwa_create_affiliate' ) ) {
    function pwwa_create_affiliate( $name, $user_id ) {
        // Failsafe. If we haven't generated a code after this many tries, throw an error.
        $attempts = 0;
        $max_attempts = 100;

        // Get a random Code and insert it. If the insertion fails, it is already in use.
        do {
            $attempts++;

            $code = pwwa_random_code();
            $affiliate = pwwa_add_affiliate( $code, $name, $user_id );

        } while ( !( $affiliate instanceof PW_Affiliate ) && $attempts < $max_attempts );

        if ( $affiliate instanceof PW_Affiliate ) {
            return $affiliate;
        } else {
            return sprintf( __( 'Failed to generate a unique random affiliate code after %d attempts. %s', 'pw-woocommerce-affiliates' ), $attempts, $affiliate );
        }
    }
}

if ( ! function_exists( 'pwwa_edit_affiliate' ) ) {
    function pwwa_edit_affiliate( $pimwick_affiliate_id, $code, $name, $user_id ) {
        global $wpdb;

        $pimwick_affiliate_id = absint( $pimwick_affiliate_id );
        if ( empty( $pimwick_affiliate_id ) ) {
            return __( 'pimwick_affiliate_id cannot be empty.', 'pw-woocommerce-affiliates' );
        }

        $code = wc_clean( $code );
        $code = preg_replace( '/[^\w]/', '', $code );
        if ( empty( $code ) ) {
            return __( 'Affiliate Code cannot be empty.', 'pw-woocommerce-affiliates' );
        }

        $name = wc_clean( $name );
        if ( empty( $name ) ) {
            return __( 'Name cannot be empty.', 'pw-woocommerce-affiliates' );
        }

        $user_id = intval( $user_id );
        if ( $user_id <= 0 ) {
            $user_id = null;
        }

        $affiliate = pwwa_get_affiliate( $pimwick_affiliate_id );
        $old_code = $affiliate->get_code();

        $result = $wpdb->update( $wpdb->pimwick_affiliate, array ( 'code' => $code, 'name' => $name, 'user_id' => $user_id ), array( 'pimwick_affiliate_id' => $pimwick_affiliate_id ) );

        if ( $result !== false ) {
            if ( $code != $old_code ) {
                // Move any orders over to the new code.
                $result = $wpdb->update( $wpdb->postmeta, array ( 'meta_value' => $code ), array( 'meta_key' => '_pw_affiliate_code', 'meta_value' => $old_code ) );
            }

            $affiliate = pwwa_get_affiliate( $pimwick_affiliate_id );

            return $affiliate;
        } else {
            return $wpdb->last_error;
        }
    }
}

if ( ! function_exists( 'pwwa_random_code' ) ) {
    function pwwa_random_code() {
        $code = '';

        for ( $counter = 0; $counter < PWWA_RANDOM_CODE_LENGTH; $counter++ ) {
            $random = str_shuffle( PWWA_RANDOM_CODE_CHARSET );
            $code .= $random[0];
        }

        return $code;
    }
}

if ( ! function_exists( 'pwwa_get_product_commission' ) ) {
    function pwwa_get_product_commission( $product, $affiliate = '', $ignore_product_commission = false ) {
        global $pw_affiliates;

        if ( is_numeric( $product ) ) {
            $product = wc_get_product( $product );
        }

        $commissions = array();

        // The overall site default.
        $commissions[] = $pw_affiliates->default_commission;

        // Product (or Variation)
        if ( false === $ignore_product_commission ) {
            $commissions[] = $product->get_meta( '_pw_affiliate_commission' );
        }

        // Parent product (for Variations)
        if ( $product->is_type( 'variation' ) ) {
            $product = wc_get_product( $product->get_parent_id() );
            $commissions[] = $product->get_meta( '_pw_affiliate_commission' );
        }

        // All categories for the product.
        foreach ( $product->get_category_ids( 'edit' ) as $category_id ) {
            $commissions[] = get_term_meta( $category_id, 'pw_affiliates_commission', true );
        }

        // The affiliate.
        if ( !empty( $affiliate ) ) {
            $commissions[] = $affiliate->get_commission();
        }

        arsort( $commissions );

        return array_shift( $commissions );
    }
}