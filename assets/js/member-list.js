(function($) {

  // Run a function when the page is fully loaded including graphics.
  $(window).on('load', function() {
    $('.js-example-basic-multiple').select2();

    $('#licence-type').change( function(e) { $.fn.setFilters($(this)); });
    $.fn.setFilters($('#licence-type'));
  });

  $.fn.setFilters = function(elem) {
    if(elem.val() == 'expert') {
      $('#courts').val('');
      $('#courts').parent().css({'visibility':'hidden','display':'none'});
      $('#expert-skill').parent().css({'visibility':'visible','display':'block'});
      $('#appeal-courts').parent().css({'visibility':'visible','display':'block'});
      $('#courts').select2().trigger('change');
    }
    else if(elem.val() == 'ceseda') {
      $('#expert-skill').val('');
      $('#appeal-courts').val('');
      $('#appeal-courts').parent().css({'visibility':'hidden','display':'none'});
      $('#expert-skill').parent().css({'visibility':'hidden','display':'none'});
      $('#courts').parent().css({'visibility':'visible','display':'block'});
      $('#appeal-courts').select2().trigger('change');
    }
    else {
      $('#expert-skill').val('');
      $('#appeal-courts').val('');
      $('#courts').val('');
      $('#appeal-courts').select2().trigger('change');
      $('#courts').select2().trigger('change');
      $('#appeal-courts').parent().css({'visibility':'hidden','display':'none'});
      $('#courts').parent().css({'visibility':'hidden','display':'none'});
      $('#expert-skill').parent().css({'visibility':'hidden','display':'none'});
    }
  };

})(jQuery);

