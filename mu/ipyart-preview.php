<?php
// Alleen voor de lokale preview: vervangt de globale stijlen van het lokale thema door die van ipyart.com (opgehaald 2026-10-06),
// op dezelfde plek in de laadvolgorde als op de live site (vóór core-block-supports).
function ipyart_preview_swap_global_styles() {
	global $wp_styles;
	if ( isset( $wp_styles->registered['global-styles'] ) ) {
		$wp_styles->registered['global-styles']->extra['after'] = array( file_get_contents( __DIR__ . '/ipyart-preview/live.css' ) );
	}
}
add_action( 'wp_enqueue_scripts', 'ipyart_preview_swap_global_styles', 99 );
add_action( 'wp_footer', 'ipyart_preview_swap_global_styles', 2 );
