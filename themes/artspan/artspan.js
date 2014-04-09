// A fix for the annoying civicrm javascript bug
//var cj = jQuery.noConflict();
//$ = cj;

(function() {

  $('.no-rgba .gallery .views-field-field-content-title-value').fadeTo(0, .8);


Drupal.vfas = Drupal.vfas || {};

Drupal.behaviors.vfas = function(context) {
  if(Drupal.settings.vfas !== undefined) {
    $('form#'+Drupal.settings.vfas.form_id+':not(.vfas-processed)', context).each(function() {
      var self = this;
      var exceptions = Drupal.settings.vfas.exceptions;
      if (exceptions) {
        exceptions = ':not('+Drupal.settings.vfas.exceptions+')';
      }
      else {
        exceptions = '';
      }
      $(self).addClass('vfas-processed');
      $('#'+Drupal.settings.vfas.submit_id, self).hide();
      $('div.views-exposed-widget input:checkbox'+exceptions, self).click(function() {
        $(self).submit();
      });
      $('div.views-exposed-widget input'+exceptions, self).change(function() {
        $(self).submit();
      });
      $('div.views-exposed-widget select'+exceptions, self).change(function() {
        $(self).submit();
      });
    });
  }
}

// END jQuery
})(jQuery);
