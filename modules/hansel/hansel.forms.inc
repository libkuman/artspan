<?php
// $Id: hansel.forms.inc,v 1.5 2011/02/08 16:01:49 mauritsl Exp $

/**
 * @file
 * Hansel forms
 */

/**
 * Generates the form for adding and editing rules.
 *
 * @param array $form_state
 * @param int $rid
 * @return array
 */
function hansel_rule_form(&$form_state, $rid = NULL) {
  $form = array();

  // This form may use collapsible fieldsets in AHAH functionality (for the token list). We need to manually load the JS for that case.
  drupal_add_js('misc/collapse.js');
  
  if ($form_state['submitted']) {
    !empty($form_state['storage']) or $form_state['storage'] = array();
    $form_state['storage'] += $form_state['values'];
  }
  
  if ($rid) {
    $form['#rid'] = $rid;
    $sql = 'SELECT r.rid, r.pid, r.name, r.crumb_action, r.crumb_action_arguments,
    rag.destination as goto_destination,
    ral.restore_original as leave_restore,
    ras.handler as switch_handler,
    ras.arguments as switch_arguments
    FROM {hansel_rule} r
    LEFT JOIN {hansel_rule_action_goto} rag ON rag.rid = r.rid
    LEFT JOIN {hansel_rule_action_leave} ral ON ral.rid = r.rid
    LEFT JOIN {hansel_rule_action_switch} ras ON ras.rid = r.rid
    WHERE r.rid = %d';
    $res = db_query($sql, $rid);
    if (!$rule = db_fetch_object($res)) {
      drupal_set_message(t('Rule not found'), 'error');
      drupal_goto(HANSEL_PATH);
    }
    $rule->crumb_action_arguments = unserialize($rule->crumb_action_arguments);
    $rule_action = 'leave';
    empty($rule->goto_destination) or $rule_action = 'goto';
    empty($rule->switch_handler) or $rule_action = 'switch';
  }
  else {
    $rule = new stdClass();
  }
  
  $form['name'] = array(
    '#type' => 'textfield',
    '#title' => t('Name'),
    '#required' => TRUE,
    '#default_value' => isset($form_state['storage']['name']) ?
      $form_state['storage']['name']
      :
      isset($rule->name) ? $rule->name : ''
  );
  
  $form['pid'] = array(
    '#type' => 'select',
    '#title' => t('Parent'),
    '#options' => _hansel_get_rules_tree(-1),
    '#default_value' => isset($form_state['storage']['pid']) ?
      $form_state['storage']['pid']
      :
      isset($rule->pid) ? $rule->pid : 0,
  );
  
  $form['config'] = array(
    '#prefix' => '<div id="hansel-config">',
    '#suffix' => '</div>',
  );
  
  $values = isset($form_state['values']) ? $form_state['values'] : array();
  if (empty($values['action'])) {
    $options = array('' => 'none');
    foreach (_hansel_get_action_types() as $types => $details) {
      $options[$types] = $types;
    }
    $form['config']['crumb_action'] = array(
      '#type' => 'select',
      '#title' => t('Action with breadcrumbs'),
      '#options' => $options,
      '#default_value' => isset($rule->crumb_action) ? $rule->crumb_action : 'add single link',
    );
    
    $form['config']['action'] = array(
      '#type' => 'radios',
      '#title' => t('Action'),
      '#required' => TRUE,
      '#options' => array(
        'goto' => t('Goto rule'),
        'leave' => t('Leave'),
        'switch' => t('Switch'),
      ),
      '#default_value' => isset($rule_action) ? $rule_action : ''
    );
    
    $form['config']['next'] = array(
      '#type' => 'submit',
      '#value' => t('Next'),
      '#ahah' => array(
        'path' => HANSEL_PATH_JS_RULE_FORM,
        'wrapper' => 'hansel-config',
        'method' => 'replace',
        'effect' => 'fade',
      ),
    );
    
    // Add a cancel link only if we are editting an existing rule
    if (isset($rule->name)) {
      $form['config']['buttons']['cancel'] = array(
        '#value' => l('Cancel', HANSEL_PATH),
      );
    }
  }
  else {
    if (!empty($values['crumb_action'])) {
      $action_types = _hansel_get_action_types();
      if (!empty($action_types[$values['crumb_action']]['config form'])) {
        $arguments = !empty($rule->crumb_action_arguments) ? $rule->crumb_action_arguments : array();
        $config_form = call_user_func($action_types[$values['crumb_action']]['config form'], $arguments);
        if (!empty($config_form)) {
          $form['config']['crumb'] = array(
            '#type' => 'fieldset',
            '#title' => t('Actions with breadcrumbs'),
          );
        }
        foreach ($config_form as $name => $element) {
          $form['config']['crumb']['crumb_action_' . $name] = $element;
        }
        // Add the list of available tokens.
        if (!empty($action_types[$values['crumb_action']]['show token list']) && module_exists('token')) {
          $form['config']['tokens'] = _hansel_rule_form_generate_token_list();
        }
      }
    }
    
    if ($values['action'] == 'goto') {
      $options = _hansel_get_rules_tree();
      
      $form['config']['goto'] = array(
        '#type' => 'fieldset',
        '#title' => t('Goto'),
      );
      
      $form['config']['goto']['destination_rid'] = array(
        '#type' => 'select',
        '#title' => t('Destination'),
        '#options' => $options,
        '#required' => TRUE,
        '#default_value' => isset($rule->goto_destination) ? $rule->goto_destination : 0,
      );
    }
    
    if ($values['action'] == 'switch') {
      $options = array();
      foreach (_hansel_get_switch_types() as $types => $details) {
        $options[$types] = $types;
      }
      
      $form['config']['switch'] = array(
        '#type' => 'fieldset',
        '#title' => t('Switch'),
      );
      
      $form['config']['switch']['handler'] = array(
        '#type' => 'select',
        '#title' => t('Switch on'),
        '#options' => $options,
        '#required' => TRUE,
        '#default_value' => isset($rule->switch_handler) ? $rule->switch_handler : '',
      );
    }
    
    if ($values['action'] == 'leave') {
      $form['config']['leave'] = array(
        '#type' => 'fieldset',
        '#title' => t('Leave'),
      );
      
      $form['config']['leave']['restore'] = array(
        '#type' => 'checkbox',
        '#title' => t('Restore original breadcrumbs'),
        '#default_value' => !empty($rule->leave_restore) ? TRUE : FALSE,
      );
    }
    
    $form['config']['buttons']['save'] = array(
      '#type' => 'submit',
      '#value' => t('Save'),
    );
    
    $form['config']['buttons']['cancel'] = array(
      '#value' => l('Cancel', HANSEL_PATH),
    );
  }
  
  return $form;
}

/**
 * Generate a fieldset with a list of available tokens.
 *
 * @param array $types
 * @return array
 */
function _hansel_rule_form_generate_token_list() {
  $placeholders = array(
    array('[arg-?]', t('URL argument. Replace the question mark by a number, 1 is the first argument.')),
  );
  $patterns = token_get_list(array('node', 'user'));
  foreach ($patterns as $type => $pattern_set) {
    foreach ($pattern_set as $pattern => $description) {
      $placeholders[] = array(check_plain("[$pattern]"), check_plain($description));
    }
  }
  $form_element = array(
    '#type' => 'fieldset',
    '#title' => t('Available tokens'),
    '#description' => t('You can use these tokens in the link title and path. Not all tokens might be available, depending on the path. You should use the "-raw" alternative if available.'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form_element['items'] = array(
    '#type' => 'markup',
    '#value' => theme('table', array(t('Token'), t('Description')), $placeholders),
  );
  return $form_element;
}

/**
 * Menu callback for the AHAH functionality.
 */
function hansel_rule_form_ahah() {
  $form_state = array('storage' => NULL, 'submitted' => FALSE);
  $form_build_id = $_POST['form_build_id'];
  $form = form_get_cache($form_build_id, $form_state);
  $args = $form['#parameters'];
  $form_id = array_shift($args);
  $form['#post'] = $_POST;
  $form['#redirect'] = FALSE;
  $form['#programmed'] = FALSE;
  $form_state['post'] = $_POST;
  drupal_process_form($form_id, $form, $form_state);
  $form = drupal_rebuild_form($form_id, $form_state, $args, $form_build_id);
  $config_form = $form['config'];
  unset($config_form['#prefix'], $config_form['#suffix']); // Prevent duplicate wrappers.
  $javascript = drupal_add_js(NULL, NULL, 'header');
  drupal_json(array(
    'status' => TRUE,
    'data' => theme('status_messages') . drupal_render($config_form),
    'settings' => call_user_func_array('array_merge_recursive', $javascript['setting']),
  ));
}

/**
 * Form submit handler.
 *
 * Saves the rule to the database.
 */
function hansel_rule_form_submit($form, &$form_state) {
  switch ($form_state['clicked_button']['#id']) {
    case 'edit-save':
      $name = $form_state['values']['name'];
      $pid = $form_state['values']['pid'];
      $action = $form_state['storage']['action'];
      $crumb_action = $form_state['storage']['crumb_action'];
      
      $rule = new stdClass();
      
      // Build the update argument for drupal_write_record()
      $update = array();
      if (!empty($form['#rid'])) {
        $update[] = 'rid';
        $rule->rid = $form['#rid'];
      }
      
      $rule->name = $name;
      $rule->pid = $pid;
      if ($crumb_action) {
        $crumb_action_arguments = array();
        foreach ($form_state['values'] as $name => $value) {
          if (preg_match('/^crumb_action_(.+)$/', $name, $match)) {
            $crumb_action_arguments[$match[1]] = $value;
          }
        }
        $rule->crumb_action = $crumb_action;
        $rule->crumb_action_arguments = serialize($crumb_action_arguments);
      }
      else {
        $rule->crumb_action = '';
        $rule->crumb_action_arguments = serialize(array());
      }
      if (!drupal_write_record('hansel_rule', $rule, $update)) {
        drupal_set_message(t('Error writing rule to database'), 'error');
        return;
      }
      
      if ($update) {
        foreach (array('goto', 'leave', 'switch') as $table_suffix) {
          db_query('DELETE FROM {hansel_rule_action_' . $table_suffix . '} WHERE rid = %d', $rule->rid);
          db_affected_rows(); // Make sure the query ran before inserting the new row
        }
      }
      
      $rule_action = new stdClass();
      $rule_action->rid = $rule->rid;
      switch ($action) {
        case 'goto':
          $rule_action->destination = $form_state['values']['destination_rid'];
          break;
        case 'leave':
          $rule_action->restore_original = $form_state['values']['restore'] ? 1 : 0;
          break;
        case 'switch':
          $rule_action->handler = $form_state['values']['handler'];
          $rule_action->arguments = serialize(array());
          break;
      }
      if (!drupal_write_record('hansel_rule_action_' . $action, $rule_action)) {
        drupal_set_message(t('Error writing rule to database'), 'error');
        return;
      }
      
      cache_clear_all(HANSEL_CONFIG_CID, 'cache');
      
      drupal_set_message(t('Succesfully saved rule %name', array('%name' => $rule->name)));
      
      $form_state['redirect'] = HANSEL_PATH;
      drupal_goto(HANSEL_PATH); // $form_state['redirect'] does not redirect for some reason
      break;
  }
}

/**
 * Alter a switch configuration form before passing it to the rendering engine.
 *
 * @param array $form
 * @return array
 */
function hansel_switch_configure_form($form) {
  global $_hansel_rid;
  
  $form['#rid'] = $_hansel_rid;
  $form['#submit'][] = 'hansel_switch_configure_form_submit';
  
  $form['buttons']['save'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
  );
  
  $form['buttons']['cancel'] = array(
    '#value' => l('Cancel', HANSEL_PATH),
  );
  
  return $form;
}

/**
 * Form submit handler for switch configuration forms.
 *
 * Saves the switch configuration to the database.
 */
function hansel_switch_configure_form_submit($form, &$form_state) {
  $arguments = $form_state['values'];
  unset($arguments['op']);
  unset($arguments['save']);
  unset($arguments['form_build_id']);
  unset($arguments['form_token']);
  unset($arguments['form_id']);
  
  $sql = 'UPDATE {hansel_rule_action_switch} SET arguments = \'%s\' WHERE rid = %d';
  db_query($sql, serialize($arguments), $form['#rid']);
  
  cache_clear_all(HANSEL_CONFIG_CID, 'cache');
  
  $form_state['redirect'] = HANSEL_PATH;
}

/**
 * Generate the switch configuration form.
 */
function hansel_config_switch_form(&$form_state, $rid) {
  // Store the rule id in a global var, will be used in hansel_switch_configure_form().
  global $_hansel_rid;
  $_hansel_rid = $rid;
  
  $sql = 'SELECT r.rid, r.name,
  ras.handler as handler,
  ras.arguments as arguments
  FROM {hansel_rule} r
  JOIN {hansel_rule_action_switch} ras ON ras.rid = r.rid
  WHERE r.rid = %d';
  $res = db_query($sql, $rid);
  if (!$rule = db_fetch_object($res)) {
    drupal_set_message(t('No switch found for rule'), 'error');
    drupal_goto(HANSEL_PATH);
  }
  
  if (!_hansel_is_configurable_switch($rule->handler)) {
    drupal_set_message(t('Invalid or non configurable switch handler'), 'error');
    drupal_goto(HANSEL_PATH);
  }
  
  $rule->arguments = empty($rule->arguments) ? array() : unserialize($rule->arguments);
  
  $types = _hansel_get_switch_types();
  return call_user_func($types[$rule->handler]['config form'], $rule->arguments);
}

/**
 * Menu callback for the delete rule form.
 */
function hansel_delete_rule_form(&$form_state, $rid) {
  $form = array();
  
  $form['#rid'] = $rid;
  
  $sql = 'SELECT r.name, (
    SELECT COUNT(*) FROM {hansel_rule} r2 WHERE r2.pid = r.rid
  ) as children
  FROM {hansel_rule} r
  WHERE r.rid = %d';
  $res = db_query($sql, $rid);
  if (!$rule = db_fetch_object($res)) {
    drupal_set_message(t('Rule not found'), 'error');
    drupal_goto(HANSEL_PATH);
  }
  
  if ($rule->children) {
    $form['info'] = array(
      '#value' => '<p>' . t('Are you sure you want to delete rule %rule with all its child rules?', array('%rule' => $rule->name)) . '</p>'
    );
  }
  else {
    $form['info'] = array(
      '#value' => '<p>' . t('Are you sure you want to delete rule %rule?', array('%rule' => $rule->name)) . '</p>'
    );
  }
  
  $form['buttons']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Delete'),
  );
  
  $form['buttons']['cancel'] = array(
    '#value' => l('Cancel', HANSEL_PATH),
  );
  
  return $form;
}

/**
 * Delete a rule with all of its subrules.
 *
 * @access private
 * @param int $rid
 * @return int
 */
function _hansel_delete_rule($rid) {
  $deleted = 0;
  $sql = 'SELECT rid FROM {hansel_rule} WHERE pid = %d';
  $res = db_query($sql, $rid);
  while ($rule = db_fetch_object($res)) {
    $deleted += _hansel_delete_rule($rule->rid);
  }
  $sql = 'DELETE FROM {hansel_rule} WHERE rid = %d';
  db_query($sql, $rid);
  $deleted += db_affected_rows();
  return $deleted;
}

/**
 * Form submit handler for the delete rule form.
 */
function hansel_delete_rule_form_submit($form, &$form_state) {
  $deleted = _hansel_delete_rule($form['#rid']);
  cache_clear_all(HANSEL_CONFIG_CID, 'cache');
  drupal_set_message(format_plural(
    $deleted,
    '1 rule deleted',
    '@count rules deleted'
  ));
  $form_state['redirect'] = HANSEL_PATH;
}
