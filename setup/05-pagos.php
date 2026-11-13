<?php
/**
 * Copayapu - activa Webpay Plus en el ambiente de integración (pruebas) de Transbank.
 * Usa el código de comercio y la llave públicos de integración que trae el propio plugin.
 * Ejecutar con: wp eval-file /setup/05-pagos.php
 */
$settings = get_option( 'woocommerce_transbank_webpay_plus_rest_settings', [] );
$settings = array_merge( is_array( $settings ) ? $settings : [], [
	'enabled'                    => 'yes',
	'environment'                => 'TEST',
	'after_payment_order_status' => 'processing',
	'payment_gateway_description' => 'Permite el pago de productos y/o servicios, con tarjetas de crédito, débito y prepago a través de Webpay Plus',
] );
update_option( 'woocommerce_transbank_webpay_plus_rest_settings', $settings );
echo "Webpay Plus activo en ambiente de integración.\n";
