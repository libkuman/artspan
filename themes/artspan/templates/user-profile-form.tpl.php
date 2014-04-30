<?php
  $curr_user = $form['_account']['#value'];
  $display_name = $form['_civi_contact']['#value']['display_name'];
  
  if(isset($form['submit'])) {
    $form['submit']['#value'] = 'Save Settings';
  }

  if(isset($form['delete'])) {
    $form['delete']['#value'] = 'Delete User';
  }

  if(isset($form['account'])) {
    $form['account']['#title'] = 'Account Settings';
  }

  // Hide the locale settings
  $form['timezone']['#access'] = FALSE;

?>

<div id="user-edit-heading">
  <h1><?php print ($curr_user->uid == $user->uid) ? 'My Account' : $display_name . '\'s Account'; ?></h1>
  <div id="user-submit-buttons">
    <?php print drupal_render($form['submit']); ?>
    <?php print drupal_render($form['delete']); ?>
  </div>
</div>

<?php
 $prof = content_profile_load('artist', $curr_user->uid);
 if (!isset($prof->field_sf_open_studios[0]['value'])) {
   $register_link = "civicrm/event/register?id=".
     ARTSPAN_NEXT_OPENSTUDIOS_CIVICRM_EVENT_ID."&amp;reset=1";

   print '<div class="osreg-block">';
   print '<div id="reg-btn-user">';
   print '<a href="/'.$register_link.
     '" target="_self">Register For ' . date('Y') . ' Open Studios</a>';
   print '</div>';
   //print '<h2>Registration For SF Open Studios Coming Soon.</h2>';
   print '</div>';
   print '<div class="clr"></div>';
 }
?>
<div id="account-stuff">
  <div class="civi-profiles">
    <?php print drupal_render($form['personal']); ?>
    <?php print drupal_render($form['demographics']); ?>
  </div>
  <?php print drupal_render($form); ?>
</div>

