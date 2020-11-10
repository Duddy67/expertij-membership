(function($) {

  // Run a function when the page is fully loaded including graphics.
  $(window).on('load', function() {

    $('#btn-payment').click( function(e) { $.fn.paymentModeConfirmation(e); });
  });

  $.fn.paymentModeConfirmation = function(e) {
    if(!confirm('Are you sure ?')) {
      e.preventDefault();
      e.stopPropagation();
      return false;
    }
  };

})(jQuery);
