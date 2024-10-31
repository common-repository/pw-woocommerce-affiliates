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

$default_asc_columns = array( 'code', 'name' );

foreach ( $table_columns as $column_id => $column ) {
    ?>
    <th class="pwwa-admin-table-sortable-column" data-column="<?php echo $column_id; ?>">
        <?php
            echo $column['label'] . '&nbsp;';

            if ( $GLOBALS['pwwa_sort'] == $column_id ) {
                if ( $GLOBALS['pwwa_sort_order'] == 'asc' ) {
                    echo '<i class="fas fa-sort-up pwwa-sort"></i>';
                } else {
                    echo '<i class="fas fa-sort-down pwwa-sort"></i>';
                }
            } else {
                if ( $column['default_sort'] == 'asc' ) {
                    echo '<i class="pwwa-sort pwwa-invisible fas fa-sort-down"></i>';
                } else {
                    echo '<i class="pwwa-sort pwwa-invisible fas fa-sort-up"></i>';
                }
            }
        ?>
    </th>
    <?php
}
