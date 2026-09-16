<?php

// Exit If Accessed Directly
if( ! defined( 'ABSPATH' ) ) { exit; }

// ReST API Class
class wpbme_api_2025 {

	// Get All Signup Forms
	static function get_forms() {

		$wpbme_signup_form_ids = get_option( 'wpbme_signup_form_ids' );
		$wpbme_signup_form_ids = preg_split( '/\R+/', trim( $wpbme_signup_form_ids ) );

		$response = [];
		foreach( $wpbme_signup_form_ids as $form_id ) {
			$response[] = (object) [
				'Name' => 'Signup Form ' . $form_id,
				'ID' => $form_id,
			];
		}

		return $response;

	}

	// Get JS Link For Signup Form
	static function get_form_data( $id ) {

		return (object) [
			'JSCode' => sprintf(
				'<script async src="https://forms.us.benchmarksend.com/assets/embed.js"></script>'
				. '<div data-bme-form-id="%s"></div>', $id
			),
		];

	}

	// Vendor Handshake - TODO
	static function update_partner() { }

	// Creates Email Campaign
	static function create_email( $name, $subject, $from_name, $from_email, $email_html, $post=false ) {

		// Get Contact Structure
		$url = trailingslashit( get_option( 'wpbme_base_url' ) ) . 'api/contact-structure';
		$request = [
			'headers' => [
				'X-API-Key' => get_option( 'wpbme_key' ),
				'Content-Type' => 'application/json',
			],
			'timeout' => 15,
		];
		$response = wp_remote_get( $url, $request );
		$response = wp_remote_retrieve_body( $response );
		$response = json_decode( $response );
		self::logger( $url, $request, $response );
		$contactStructureId = isset( $response[0]->_id ) ? $response[0]->_id : '';

		// Get List
		$url = trailingslashit( get_option( 'wpbme_base_url' ) )
			. 'api/contact-structure/' . $contactStructureId . '/lists/all';
		$request = [
			'headers' => [
				'X-API-Key' => get_option( 'wpbme_key' ),
				'Content-Type' => 'application/json',
			],
			'timeout' => 15,
		];
		$response = wp_remote_get( $url, $request );
		$response = wp_remote_retrieve_body( $response );
		$response = json_decode( $response );
		self::logger( $url, $request, $response );
		$listId = isset( $response->records[0]->_id ) ? $response->records[0]->_id : '';

		// Create Campaign
		$url = trailingslashit( get_option( 'wpbme_base_url' ) ) . 'api/email/campaign';
		$request = [
			'headers' => [
				'X-API-Key' => get_option( 'wpbme_key' ),
				'Content-Type' => 'application/json',
			],
			'body' => json_encode(
				(object) [
					'name' => $name,
					//'from' => $from_email,
					'fromName' => $from_name,
					'subject' => $subject,
					'previewText' => 'Exciting updates inside',
					'body' => "<html><body>${email_html}</body></html>",
					'plainTextBody' => strip_tags( $email_html ),
					//'replyToAddresses' => [ $from_email ],
					'contactStructureId' => $contactStructureId,
					'lists' => [ $listId ],
				]
			),
			'timeout' => 15,
		];
		$response = wp_remote_post( $url, $request );
		$response = wp_remote_retrieve_body( $response );
		$response = json_decode( $response );
		self::logger( $url, $request, $response );

		return isset( $response->_id ) ? $response->_id : false;

	}

	// Log API Communications
	static function logger( $url, $request, $response ) {

	   $wpbme_debug = get_option( 'wpbme_debug' );
		if( ! $wpbme_debug ) {
			return;
		}

		if( ! function_exists( 'wc_get_logger' ) ) {
			return;
		}

		$details = sprintf(
			"==URL==\n%s\n\n==REQUEST==\n%s\n\n==RESPONSE==\n%s",
			$url,
			print_r( $request, true ),
			print_r( $response, true )
		);

		wc_get_logger()->info( $details, [ 'source' => 'benchmark-email-lite' ] );

	}

}