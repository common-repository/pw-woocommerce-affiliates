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

global $pw_affiliates_admin;

?>
<div id="pwwa-section-commissions" class="pwwa-section">
    <form id="pwwa-save-commissions-form" method="post">
        <div class="pwwa-section-subtitle">
            <?php _e( 'Default commission', 'pw-woocommerce-affiliates' ); ?>
        </div>
        <div>
            <?php _e( 'Used when creating new Affiliates. Can be from 0 to 100% with up to 4 decimal precision.', 'pw-woocommerce-affiliates' ); ?>
        </div>
        <div>
            <input type="text" class="pwwa-input-text-small" name="default_commission" value="<?php echo number_format( get_option( 'pw_affiliates_default_commission', '0' ), 4 ); ?>" autocomplete="off" required>%
        </div>
        <div style="margin-top: 4px;">
            <input type="submit" id="pwwa-save-commissions-button" class="button button-primary" value="<?php _e( 'Save', 'pw-woocommerce-affiliates' ); ?>">
            <div id="pwwa-save-commissions-message"></div>
        </div>

        <div class="pwwa-section-spacer"></div>
        <div class="pwwa-section-subtitle">
            <?php _e( 'Commissions by Category', 'pw-woocommerce-affiliates' ); ?>
        </div>
        <div>
            <?php
                _e( 'As a way to encourage affiliates to push certain categories, consider offering a higher commission for that category.', 'pw-woocommerce-affiliates' );
            ?>
            <br>
            <?php
                printf( __( 'To specify a commission by category, upgrade to %s', 'pw-woocommerce-affiliates' ), '<a href="https://www.pimwick.com/affiliates/" target="_blank"><strong>PW WooCommerce Affiliates Pro</strong></a>' );
            ?>
        </div>

        <div class="pwwa-section-spacer"></div>
        <div class="pwwa-section-subtitle">
            <?php _e( 'Commissions by Product', 'pw-woocommerce-affiliates' ); ?>
        </div>
        <div>
            <?php
                _e( 'Individual products can have their own commission rate.', 'pw-woocommerce-affiliates' );
            ?>
            <br>
            <?php
                printf( __( 'To specify a commissions by product, upgrade to %s', 'pw-woocommerce-affiliates' ), '<a href="https://www.pimwick.com/affiliates/" target="_blank"><strong>PW WooCommerce Affiliates Pro</strong></a>' );
            ?>
        </div>

        <div class="pwwa-section-spacer"></div>
        <div class="pwwa-section-subtitle">
            <?php _e( 'Commissions by Affiliate', 'pw-woocommerce-affiliates' ); ?>
        </div>
        <div>
            <?php
                printf( __( 'Each Affiliate can have their own commission rate. To specify a commissions by affiliate, upgrade to %s', 'pw-woocommerce-affiliates' ), '<a href="https://www.pimwick.com/affiliates/" target="_blank"><strong>PW WooCommerce Affiliates Pro</strong></a>' );
            ?>
        </div>
    </form>
</div>
