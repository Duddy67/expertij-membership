(function($) {

  // Run a function when the page is fully loaded including graphics.
  $(window).load(function() {
      // Disables both top and left panels of the editing form.
      $('#layout-mainmenu').prepend('<div class="disable-panel top-panel">&nbsp;</div>');
      $('#layout-sidenav').prepend('<div class="disable-panel">&nbsp;</div>');
      $('.control-toolbar').attr('style', 'table-layout: auto !important');

      $('#btn-vote').click( function(e) { $.fn.confirmation(e, 'vote'); });
      $('[id^="on-save"]').click( function(e) { $.fn.checkStatusChange(e); });
      $('#btn-save-payment').click( function(e) { $.fn.confirmation(e, 'payment'); });
      $('.nav-tabs li').click( function() { $.fn.checkTabs($(this)); });

      $.fn.setStatuses();
  });

  $.fn.checkTabs = function(elem) {
      // Gets the value of the child link. 
      let link = elem.children('a').prop('href');
      // Gets the substring after the # character.
      link = link.slice(link.lastIndexOf('#') + 1);

      if (link == 'secondarytab-codaliamembershiplangmembertab-profile') {
	  $('.loading-indicator-container').css({'visibility':'hidden','display':'none'});
      }
      else {
	  $('.loading-indicator-container').css({'visibility':'visible','display':'block'});
      }
  }

  /*
   * Initializes the status dropdown list according to the current status.
   */
  $.fn.setStatuses = function() {
      let currentStatus = $('#Form-field-Member-status').val();

      // Disables the dropdown list.
      if (currentStatus == 'member' || currentStatus == 'refused' || currentStatus == 'cancelled' || currentStatus == 'revoked' || currentStatus == 'cancellation') {
	  $('#Form-field-Member-status').prop('disabled', true);
      }
      // Disables some options according to the pending status.
      else {
	  let disabled = {pending: ['cancelled', 'pending_renewal', 'member', 'revoked', 'cancellation'],
			  pending_subscription: ['pending', 'refused', 'member', 'pending_renewal', 'revoked', 'cancellation'],
			  pending_renewal: ['pending', 'refused', 'member', 'pending_subscription', 'cancelled', 'cancellation']};

	  disabled[currentStatus].forEach( function(stat) {
	      $('#Form-field-Member-status option[value="'+stat+'"]').prop('disabled', true);
	  });

	  if (currentStatus == 'pending_subscription') {
	      $('#Form-field-Member-status option[value="cancelled"]').prop('disabled', false);
	  }
      }

      // Refreshes the dropdown list.
      $('#Form-field-Member-status').select2().trigger('change');
  };

  $.fn.checkPaymentStatus = function() {
      let currentStatus = $('#payment-status').val();

      if (currentStatus == 'completed' && $('#payment-item-type').val() == 'subscription') {
	  // The candidate is now member.
	  $('#Form-field-Member-status').val('member');
	  $('#Form-field-Member-status').prop('disabled', true);
	  $('#Form-field-Member-status').select2().trigger('change');
      }

      $('#payment-status').prop('disabled', true);
  };

  $.fn.checkStatusChange = function(e) {
      if ($('#Form-field-Member-status').val() != $('#current-status').val()) {
	  let messages = JSON.parse($('#js-messages').val());

	  if (!confirm(messages.status_change_confirmation)) {
	      e.preventDefault();
	      e.stopPropagation();
	      return false;
	  }
      }
  };

  /*
   * Called through Ajax once all of the process is done.
   */
  $.fn.refreshStatus = function() {
      let memberStatus = $('#Form-field-Member-status').val();
      $('#current-status').val(memberStatus);

      if (memberStatus != 'pending') {
	  $('#btn-email-sendings').css({'visibility':'hidden','display':'none'});
      }

      $.fn.setStatuses();
  };

  $.fn.setUserEditFields = function(data) {
      let fields = ['civility', 'first_name', 'last_name', 'birth_name', 'birth_date', 'birth_location',
		    'citizenship', 'street', 'additional_address', 'city', 'postcode', 'phone'];
      let value = true;

      if (data.action == 'enable') {
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
	  let inputId = 'Form-field-Member-profile-';

	  if (field == 'birth_date') {
	      inputId = 'DatePicker-formProfileBirthDate-date-profile-';
	  }

	  $('#'+inputId+field).prop('disabled', value);
      });
  };

  $.fn.confirmation = function(e, action) {
      let messages = JSON.parse($('#js-messages').val());

      // Do not save on a pending status.
      if ($('#payment-status').val() == 'pending') {
	  e.preventDefault();
	  e.stopPropagation();
	  return false;
      }
      

      if (!confirm(messages[action+'_confirmation'])) {
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

