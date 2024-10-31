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

global $pw_affiliates;
global $wpdb;

$affiliates_list = pwwa_affiliates_list();

$pw_affiliate_buttons['affiliates-report'] = array( 'title' => __( 'Affiliates report', 'pw-woocommerce-affiliates' ), 'icon' => 'list-alt' );
$pw_affiliate_buttons['create'] = array( 'title' => __( 'Create an Affiliate', 'pw-woocommerce-affiliates' ), 'icon' => 'plus-square' );
$pw_affiliate_buttons['commissions'] = array( 'title' => __( 'Commissions', 'pw-woocommerce-affiliates' ), 'icon' => 'money-bill-wave' );
$pw_affiliate_buttons['settings'] = array( 'title' => __( 'Settings', 'pw-woocommerce-affiliates' ), 'icon' => 'cog' );

require( 'header.php' );

?>
<div class="pwwa-main-content">
    <div class="pwwa-section-container">
        <div class="pwwa-sections">
            <?php
                $selected_class = 'pwwa-section-item-selected';
                foreach ( $pw_affiliate_buttons as $name => $section ) {
                    ?>
                    <div class="pwwa-section-item <?php echo $selected_class; $selected_class = ''; ?>" data-section="<?php echo esc_attr( $name ); ?>">
                        <i class="fas fa-<?php echo $section['icon']; ?> fa-3x"></i>
                        <div class="pwwa-reports-item-title"><?php echo esc_html( $section['title'] ); ?></div>
                    </div>
                    <?php
                }
            ?>
        </div>
    </div>
    <?php
        foreach ( $pw_affiliate_buttons as $name => $section ) {
            require( 'sections/' . $name . '.php' );
        }
    ?>
</div>
<?php

require( 'footer.php' );
