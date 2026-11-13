<?php
/**
 * Plugin Name: Copayapu - Reglas del checkout
 * Description: Agrega al checkout de WooCommerce la declaración de mayoría de edad (RF17) y el consentimiento opcional para ofertas personalizadas (RF20).
 * Version: 1.0.0
 * Author: Michael Taborda Escobar (proyecto académico)
 */
add_action( 'woocommerce_init', function () {
	if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) {
		return;
	}
	woocommerce_register_additional_checkout_field( [
		'id'       => 'copayapu/mayor-de-edad',
		'label'    => 'Declaro ser mayor de 18 años. Los productos con alcohol solo se venden a mayores de edad (Ley N.° 19.925).',
		'location' => 'order',
		'type'     => 'checkbox',
		'required' => true,
	] );
	woocommerce_register_additional_checkout_field( [
		'id'       => 'copayapu/ofertas-personalizadas',
		'label'    => 'Acepto recibir ofertas y recomendaciones según mis compras. Puedo retirar este consentimiento en Mi cuenta.',
		'location' => 'order',
		'type'     => 'checkbox',
		'required' => false,
	] );
} );
