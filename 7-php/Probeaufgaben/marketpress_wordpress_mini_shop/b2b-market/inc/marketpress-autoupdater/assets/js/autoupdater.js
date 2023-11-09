jQuery( document ).ready( function(){
	jQuery( '.marketpress-autoupdater-activate-button' ).click( function(){
		jQuery( this ).closest( 'tr' ).prev( 'tr' ).find( '[type=checkbox]' ).prop( 'checked', true );
	});
});
