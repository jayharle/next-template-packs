/* global _ */
window.wp = window.wp || {};

( function( wp, $ ) {

	if ( undefined === typeof wp.customizer ) {
		return;
	}

	$( document ).ready( function() {

		// If the Main Group setting is disabled, hide all others
		$( wp.customize.control( 'group_front_page' ).selector ).on( 'click', 'input[type=checkbox]', function( event ) {
			var checked = $( event.currentTarget ).prop( 'checked' ), controller = $( event.delegateTarget ).prop( 'id' );

			_.each( wp.customize.section( 'bp_nouveau_group_front_page' ).controls(), function( control ) {
				if ( control.selector !== '#' + controller ) {
					if ( true === checked ) {
						$( control.selector ).show();
					} else {
						$( control.selector ).hide();
					}
				}
			} );
		} );

		// If the Main User setting is disabled, hide all others
		$( wp.customize.control( 'user_front_page' ).selector ).on( 'click', 'input[type=checkbox]', function( event ) {
			var checked = $( event.currentTarget ).prop( 'checked' ), controller = $( event.delegateTarget ).prop( 'id' );

			_.each( wp.customize.section( 'bp_nouveau_user_front_page' ).controls(), function( control ) {
				if ( control.selector !== '#' + controller ) {
					if ( true === checked ) {
						$( control.selector ).show();
					} else {
						$( control.selector ).hide();
					}
				}
			} );
		} );

	} );

} )( window.wp, jQuery );
