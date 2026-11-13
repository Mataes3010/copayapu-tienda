<?php
/**
 * Copayapu - catálogo: categorías, atributos, productos, recomendaciones nativas y reseñas.
 * Ejecutar con: wp eval-file /setup/02-catalogo.php
 */
if ( ! defined( 'CPY_ASSETS' ) ) { define( 'CPY_ASSETS', '/assets/' ); }
if ( ! defined( 'CPY_SETUP' ) ) { define( 'CPY_SETUP', '/setup/' ); }
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

function cpy_media( $file, $title ) {
	$existing = get_posts( [ 'post_type' => 'attachment', 'title' => $title, 'posts_per_page' => 1, 'fields' => 'ids' ] );
	if ( $existing ) { return $existing[0]; }
	$tmp = wp_tempnam( basename( $file ) );
	copy( CPY_ASSETS . $file, $tmp );
	$id = media_handle_sideload( [ 'name' => $file, 'tmp_name' => $tmp ], 0, $title );
	return is_wp_error( $id ) ? 0 : $id;
}
function cpy_term( $name, $tax, $desc = '' ) {
	$t = term_exists( $name, $tax );
	if ( ! $t ) { $t = wp_insert_term( $name, $tax, [ 'description' => $desc ] ); }
	return (int) $t['term_id'];
}
function cpy_attribute( $name, $slug, $terms ) {
	$id = wc_attribute_taxonomy_id_by_name( $slug );
	if ( ! $id ) { $id = wc_create_attribute( [ 'name' => $name, 'slug' => $slug, 'type' => 'select', 'order_by' => 'menu_order', 'has_archives' => false ] ); }
	$tax = wc_attribute_taxonomy_name( $slug );
	if ( ! taxonomy_exists( $tax ) ) { register_taxonomy( $tax, 'product', [ 'hierarchical' => false ] ); }
	foreach ( $terms as $t ) { if ( ! term_exists( $t, $tax ) ) { wp_insert_term( $t, $tax ); } }
	return [ $id, $tax ];
}

// ---------- Categorías (RF02) ----------
$cats = [
	'Aceites y aceitunas' => 'Aceite de oliva extra virgen con Denominación de Origen Valle del Huasco y aceitunas sevillanas.',
	'Pasas y frutos secos' => 'Pasas del valle de Copiapó, nueces y frutos secos de temporada.',
	'Dulces y conservas' => 'Mermelada y arrope de chañar, miel y dulces artesanales.',
	'Pajarete y licores' => 'Vinos generosos tradicionales. Venta solo a mayores de 18 años.',
	'Canastas de regalo' => 'Canastas armadas para regalar y canastas corporativas.',
	'Artesanía' => 'Piezas de cobre y cerámica de artesanos de la región.',
	'Despensa costera' => 'Productos del litoral de Atacama.',
];
$cat = [];
foreach ( $cats as $n => $d ) { $cat[ $n ] = cpy_term( $n, 'product_cat', $d ); }
$uncat = get_term_by( 'slug', 'uncategorized', 'product_cat' ) ?: get_term_by( 'slug', 'sin-categorizar', 'product_cat' );

// ---------- Atributos para filtros (RF03) ----------
[ $a_origen, $t_origen ] = cpy_attribute( 'Origen', 'origen', [ 'Valle del Huasco', 'Valle de Copiapó', 'Alto del Carmen', 'Tierra Amarilla', 'Caldera', 'Copiapó', 'Selección Copayapu' ] );
[ $a_ocasion, $t_ocasion ] = cpy_attribute( 'Ocasión', 'ocasion', [ 'Consumo diario', 'Para regalar', 'Regalo corporativo' ] );

// ---------- Productos ----------
$P = [
 // clave, nombre, categoría, precio, oferta, stock, peso, origen, ocasiones, destacado, corta, larga
 [ 'aceite500', 'Aceite de oliva extra virgen D. O. Valle del Huasco 500 ml', 'Aceites y aceitunas', 8990, '', 18, 0.9, 'Valle del Huasco', [ 'Consumo diario', 'Para regalar' ], true,
   'Variedad sevillana 100 %, extracción en frío. Almazara autorizada por INAPI para usar la Denominación de Origen Valle del Huasco.',
   'Aceite frutado y suave, ideal para ensaladas y pan. Cosecha 2026. Acidez menor a 0,3 %. Consumir antes de mayo de 2028. Precio por litro: $17.980.' ],
 [ 'aceite1l', 'Aceite de oliva extra virgen D. O. Valle del Huasco 1 L', 'Aceites y aceitunas', 15990, 14990, 9, 1.6, 'Valle del Huasco', [ 'Consumo diario' ], false,
   'Formato familiar del aceite con Denominación de Origen Valle del Huasco.', 'Variedad sevillana, extracción en frío. Cosecha 2026.' ],
 [ 'aceitunas', 'Aceitunas sevillanas del Huasco 500 g', 'Aceites y aceitunas', 4990, '', 31, 0.6, 'Valle del Huasco', [ 'Consumo diario' ], false,
   'Aceitunas sevillanas en salmuera, cosecha del valle del Huasco.', 'Envase de vidrio de 500 g, peso drenado 300 g.' ],
 [ 'pasas', 'Pasas flame de Copiapó 500 g', 'Pasas y frutos secos', 3990, '', 54, 0.5, 'Valle de Copiapó', [ 'Consumo diario' ], true,
   'Pasas de uva flame del valle de Copiapó, secadas al sol.', 'Sin azúcar añadida. Bolsa resellable de 500 g.' ],
 [ 'pasasm', 'Pasas morenas sin semilla 1 kg', 'Pasas y frutos secos', 6990, '', 22, 1.0, 'Valle de Copiapó', [ 'Consumo diario' ], false,
   'Formato de 1 kg para repostería y consumo diario.', 'Sin azúcar añadida.' ],
 [ 'nueces', 'Nueces mariposa 500 g', 'Pasas y frutos secos', 7490, '', 3, 0.5, 'Valle del Huasco', [ 'Consumo diario', 'Para regalar' ], false,
   'Nueces mariposa extra light de la temporada 2026.', 'Bolsa de 500 g.' ],
 [ 'chanar', 'Mermelada de chañar 250 g', 'Dulces y conservas', 4490, '', 26, 0.35, 'Tierra Amarilla', [ 'Para regalar' ], false,
   'Mermelada artesanal de chañar, fruto nativo de Atacama.', 'Elaborada por productores de la comuna de Tierra Amarilla.' ],
 [ 'arrope', 'Arrope de chañar 250 ml', 'Dulces y conservas', 5290, '', 14, 0.35, 'Tierra Amarilla', [ 'Para regalar' ], false,
   'Arrope tradicional de chañar, receta de comunidades originarias de Copiapó.', 'Ideal para postres y leche.' ],
 [ 'miel', 'Miel multifloral de Alto del Carmen 500 g', 'Dulces y conservas', 6490, '', 20, 0.6, 'Alto del Carmen', [ 'Consumo diario', 'Para regalar' ], false,
   'Miel multifloral cosechada en el valle de Alto del Carmen.', 'Frasco de vidrio de 500 g.' ],
 [ 'pajarete', 'Pajarete de Alto del Carmen 750 ml', 'Pajarete y licores', 9990, '', 12, 1.3, 'Alto del Carmen', [ 'Para regalar' ], false,
   'Vino generoso tradicional del valle. Venta solo a mayores de 18 años.', 'Bebida alcohólica. Prohibida su venta a menores de 18 años (Ley N.° 19.925).' ],
 [ 'sal', 'Sal de mar con hierbas de Caldera 200 g', 'Despensa costera', 3990, '', 40, 0.25, 'Caldera', [ 'Consumo diario' ], false,
   'Sal de mar con hierbas del desierto costero.', 'Frasco de 200 g.' ],
 [ 'cobre', 'Posavasos de cobre, set de 4', 'Artesanía', 12990, '', 7, 0.4, 'Copiapó', [ 'Para regalar', 'Regalo corporativo' ], false,
   'Set de 4 posavasos de cobre hechos por artesanos de Copiapó.', 'Diámetro 10 cm.' ],
 [ 'cvalle', 'Canasta Valle de Copiapó', 'Canastas de regalo', 34990, '', 15, 3.5, 'Selección Copayapu', [ 'Para regalar', 'Regalo corporativo' ], true,
   'Aceite D. O. Huasco 500 ml, pasas flame, mermelada de chañar, miel y nueces en canasta de mimbre.', 'Incluye tarjeta de saludo.' ],
 [ 'cflorido', 'Canasta Desierto Florido', 'Canastas de regalo', 49990, '', 10, 4.5, 'Selección Copayapu', [ 'Para regalar' ], true,
   'Nuestra canasta de temporada: aceite 1 L, pajarete, arrope, miel, pasas y posavasos de cobre.', 'Con código FLORIDO15 obtienes 15 % de descuento durante la temporada.' ],
 [ 'cfaena', 'Canasta Faena (corporativa)', 'Canastas de regalo', 24990, '', 60, 3.0, 'Selección Copayapu', [ 'Regalo corporativo' ], false,
   'Canasta para equipos de trabajo, sin alcohol. Pedido mínimo de 30 unidades con descuento por volumen.', 'Personalizable con tarjeta y logo. Las canastas personalizadas no tienen derecho a retracto.' ],
];
$ids = [];
foreach ( $P as $row ) {
	[ $key, $name, $c, $price, $sale, $stock, $w, $origen, $ocas, $feat, $short, $long ] = $row;
	$sku = 'CPY-' . strtoupper( $key );
	$pid = wc_get_product_id_by_sku( $sku );
	$p = $pid ? wc_get_product( $pid ) : new WC_Product_Simple();
	$p->set_name( $name );
	$p->set_sku( $sku );
	$p->set_regular_price( (string) $price );
	$p->set_sale_price( $sale === '' ? '' : (string) $sale );
	$p->set_manage_stock( true );
	$p->set_stock_quantity( $stock );
	$p->set_low_stock_amount( 5 );
	$p->set_weight( (string) $w );
	$p->set_featured( $feat );
	$p->set_short_description( $short );
	$p->set_description( $long );
	$p->set_category_ids( [ $cat[ $c ] ] );
	$p->set_reviews_allowed( true );
	$img = cpy_media( $key . '.png', $name );
	if ( $img ) { $p->set_image_id( $img ); }
	$attrs = [];
	foreach ( [ [ $a_origen, $t_origen, [ $origen ] ], [ $a_ocasion, $t_ocasion, $ocas ] ] as [ $aid, $tax, $vals ] ) {
		$at = new WC_Product_Attribute();
		$at->set_id( $aid );
		$at->set_name( $tax );
		$at->set_options( array_map( fn( $v ) => get_term_by( 'name', $v, $tax )->term_id, $vals ) );
		$at->set_visible( true );
		$attrs[] = $at;
	}
	$p->set_attributes( $attrs );
	$ids[ $key ] = $p->save();
}

// ---------- Recomendaciones nativas: venta cruzada y ventas dirigidas (línea base de reglas, RF14) ----------
$rel = [
	'aceite500' => [ [ 'aceite1l' ], [ 'aceitunas', 'sal' ] ], 'aceitunas' => [ [], [ 'aceite500', 'sal' ] ],
	'pasas' => [ [ 'pasasm' ], [ 'nueces', 'miel', 'chanar' ] ], 'pasasm' => [ [], [ 'nueces' ] ],
	'miel' => [ [], [ 'chanar', 'arrope' ] ], 'chanar' => [ [], [ 'arrope', 'miel' ] ], 'pajarete' => [ [], [ 'pasas', 'nueces' ] ],
	'cvalle' => [ [ 'cflorido' ], [ 'cobre' ] ], 'cflorido' => [ [], [ 'cobre' ] ], 'cobre' => [ [], [ 'miel' ] ],
];
foreach ( $rel as $k => [ $up, $cross ] ) {
	$p = wc_get_product( $ids[ $k ] );
	$p->set_upsell_ids( array_map( fn( $x ) => $ids[ $x ], $up ) );
	$p->set_cross_sell_ids( array_map( fn( $x ) => $ids[ $x ], $cross ) );
	$p->save();
}

// ---------- Reseñas verificadas (RF13) ----------
$reviews = [
	[ 'aceite500', 'Paula G.', 'paula@ejemplo.local', 5, 'Suave y frutado. Lo compré de recuerdo después de ver el Desierto Florido y ahora lo pido todos los meses.' ],
	[ 'aceite500', 'Mauricio T.', 'mauricio@ejemplo.local', 4, 'Llegó en dos días con Starken, muy bien embalado. Me habría gustado un formato de 250 ml.' ],
	[ 'aceite500', 'Rosa D.', 'rosa@ejemplo.local', 5, 'El mejor aceite de la zona. Lo retiro en la tienda el mismo día.' ],
	[ 'pasas', 'Jorge P.', 'jorge@ejemplo.local', 5, 'Las mismas pasas que comía de niño en Copiapó. Llegaron perfectas a Antofagasta.' ],
	[ 'cflorido', 'Valentina S.', 'vale@ejemplo.local', 5, 'Regalé esta canasta para un cumpleaños y fue un éxito. Muy linda presentación.' ],
];
foreach ( $reviews as [ $k, $author, $mail, $rating, $text ] ) {
	$exists = get_comments( [ 'post_id' => $ids[ $k ], 'author_email' => $mail, 'count' => true ] );
	if ( $exists ) { continue; }
	$cid = wp_insert_comment( [ 'comment_post_ID' => $ids[ $k ], 'comment_author' => $author, 'comment_author_email' => $mail,
		'comment_content' => $text, 'comment_type' => 'review', 'comment_approved' => 1 ] );
	update_comment_meta( $cid, 'rating', $rating );
	update_comment_meta( $cid, 'verified', 1 );
}
foreach ( $ids as $id ) { WC_Comments::clear_transients( $id ); }
if ( $uncat && ! get_term( $uncat->term_id )->count ) { /* se mantiene la categoría por defecto de WooCommerce */ }
echo 'Productos: ' . count( $ids ) . "\n";
echo json_encode( $ids ) . "\n";
