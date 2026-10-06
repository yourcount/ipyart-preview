<?php
/**
 * Vult een verse WordPress (Playground) met het nieuwe ontwerp van ipyart.com:
 * pagina's, header en footer, voorbeeldgedichten, logo en instellingen.
 * Wordt aangeroepen vanuit blueprint.json (deelbare preview).
 */

require '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

do_action( 'init' );

$seed = json_decode( file_get_contents( '/wordpress/ipy-seed.json' ), true );

// Standaardinhoud weg.
foreach ( array( 1, 2, 3 ) as $id ) {
	if ( get_post( $id ) ) {
		wp_delete_post( $id, true );
	}
}

// Pagina's.
$ids = array();
foreach ( $seed['pages'] as $page ) {
	$id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => $page['status'],
			'post_name'    => $page['slug'],
			'post_title'   => $page['title'],
			'post_content' => wp_slash( $page['content'] ),
		)
	);
	if ( $page['template'] ) {
		update_post_meta( $id, '_wp_page_template', $page['template'] );
	}
	$ids[ $page['slug'] ] = $id;
}

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $ids['home'] );

// Header en footer als templateonderdelen van het thema (bewerkbaar in de Site Editor).
$theme = get_stylesheet();
foreach ( $seed['parts'] as $slug => $content ) {
	$id = wp_insert_post(
		array(
			'post_type'    => 'wp_template_part',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => ucfirst( $slug ),
			'post_content' => wp_slash( $content ),
		)
	);
	wp_set_object_terms( $id, $theme, 'wp_theme' );
	wp_set_object_terms( $id, $slug, 'wp_template_part_area' );
}

// Gedichten van Iris.
foreach ( $seed['gedichten'] ?? array() as $i => $g ) {
	wp_insert_post(
		array(
			'post_type'    => 'gedicht',
			'post_status'  => 'publish',
			'post_name'    => $g['slug'],
			'post_title'   => $g['title'],
			'post_date'    => gmdate( 'Y-m-d H:i:s', time() - $i * DAY_IN_SECONDS ),
			'post_content' => '<!-- wp:paragraph {"className":"is-style-gedicht"} --><p class="is-style-gedicht">' . implode( '<br>', array_map( 'esc_html', $g['regels'] ) ) . '</p><!-- /wp:paragraph -->',
		)
	);
}

// Logo (meegeleverd in het pakket).
$logo_file = '/wordpress/ipy-logo.png';
if ( file_exists( $logo_file ) ) {
	$upload = wp_upload_bits( 'logo-ipy-art.png', null, file_get_contents( $logo_file ) );
	if ( empty( $upload['error'] ) ) {
		$att = wp_insert_attachment( array( 'post_mime_type' => 'image/png', 'post_title' => 'IPY ART logo', 'post_status' => 'inherit' ), $upload['file'] );
		wp_update_attachment_metadata( $att, wp_generate_attachment_metadata( $att, $upload['file'] ) );
		set_theme_mod( 'custom_logo', $att );
	}
}

update_option( 'ipyart_ontwerp_modus', 'live' );
update_option( 'ipyart_popup', 'aan' );
flush_rewrite_rules();
