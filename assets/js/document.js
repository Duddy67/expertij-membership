(function($) {

    // Run a function when the page is fully loaded including graphics.
    $(window).load(function() {

	// Disables both top and left panels of the editing form.
	$('#layout-mainmenu').prepend('<div class="disable-panel top-panel">&nbsp;</div>');
	$('#layout-sidenav').prepend('<div class="disable-panel">&nbsp;</div>');
	$('.control-toolbar').attr('style', 'table-layout: auto !important');

	$('#Form-field-Document-licence_types').change( function() { $.fn.setLicenceTypes($(this)); });
	$.fn.setLicenceTypes($('#Form-field-Document-licence_types'));
    });

    $.fn.setLicenceTypes = function(elem) {
        if (elem.val() == '') {
	    $('#Form-field-Document-appeal_courts').prop('disabled', true);
	    $('#Form-field-Document-courts').prop('disabled', true);
	    $('#Form-field-Document-languages').prop('disabled', true);
	    
	    return;
	}

	let types = elem.val();

        if (types.length == 1 && types[0] == 'expert') {
	    $('#Form-field-Document-appeal_courts').prop('disabled', false);
	    $('#Form-field-Document-courts').prop('disabled', true);
	    $('#Form-field-Document-languages').prop('disabled', false);
	}
        else if (types.length == 1 && types[0] == 'ceseda') {
	    $('#Form-field-Document-appeal_courts').prop('disabled', true);
	    $('#Form-field-Document-courts').prop('disabled', false);
	    $('#Form-field-Document-languages').prop('disabled', false);
	}
        else if (types.length == 2) {
	    $('#Form-field-Document-appeal_courts').prop('disabled', false);
	    $('#Form-field-Document-courts').prop('disabled', false);
	    $('#Form-field-Document-languages').prop('disabled', false);
	}
    }

})(jQuery);
