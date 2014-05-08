// **** SFOSUPDATE **** Change this custom field id to reflect the new neighborhood select. In 2013 these are custom_96 and custom_94

$(document).ready(function(){

//setting a global variable so that some junky jquery can run on the first scroll event
window.scrollFireCount = 1;

  var twoWeekends = false;
  var groupStudio1 = false;
  var groupStudio2 = false;
  var numTiffUploads = 0;
        
  $('#url-1-website_type_id').val(6);
  $('.editrow_custom_25-section label').append(' <span class="marker">*</span>');
  $('.editrow_custom_71-section label').append(' <span class="marker">*</span>');
  $('.editrow_custom_72-section label').append(' <span class="marker">*</span>');
  $('.editrow_custom_28-section label').append(' <span class="marker">*</span>');
  $('.editrow_custom_76-section div.label').append(' <span class="marker">*</span>');
  $('.editrow_custom_81-section label').append(' <span class="marker">*</span>');
  $('.editrow_custom_77-section label').append(' <span class="marker">*</span>');
  $('.editrow_custom_78-section label').append(' <span class="marker">*</span>');
  $('.editrow_custom_82-section label').append(' <span class="marker">*</span>');
  $('.editrow_custom_58-section label').append(' <span class="marker">*</span>');
  $('.editrow_custom_60-section label').append(' <span class="marker">*</span>');
  $('.editrow_custom_56-section label').append(' <span class="marker">*</span>');
  $('.editrow_custom_57-section label').append(' <span class="marker">*</span>');
  $('.editrow_custom_59-section label').append(' <span class="marker">*</span>');
  $('.editrow_custom_61-section label').append(' <span class="marker">*</span>');
$('.editrow_custom_14-section label').append(' <span class="marker">*</span>');

  //4/13/2014 now making function rely on the cj (civicrm jquery) framework
  cj(".price-set-row input").click(function (event) {
    handleMultipleWeekends();
  });

  cj(window).scroll(function (event) {
    handleMultipleWeekends();
  });

  function handleMultipleWeekends() {
    var clicked_id = $(".price_set-section input[type='radio']:checked").attr('id');
    var label = $("label[for=" + clicked_id + "]").text();

    var guide_section = $('.editrow_custom_16-section').parents('fieldset').not('.crm_user-group');
    var label_wkend_1 = $('#weekend_1 label[for=studio_weekend_no]');
    var label_wkend_2 = $('#weekend_2 label[for=studio_weekend_no_2]');
    
    if (label.search(/2 Weekend/i) >= 0) {
        //This will run for 2 weekends
        $('#weekend_2, .weekend_2, .weekend_2_loc').show();

        $('.editrow_custom_94-section label h4').remove();
        $('.editrow_custom_96-section label h4').remove();
        $('.editrow_custom_16-section label h4').remove();
        $('.editrow_custom_56-section label h4').remove();
        $('.editrow_custom_94-section label').prepend('<h4>Your First Weekend</h4>');
        $('.editrow_custom_96-section label').prepend('<h4>Your Second Weekend</h4>');
        $('.editrow_custom_16-section label').prepend('<h4>Your First Weekend</h4>');
        $('.editrow_custom_56-section label').prepend('<h4>Your Second Weekend</h4>');
        twoWeekends = true;
      } else {
        //This will run for 1 weekend
        $('#weekend_2, .weekend_2, .weekend_2_loc').hide(); 
        $('.editrow_custom_94-section label h4').remove();
        $('.editrow_custom_96-section label h4').remove();
        $('.editrow_custom_16-section label h4').remove();
        $('.editrow_custom_56-section label h4').remove();
        twoWeekends = false;
      }

      if ((label.search(/premier/i) >= 0)){
        guide_section.show();
        if(label.search(/2 weekends/i) >= 0 && label.search(/Combo/i) < 0) {
          guide_section.find('.weekend_2').show();
          label_wkend_1.text('First Premier Weekend/Location');
          label_wkend_2.text('Second Premier Weekend/Location');
          numTiffUploads = 2;
        }
        else {
          guide_section.find('.weekend_2').hide();
          label_wkend_1.text('Premier Weekend/Location');
          label_wkend_2.text('Participating Weekend/Location');
          numTiffUploads = 1;
        }
      }
      else {
        guide_section.hide();
        numTiffUploads = 0;
      }

  }
  handleMultipleWeekends();

  (function() {
    var handleGroupSite1 = function() {
          groupStudio1 = true; 
          if ( $('[name=custom_70]:checked').val() == 0) {
          $('.editrow_custom_83-section').hide(); 
          $('.helprow-custom_83-section').hide(); 
          } else if ( $('[name=custom_70]:checked').val() == 1) {
          $('.editrow_custom_24-section').hide(); 
          $('.editrow_custom_25-section').hide(); 
          $('.editrow_custom_71-section').hide(); 
          $('.editrow_custom_72-section').hide(); 
          $('.editrow_custom_28-section').hide(); 
          } else {
          $('.editrow_custom_83-section').hide(); 
          $('.helprow-custom_83-section').hide(); 
          $('.editrow_custom_73-section').hide(); 
          $('.editrow_custom_74-section').hide(); 
          $('.editrow_custom_24-section').hide(); 
          $('.editrow_custom_25-section').hide(); 
          $('.editrow_custom_71-section').hide(); 
          $('.editrow_custom_72-section').hide(); 
          $('.editrow_custom_28-section').hide(); 
          }
      $("input:radio[name='custom_70']").click(function(){
        if ($(this).val() == '1') {
          $('.editrow_custom_25-section').hide(); 
          $('.editrow_custom_71-section').hide(); 
          $('.editrow_custom_72-section').hide(); 
          $('.editrow_custom_28-section').hide(); 

          $('#custom_25').val('');
          $('#custom_71').val('');
          $('#custom_72').val('');
          $('#custom_28').val('');

          $('.editrow_custom_83-section').show(); 
          $('.helprow-custom_83-section').show(); 

          $('.editrow_custom_73-section').show(); 
          $('.editrow_custom_74-section').show(); 
        } else if ($(this).val() == '0') { 
          $('.editrow_custom_83-section').hide(); 
	  $('.helprow-custom_83-section').hide(); 

          $('.editrow_custom_73-section').show(); 
          $('.editrow_custom_74-section').show(); 
          $('#custom_83').val('');
          $('#custom_73').val('');
          $('#custom_74').val('');

          $('.editrow_custom_25-section').show(); 
          $('.editrow_custom_71-section').show(); 
          $('.editrow_custom_72-section').show(); 
          $('.editrow_custom_28-section').show(); 
        } else {
          $('.editrow_custom_24-section').hide(); 
          $('.editrow_custom_73-section').hide(); 
          $('.editrow_custom_74-section').hide(); 
          $('.editrow_custom_25-section').hide(); 
          $('.editrow_custom_71-section').hide(); 
          $('.editrow_custom_72-section').hide(); 
          $('.editrow_custom_28-section').hide(); 
	  $('.helprow-custom_83-section').hide(); 
        }
      });
    };
    
  cj(window).scroll(function (event) {
    handleGroupSite1(); 
  });

    $('.editrow_custom_70-section input').click(handleGroupSite1);
    handleGroupSite1();
  })();


  (function() {
  var handleGroupSite2 = function() {
          groupStudio2 = true; 
          $('.editrow_custom_81-section').hide(); 
          $('.editrow_custom_77-section').hide(); 
          $('.editrow_custom_78-section').hide(); 
          $('.editrow_custom_82-section').hide(); 
          $('.editrow_custom_84-section').hide(); 
          $('.editrow_custom_79-section').hide(); 
          $('.editrow_custom_80-section').hide(); 
          $('.editrow_custom_84-section').hide(); 

      $("input:radio[name='custom_76']").click(function(){
        if ($(this).val() == '1') {
          $('.editrow_custom_81-section').hide(); 
          $('.editrow_custom_77-section').hide(); 
          $('.editrow_custom_78-section').hide(); 
          $('.editrow_custom_82-section').hide(); 
          $('.editrow_custom_84-section').show(); 
          $('.editrow_custom_79-section').show(); 
          $('.editrow_custom_80-section').show(); 
	  $('.helprow-custom_84-section').show(); 
        } else if ($(this).val() == '0') { 
          $('.editrow_custom_84-section').hide(); 
	  $('.helprow-custom_84-section').hide(); 
          $('.editrow_custom_79-section').show(); 
          $('.editrow_custom_80-section').show();  
          $('.editrow_custom_81-section').show(); 
          $('.editrow_custom_77-section').show(); 
          $('.editrow_custom_78-section').show(); 
          $('.editrow_custom_82-section').show();
        }
      });
    };
      var handleGroupSiteScroll2 = function() {
	  if (window.scrollFireCount == 1) {
              $('.editrow_custom_84-section').show(); 
	      $('.helprow-custom_84-section').show(); 
              $('.editrow_custom_79-section').show(); 
              $('.editrow_custom_80-section').show(); 
              $('.editrow_custom_81-section').show(); 
              $('.editrow_custom_77-section').show(); 
              $('.editrow_custom_78-section').show(); 
              $('.editrow_custom_82-section').show();
	  }
	  window.scrollFireCount = window.scrollFireCount + 1;
      };  
  cj(window).scroll(function (event) {
    handleGroupSiteScroll2(); 
  });
    $('.editrow_custom_76-section input').click(handleGroupSite2);
    handleGroupSite2();
  }


  )();

  // Studios
  // This code checks weekend location settings **** SFOSUPDATE **** Change this custom field id to reflect the new neighborhood select.
  (function() {
    var location_section = $('.editrow_custom_94-section').parents('fieldset').not('.crm_user-group');
    var children = location_section.children().not('legend, .editrow_custom_10-section, .editrow_custom_21-section');
    var half = (children.size() / 2) - 1;

    $("#selectbox option:not(option:first, option:last)").remove();


    location_section.append('<div class="weekend_loc_block premier weekend_1_loc"></div>');
    location_section.append('<div class="weekend_loc_block premier weekend_2_loc"></div>');

    location_section.children().not('legend, , .editrow_custom_10-section, .editrow_custom_21-section, .weekend_loc_block, .helprow-custom_21-section, , .helprow-custom_10-section').each( function(i) {

      if(i < half) {
        $(this).appendTo($('.weekend_1_loc'));
      }
      else {
        $(this).appendTo($('.weekend_2_loc'));
      }
    });
  })();


  // Guide artwork
  (function() {
    var guide_section = $('.editrow_custom_16-section').parents('fieldset').not('.crm_user-group');
    var children = guide_section.children().not('legend');
    var half = children.size() / 2;

    guide_section.append('<div class="weekend_block premier weekend_1"></div>');
    guide_section.append('<div class="weekend_block premier weekend_2"></div>');

    guide_section.children().not('legend, .weekend_block').each( function(i) {
      if(i < half) {
        $(this).appendTo($('.weekend_1'));
      }
      else {
        $(this).appendTo($('.weekend_2'));
      }
    });
  })();


  // Validation & next/prev buttons.
  (function() {
    var validate = function (element, message, f, element_section) {
      var jElement = $(element);
      var test;

      if(message === undefined) {
        message = 'This is a required field.';
      }
      if(typeof f !== 'function') {
        f = function() {
          return ($(this).val() !== '');
        }
      }

      if(!jElement.data('validator')) {
        test = function() {
          var section = element_section || jElement.parents('.crm-section');
          if(f.apply(element)) {
            section.data('error', false);
            section.find('label').removeClass('crm-error');
            section.find('.crm-error').remove();
            return true;
          }
          else if(!section.data('error')) {
            section.find('label').addClass('crm-error');
            jElement.after('<div class="crm-error">' + message + '</div>');
            section.data('error', true);
          }
          return false;
        }
        jElement.change(test);
        jElement.data('validator', test);
      }
      else {
        test = jElement.data('validator');
      }

      return test();
    }

    var groupValidate = function(group) {
      var allPassed = true;
      var element;
      for(i in group) {
        element = group[i];
        if(!validate(element.ref, element.message, element.validator, element.section)) {
          allPassed = false;
        }
      }
      return allPassed;
    }

    var phoneValidator = function() {
      var reg = /^(1-?)?(\([2-9]\d{2}\)|[2-9]\d{2})-?[2-9]\d{2}-?\d{4}$/;
      return reg.test($(this).val());
    }

    
    var emailValidator = function() {
      var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
      return reg.test($(this).val());
    }

    $('.form-submit').click( function() {
      // Make sure required values are set
      var selectWeekendValidator = function() {
        return ($(this).val());
      }
//      var groupLocationValidator = function() {
//        return ($(this).val() != 0);
//      }
      var tiffValidator = function() {
        return ($(this).val().match(/tiff?$/));
      }


      if ($("#phone-7-1").val()) {
      var allGroup = groupValidate([
        {
          ref: $("#phone-7-1"),
          validator: phoneValidator,
          message: "Please enter a valid phone number. Do not include any letters."
        }
      ]);
      } else {
        var allGroup = true;
      }


      //groupOptions1 = [{ ref: $('#custom_83') }, { ref: $('#custom_73') }, { ref: $('#custom_74') } ];
      if ( $('[name=custom_70]:checked').val() == 1) {
        groupOptions1 = [{ ref: $('#custom_83') } ];
      } else if ($('[name=custom_70]:checked').val() == 0) {
        groupOptions1 = [{ ref: $('#custom_25') }, { ref: $('#custom_71') }, { ref: $('#custom_72') }, { ref: $('#custom_28') } ];
      }
      var firstGroupSite = groupValidate(groupOptions1);


      //groupOptions2 = [{ ref: $('#custom_84') }, { ref: $('#custom_79') }, { ref: $('#custom_80') } ];
       if (twoWeekends) {
        if ( $('[name=custom_76]:checked').val() == 1) {
          groupOptions2 = [{ ref: $('#custom_84') } ];
        } else {
          groupOptions2 = [{ ref: $('#custom_81') }, { ref: $('#custom_77') }, { ref: $('#custom_78') }, { ref: $('#custom_82') } ];
        }
        var secondGroupSite = groupValidate(groupOptions2);
      } else {
        var secondGroupSite =  true;
      } 

// This code checks weekend location settings **** SFOSUPDATE **** Change this custom field id to reflect the new neighborhood select.
      var secondWeekend = (!twoWeekends || groupValidate([
        {
          ref: $("select[name=custom_96]"),
          validator: selectWeekendValidator,
          message: "Please select a location."
        }
      ]));
//
//      var secondGroupSite = (!twoWeekends || $('[name=group_site_2]:checked').val() === 'no' || groupValidate([
//        {
//          ref: $("[name=studio_group_location_2]"),
//          validator: groupLocationValidator,
//          message: "Please select a group location."
//        }
//      ]));
//
//      var secondNonGroupSite = (!twoWeekends || $('[name=group_site_2]:checked').val() === 'yes' || groupValidate([
//        { ref: $('#studio_address_2') },
//        { ref: $('#studio_city_2') },
//        { ref: $('#studio_zip_2') }
//      ]));
//
      var firstArtwork = (numTiffUploads < 1 || groupValidate([
        {
          ref: $('#custom_16'),
          validator: tiffValidator,
          message: "Please upload a .tif or .tiff file. If you need assistance contact ArtSpan at info@artspan.org."
        },
        { ref: $('#custom_14') },
        { ref: $('#custom_58') },
        { ref: $('#custom_60') }
      ]));

      var secondArtwork = (numTiffUploads < 2 || groupValidate([
        {
          ref: $('#custom_56'),
          validator: tiffValidator,
          message: "Please upload a .tif or .tiff file.  If you need assistance contact ArtSpan at info@artspan.org."
        },
        { ref: $('#custom_57') },
        { ref: $('#custom_59') },
        { ref: $('#custom_61') }
      ]));

      //if(allGroup && firstGroupSite && firstNonGroupSite && secondWeekend && secondGroupSite && secondNonGroupSite
      if(allGroup && secondWeekend && firstGroupSite && secondGroupSite && firstArtwork && secondArtwork) {
        return true;
      }
      else {
        $.scrollTo('.crm-error', "slow");
        //$.scrollTo('#errors', "slow");
      }
      return false;
    });
  })();

  // Disable nonselectable options in select list.
  (function() {
    $('select[name^=custom_94] option.[label^=Weekend]').attr('disabled', 'true');
    $('select[name^=custom_96] option.[label^=Weekend]').attr('disabled', 'true');
  })();
  $('#same').click(function(){
    if($('#same').attr('checked')){
    $('#billing_first_name').val($('#first_name').val());
    $('#billing_middle_name').val($('#middle_name').val());
    $('#billing_last_name').val($('#last_name').val());
    $('#billing_street_address-5').val($('#street_address-1').val());
    $('#billing_city-5').val($('#city-Primary').val());
    $('#billing_postal_code-5').val($('#postal_code-Primary').val());
    $('#billing_state_province_id-5').val($('#state_province-Primary').val());
    var state = $('#state option:selected').val();
    $('#billing_state_province_id-5 option[value=' + state + ']').attr('selected','selected');
		};
	});
});
