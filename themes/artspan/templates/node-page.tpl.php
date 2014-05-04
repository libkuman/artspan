<?php global $user; ?>
<?php if (in_array('site architect', array_values($user->roles))) { ?>
<?php if ($node->nid == 15): ?>
<div class="back-events"><a href="/events/calendar">Calendar View</a></div>
<?php endif; ?>
<?php } ?>

<h1><?php print $title; ?></h1>

<div class="node <?php print $classes; ?>" id="node-<?php print $node->nid; ?>">
  <div class="node-inner">

    <?php if (!$page): ?>
      <h2 class="title"><a href="<?php print $node_url; ?>"><?php print $title; ?></a></h2>
    <?php endif; ?>

      <?php
        if ($node->nid == 8545 && 
	    variable_get('artspan_custom_applications_open')) {
          print '<div id="reg-btn">';
          global $user;
	  $register_link = "civicrm/event/register?id=".
	    ARTSPAN_NEXT_OPENSTUDIOS_CIVICRM_EVENT_ID."&amp;reset=1";
          if ($user->uid) {
            print '<a href="/'.$register_link.'" target="_self">Register For Open Studios</a>';
          } else {
            print '<a href="/user/login?destination='.$register_link.'" target="_self">Login to Register For Open Studios</a>';
          }
          print '</div>';
        }
      ?>

    <div class="content">
      <?php print $node->field_body[0]['value']; ?>
      <?php print $node->field_list[0]['view']; ?>
      <?php print $node->field_footer_text[0]['value']; ?>
    </div>
    <div id="reg-btn">
      <?php 
        if ($node->nid == 8545  && 
	    variable_get('artspan_custom_applications_open')) {
	  $register_link = "civicrm/event/register?id=".
	    ARTSPAN_NEXT_OPENSTUDIOS_CIVICRM_EVENT_ID."&amp;reset=1";
          if ($user->uid) {
            print '<a href="/'.$register_link.
	      '" target="_self">Register For Open Studios</a>';
          } 
	  else {
            print '<a href="/user/login?destination='.$register_link.
	    '" target="_self">Login to Register For Open Studios</a>';
          }
        }
      ?>
    </div>


  </div> <!-- /node-inner -->
</div> <!-- /node-->
