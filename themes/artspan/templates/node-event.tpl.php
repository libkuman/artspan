<?php global $user; ?>
<?php if (in_array('site architect', array_values($user->roles))) { ?>
<div class="back-events"><a href="/events/calendar">Back To Calendar</a></div>
<?php } ?>
<h1><?php print $node->field_content_title[0]['value']; ?></h1>
<div class="node <?php print $classes; ?>" id="node-<?php print $node->nid; ?>">
  <div class="node-inner">
    <div class="content">
      <?php //print_r($node); ?>
      <div class="event-left">
        <?php print $node->field_image[0]['view']; ?>
        <?php print $node->field_body[0]['value']; ?>
      </div>
      <div class="event-right">
        <h3>Date</h3>
        <?php
          if (substr($node->field_date[0]['value'], 0, 10) == substr($node->field_date[0]['value2'], 0, 10)) {
            print format_date(strtotime($node->field_date[0]['value']), 'custom', 'M d Y', $timezone, "en");
          } else {
            print format_date(strtotime($node->field_date[0]['value']), 'custom', 'M d Y', $timezone, "en");
            print " - ";
            print format_date(strtotime($node->field_date[0]['value2']), 'custom', 'M d Y', $timezone, "en");
          }
        ?>
        <h3>Time</h3>
        <?php print format_date(strtotime($node->field_date[0]['value']), 'custom', 'h:i A', $timezone, "en"); ?>
        -
        <?php print format_date(strtotime($node->field_date[0]['value2']), 'custom', 'h:i A', $timezone, "en"); ?>
        <h3>Location</h3>
        <?php print $node->field_event_location[0]['view']; ?>
        <h3>Website</h3>
        <?php print $node->field_event_link[0]['view']; ?>
        <h3>Contact</h3>
        <?php print $node->field_event_contact_name[0]['view']; ?><br />
        <?php print $node->field_event_contact_phone[0]['view']; ?><br />
        <?php print $node->field_event_contact_email[0]['view']; ?>
      </div>
    </div>

  </div> <!-- /node-inner -->
</div> <!-- /node-->
