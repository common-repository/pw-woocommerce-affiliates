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

$affiliate_code = pwwa_current_user_affiliate_code();
if ( $affiliate_code === false ) {
    echo __( 'This account is not linked to an Affiliate account.', 'pw-woocommerce-affiliates' );
    return;
}

global $pw_affiliates;

$begin_date = isset( $_REQUEST['begin_date'] ) ? addslashes( date( 'Y-m-d 00:00', strtotime( $_REQUEST['begin_date'] ) ) ) : date( 'Y-m-01 00:00:00' );
$end_date = isset( $_REQUEST['end_date'] ) ? addslashes( date( 'Y-m-d 00:00', strtotime( $_REQUEST['end_date'] ) ) ) : date( 'Y-m-01 00:00', strtotime( '+1 month' ) );

$affiliate = new PW_Affiliate( $affiliate_code );

?>
<style>
    .pwwa-title {
        font-weight: 600;
        font-size: 150%;
    }

    .pwwa-form {
        margin-top: 2.0em;
        margin-bottom: 1.0em;
    }

    .pwwa-stats {
        display: flex;
    }

    .pwwa-section {
        margin-right: 32px;
    }
</style>

<div class="pwwa-title"><?php _e( 'Affiliate URL', 'pw-woocommerce-affiliates' ); ?></div>
<a href="<?php echo $affiliate->get_url(); ?>" class="pwwa-copy-url" title="<?php _e( 'Copy URL to Clipboard', 'pw-woocommerce-affiliates' ); ?>"><?php echo $affiliate->get_url(); ?></a>

<?php
    if ( !empty( $affiliate->get_commission() ) ) {
        ?>
        <form class="pwwa-form" method="GET">
            <div><?php _e( 'Order Dates', 'pw-woocommerce-affiliates' ); ?></div>
            <input type="text" name="begin_date" class="pwwa-date" value="<?php echo date( 'Y-m-d', strtotime( $begin_date ) ); ?>" autocomplete="off" required>
            <input type="text" name="end_date" class="pwwa-date" value="<?php echo date( 'Y-m-d', strtotime( $end_date ) ); ?>" autocomplete="off" required>
            <input type="submit" class="button button-primary" value="<?php _e( 'Filter', 'pw-woocommerce-affiliates' ); ?>">
        </form>
        <div class="pwwa-stats">
            <div class="pwwa-section">
                <div class="pwwa-title"><?php _e( 'Commission rate', 'pw-woocommerce-affiliates' ); ?></div>
                <div>
                    <?php echo $affiliate->get_commission(); ?> %
                </div>
            </div>
            <div class="pwwa-section">
                <div class="pwwa-title"><?php _e( 'Orders', 'pw-woocommerce-affiliates' ); ?></div>
                <div>
                    <?php echo count( $affiliate->get_orders( $begin_date, $end_date ) ); ?>
                </div>
            </div>
            <div class="pwwa-section">
                <div class="pwwa-title"><?php _e( 'Commission', 'pw-woocommerce-affiliates' ); ?></div>
                <div>
                    <?php echo wc_price( $affiliate->get_total_commission( $begin_date, $end_date ) ); ?>
                </div>
            </div>
        </div>
    <?php
}
