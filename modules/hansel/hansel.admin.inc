<?php
// $Id: hansel.admin.inc,v 1.8 2011/02/08 23:36:06 mauritsl Exp $

/**
 * @file
 * Hansel admin pages
 */

/**
 * Page callback
 *
 * Generates the admin page
 */
function hansel_page() {
  $output = '';
  
  drupal_add_css(drupal_get_path('module', 'hansel') . '/hansel.admin.css');

  if ($rules = _hansel_list_rules()) {
    $output .= '<div id="hansel-rules">';
    $output .= $rules;
    $output .= '</div>';
  }
  else {
    // No rules are defined yet.
    $output .= '<p>' . t('You do not have any rules defined yet. You can create a new rule with the form below.') . '</p>';
    $output .= '<p>' . t('All configurations must have a rule with the name "start", which is the first rule to be executed.') . '</p>';
  }
  
  module_load_include('inc', 'hansel', 'hansel.forms');
  
  $output .= theme('box', t('Add rule'), drupal_get_form('hansel_rule_form'));
  
  return $output;
}

function _hansel_list_rules($pid = 0) {
  $output = '';
  foreach (_hansel_get_rules($pid) as $rule) {
    $delete_path = str_replace('%', $rule->rid, HANSEL_PATH_DELETE_RULE);
    $config_path = str_replace('%', $rule->rid, HANSEL_PATH_CONFIG_RULE);
    $delete_link = '<a href="' . url($delete_path) . '" class="hansel-delete-link"><span>delete</span></a>';
    $config_link = '<a href="' . url($config_path) . '" class="hansel-config-rule-link"><span>configure rule</span></a>';
    $output .= '<div class="hansel-rule">';
    
    // Add rule name, configure and delete link
    $output .= '<div class="hansel-name">';
    $output .= check_plain($rule->name);
    $output .= '<div class="hansel-buttons">' . $config_link . ' ' . $delete_link . '</div>';
    $output .= '</div>';
    
    $output .= '<div class="hansel-info">';
    
    // Add crumb info (if any)
    if (!empty($rule->crumb_action)) {
      $output .= '<div class="hansel-crumb-info">';
      $output .= _hansel_get_crumb_action_info($rule);
      $output .= '</div>';
    }
    
    // Add action info
    $output .= '<div class="hansel-type">';
    switch ($rule->action) {
      case 'goto':
        $sql = 'SELECT r.name FROM {hansel_rule} r WHERE r.rid = %d';
        $res = db_query($sql, $rule->destination);
        if (!$name = db_result($res)) {
          $name = t('(broken link)');
        }
        $output .= 'goto ' . check_plain($name);
        break;
      case 'leave':
        if ($rule->restore) {
          $output .= 'leave (restore original breadcrumbs)';
        }
        else {
          $output .= 'leave';
        }
        break;
      case 'switch':
        if (_hansel_is_configurable_switch($rule->handler)) {
          $config_path = str_replace('%', $rule->rid, HANSEL_PATH_CONFIG_SWITCH);
          $output .= 'switch on ' . l($rule->handler, $config_path);
        }
        else {
          $output .= 'switch on ' . check_plain($rule->handler);
        }
        $output .= ' ' . _hansel_get_switch_info($rule);
        break;
    }
    $output .= '</div>';

    // Add child rules (if any)
    $children = _hansel_list_rules($rule->rid);
    if ($children) {
      $output .= '<div class="hansel-children">' . $children . '</div>';
    }

    $output .= '</div>'; // Close .hansel-info
    $output .= '</div>'; // Close .hansel-rule
  }
  return $output;
}

/**
 * Page callback.
 *
 * Generates the settings page for Hansel.
 */
function hansel_settings($form_state) {
  $form = array();

  $form[HANSEL_VAR_LAST_ITEM_LINK] = array(
    '#type' => 'checkbox',
    '#title' => t('Render last item as a link'),
    '#default_value' => variable_get(HANSEL_VAR_LAST_ITEM_LINK, TRUE),
    '#description' => t('When turned on, the last item in the breadcrumb will be a link.'),
  );

  $form[HANSEL_VAR_LAST_ITEM_HIDE] = array(
    '#type' => 'checkbox',
    '#title' => t('Hide last item'),
    '#default_value' => variable_get(HANSEL_VAR_LAST_ITEM_HIDE, FALSE),
    '#description' => t('Remove the last item of the breadcrumb.'),
  );

  $form[HANSEL_VAR_MAX_ITEM_LENGTH] = array(
    '#type' => 'textfield',
    '#title' => t('Maximum length for individual items'),
    '#default_value' => variable_get(HANSEL_VAR_MAX_ITEM_LENGTH, 0),
    '#description' => t('Maximum length in characters. Use 0 to disable trimming.'),
    '#size' => 5,
  );

  $form[HANSEL_VAR_TRIM_ON_WORD_BOUNDARY] = array(
    '#type' => 'checkbox',
    '#title' => t('Trim on word boundary'),
    '#default_value' => variable_get(HANSEL_VAR_TRIM_ON_WORD_BOUNDARY, TRUE),
  );

  $form[HANSEL_VAR_TRIM_ELLPIPSIS] = array(
    '#type' => 'textfield',
    '#title' => t('Ellipsis'),
    '#default_value' => variable_get(HANSEL_VAR_TRIM_ELLPIPSIS, HANSEL_VAR_TRIM_ELLPIPSIS_DEFAULT),
    '#description' => t('Text to add after trimmed texts.'),
  );

  $form[HANSEL_VAR_MAX_ITEM_COUNT] = array(
    '#type' => 'textfield',
    '#title' => t('Maximum number of items'),
    '#default_value' => variable_get(HANSEL_VAR_MAX_ITEM_COUNT, 0),
    '#description' => t('Maximum number of breadcrumb items. Items will be removed from the middle. Use 0 to disable.'),
    '#size' => 5,
  );

  $form[HANSEL_VAR_REMOVED_ITEMS_REPLACEMENT] = array(
    '#type' => 'textfield',
    '#title' => t('Removed items replacement'),
    '#default_value' => variable_get(HANSEL_VAR_REMOVED_ITEMS_REPLACEMENT, HANSEL_VAR_REMOVED_ITEMS_REPLACEMENT_DEFAULT),
    '#description' => t('Removed breadcrumb items will be replaced by this text.'),
  );

  $options = array(60, 120, 300, 600, 900, 1800, 3600, 10800, 21600, 43200, 86400);
  $options = array(0 => t('Disable')) + drupal_map_assoc($options, 'format_interval');
  $form[HANSEL_VAR_CACHE] = array(
    '#type' => 'select',
    '#title' => t('Cache'),
    '#options' => $options,
    '#default_value' => variable_get(HANSEL_VAR_CACHE, HANSEL_VAR_CACHE_DEFAULT),
    '#description' => t('Only parts which require interaction with the database are cached. Enabling this cache is only recommended when you use an efficient caching system (such as memcache).'),
   );

  $form[HANSEL_VAR_SET_MENU_NAME] = array(
    '#type' => 'checkbox',
    '#title' => t('Set active menu name for current path'),
    '#default_value' => variable_get(HANSEL_VAR_SET_MENU_NAME, HANSEL_VAR_SET_MENU_NAME_DEFAULT),
    '#description' => t('This is required for the \'leave original breadcrumbs\' option to work for pages which are not in the default menu. Turn this option off if you experience side effects.'),
  );

  return system_settings_form($form);
}

/**
 * Validate callback for the Hansel settings form.
 */
function hansel_settings_validate($form, &$form_state) {
  if (!preg_match('/^[0-9]+$/', $form_state['values'][HANSEL_VAR_MAX_ITEM_LENGTH])) {
    form_set_error(HANSEL_VAR_MAX_ITEM_LENGTH, t('The maximum length of individual breadcrumb items must be a number.'));
  }
  if (!preg_match('/^[0-9]+$/', $form_state['values'][HANSEL_VAR_MAX_ITEM_COUNT])) {
    form_set_error(HANSEL_VAR_MAX_ITEM_COUNT, t('The maximum number of items must be a number.'));
  }
}