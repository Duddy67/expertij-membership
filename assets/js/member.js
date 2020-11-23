(function($) {

  // Run a function when the page is fully loaded including graphics.
  $(window).load(function() {

    // Disables both top and left panels of the editing form.
    $('#layout-mainmenu').prepend('<div class="disable-panel top-panel">&nbsp;</div>');
    $('#layout-sidenav').prepend('<div class="disable-panel">&nbsp;</div>');
    $('.control-toolbar').attr('style', 'table-layout: auto !important');

    $('#btn-vote').click( function(e) { $.fn.voteConfirmation(e); });
    $('[id^="on-save"]').click( function(e) { $.fn.checkStatusChange(e); });
    $('#btn-save-payment').click( function(e) { $.fn.paymentStatusConfirmation(e); });

    $.fn.setStatuses();
  });

  /*
   * Initializes the status dropdown list according to the current status.
   */
  $.fn.setStatuses = function() {
    let currentStatus = $('#Form-field-Member-status').val();

    // Disables the dropdown list.
    if(currentStatus == 'member' || currentStatus == 'refused' || currentStatus == 'canceled' || currentStatus == 'revoked') {
      $('#Form-field-Member-status').prop('disabled', true);
    }
    // Disables some options according to the pending status.
    else {
      let disabled = {pending: ['canceled', 'pending_renewal', 'member', 'revoked'],
		      pending_subscription: ['pending', 'refused', 'member', 'pending_renewal', 'revoked'],
		      pending_renewal: ['pending', 'refused', 'member', 'pending_subscription', 'canceled']};

      disabled[currentStatus].forEach( function(stat) {
	$('#Form-field-Member-status option[value="'+stat+'"]').prop('disabled', true);
      });

      if(currentStatus == 'pending_subscription') {
	$('#Form-field-Member-status option[value="canceled"]').prop('disabled', false);
      }
    }

    // Refreshes the dropdown list.
    $('#Form-field-Member-status').select2().trigger('change');
  };

  $.fn.checkPaymentStatus = function() {
    let currentStatus = $('#payment-status').val();

    if(currentStatus == 'completed') {
      // The candidate is now member.
      $('#Form-field-Member-status').val('member');
      $('#Form-field-Member-status').prop('disabled', true);
      $('#Form-field-Member-status').select2().trigger('change');
    }

    $('#payment-status').prop('disabled', true);
  };

  $.fn.checkStatusChange = function(e) {
    if($('#Form-field-Member-status').val() != $('#current-status').val()) {
      if(!confirm('Are you sure ?')) {
	e.preventDefault();
	e.stopPropagation();
	return false;
      }
    }
  };

  $.fn.paymentStatusConfirmation = function(e) {
    if(!confirm('Are you sure ?')) {
      e.preventDefault();
      e.stopPropagation();
      return false;
    }
  };

  /*
   * Called through Ajax once all of the process is done.
   */
  $.fn.refreshForm = function() {
    let memberStatus = $('#Form-field-Member-status').val();
    $('#current-status').val(memberStatus);

    if(memberStatus != 'pending') {
      $('#btn-send-email').css({'visibility':'hidden','display':'none'});
    }

    $.fn.setStatuses();
  };

  $.fn.setUserEditFields = function(data) {
    let fields = ['first_name', 'last_name', 'street', 'city', 'postcode', 'country', 'user-email'];
    let value = true;

    if(data.action == 'enable') {
      value = false;
      $('#btn-save-user').prop('disabled', false);
      $('#btn-edit-user').prop('disabled', true);
    }
    else {
      // Refreshes full name.
      let fullName = $('#Form-field-Member-profile-last_name').val()+' '+$('#Form-field-Member-profile-first_name').val();
      $('#Form-field-Member-profile-user-name').val(fullName);
      $('#btn-save-user').prop('disabled', true);
      $('#btn-edit-user').prop('disabled', false);
    }

    fields.forEach( function(field) {
      $('#Form-field-Member-profile-'+field).prop('disabled', value);
    });
  };

  $.fn.voteConfirmation = function(e) {
    if(!confirm('Are you sure ?')) {
      e.preventDefault();
      e.stopPropagation();
      return false;
    }
  };

  $.fn.disableVotingForm = function() {
      $('#Form-field-Vote-choice').prop('disabled', true);
      $('#Form-field-Vote-note').prop('disabled', true);
      $('#btn-vote').css({'visibility':'hidden','display':'none'});

  };
})(jQuery);

