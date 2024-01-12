(function($) {

  // Run a function when the page is fully loaded including graphics.
  $(window).on('load', function() {
      $('#btn-payment').click( function(e) { $.fn.confirmation(e, 'payment'); });
      $('#btn-cancellation').click( function(e) { $.fn.confirmation(e, 'cancellation'); });

      $('#inputProStatus').change( function() { $.fn.checkProStatus($(this)); });
      $.fn.checkProStatus($('#inputProStatus'));
  });

  $.fn.confirmation = function(e, action) {
      let paymentMode = $('[name="payment_mode"]:checked').val();
      let messages = JSON.parse($('#js-messages').val());
      // Set the index message by default to the chosen payment mode confirmation.
      let index = 'pay_'+paymentMode+'_confirmation';

      if (action == 'cancellation') {
          index = 'cancellation_confirmation';
      }

      if (!confirm(messages[index])) {
	  e.preventDefault();
	  e.stopPropagation();
	  return false;
      }
  };

  $.fn.checkProStatus = function(elem) {
      if (elem.val() == 'other') {
	  $('#inputProStatusInfo').parent().css({'visibility':'visible','display':'block'});
      }
      else {
	  $('#inputProStatusInfo').val('');
	  $('#inputProStatusInfo').parent().css({'visibility':'hidden','display':'none'});
      }
  };
})(jQuery);
