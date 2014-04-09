<h1><?php print $node->field_content_title[0]['value']; ?></h1>

<div class="node <?php print $classes; ?>" id="node-<?php print $node->nid; ?>">
  <div class="node-inner">

    <div class="content">
      <?php print $node->field_body[0]['value']; ?>
    </div>

  </div> <!-- /node-inner -->
</div> <!-- /node-->
