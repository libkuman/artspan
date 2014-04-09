<?php foreach ($fields as $id => $field): ?>
  <?php if (!empty($field->separator)): ?>
    <?php print $field->separator; ?>
  <?php endif; ?>

<?php global $user; ?>
<?php if ($user->uid) { ?>
<?php //print_r($row); ?>
<?php } ?>
  <<?php print $field->inline_html;?> class="views-field-<?php print $field->class; if($row->users_civicrm_uf_match2__civicrm_value_artist_information_1_i_m_attending_89) { print " sfos-participant"; } ?>">
    <?php if ($field->label): ?>
      <label class="views-label-<?php print $field->class; ?>">
        <?php print $field->label; ?>:
      </label>
    <?php endif; ?>
    <?php
      // $field->element_type is either SPAN or DIV depending upon whether or not
      // the field is a 'block' element type or 'inline' element type.
    ?>
    <<?php print $field->element_type; ?> class="field-content"><?php print $field->content; ?></<?php print $field->element_type; ?>>
  </<?php print $field->inline_html;?>>
<?php endforeach; ?>
