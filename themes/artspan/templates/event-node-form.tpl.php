<?php

// Change "Artist display name" to just "Display Name"
//$form['field_content_title']['#title'] = 'Display Name';
//$form['field_content_title'][0]['#title'] = 'Display Name';
$form['field_content_title'][0]['value']['#title'] = 'Event Name';
$form['buttons']['submit']['#value'] = 'Save Profile';

// Hide the preview button
$form['buttons']['preview']['#access'] = FALSE;

?>
<div id="user-edit-heading">
  <h1><?php print 'Add An Event'; ?></h1>
  <div id="user-submit-buttons">
    <?php print drupal_render($form['buttons']); ?>
  </div>
</div>

<div id="location-options">
  <div class="selections location-selections">
    <?php print drupal_render($form['field_event_location']); ?>
  </div>
</div>

<div id="event-options">
  <div class="display_title">
    <?php print drupal_render($form['field_content_title']); ?>
  </div>
  <div class="display_title">
    <?php print drupal_render($form['taxonomy']); ?>
  </div>
  <div class="display_title">
    <?php print drupal_render($form['field_date']); ?>
  </div>
  <div class="display_title">
    <?php print drupal_render($form['field_event_link']); ?>
  </div>
  <div class="display_title">
    <?php print drupal_render($form['field_event_contact_name']); ?>
  </div>
  <div class="display_title">
    <?php print drupal_render($form['field_event_contact_phone']); ?>
  </div>
  <div class="display_title">
    <?php print drupal_render($form['field_event_contact_email']); ?>
  </div>
  <div class="profile_image">
    <?php print drupal_render($form['field_image']); ?>
  </div>
  
</div>
<div class="clr"></div>
  <div class="rest_of_form">
    <?php print drupal_render($form); ?>
  </div>
  <div class="buttons_copy">
  </div>
<script type="text/javascript">
$('#user-submit-buttons').clone().appendTo('.buttons_copy');
</script>

