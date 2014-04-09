<?php

function print_blocks($block_list) {
  foreach($block_list as $block) {
    print $block['view'];
  }
}

?>
<div class="node <?php print $classes; ?>" id="node-<?php print $node->nid; ?>">
  <div class="node-inner">

    <div class="content">
    	<div class="main-header">
    	<?php print_blocks($node->field_main_header); ?>
    	</div>
      <div class="main-area">
        <?php print $node->field_main_view[0]['view']; ?>
        <?php if(count($node->field_feature_url) > 0): ?>
          <?php print '<a href="'. $node->field_feature_url[0]['value'] .'">'; ?>
        <?php endif; ?>
        <?php if(count($node->field_image) > 0): ?>
          <?php print $node->field_image[0]['view']; ?>
        <?php endif; ?>
        <?php if(count($node->field_feature_url) > 0): ?>
          <?php print '</a>'; ?>
        <?php endif; ?>
        <?php if(count($node->field_body) > 0): ?>
          <?php print $node->field_body[0]['view']; ?>
        <?php endif; ?>
      </div>
      <div class="block-area">
        <div class="column col_1">
          <?php print_blocks($node->field_block_col1); ?>
        </div>
        <div class="column col_2">
          <?php print_blocks($node->field_block_col2); ?>
        </div>
        <div class="column col_3">
          <?php print_blocks($node->field_block_col3); ?>
        </div>
      </div>
    </div>

  </div> <!-- /node-inner -->
</div> <!-- /node-->
