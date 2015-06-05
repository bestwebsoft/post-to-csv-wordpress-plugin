(function($) {
	$(document).ready( function() {
		$( '.psttcsv_div_select_all' ).show();
		$( '#psttcsv_settings_form input[type="checkbox"]' ).change( function() {
			var	$select_all = $( this ).parents( 'td' ).filter( ':first' ).find( '.psttcsv_select_all' ),
				$checkboxes = $( this ).parents( 'td' ).filter( ':first' ).find( 'input[type="checkbox"]:not(.psttcsv_select_all)' ),
				checkboxes_size = $checkboxes.size(),
				checkboxes_selected_size = $checkboxes.filter( ':checked' ).size();
			
			if ( $( this ).hasClass( 'psttcsv_select_all' ) ) {
				if ( $( this ).is( ':checked' ) ) {
					$checkboxes.attr( 'checked', true );
				} else {
					$checkboxes.attr( 'checked', false );
				}
			} else {
				if ( checkboxes_size == checkboxes_selected_size ) {
					$select_all.attr( 'checked', true );
				} else {
					$select_all.attr( 'checked', false );
				}				
			}
		});
	});
})(jQuery);
