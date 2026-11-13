<?php
/**
 * Copayapu - ajustes visuales de la tienda: páginas en español, imágenes de categorías,
 * portada, pie de página y CSS adicional para Storefront.
 * Ejecutar con: wp eval-file /setup/04-ajustes-visuales.php
 */
// Páginas de WooCommerce en español
foreach ( [ 'shop' => 'Tienda', 'cart' => 'Carrito', 'checkout' => 'Finalizar compra', 'myaccount' => 'Mi cuenta' ] as $k => $t ) {
	wp_update_post( [ 'ID' => wc_get_page_id( $k ), 'post_title' => $t ] );
}

// Imagen de cada categoría (tomada de un producto representativo)
$map = [ 'Aceites y aceitunas' => 'aceite500', 'Pasas y frutos secos' => 'pasas', 'Dulces y conservas' => 'chanar',
	'Pajarete y licores' => 'pajarete', 'Canastas de regalo' => 'cflorido', 'Artesanía' => 'cobre', 'Despensa costera' => 'sal' ];
foreach ( $map as $cat => $key ) {
	$term = get_term_by( 'name', $cat, 'product_cat' );
	$pid  = wc_get_product_id_by_sku( 'CPY-' . strtoupper( $key ) );
	update_term_meta( $term->term_id, 'thumbnail_id', get_post_thumbnail_id( $pid ) );
}

// Portada: banner como imagen del contenido (no como fondo) y texto de propuesta de valor
$home   = (int) get_option( 'page_on_front' );
$banner = get_post_thumbnail_id( $home );
$src    = wp_get_attachment_url( $banner );
delete_post_thumbnail( $home );
wp_update_post( [ 'ID' => $home, 'post_content' =>
	'<!-- wp:image {"id":' . $banner . ',"sizeSlug":"full","linkDestination":"custom","align":"wide"} --><figure class="wp-block-image alignwide size-full"><a href="/categoria-producto/canastas-de-regalo/"><img src="' . esc_url( $src ) . '" alt="Campaña Desierto Florido: 15 % de descuento en canastas de regalo con el código FLORIDO15" class="wp-image-' . $banner . '"/></a></figure><!-- /wp:image -->' .
	'<!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center"><strong>Sabores de Atacama, cosechados en los valles de Copiapó y del Huasco.</strong> Aceite de oliva D. O. Valle del Huasco, pasas, chañar, miel y canastas de regalo de productores locales. Despachamos a todo Chile en 24 a 72 horas hábiles y puedes retirar gratis en nuestra tienda de Copiapó.</p><!-- /wp:paragraph -->' ] );

// Pie de página con widgets de texto
$sidebars = get_option( 'sidebars_widgets', [] );
$texts    = get_option( 'widget_text', [] );
$blocks = [
	'footer-1' => [ 'Copayapu, Sabores de Atacama', 'Productos de los valles de Copiapó y del Huasco. Calle Atacama 520, Copiapó. Negocio ficticio creado para la Evaluación N.º 3 de E-Commerce (INACAP).' ],
	'footer-2' => [ 'Ayuda', '<a href="/cambios-devoluciones-y-retracto/">Cambios y derecho a retracto (10 días)</a><br><a href="/preguntas-frecuentes/">Preguntas frecuentes</a><br><a href="/canastas-corporativas/">Canastas corporativas</a>' ],
	'footer-3' => [ 'Legal y privacidad', '<a href="/terminos-y-condiciones/">Términos y condiciones</a><br><a href="/politica-de-privacidad/">Política de privacidad (Ley N.° 21.719)</a><br>Pagos seguros con Webpay Plus y Mercado Pago' ],
	'footer-4' => [ 'Síguenos', 'Instagram @copayapu.cl<br>Facebook Copayapu<br>TikTok @copayapu<br>WhatsApp +56 9 0000 0000' ],
];
$n = 100;
foreach ( $blocks as $sb => [ $title, $text ] ) {
	$texts[ $n ] = [ 'title' => $title, 'text' => $text, 'filter' => true, 'visual' => true ];
	$sidebars[ $sb ] = [ "text-$n" ];
	$n++;
}
$texts['_multiwidget'] = 1;
update_option( 'widget_text', $texts );
update_option( 'sidebars_widgets', $sidebars );

// CSS adicional del tema
wp_update_custom_css_post( '
.page-template-template-homepage .entry-header{display:none}
.page-template-template-homepage .entry-content{max-width:none}
.page-template-template-homepage .wp-block-image img{border-radius:14px}
.storefront-popular-products,.storefront-on-sale-products,.storefront-woocommerce-brands{display:none}
.site-header{border-bottom:1px solid #D8DCCD}
.site-header .custom-logo-link img{max-width:230px}
.woocommerce-store-notice,p.demo_store{background:#2F3D14}
.onsale{color:#B4245C;border-color:#B4245C}
.star-rating span::before,.star-rating::before{color:#A55D27}
', 'storefront' );

update_option( 'woocommerce_demo_store', 'no' );

// Filtros de la tienda en la barra lateral (RF03)
$sb = get_option( 'sidebars_widgets', [] );
update_option( 'widget_woocommerce_product_categories', [ 1 => [ 'title' => 'Categorías', 'orderby' => 'name', 'dropdown' => 0, 'count' => 1, 'hierarchical' => 1, 'show_children_only' => 0, 'hide_empty' => 1, 'max_depth' => '' ], '_multiwidget' => 1 ] );
update_option( 'widget_woocommerce_layered_nav', [ 1 => [ 'title' => 'Origen', 'attribute' => 'origen', 'display_type' => 'list', 'query_type' => 'and' ], 2 => [ 'title' => 'Ocasión', 'attribute' => 'ocasion', 'display_type' => 'list', 'query_type' => 'and' ], '_multiwidget' => 1 ] );
update_option( 'widget_woocommerce_price_filter', [ 1 => [ 'title' => 'Filtrar por precio' ], '_multiwidget' => 1 ] );
update_option( 'widget_woocommerce_product_search', [ 1 => [ 'title' => '' ], '_multiwidget' => 1 ] );
$sb['sidebar-1'] = [ 'woocommerce_product_search-1', 'woocommerce_product_categories-1', 'woocommerce_layered_nav-1', 'woocommerce_layered_nav-2', 'woocommerce_price_filter-1' ];
update_option( 'sidebars_widgets', $sb );

// Categoría por defecto y direcciones en español
$u = get_term_by( 'slug', 'uncategorized', 'product_cat' );
if ( $u ) { wp_update_term( $u->term_id, 'product_cat', [ 'name' => 'Sin categoría', 'slug' => 'sin-categoria' ] ); }
$pl = (array) get_option( 'woocommerce_permalinks', [] );
$pl['category_base'] = 'categoria-producto'; $pl['tag_base'] = 'etiqueta-producto'; $pl['product_base'] = '/producto'; $pl['attribute_base'] = '';
update_option( 'woocommerce_permalinks', $pl );
foreach ( [ 'shop' => 'tienda', 'cart' => 'carrito', 'checkout' => 'finalizar-compra', 'myaccount' => 'mi-cuenta' ] as $k => $slug ) {
	wp_update_post( [ 'ID' => wc_get_page_id( $k ), 'post_name' => $slug ] );
}
update_option( 'permalink_structure', '/%postname%/' );
delete_option( 'rewrite_rules' );
echo "Ajustes visuales listos.\n";
