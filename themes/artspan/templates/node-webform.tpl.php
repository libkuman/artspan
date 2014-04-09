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


    <div class="content">
      <?php print $node->body; ?>
      <?php //print $node->field_body[0]['value']; ?>
      <?php //print $node->content['webform']['#value']; ?>
    </div>


  </div> <!-- /node-inner -->
</div> <!-- /node-->
