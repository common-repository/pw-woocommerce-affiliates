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

?>
<div id="pwwa-section-create" class="pwwa-section">
    <form id="pwwa-create-affiliate-form">
        <p class="form-field">
            <label for="pwwa-create-name"><?php _e( 'Affiliate name', 'pw-woocommerce-affiliates' ); ?></label>
            <input type="text" name="name" id="pwwa-create-name" required>
        </p>
        <p class="form-field">
            <input type="checkbox" name="create-code-automatically" id="pwwa-create-code-automatically" checked="checked">
            <label for="pwwa-create-code-automatically"><?php _e( 'Automatically generate an affiliate code', 'pw-woocommerce-affiliates' ); ?></label>
        </p>
        <p id="pwwa-create-manual-code-container" class="form-field pwwa-hidden">
            <label for="pwwa-create-code"><?php _e( 'Affiliate code', 'pw-woocommerce-affiliates' ); ?></label>
            <input type="text" name="code" id="pwwa-create-code" class="pwwa-alphanumeric">
        </p>
        <p class="form-field">
            <label for="pwwa-create-user_id"><?php _e( 'User who can see the reports for this Affiliate when logged in.', 'pw-woocommerce-affiliates' ); ?></label>
            <?php
                wp_dropdown_users( array(
                    'show_option_none'  => 'None',
                    'name'              => 'user_id',
                    'id'                => 'pwwa-create-user_id',
                    'show'              => 'user_login',
                ) );
            ?>
        </p>
        <p class="form-field">
            <?php
                printf( __( 'Commission rate is %s', 'pw-woocommerce-affiliates' ), number_format( $pw_affiliates->default_commission, 4 ) . '%' );
            ?>
            <br>
            <?php
                printf( __( 'Want to specify a custom commission for this affiliate? Upgrade to %s', 'pw-woocommerce-affiliates' ), '<a href="https://www.pimwick.com/affiliates/" target="_blank"><strong>PW WooCommerce Affiliates Pro</strong></a>' );
            ?>
        </p>
        <div class="pwwa-input-field-container" style="margin-top: 12px;">
            <input type="submit" id="pwwa-create-affiliate-save-button" class="button button-primary" value="<?php _e( 'Create affiliate', 'pw-woocommerce-affiliates' ); ?>">
        </div>
        <div class="pwwa-admin-message"></div>
    </form>
</div>
