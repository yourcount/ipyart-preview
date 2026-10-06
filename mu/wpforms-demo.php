<?php
// Alleen lokale preview: bouwt de twee WPForms-formulieren van ipyart.com na met dezelfde ID's (22 contact, 356 nieuwsbrief).
add_action( 'init', function () {
	if ( ! post_type_exists( 'wpforms' ) || get_option( 'ipy_demo_forms' ) ) {
		return;
	}
	$forms = array(
		22  => array(
			'title'  => 'Contact',
			'fields' => array(
				'1' => array( 'id' => '1', 'type' => 'name', 'label' => 'Naam', 'format' => 'simple', 'required' => '1', 'size' => 'large' ),
				'2' => array( 'id' => '2', 'type' => 'email', 'label' => 'E-mail', 'required' => '1', 'size' => 'large' ),
				'3' => array( 'id' => '3', 'type' => 'text', 'label' => 'Onderwerp', 'size' => 'large' ),
				'4' => array( 'id' => '4', 'type' => 'textarea', 'label' => 'Bericht', 'required' => '1', 'size' => 'large' ),
			),
			'submit' => 'Verzend',
			'thanks' => 'Bedankt voor je bericht! Ik neem snel contact met je op.',
		),
		356 => array(
			'title'  => 'Nieuwsbrief',
			'fields' => array(
				'1' => array( 'id' => '1', 'type' => 'email', 'label' => 'E-mail', 'required' => '1', 'size' => 'large', 'placeholder' => 'Je e-mailadres' ),
			),
			'submit' => 'Aanmelden',
			'thanks' => 'Dank je wel! Je bent aangemeld.',
		),
	);
	foreach ( $forms as $id => $f ) {
		if ( get_post( $id ) ) {
			continue;
		}
		$data = array(
			'id'       => (string) $id,
			'field_id' => count( $f['fields'] ) + 1,
			'fields'   => $f['fields'],
			'settings' => array(
				'form_title'             => $f['title'],
				'submit_text'            => $f['submit'],
				'submit_text_processing' => 'Bezig…',
				'ajax_submit'            => '1',
				'notification_enable'    => '1',
				'notifications'          => array( '1' => array( 'email' => '{admin_email}', 'subject' => 'Nieuw: ' . $f['title'], 'sender_name' => 'ipyart', 'sender_address' => '{admin_email}' ) ),
				'confirmations'          => array( '1' => array( 'type' => 'message', 'message' => '<p>' . $f['thanks'] . '</p>' ) ),
			),
			'meta'     => array( 'template' => 'blank' ),
		);
		wp_insert_post( array( 'import_id' => $id, 'post_type' => 'wpforms', 'post_status' => 'publish', 'post_title' => $f['title'], 'post_content' => wp_slash( wp_json_encode( $data ) ) ) );
	}
	update_option( 'ipy_demo_forms', 1 );
}, 20 );

// ID 22 is in deze testomgeving al bezet: maak het contactformulier onder een ander ID aan en verwijs [wpforms id="22"] ernaar.
add_action( 'init', function () {
	if ( ! post_type_exists( 'wpforms' ) || 'wpforms' === get_post_type( 22 ) || get_option( 'ipy_demo_contact' ) ) {
		return;
	}
	$data = array( 'field_id' => 5, 'fields' => array(
		'1' => array( 'id' => '1', 'type' => 'name', 'label' => 'Naam', 'format' => 'simple', 'required' => '1', 'size' => 'large' ),
		'2' => array( 'id' => '2', 'type' => 'email', 'label' => 'E-mail', 'required' => '1', 'size' => 'large' ),
		'3' => array( 'id' => '3', 'type' => 'text', 'label' => 'Onderwerp', 'size' => 'large' ),
		'4' => array( 'id' => '4', 'type' => 'textarea', 'label' => 'Bericht', 'required' => '1', 'size' => 'large' ),
	), 'settings' => array( 'form_title' => 'Contact', 'submit_text' => 'Verzend', 'ajax_submit' => '1', 'confirmations' => array( '1' => array( 'type' => 'message', 'message' => '<p>Bedankt voor je bericht!</p>' ) ) ), 'meta' => array( 'template' => 'blank' ) );
	$id = wp_insert_post( array( 'post_type' => 'wpforms', 'post_status' => 'publish', 'post_title' => 'Contact' ) );
	$data['id'] = (string) $id;
	wp_update_post( array( 'ID' => $id, 'post_content' => wp_slash( wp_json_encode( $data ) ) ) );
	update_option( 'ipy_demo_contact', $id );
}, 21 );

add_filter( 'shortcode_atts_wpforms', function ( $out ) {
	$alt = (int) get_option( 'ipy_demo_contact' );
	if ( $alt && '22' === (string) $out['id'] && 'wpforms' !== get_post_type( 22 ) ) {
		$out['id'] = $alt;
	}
	return $out;
} );
