<?php


// Change "Artist display name" to just "Display Name"
$form['field_content_title']['#title'] = 'Display Name';
$form['field_content_title'][0]['#title'] = 'Display Name';
$form['field_content_title'][0]['value']['#title'] = 'Display Name';
$form['buttons']['submit']['#value'] = 'Save Profile';

// Hide the preview button
$form['buttons']['preview']['#access'] = FALSE;

?>
<div id="user-edit-heading">
  <h1><?php print ($curr_user->uid == $user->uid) ? 'Edit My Profile' : $display_name . 'Edit Profile'; ?></h1>
  <div id="user-submit-buttons">
    <?php print drupal_render($form['buttons']); ?>
  </div>
</div>

<div id="taxonomy-options">
  <div class="selections mediums-selections">
    <?php print drupal_render($form['field_mediums_list']); ?>
  </div>
  <div class="selections primary-mediums-selections">
    <?php print drupal_render($form['field_primary_medium']); ?>
  </div>
  <div class="selections styles-selections">
    <?php print drupal_render($form['field_styles_list']); ?>
  </div>
  <div class="selections locations-selections">
    <?php print drupal_render($form['field_location_list']); ?>
  </div>
</div>

<div id="other-options">
  <div class="display_title">
    <?php print drupal_render($form['field_content_title']); ?>
  </div>

  <div class="profile_image">
    <?php print drupal_render($form['field_image']); ?>
  </div>
  
  <div class="artist_statement">
    <?php print drupal_render($form['field_artist_statement']); ?>
	</div>

  <div class="floatregion contact">
    <?php print drupal_render($form['websites']); ?>
    <?php print drupal_render($form['phone']); ?>
    <?php print drupal_render($form['email']); ?>
  </div>

  <div class="floatregion social">
    <?php print drupal_render($form['twitter']); ?>
    <?php print drupal_render($form['facebook']); ?>
  </div>

  <div class="floatregion thumbnail_art">
    <?php print drupal_render($form['field_ref_thumbnail_art']); ?>
  </div>

  <div class="studio_information">
    <?php print drupal_render($form['studio']); ?>
  </div>
  <br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
  <br/><br/><br/><br/><br/><br/>
  <div class="rest_of_form">
    <?php print drupal_render($form); ?>
  </div>
  <div class="buttons_copy">
  </div>
</div>
<script type="text/javascript">
$('#user-submit-buttons').clone().appendTo('.buttons_copy');
</script>

