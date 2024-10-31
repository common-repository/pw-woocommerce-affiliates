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
<div id="pwwa-section-settings" class="pwwa-section">
    <form id="pwwa-save-settings-form" method="post">
        <?php
            $settings = $pw_affiliates_admin->settings;
            $settings[0]['title'] = '';

            WC_Admin_Settings::output_fields( $settings );
        ?>
        <p><input type="submit" id="pwwa-save-settings-button" class="button button-primary" value="<?php _e( 'Save settings', 'pw-woocommerce-affiliates' ); ?>"></p>
        <div id="pwwa-save-settings-message"></div>
    </form>
</div>
