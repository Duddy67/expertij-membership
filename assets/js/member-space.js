(function($) {

  // Run a function when the page is fully loaded including graphics.
  $(window).on('load', function() {

    $('#btn-payment').click( function(e) { $.fn.confirmation(e, 'payment'); });
    $('#btn-cancellation').click( function(e) { $.fn.confirmation(e, 'cancellation'); });
  });

  $.fn.confirmation = function(e, action) {
    if(!confirm('Are you sure ?')) {
      e.preventDefault();
      e.stopPropagation();
      return false;
    }
  };

})(jQuery);
