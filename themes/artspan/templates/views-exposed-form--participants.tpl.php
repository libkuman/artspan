<?php
// $Id: views-exposed-form.tpl.php,v 1.4.4.1 2009/11/18 20:37:58 merlinofchaos Exp $
/**
 * @file views-exposed-form.tpl.php
 *
 * This template handles the layout of the views exposed filter form.
 *
 * Variables available:
 * - $widgets: An array of exposed form widgets. Each widget contains:
 * - $widget->label: The visible label to print. May be optional.
 * - $widget->operator: The operator for the widget. May be optional.
 * - $widget->widget: The widget itself.
 * - $button: The submit button for the form.
 *
 * @ingroup views_templates
 */
?>
<?php if (!empty($q)): ?>
  <?php
    // This ensures that, if clean URLs are off, the 'q' is added first so that
    // it shows up first in the URL.
    print $q;
  ?>
<?php endif; ?>
<div class="views-exposed-form">
  <div class="views-exposed-widgets clear-block">
  <div class="filter-header">
  <h3>Search Artist at Open Studios</h3>
  <a href="">Week 1 (October 13/14)</a><br />
  <a href="">Week 2 (October 20/21)</a><br />
  <a href="">Week 3 (October 23/24)</a><br />
  <a href="">Week 4 (November 3/4)</a><br />
  </div>
    <?php foreach($widgets as $id => $widget): ?>
      <div class="views-exposed-widget" id="exposed-widget-<?php print $widget->id; ?>">
        <?php if (!empty($widget->label)): ?>
          <label for="<?php print $widget->id; ?>">
            <?php print $widget->label; ?>
          </label>
        <?php endif; ?>
        <?php if (!empty($widget->operator)): ?>
          <div class="views-operator">
            <?php print $widget->operator; ?>
          </div>
        <?php endif; ?>
        <div class="views-widget">
          <?php print $widget->widget; ?>
        </div>
      </div>
    <?php endforeach; ?>
    <div class="views-exposed-widget submit-area">
      <?php print $button ?>
    </div>
  </div>
</div>


