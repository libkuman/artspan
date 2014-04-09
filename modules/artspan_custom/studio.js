// Making the "group site" options a bit more responsive.

jQuery( function($) {
  var setGroupSiteOption = function() {
    if($('input[name=groupsite_question]:checked').val() === '1') {
      $('.groupsite_no').hide();
      $('.groupsite_yes').show();
    }
    else {
      $('.groupsite_no').show();
      $('.groupsite_yes').hide();
    }
  }

  $('input[name=groupsite_question]').change( setGroupSiteOption );
  setGroupSiteOption();
});

