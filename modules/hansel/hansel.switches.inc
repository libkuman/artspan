<?php
// $Id: hansel.switches.inc,v 1.10 2011/02/08 22:44:42 mauritsl Exp $

/**
 * @file
 * Hansel switches
 *
 * Switches from Hansel core are located in this file.
 * The definitions resides in hansel.module
 *
 * @see hansel_hansel_switch_types()
 */

/**
 * Callback for "url argument" switch to compare a given value.
 *
 * @param array $arguments
 * @param string $value
 * @return boolean
 */
function hansel_switch_url_argument_compare($arguments, $value) {
  if (empty($arguments['argument'])) {
    $arg = hansel_arg(0);
  }
  else {
    $arg = hansel_arg(((int) $arguments['argument']) - 1);
  }
  
  // Check for <empty>
  if ($arg == '' && $value == HANSEL_SWITCH_EMPTY) {
    return TRUE;
  }

  if (empty($arguments['regex'])) {
    if (empty($arguments['cs'])) {
      // Use case insensitive matching.
      return strtolower($arg) == strtolower($value);
    }
    else {
      return $arg == $value;
    }
  }
  else {
    // Backslash forward slashes
    $pattern = str_replace('/', '\\/', $value);
    $pattern = "/$pattern/s";
    if (empty($arguments['cs'])) {
      // Use case insensitive matching.
      $pattern .= 'i';
    }
    return (bool) @preg_match($pattern, $arg);
  }
}

/**
 * Callback for "url argument" switch to generate the information line.
 *
 * @param array $arguments
 * @return string
 */
function hansel_switch_url_argument_info($arguments) {
  if (empty($arguments)) {
    return '1';
  }
  return $arguments['argument'];
}

/**
 * Callback for "url argument" switch to generate the configuration form.
 *
 * @param array $arguments
 * @return array
 */
function hansel_switch_url_argument_config_form($arguments) {
  $form = array();
  
  $form['argument'] = array(
    '#type' => 'textfield',
    '#title' => t('Argument number'),
    '#default_value' => empty($arguments['argument']) ? '1' : $arguments['argument'],
    '#size' => 5,
    '#description' => t('Must be numeric. Use 1 for the first argument.'),
    '#required' => TRUE,
  );

  $form['cs'] = array(
    '#type' => 'checkbox',
    '#title' => t('Use case sensitive matching'),
    '#default_value' => !empty($arguments['cs']),
    '#description' => t('Do not match on rule "node" when on path "Node".'),
  );

  $form['regex'] = array(
    '#type' => 'checkbox',
    '#title' => t('Use regular expressions'),
    '#default_value' => !empty($arguments['regex']),
    '#description' => t('If enabled, all subrules are parsed as Perl compatible regular expressions.'),
  );

  $form['#validate'] = array('hansel_switch_url_arguments_config_form_validate');
  
  return hansel_switch_configure_form($form);
}

/**
 * Validate callback for the "url argument" switch configuration form.
 */
function hansel_switch_url_arguments_config_form_validate($form, &$form_state) {
  if (!preg_match('/^[1-9][0-9]?$/', $form_state['values']['argument'])) {
    form_set_error('argument', t('Argument must be a number between 1 and 99'));
  }
}

/**
 * Callback for "PHP code" switch to compare a given value.
 *
 * @param array $arguments
 * @param string $value
 * @return boolean
 */
function hansel_switch_php_argument_compare($arguments, $value) {
  return drupal_eval($arguments['php_argument']) == $value;
}

/**
 * Callback for "PHP code" switch to generate the configuration form.
 *
 * @param array $arguments
 * @return array
 */
function hansel_switch_php_argument_config_form($arguments) {
  $form = array();

  if (user_access('use PHP for hansel configuration')) {
    $form['php_argument'] = array(
      '#type' => 'textarea',
      '#title' => t('PHP evaluation'),
      '#default_value' => isset($arguments['php_argument']) ? $arguments['php_argument'] : '',
      '#description' => t('PHP code to return the argument. Include %php tags.', array('%php' => '<?php ?>')),
      '#required' => TRUE,
    );

    return hansel_switch_configure_form($form);
  }
  else {
    $form['php_argument'] = array(
      '#type' => 'value',
      '#value' => isset($arguments['php_argument']) ? $arguments['php_argument'] : '',
    );
    $form['php_argument_info'] = array(
      '#type' => 'markup',
      '#value' => t('You don\'t have sufficient permissions to use PHP in Hansel.'),
    );
    return $form;
  }
}

/**
 * Callback for "node id" switch to compare a given value.
 *
 * @param array $arguments
 * @param string $value
 * @return boolean
 */
function hansel_switch_node_id_compare($arguments, $value) {
  if (hansel_arg(0) == 'node' && is_numeric(hansel_arg(1))) {
    return hansel_arg(1) == $value;
  }
  return FALSE;
}

/**
 * Callback for "node id" switch to compare a given value.
 *
 * @param array $arguments
 * @param string $value
 * @return boolean
 */
function hansel_switch_node_type_compare($arguments, $value) {
  if (strtolower(hansel_arg(0)) == 'node' && is_numeric(hansel_arg(1))) {
    if ($node = node_load(hansel_arg(1))) {
      return strtolower($node->type) == strtolower($value);
    }
  }
  return FALSE;
}

/**
 * Callback for "node age" switch to generate the configuration form.
 *
 * @param array $arguments
 * @return array
 */
function hansel_switch_node_age_config_form($arguments) {
  $form = array();

  $options = drupal_map_assoc(array(1, 60, 3600, 86400, 86400 * 30, 86400 * 365), 'format_interval');
  $form['unit'] = array(
    '#type' => 'radios',
    '#title' => t('Unit definition'),
    '#options' => $options,
    '#default_value' => empty($arguments['unit']) ? 86400 : $arguments['unit'],
    '#required' => TRUE,
    '#description' => t('When this is set to 1 day, a subrule with the name <em>&lt; 2</em> means less than two days old.'),
  );

  return hansel_switch_configure_form($form);
}

/**
 * Callback for "node age" switch to compare a given value.
 *
 * @param array $arguments
 * @param string $value
 * @return boolean
 */
function hansel_switch_node_age_compare($arguments, $value) {
  if (strtolower(hansel_arg(0)) == 'node' && is_numeric(hansel_arg(1))) {
    if ($node = node_load(hansel_arg(1))) {
      $unit = empty($arguments['unit']) ? 86400 : $arguments['unit'];
      $age = (time() - $node->created) / $unit;
      // Check for ranges (e.g. "0-100" or "0.5-1").
      if (preg_match('/^([0-9\\.]+)\\-([0-9\\.]+)$/si', $value, $match)) {
        $min = (float) $match[1];
        $max = (float) $match[2];
        return ($age >= $min && $age < $max);
      }
      // Check for value with operator (e.g. "< 3", "<3", "> 4" or "<=0.5").
      elseif (preg_match('/^(\\<|\\>|\\<\\=|\\>\\=) *([0-9\\.]+)$/si', $value, $match)) {
        $operator = $match[1];
        $check_value = (float) $match[2];
        switch ($operator) {
          case '<':
            return ($age < $check_value);
          case '>':
            return ($age > $check_value);
          case '<=':
            return ($age <= $check_value);
          case '>=':
            return ($age >= $check_value);
        }
      }
    }
  }
  return FALSE;
}

/**
 * Callback for "node age" switch to generate the information line.
 *
 * @param array $arguments
 * @return string
 */
function hansel_switch_node_age_info($arguments) {
  $unit = empty($arguments['unit']) ? 86400 : $arguments['unit'];
  return t('with units of %interval', array('%interval' => format_interval($unit)));
}

/**
 * Callback for "path alias" switch to compare a given value.
 *
 * @param array $arguments
 * @param string $value
 * @return boolean
 */
function hansel_switch_path_alias_compare($arguments, $value) {
  if (!module_exists('path')) {
    return FALSE;
  }

  $alias = drupal_get_path_alias(hansel_path());
  
  $mode = empty($arguments['mode']) ? HANSEL_OPTION_STARTS_WITH : $arguments['mode'];
  switch ($mode) {
    case HANSEL_OPTION_STARTS_WITH:
      $pattern = '^' . preg_quote($value);
      break;
    case HANSEL_OPTION_ENDS_WITH:
      $pattern = preg_quote($value) . '$';
      break;
    case HANSEL_OPTION_CONTAINS:
      $pattern = preg_quote($value);
      break;
    case HANSEL_OPTION_REGEX:
      $pattern = $value;
      break;
  }
  $pattern = str_replace('/', '\\/', $pattern);
  $pattern = "/$pattern/s";
  if (empty($arguments['cs'])) {
    $pattern .= 'i';
  }
  
  return (bool) @preg_match($pattern, $alias);
}

/**
 * Callback for "path alias" switch to generate the information line.
 *
 * @param array $arguments
 * @return string
 */
function hansel_switch_path_alias_info($arguments) {
  $mode = empty($arguments['mode']) ? HANSEL_OPTION_STARTS_WITH : $arguments['mode'];
  switch ($mode) {
    case HANSEL_OPTION_STARTS_WITH:
      return '(' . t(HANSEL_OPTION_STARTS_WITH_TEXT) . ')';
    case HANSEL_OPTION_ENDS_WITH:
      return '(' . t(HANSEL_OPTION_ENDS_WITH_TEXT) . ')';
    case HANSEL_OPTION_CONTAINS:
      return '(' . t(HANSEL_OPTION_CONTAINS_TEXT) . ')';
    case HANSEL_OPTION_REGEX:
      return '(' . t(HANSEL_OPTION_REGEX_TEXT) . ')';
  }
}

/**
 * Callback for "path alias" switch to generate the configuration form.
 *
 * @param array $arguments
 * @return array
 */
function hansel_switch_path_alias_config_form($arguments) {
  $form = array();

  $options = array(
    HANSEL_OPTION_STARTS_WITH => t(HANSEL_OPTION_STARTS_WITH_TEXT),
    HANSEL_OPTION_ENDS_WITH => t(HANSEL_OPTION_ENDS_WITH_TEXT),
    HANSEL_OPTION_CONTAINS => t(HANSEL_OPTION_CONTAINS_TEXT),
    HANSEL_OPTION_REGEX => t(HANSEL_OPTION_REGEX_TEXT),
  );
  $form['mode'] = array(
    '#type' => 'radios',
    '#title' => t('Mode'),
    '#options' => $options,
    '#default_value' => empty($arguments['mode']) ? HANSEL_OPTION_STARTS_WITH : $arguments['mode'],
    '#required' => TRUE,
  );

  $form['cs'] = array(
    '#type' => 'checkbox',
    '#title' => t('Use case sensitive matching'),
    '#default_value' => !empty($arguments['cs']),
    '#description' => t('Do not match on rule "node" when on path "Node".'),
  );

  return hansel_switch_configure_form($form);
}