jQuery( document ).ready(function( $ ) {
	$( document ).on( 'click', '.add-new-tax', function( e ) {
		e.preventDefault();
		
		var clone = $(".tc-repeater-row:first").clone();
		clone.find("input[type=\"number\"]").val("");
		clone.find("select").val("");
		clone.insertAfter(".tc-repeater-row:last");
	});

	$( document ).on( 'click', '.delete-new-tax', function( e ) {
		e.preventDefault();
		
		if( $( '.delete-new-tax' ).length > 1 ) {
			$( this ).parents( '.tc-repeater-row ' ).remove();
		} else {
			$('.inp_category_taxes').val( '' );
			$('.sel_category_taxes').val( '' );
		}
	});
});
