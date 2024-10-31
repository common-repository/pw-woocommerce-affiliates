<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $pw_affiliate_code;

if ( !empty( $pw_affiliate_code ) ) {
	?>
    <tr class="pw-affiliate-code-checkout">
        <th><?php _e( 'Affiliate Code', 'pw-woocommerce-affiliates' ); ?></th>
        <td data-title="<?php esc_attr_e( 'Affiliate Code', 'pw-woocommerce-affiliates' ); ?>"><?php echo esc_html( $pw_affiliate_code ); ?></td>
    </tr>
	<?php
}
