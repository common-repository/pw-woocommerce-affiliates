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

if ( is_array( $affiliates ) ) {

    ?>
    <div class="pwwa-admin-report-table-container">
        <button id="pwwa-affiliates-report-export-button" class="button pwwa-export-button"><i class="fas fa-file-export"></i> <?php _e( 'Export', 'pw-woocommerce-affiliates' ); ?></button>

        <table id="pwwa-affiliates-table" class="pwwa-admin-table">
            <thead>
                <tr>
                    <?php
                        $table_columns = pwwa_affiliates_report_columns();
                        require( 'table-header.php' );
                    ?>
                    <th>
                        &nbsp;
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if ( count( $affiliates ) > 0 ) {
                        foreach ( $affiliates as $affiliate ) {
                            ?>
                            <tr data-id="<?php echo $affiliate->pimwick_affiliate_id; ?>"
                                data-code="<?php echo esc_html( $affiliate->code ); ?>"
                                data-name="<?php echo esc_html( $affiliate->name ); ?>"
                                data-user_id="<?php echo esc_html( $affiliate->user_id ); ?>"
                            >
                                <td>
                                    <?php echo esc_html( $affiliate->name ); ?>
                                </td>
                                <td>
                                    <?php echo esc_html( $affiliate->user ); ?>
                                </td>
                                <td>
                                    <?php echo esc_html( $affiliate->code ); ?>
                                </td>
                                <td>
                                    <a href="<?php echo $affiliate->url; ?>" class="pwwa-copy-url" title="<?php _e( 'Copy URL to Clipboard', 'pw-woocommerce-affiliates' ); ?>"><?php echo $affiliate->url; ?></a>
                                    <i class="fas fa-copy fa-fw" style="visibility: hidden;"></i>
                                </td>
                                <td>
                                    <?php echo number_format( $affiliate->order_count ); ?>
                                </td>
                                <td>
                                    <?php echo wc_price( $affiliate->revenue ); ?>
                                </td>
                                <td>
                                    <?php echo wc_price( $affiliate->total_commission ); ?>
                                </td>
                                <td>
                                    <a href="#" class="button pwwa-view-orders" title="<?php _e( 'View Orders', 'pw-woocommerce-affiliates' ); ?>"><i class="fas fa-external-link-alt"></i> <?php _e( 'View Orders', 'pw-woocommerce-affiliates' ); ?></a>
                                    <a href="#" class="button pwwa-edit-affiliate" title="<?php _e( 'Edit Affiliate', 'pw-woocommerce-affiliates' ); ?>"><i class="fas fa-edit"></i> <?php _e( 'Edit Affiliate', 'pw-woocommerce-affiliates' ); ?></a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="<?php echo count( $table_columns ) + 1; ?>">
                                <?php _e( 'No results', 'pw-woocommerce-affiliates' ); ?>
                            </td>
                        </tr>
                        <?php
                    }
                ?>
            </tbody>
        </table>
    </div>
    <?php
} else {
    ?>
    <div class="pwwa-admin-message pwwa-admin-error"><?php echo esc_html( $affiliates ); ?></div>
    <?php
}