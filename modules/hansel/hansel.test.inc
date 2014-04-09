<?php
// $Id: hansel.test.inc,v 1.1 2010/04/20 20:06:35 mauritsl Exp $

/**
 * @file
 * Hansel test page
 */

/**
 * Menu callback to generate the Hansel test form.
 *
 * @param array $form_state
 * @return array
 */
function hansel_test_form($form_state) {
  $form = array();
  
  $path = empty($form_state['values']['path']) ? base64_decode(arg(4)) : $form_state['values']['path'];

  $form['input'] = array(
    '#type' => 'fieldset',
    '#title' => t('Test input'),
  );
  
  $form['input']['path'] = array(
    '#type' => 'textfield',
    '#title' => t('Path'),
    '#default_value' => $path,
  );
  
  $form['input']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Test'),
    '#ahah' => array(
      'path' => HANSEL_PATH_JS_TEST_FORM,
      'wrapper' => 'hansel-output',
      'method' => 'replace',
      'effect' => 'fade',
    ),
  );
  
  $form['output'] = array(
    '#prefix' => '<div id="hansel-output">',
    '#suffix' => '</div>',
  );
  
  if (!isset($form_state['values']) && arg(4) == '') {
    $form['output']['info'] = array(
      // It is important to have any content in the wrapper, otherwise the
      // wrapper will not be displayed resulting in ahah malfunction
      '#value' => ' '
    );
  }
  else {
    _hansel_set_test_path($path);
    $breadcrumbs = hansel_get_breadcrumbs(TRUE);
    global $_hansel_test_messages;
    $breadcrumbs = is_array($breadcrumbs) ? implode(' &raquo; ', $breadcrumbs) : t('Restore old breadcrumbs');
    $form['output']['breadcrumbs'] = array(
      '#type' => 'fieldset',
      '#title' => t('Breadcrumbs'),
    );
    $form['output']['breadcrumbs']['output'] = array(
      '#value' => $breadcrumbs
    );
    $form['output']['trace'] = array(
      '#type' => 'fieldset',
      '#title' => t('Trace'),
    );
    $form['output']['trace']['messages'] = array(
      '#value' => '<ul><li>' . implode('</li><li>', $_hansel_test_messages) . '</li></ul>'
    );
  }
  
  return $form;
}

/**
 * Form submit handler.
 */
function hansel_test_form_submit($form, &$form_state) {
  $form_state['redirect'] = HANSEL_PATH_TEST.'/'.base64_encode($form_state['values']['path']);
}

/**
 * Menu callback for AHAH functionality.
 */
function hansel_test_form_ahah() {
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
  $output_form = $form['output'];
  unset($output_form['#prefix'], $output_form['#suffix']); // Prevent duplicate wrappers.
  $javascript = drupal_add_js(NULL, NULL, 'header');
  drupal_json(array(
    'status' => TRUE,
    'data' => theme('status_messages') . drupal_render($output_form),
    'settings' => call_user_func_array('array_merge_recursive', $javascript['setting']),
  ));
}

