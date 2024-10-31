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

$begin_date = isset( $_REQUEST['begin_date'] ) ? addslashes( date( 'Y-m-d 00:00', strtotime( $_REQUEST['begin_date'] ) ) ) : date( 'Y-m-01 00:00:00' );
$end_date = isset( $_REQUEST['end_date'] ) ? addslashes( date( 'Y-m-d 00:00', strtotime( $_REQUEST['end_date'] ) ) ) : date( 'Y-m-01 00:00', strtotime( '+1 month' ) );

?>
<div id="pwwa-section-affiliates-report" class="pwwa-section" style="display: block;">
    <form id="pwwa-affiliates-report-form">
        <input type="hidden" name="report_type" value="affiliates">
        <input type="hidden" name="sort" value="">
        <input type="hidden" name="sort_order" value="">

        <table>
            <tr>
                <td style="text-align: right;"><?php _e( 'Affiliate', 'pw-woocommerce-affiliates' ); ?></td>
                <td>
                    <select name="affiliate_id">
                        <option value="0">
                            <?php esc_html_e( 'All Affiliates', 'pw-woocommerce-affiliates' ); ?>
                        </option>

                        <?php
                            foreach ( $affiliates_list as $affiliate ) {
                                ?>
                                <option value="<?php echo esc_attr( $affiliate->pimwick_affiliate_id ); ?>">
                                    <?php echo esc_html( sprintf( '%s (%s)', $affiliate->name, $affiliate->code ) ); ?>
                                </option>
                                <?php
                            }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td style="text-align: right;"><?php _e( 'Order Dates', 'pw-woocommerce-affiliates' ); ?></td>
                <td>
                    <input type="text" name="begin_date" class="pwwa-date" value="<?php echo date( 'Y-m-d', strtotime( $begin_date ) ); ?>" autocomplete="off" required>
                    <input type="text" name="end_date" class="pwwa-date" value="<?php echo date( 'Y-m-d', strtotime( $end_date ) ); ?>" autocomplete="off" required>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <input type="submit" class="button button-primary" value="<?php _e( 'Apply filters', 'pw-woocommerce-affiliates' ); ?>">
                </td>
            </tr>
        </table>
    </form>
    <div id="pwwa-affiliates-report-results" class="pwwa-section-reports-results"></div>
    <div id="pwwa-edit-affiliate-container">
        <a href="#" class="button pwwa-edit-affiliate-cancel pwwa-edit-affiliate-close-button"><i class="fas fa-times"></i></a>
        <form id="pwwa-edit-affiliate-form">
            <input type="hidden" id="pwwa-edit-affiliate-id" name="affiliate_id" value="">
            <p class="form-field">
                <label for="pwwa-edit-name"><?php _e( 'Affiliate name', 'pw-woocommerce-affiliates' ); ?></label>
                <input type="text" name="name" id="pwwa-edit-name" required>
            </p>
            <p class="form-field">
                <label for="pwwa-edit-user_id"><?php _e( 'User', 'pw-woocommerce-affiliates' ); ?></label>
                <?php
                    wp_dropdown_users( array(
                        'show_option_none'  => 'None',
                        'name'              => 'user_id',
                        'id'                => 'pwwa-edit-user_id',
                        'show'              => 'user_login',
                    ) );
                ?>
            </p>
            <p class="form-field">
                <label for="pwwa-edit-code"><?php _e( 'Affiliate code', 'pw-woocommerce-affiliates' ); ?></label>
                <input type="text" name="code" id="pwwa-edit-code" class="pwwa-alphanumeric" required>
            </p>
            <div class="pwwa-input-field-container">
                <input type="submit" id="pwwa-edit-affiliate-save-button" class="button button-primary" value="<?php _e( 'Save', 'pw-woocommerce-affiliates' ); ?>">
                <a href="#" class="pwwa-edit-affiliate-cancel"><?php _e( 'Cancel', 'pw-woocommerce-affiliates' ); ?></a>
                <a href="#" class="pwwa-edit-affiliate-delete"><?php _e( 'Delete Affiliate', 'pw-woocommerce-affiliates' ); ?></a>
            </div>
            <div class="pwwa-admin-message"></div>
        </form>
    </div>
</div>
<?php
