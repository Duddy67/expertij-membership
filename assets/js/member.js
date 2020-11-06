(function($) {

  // Run a function when the page is fully loaded including graphics.
  $(window).load(function() {

    // Disables both top and left panels of the editing form.
    $('#layout-mainmenu').prepend('<div class="disable-panel top-panel">&nbsp;</div>');
    $('#layout-sidenav').prepend('<div class="disable-panel">&nbsp;</div>');
    $('.control-toolbar').attr('style', 'table-layout: auto !important');

  });

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

  $.fn.vote = function(data) {
  };
})(jQuery);

