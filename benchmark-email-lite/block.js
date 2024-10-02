( function( wp ) {

	// Variables
	const el = wp.element.createElement;
	const __ = wp.i18n.__;
	const useBlockProps = wp.blockEditor.useBlockProps;

	// Get Contact Lists
	var signupFormOptions = [ {
		label: __( 'Please select', 'benchmark-email-lite' ),
		value: ''
	} ];
	wp.apiFetch( {
		path: '/wpbme/v1/signupforms'
	} ).then( function( data ) {
		console.log( data );
		for( let [ key, item ] of Object.entries( data ) ) {
			signupFormOptions.push( {
				label: item.Name,
				value: item.ID
			} );
		}
	} );

	// Define Editor Styles
	var blockStyle = {
		backgroundColor: '#4253ed',
		color: '#fff',
		padding: '20px',
	};

	// Register Block
	wp.blocks.registerBlockType( 'benchmark-email/signup', {

		// Editor
		edit: function( props ) {
			return el(
				'p',
				useBlockProps( { style: blockStyle } ),
				__(
					'Benchmark signup form will display here.',
					'benchmark-email-lite'
				),
				el( wp.element.Fragment, {},
					el( wp.editor.InspectorControls, {},
						el( wp.components.PanelBody, {
							title: __(
								'Signup Form Settings',
								'benchmark-email-lite'
							),
							initialOpen: true
						},
							el( wp.components.PanelRow, {},
								el( wp.components.SelectControl, {
									label: __(
										'Select Signup Form',
										'benchmark-email-lite'
									),
									options: signupFormOptions,
									onChange: ( value ) => {
										props.setAttributes( { listId: value } );
									},
									value: props.attributes.listId
								} )
							)
						)
					)
				)
			);
		},

		// Save Markup
		save: function( props ) {
			return el(
				'p',
				useBlockProps.save( { style: blockStyle } ),
				__(
					'Benchmark signup form will display here.',
					'benchmark-email-lite'
				)
			);
		},

	} );

}( window.wp ) );