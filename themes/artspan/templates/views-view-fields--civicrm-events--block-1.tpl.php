<div class="eventcal-list">
  <?php if ($fields['event_landing_page_67']->content) { ?>
  <div><a href="<?php print $fields['event_landing_page_67']->content; ?>"><?php print strip_tags($fields['title']->content); ?></a></div>
  <?php } else { ?>
  <div><?php print $fields['title']->content; ?></div>
  <?php } ?>
  <div class="event-list-date"><?php print $fields['start_date']->content; ?></div>
  <?php print $fields['summary']->content; ?>
</div>
