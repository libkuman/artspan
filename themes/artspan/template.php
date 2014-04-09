<?php
    
// Auto-rebuild the theme registry during theme development.
if (theme_get_setting('artspan_rebuild_registry')) {
  drupal_rebuild_theme_registry();
}

// Add Zen Tabs styles
if (theme_get_setting('artspan_zen_tabs')) {
  drupal_add_css( drupal_get_path('theme', 'artspan') .'/css/tabs.css', 'theme', 'screen');
}



/*
 *	 This function creates the body classes that are relative to each page
 *	
 *	@param $vars
 *	  A sequential array of variables to pass to the theme template.
 *	@param $hook
 *	  The name of the theme function being called ("page" in this case.)
 */
function artspan_preprocess_page(&$vars, $hook) {

  // On user or profile edit pages, add template suggestions.
  $current_item = menu_get_item();
  if(isset($current_item['page_arguments']) && isset($current_item['page_arguments'][1])) {
    $current_page_type = $current_item['page_arguments'];
    if($current_page_type[1] == 'user_edit') {
      $vars['template_file'] = 'page-user-edit';
    }
    else if($current_page_type[1] == 'content_profile_page_edit') {
      $vars['template_file'] = 'page-profile-edit';
    }
  }
  //if ($vars['node']->type == "artwork") {
  //  $vars['template_files'][] = 'page-node-artwork';
  //}

  // Don't display empty help from node_help().
  if ($vars['help'] == "<div class=\"help\"><p></p>\n</div>") {
    $vars['help'] = '';
  }

  // Classes for body element. Allows advanced theming based on context
  // (home page, node of certain type, etc.)
  $body_classes = array($vars['body_classes']);
  if (user_access('administer blocks')) {
	  $body_classes[] = 'admin';
	}
	if (theme_get_setting('artspan_wireframe')) {
    $body_classes[] = 'with-wireframes'; // Optionally add the wireframes style.
  }
  if (!empty($vars['primary_links']) or !empty($vars['secondary_links'])) {
    $body_classes[] = 'with-navigation';
  }
  if (!empty($vars['secondary_links'])) {
    $body_classes[] = 'with-secondary';
  }
  if (module_exists('taxonomy') && $vars['node']->nid) {
    foreach (taxonomy_node_get_terms($vars['node']) as $term) {
    $body_classes[] = 'tax-' . eregi_replace('[^a-z0-9]', '-', $term->name);
    }
  }
  if (!$vars['is_front']) {
    // Add unique classes for each page and website section
    $path = drupal_get_path_alias($_GET['q']);
    list($section, ) = explode('/', $path, 2);
    $body_classes[] = artspan_id_safe('page-'. $path);
    $body_classes[] = artspan_id_safe('section-'. $section);

    if (arg(0) == 'node') {
      if (arg(1) == 'add') {
        if ($section == 'node') {
          array_pop($body_classes); // Remove 'section-node'
        }
        $body_classes[] = 'section-node-add'; // Add 'section-node-add'
      }
      elseif (is_numeric(arg(1)) && (arg(2) == 'edit' || arg(2) == 'delete')) {
        if ($section == 'node') {
          array_pop($body_classes); // Remove 'section-node'
        }
        $body_classes[] = 'section-node-'. arg(2); // Add 'section-node-edit' or 'section-node-delete'
      }
    }
  }
  /*  // Check what the user's browser is and add it as a body class
      // DEACTIVATED - Only works if page cache is deactivated
      $user_agent = $_SERVER['HTTP_USER_AGENT'];
      if($user_agent) {
        if (strpos($user_agent, 'MSIE')) {
          $body_classes[] = 'browser-ie';
        } else if (strpos($user_agent, 'MSIE 6.0')) {
          $body_classes[] = 'browser-ie6';
        } else if (strpos($user_agent, 'MSIE 7.0')) {
          $body_classes[] = 'browser-ie7';
        } else if (strpos($user_agent, 'MSIE 8.0')) {
          $body_classes[] = 'browser-ie8'; 
        } else if (strpos($user_agent, 'Firefox/2')) {
          $body_classes[] = 'browser-firefox2';
        } else if (strpos($user_agent, 'Firefox/3')) {
          $body_classes[] = 'browser-firefox3';
        }else if (strpos($user_agent, 'Safari')) {
          $body_classes[] = 'browser-safari';
        } else if (strpos($user_agent, 'Opera')) {
          $body_classes[] = 'browser-opera';
        }
      }
  
  /* Add template suggestions based on content type
   * You can use a different page template depending on the
   * content type or the node ID
   * For example, if you wish to have a different page template
   * for the story content type, just create a page template called
   * page-type-story.tpl.php
   * For a specific node, use the node ID in the name of the page template
   * like this : page-node-22.tpl.php (if the node ID is 22)
   */
  
  if ($vars['node']->type != "") {
      $vars['template_files'][] = "page-type-" . $vars['node']->type;
    }
  if ($vars['node']->nid != "") {
      $vars['template_files'][] = "page-node-" . $vars['node']->nid;
    }
  $vars['body_classes'] = implode(' ', $body_classes); // Concatenate with spaces
}

/*
 *	 This function creates the NODES classes, like 'node-unpublished' for nodes
 *	 that are not published, or 'node-mine' for node posted by the connected user...
 *	
 *	@param $vars
 *	  A sequential array of variables to pass to the theme template.
 *	@param $hook
 *	  The name of the theme function being called ("node" in this case.)
 */

function artspan_preprocess_node(&$vars, $hook) {
  // Special classes for nodes
  $classes = array('node');
  if ($vars['sticky']) {
    $classes[] = 'sticky';
  }
  // support for Skinr Module
  if (module_exists('skinr')) {
    $classes[] = $vars['skinr'];
  }
  if (!$vars['status']) {
    $classes[] = 'node-unpublished';
    $vars['unpublished'] = TRUE;
  }
  else {
    $vars['unpublished'] = FALSE;
  }
  if ($vars['uid'] && $vars['uid'] == $GLOBALS['user']->uid) {
    $classes[] = 'node-mine'; // Node is authored by current user.
  }
  if ($vars['teaser']) {
    $classes[] = 'node-teaser'; // Node is displayed as teaser.
  }
  $classes[] = 'clearfix';
  
  // Class for node type: "node-type-page", "node-type-story", "node-type-my-custom-type", etc.
  $classes[] = artspan_id_safe('node-type-' . $vars['type']);
  $vars['classes'] = implode(' ', $classes); // Concatenate with spaces
}

function artspan_preprocess_comment_wrapper(&$vars) {
  $classes = array();
  $classes[] = 'comment-wrapper';
  
  // Provide skinr support.
  if (module_exists('skinr')) {
    $classes[] = $vars['skinr'];
  }
  $vars['classes'] = implode(' ', $classes);
}


/*
 *	This function create the EDIT LINKS for blocks and menus blocks.
 *	When overing a block (except in IE6), some links appear to edit
 *	or configure the block. You can then edit the block, and once you are
 *	done, brought back to the first page.
 *
 * @param $vars
 *   A sequential array of variables to pass to the theme template.
 * @param $hook
 *   The name of the theme function being called ("block" in this case.)
 */ 

function artspan_preprocess_block(&$vars, $hook) {
    $block = $vars['block'];

    // special block classes
    $classes = array('block');
    $classes[] = artspan_id_safe('block-' . $vars['block']->module);
    $classes[] = artspan_id_safe('block-' . $vars['block']->region);
    $classes[] = artspan_id_safe('block-id-' . $vars['block']->bid);
    $classes[] = 'clearfix';
    
    // support for Skinr Module
    if (module_exists('skinr')) {
      $classes[] = $vars['skinr'];
    }
    
    $vars['block_classes'] = implode(' ', $classes); // Concatenate with spaces

    if (theme_get_setting('artspan_block_editing') && user_access('administer blocks')) {
        // Display 'edit block' for custom blocks.
        if ($block->module == 'block') {
          $edit_links[] = l('<span>' . t('edit block') . '</span>', 'admin/build/block/configure/' . $block->module . '/' . $block->delta,
            array(
              'attributes' => array(
                'title' => t('edit the content of this block'),
                'class' => 'block-edit',
              ),
              'query' => drupal_get_destination(),
              'html' => TRUE,
            )
          );
        }
        // Display 'configure' for other blocks.
        else {
          $edit_links[] = l('<span>' . t('configure') . '</span>', 'admin/build/block/configure/' . $block->module . '/' . $block->delta,
            array(
              'attributes' => array(
                'title' => t('configure this block'),
                'class' => 'block-config',
              ),
              'query' => drupal_get_destination(),
              'html' => TRUE,
            )
          );
        }
        // Display 'edit menu' for Menu blocks.
        if (($block->module == 'menu' || ($block->module == 'user' && $block->delta == 1)) && user_access('administer menu')) {
          $menu_name = ($block->module == 'user') ? 'navigation' : $block->delta;
          $edit_links[] = l('<span>' . t('edit menu') . '</span>', 'admin/build/menu-customize/' . $menu_name,
            array(
              'attributes' => array(
                'title' => t('edit the menu that defines this block'),
                'class' => 'block-edit-menu',
              ),
              'query' => drupal_get_destination(),
              'html' => TRUE,
            )
          );
        }
        // Display 'edit menu' for Menu block blocks.
        elseif ($block->module == 'menu_block' && user_access('administer menu')) {
          list($menu_name, ) = split(':', variable_get("menu_block_{$block->delta}_parent", 'navigation:0'));
          $edit_links[] = l('<span>' . t('edit menu') . '</span>', 'admin/build/menu-customize/' . $menu_name,
            array(
              'attributes' => array(
                'title' => t('edit the menu that defines this block'),
                'class' => 'block-edit-menu',
              ),
              'query' => drupal_get_destination(),
              'html' => TRUE,
            )
          );
        }
        $vars['edit_links_array'] = $edit_links;
        $vars['edit_links'] = '<div class="edit">' . implode(' ', $edit_links) . '</div>';
      }
  }

/*
 * Override or insert PHPTemplate variables into the block templates.
 *
 *  @param $vars
 *    An array of variables to pass to the theme template.
 *  @param $hook
 *    The name of the template being rendered ("comment" in this case.)
 */

function artspan_preprocess_comment(&$vars, $hook) {
  // Add an "unpublished" flag.
  $vars['unpublished'] = ($vars['comment']->status == COMMENT_NOT_PUBLISHED);

  // If comment subjects are disabled, don't display them.
  if (variable_get('comment_subject_field_' . $vars['node']->type, 1) == 0) {
    $vars['title'] = '';
  }

  // Special classes for comments.
  $classes = array('comment');
  if ($vars['comment']->new) {
    $classes[] = 'comment-new';
  }
  $classes[] = $vars['status'];
  $classes[] = $vars['zebra'];
  if ($vars['id'] == 1) {
    $classes[] = 'first';
  }
  if ($vars['id'] == $vars['node']->comment_count) {
    $classes[] = 'last';
  }
  if ($vars['comment']->uid == 0) {
    // Comment is by an anonymous user.
    $classes[] = 'comment-by-anon';
  }
  else {
    if ($vars['comment']->uid == $vars['node']->uid) {
      // Comment is by the node author.
      $classes[] = 'comment-by-author';
    }
    if ($vars['comment']->uid == $GLOBALS['user']->uid) {
      // Comment was posted by current user.
      $classes[] = 'comment-mine';
    }
  }
  $vars['classes'] = implode(' ', $classes);
}

/* 	
 * 	Customize the PRIMARY and SECONDARY LINKS, to allow the admin tabs to work on all browsers
 * 	An implementation of theme_menu_item_link()
 * 	
 * 	@param $link
 * 	  array The menu item to render.
 * 	@return
 * 	  string The rendered menu item.
 */ 	

function artspan_menu_item_link($link) {
  if (empty($link['localized_options'])) {
    $link['localized_options'] = array();
  }

  // If an item is a LOCAL TASK, render it as a tab
  if ($link['type'] & MENU_IS_LOCAL_TASK) {
    $link['title'] = '<span class="tab">' . check_plain($link['title']) . '</span>';
    $link['localized_options']['html'] = TRUE;
  }

  return l($link['title'], $link['href'], $link['localized_options']);
}

// Returns true if the supplied user object has the 'artist' role.
function artspan_is_user_artist($user) {
  $roles = $user->roles;
  foreach($roles as $role) {
    if($role == 'artist') {
      return true;
    }
  }
  return false;
}


// Returns true if the supplied user is the current user.
function artspan_is_current_user($user) {
  if(isset($GLOBALS["user"]->uid)) {
    return $GLOBALS["user"]->uid == $user->uid;
  }
}

/*
 *  Duplicate of theme_menu_local_tasks() but adds clear-block to tabs.
 *  Except, I added some stuff. If these are the tabs for editing profiles, only show
 *  the links for "my account", "edit profile", and "add artwork". In addition,
 *  make these the primary local tasks instead of the secondary local tasks.
 */
function artspan_menu_local_tasks() {
  
  // On user or profile edit pages, mess with the tabs.
  $current_item = menu_get_item();
  if(isset($current_item['page_arguments']) && isset($current_item['page_arguments'][1])) {
    $current_page_argument = $current_item['page_arguments'][0];
    $current_page_type = $current_item['page_arguments'][1];
    if($current_page_type == 'user_edit' || $current_page_type == 'content_profile_page_edit' || ($current_page_argument == 'artwork' && $current_item['path'] == 'user/%/edit/artwork')) {
      $user = $current_item['page_arguments'][count($current_item['page_arguments']) - 1];
      if($current_page_argument == 'artwork') {
        $user = user_load($user);
      }
      $output = '';
      if(artspan_is_user_artist($user)) {
        $uid = artspan_is_current_user($user) ? 'me' : $user->uid;
        $output .= '<li>' . l('<span class="tab">View Your Profile</span>', 'user', array('html'=>true)) . '</li>';
        //$output .= '<li>' . l('<span class="tab">View Your Events</span>', 'events/my-events', array('html'=>true)) . '</li>';
        $output .= '<li>' . l('<span class="tab">Edit Account</span>', 'user/' . $uid . '/edit', array('html'=>true)) . '</li>';
        $output .= '<li>' . l('<span class="tab">Edit Profile</span>', 'user/' . $uid . '/edit/artist', array('html'=>true)) . '</li>';
        $output .= '<li>' . l('<span class="tab">Add Artwork</span>', 'user/' . $user->uid . '/edit/artwork', array('html'=>true)) . '</li>';
        //$output .= '<li>' . l('<span class="tab">Add Event</span>', 'node/add/event', array('html'=>true)) . '</li>';
        $output = '<ul class="tabs secondary clearfix">' . $output . '</ul>';
      }
      return $output;
    }
  }
  if(isset($current_item['page_arguments']) && ($current_item['page_arguments'][0] == 'event' || $current_item['page_arguments'][0] == 'eventlist')) {
//    $current_page_argument = $current_item['page_arguments'][0];
//    $current_page_type = $current_item['page_arguments'][1];
    if($current_item['path'] == 'node/add/event' || $current_item['path'] == 'events/my-events') {
      global $user;
      $uid = $user->uid;
//      //$user = $current_item['page_arguments'][count($current_item['page_arguments']) - 1];
//      if($current_page_argument == 'event') {
//        $user = user_load($user);
//      }
      $output = '';
//      //if(artspan_is_user_artist($user)) {
//        //$uid = artspan_is_current_user($user) ? 'me' : $user->uid;
        $output .= '<li>' . l('<span class="tab">View Your Profile</span>', 'user', array('html'=>true)) . '</li>';
        $output .= '<li>' . l('<span class="tab">View Your Events</span>', 'events/my-events', array('html'=>true)) . '</li>';
        $output .= '<li>' . l('<span class="tab">Edit Account</span>', 'user/' . $uid . '/edit', array('html'=>true)) . '</li>';
        $output .= '<li>' . l('<span class="tab">Edit Profile</span>', 'user/' . $uid . '/edit/artist', array('html'=>true)) . '</li>';
        $output .= '<li>' . l('<span class="tab">Add Artwork</span>', 'user/' . $user->uid . '/edit/artwork', array('html'=>true)) . '</li>';
        $output .= '<li>' . l('<span class="tab">Add Event</span>', 'node/add/event', array('html'=>true)) . '</li>';
        $output = '<ul class="tabs secondary clearfix">' . $output . '</ul>';
//      //}
      return $output;
    }
  }
  

  $output = '';
  if ($primary = menu_primary_local_tasks()) {
    if(menu_secondary_local_tasks()) {
      $output .= '<ul class="tabs primary with-secondary clearfix">' . $primary . '</ul>';
    }
    else {
      $output .= '<ul class="tabs primary clearfix">' . $primary . '</ul>';
    }
  }
  if ($secondary = menu_secondary_local_tasks()) {
    $output .= '<ul class="tabs secondary clearfix">' . $secondary . '</ul>';
  }
  return $output;
}

/* 	
 * 	Add custom classes to menu item
 */	
	
function artspan_menu_item($link, $has_children, $menu = '', $in_active_trail = FALSE, $extra_class = NULL) {
  $class = ($menu ? 'expanded' : ($has_children ? 'collapsed' : 'leaf'));
  if (!empty($extra_class)) {
    $class .= ' '. $extra_class;
  }
  if ($in_active_trail) {
    $class .= ' active-trail';
  }
#New line added to get unique classes for each menu item
  $css_class = artspan_id_safe(str_replace(' ', '_', strip_tags($link)));
  return '<li class="'. $class . ' ' . $css_class . '">' . $link . $menu ."</li>\n";
}

/*	
 *	Converts a string to a suitable html ID attribute.
 *	
 *	 http://www.w3.org/TR/html4/struct/global.html#h-7.5.2 specifies what makes a
 *	 valid ID attribute in HTML. This function:
 *	
 *	- Ensure an ID starts with an alpha character by optionally adding an 'n'.
 *	- Replaces any character except A-Z, numbers, and underscores with dashes.
 *	- Converts entire string to lowercase.
 *	
 *	@param $string
 *	  The string
 *	@return
 *	  The converted string
 */	

function artspan_id_safe($string) {
  // Replace with dashes anything that isn't A-Z, numbers, dashes, or underscores.
  $string = strtolower(preg_replace('/[^a-zA-Z0-9_-]+/', '-', $string));
  // If the first character is not a-z, add 'n' in front.
  if (!ctype_lower($string{0})) { // Don't use ctype_alpha since its locale aware.
    $string = 'id'. $string;
  }
  return $string;
}

/**
* Return a themed breadcrumb trail.
*
* @param $breadcrumb
* An array containing the breadcrumb links.
* @return
* A string containing the breadcrumb output.
*/
function artspan_breadcrumb($breadcrumb) {
  // Determine if we are to display the breadcrumb.
  $show_breadcrumb = theme_get_setting('artspan_breadcrumb');
  if ($show_breadcrumb == 'yes' || $show_breadcrumb == 'admin' && arg(0) == 'admin') {

    // Optionally get rid of the homepage link.
    $show_breadcrumb_home = theme_get_setting('artspan_breadcrumb_home');
    if (!$show_breadcrumb_home) {
      array_shift($breadcrumb);
    }

    // Return the breadcrumb with separators.
    if (!empty($breadcrumb)) {
      $breadcrumb_separator = theme_get_setting('artspan_breadcrumb_separator');
      $trailing_separator = $title = '';
      if (theme_get_setting('artspan_breadcrumb_title')) {
        if ($title = drupal_get_title()) {
          $trailing_separator = $breadcrumb_separator;
        }
      }
      elseif (theme_get_setting('artspan_breadcrumb_trailing')) {
        $trailing_separator = $breadcrumb_separator;
      }
      return '<div class="breadcrumb">' . implode($breadcrumb_separator, $breadcrumb) . "$trailing_separator$title</div>";
    }
  }
  // Otherwise, return an empty string.
  return '';
}

function artspan_links($links, $attributes = array('class' => 'links')) {
	$output = '';

  if (count($links) > 0) {
    $output = '<ul' . drupal_attributes($attributes) . '>';

    $num_links = count($links);
    $i = 1;

    foreach ($links as $key => $link) {
      $class = $key;

      // Automatically add a class to each link and also to each LI
      if (isset($link['attributes']) && isset($link['attributes']['class'])) {
        $link['attributes']['class'] .= ' ' . $key;
      }
      else {
        $link['attributes']['class'] = $key;
      }

      // Add first and last classes to the list of links to help out themers.
      $extra_class = '';
      if ($i == 1) {
        $extra_class .= 'first ';
      }
      if ($i == $num_links) {
        $extra_class .= 'last ';
      }

			// Add id_safe name of link as a class
			$extra_class .= artspan_id_safe($link['title']) . ' ';

      $output .= '<li ' . drupal_attributes(array('class' => $extra_class . $class)) . '>';

      // Is the title HTML?
      $html = isset($link['html']) && $link['html'];

      // Initialize fragment and query variables.
      $link['query'] = isset($link['query']) ? $link['query'] : NULL;
      $link['fragment'] = isset($link['fragment']) ? $link['fragment'] : NULL;

			// Is this the primary menu? If so, add descriptions below links in a span
			if($attributes['id'] == 'primary') {
				$link['title'] = '<span class="name">' . $link['title'] . '</span><span class="description">' . $link['attributes']['title'] . '</span>';
				$link['html'] = TRUE;
			}

      if (isset($link['href'])) {
        $output .= l($link['title'], $link['href'], $link);
      }
      else if ($link['title']) {
        //Some links are actually not links, but we wrap these in <span> for adding title and class attributes
        if (!$html) {
          $link['title'] = check_plain($link['title']);
        }
        $output .= '<span' . drupal_attributes($link['attributes']) . '>' . $link['title'] . '</span>';
      }

      $i++;
      $output .= "</li>\n";
    }

    $output .= '</ul>';
  }

  return $output;
}

function artspan_theme() {
  return array(
    'user_profile_form' => array(
      'arguments' => array('form' => NULL),
      'template' => 'templates/user-profile-form',
    ),
    'user_login' => array(
      'arguments' => array('form' => NULL),
      'template' => 'templates/user-login',
    ),
    'artist_node_form' => array(
      'arguments' => array('form' => NULL),
      'template' => 'templates/artist-node-form',
    ),
    'event_node_form' => array(
      'arguments' => array('form' => NULL),
      'template' => 'templates/event-node-form',
    ),
    'user_register' => array(
      'arguments' => array('form' => NULL),
      'template' => 'templates/user-register',
    )
  );
}

function artspan_filefield_widget_file($element) {
  $output = '';
  $output .= '<div class="filefield-upload clear-block">';

  if (isset($element['#field_prefix'])) {
    $output .= $element['#field_prefix'];
  }

  _form_set_class($element, array('form-file'));
  $output .= '<input type="file" name="'. $element['#name'] .'"'. ($element['#attributes'] ? ' '. drupal_attributes($element['#attributes']) : '') .' id="'. $element['#id'] .'" size="'. $element['#size'] ."\" />\n";

  if (isset($element['#field_suffix'])) {
    $output .= $element['#field_suffix'];
  }

  $output .= '</div>';

  return theme('form_element', $element, $output);
}

function artspan_table($header, $rows, $attributes = array(), $caption = NULL) {
  if($attributes['id'] == 'field_dimensions_values') {
    $output = '';
    foreach($rows as $i => $row) {
      $output .= $row['data'][1];
      if($i != count($rows) - 1)
        $output .= '<span class="by">by</span> ';
    }
    return $output;
  }

  // Add sticky headers, if applicable.
  if (count($header)) {
    drupal_add_js('misc/tableheader.js');
    // Add 'sticky-enabled' class to the table to identify it for JS.
    // This is needed to target tables constructed by this function.
    $attributes['class'] = empty($attributes['class']) ? 'sticky-enabled' : ($attributes['class'] . ' sticky-enabled');
  }

  $output = '<table' . drupal_attributes($attributes) . ">\n";

  if (isset($caption)) {
    $output .= '<caption>' . $caption . "</caption>\n";
  }

  // Format the table header:
  if (count($header)) {
    $ts = tablesort_init($header);
    // HTML requires that the thead tag has tr tags in it followed by tbody
    // tags. Using ternary operator to check and see if we have any rows.
    $output .= (count($rows) ? ' <thead><tr>' : ' <tr>');
    foreach ($header as $cell) {
      $cell = tablesort_header($cell, $header, $ts);
      $output .= _theme_table_cell($cell, TRUE);
    }
    // Using ternary operator to close the tags based on whether or not there are rows
    $output .= (count($rows) ? " </tr></thead>\n" : "</tr>\n");
  }
  else {
    $ts = array();
  }

  // Format the table rows:
  if (count($rows)) {
    $output .= "<tbody>\n";
    $flip = array(
        'even' => 'odd',
        'odd' => 'even',
        );
    $class = 'even';
    foreach ($rows as $number => $row) {
      $attributes = array();

      // Check if we're dealing with a simple or complex row
      if (isset($row['data'])) {
        foreach ($row as $key => $value) {
          if ($key == 'data') {
            $cells = $value;
          }
          else {
            $attributes[$key] = $value;
          }
        }
      }
      else {
        $cells = $row;
      }
      if (count($cells)) {
        // Add odd/even class
        $class = $flip[$class];
        if (isset($attributes['class'])) {
          $attributes['class'] .= ' ' . $class;
        }
        else {
          $attributes['class'] = $class;
        }

        // Build row
        $output .= ' <tr' . drupal_attributes($attributes) . '>';
        $i = 0;
        foreach ($cells as $cell) {
          $cell = tablesort_cell($cell, $header, $ts, $i++);
          $output .= _theme_table_cell($cell);
        }
        $output .= " </tr>\n";
      }
    }
    $output .= "</tbody>\n";
  }

  $output .= "</table>\n";
  return $output;
}


function artspan_editview_node_form($form) {
  $view = $form['#parameters'][3];
  $header = array();
  $row = array();
  // Add selected fields to table as form fields.
  foreach ($view->field as $field) {
    // If auto_nodetitle module is enabled and the title field is to be overridden don't display it.
    if (module_exists('auto_nodetitle') && $field->real_field == 'title') {
      if (auto_nodetitle_get_setting($form['#node']->type) == AUTO_NODETITLE_ENABLED) {
        continue;
      }
    }

    $header[] = $field->label();
    $classes = array();
    $cell = array('data' => '');
    foreach (module_implements('field_form_render') as $module) {
      $function = $module .'_field_form_render';
      $result = $function($form, $field);
      foreach ($result as $key => $value) {
        switch ($key) {
          case 'data':
            $cell['data'] .= $value;
            break;
          case 'class':
            $classes[] = $value;
            break;
          default:
            $cell[$key] = $value;
            break;
        }
      }
    }
    $cell['class'] = implode(' ', $classes);
    $row[] = $cell;
  }
  if(isset($form['buttons']['delete'])) {
    $form['buttons']['delete']['#attributes']['class'] .= ' form-delete';
  }
  $buttons = drupal_render($form['buttons']['submit']);
  $buttons .= drupal_render($form['buttons']['delete']) .'<div style="display: none;">'. drupal_render($form) .'</div>'."\n";
  $handler = $view->style_plugin;
  $active = !empty($handler->active) ? $handler->active : '';
  $order = !empty($handler->order) ? $handler->order : 'asc';
	
	$output = '<div id="user-edit-heading">';
	if(isset($form['#node']->nid)) {
		$title = $form['#node']->field_content_title[0]['value'];
  	$output .= "<h1>$title</h1>";
	}
	else {
		$output .= "<h1>Add New Artwork</h1>";
	}
	$output .= '<div id="user-submit-buttons">' . $buttons . '</div></div>';
  foreach($row as $i => $r) {
    $output .= "<div class='editview-field field_$i'>" . "<label>" . $header[$i] . "</label>" . $r['data'] . "</div>";
  }
  return "<div class='editview-row'>$output</div>";
  return theme('table', $header, array($row), array('class' => 'editview-row'));
}

function artspan_textfield($element) {
  // Modify the search field for exposed views filters slightly
  if($element['#post']['form_id'] == 'views_exposed_form' && $element['#id'] == 'edit-keys') {
    $element['#attributes']['title'] = 'ENTER KEYWORDS';
  }


  $size = empty($element['#size']) ? '' : ' size="' . $element['#size'] . '"';
  $maxlength = empty($element['#maxlength']) ? '' : ' maxlength="' . $element['#maxlength'] . '"';
  $class = array('form-text');
  $extra = '';
  $output = '';

  if ($element['#autocomplete_path'] && menu_valid_path(array('link_path' => $element['#autocomplete_path']))) {
    drupal_add_js('misc/autocomplete.js');
    $class[] = 'form-autocomplete';
    $extra =  '<input class="autocomplete" type="hidden" id="' . $element['#id'] . '-autocomplete" value="' . check_url(url($element['#autocomplete_path'], array('absolute' => TRUE))) . '" disabled="disabled" />';
  }
  _form_set_class($element, $class);

  if (isset($element['#field_prefix'])) {
    $output .= '<span class="field-prefix">' . $element['#field_prefix'] . '</span> ';
  }

  if(isset($element['#attributes']['title'])) {
    $placeholder = ' placeholder="' . $element['#attributes']['title'] . '"';
  }

  $output .= '<input type="text"' . $maxlength . ' name="' . $element['#name'] . '" id="' . $element['#id'] . '"' . $size . ' value="' . check_plain($element['#value']) . '"' . drupal_attributes($element['#attributes']) . $placeholder .' />';

  if (isset($element['#field_suffix'])) {
    $output .= ' <span class="field-suffix">' . $element['#field_suffix'] . '</span>';
  }

  return theme('form_element', $element, $output) . $extra;
}

function artspan_preprocess_user_register(&$variables) {
  $variables['form']['account']['name']['#description'] = t('Spaces are allowed; punctuation is not allowed except for periods, hyphens, and underscores. Username will be used in your personalized URL for your online artist profile page (Example: http://www.artspan.org/artist/username). Set your username as your artist name, NOT your email address.');
  $variables['rendered'] = drupal_render($variables['form']);
}

function artspan_preprocess_user_profile_form(&$variables) {
  $variables['form']['account']['name']['#description'] = t('Spaces are allowed; punctuation is not allowed except for periods, hyphens, and underscores. Username will be used in your personalized URL for your online artist profile page (Example: http://www.artspan.org/artist/username). Set your username as your artist name, NOT your email address.');
}
