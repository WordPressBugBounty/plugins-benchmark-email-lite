<?php

defined( 'ABSPATH' ) || exit;

add_action( 'init', function() {

	// Gutenberg is not active.
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	// Register the block by passing the path to it's block.json file.
	register_block_type_from_metadata(
		__DIR__,
		array(
			'render_callback' => function( $attributes ) {
				return sprintf(
					'<div class="benchmark-email-signup">%s</div>',
					wpbme_frontend::get_signup_form( $attributes['listId'] )
				);
			},
		)
	);

} );

add_action( 'rest_api_init', function() {

	register_rest_route(
		'wpbme/v1',
		'/signupforms',
		[
			'callback' => [
				'wpbme_api',
				'get_forms'
			],
			'methods' => 'GET',
			'permission_callback' => '__return_true',
		]
	);

} );
