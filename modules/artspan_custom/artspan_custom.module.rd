<?php
// Load the Civi API
function _artspan_load_civi_api() {
  civicrm_initialize(true);
  global $civicrm_root;
  $civi_api = "$civicrm_root/api/v2";
  include_once "$civi_api/UFGroup.php";
  include_once "$civi_api/Contact.php";
  include_once "$civi_api/Location.php";
  include_once "$civi_api/Participant.php";
}

// Get a Civi Contact
function _artspan_get_civi_contact($user, $options = array()) {
  _artspan_load_civi_api();
  if($user != FALSE) {
    $civi_uid = civicrm_uf_match_id_get($user);
    $options['contact_id'] = $civi_uid;
  }
  $civi_user = civicrm_contact_get($options);
  $options = array('contact_id' => $options['contact_id'], 'version' => '3.0');
  $civi_location = civicrm_location_get($options); 
  return array_merge($civi_user[$options['contact_id']], $civi_location);
}

// Get a Civi Contact's Location info off their Event Registration
function _artspan_get_civi_contact_public_location_info($user, $options = array()) {
  _artspan_load_civi_api();
  $PUBLIC_LOCATION_ID = 7;
  $STUDIO_LOCATION_ID = 6;
  $STUDIO_LOCATION_ID_OS1 = 8;
  $STUDIO_LOCATION_ID_OS2 = 9;
  $contact = _artspan_get_civi_contact($user, $options);
  
  foreach($contact['phone'] as $number) {
    if($number['location_type_id'] == $PUBLIC_LOCATION_ID) {
      $contact['phone'] = $number['phone'];
      break;
    }
  }

  if(is_array($contact['phone'])) {
    $contact['phone'] = NULL;
  }

  foreach($contact['email'] as $email) {
    if($email['location_type_id'] == $PUBLIC_LOCATION_ID) {
      $contact['email'] = $email['email'];
      break;
    }
  }

  foreach($contact['address'] as $address) {
    if($address['location_type_id'] == $STUDIO_LOCATION_ID) {
      $contact['address'] = $address;
    }
    if($address['location_type_id'] == $STUDIO_LOCATION_ID_OS1) {
      $contact['address_os1'] = $address;
    }
    if($address['location_type_id'] == $STUDIO_LOCATION_ID_OS2) {
      $contact['address_os2'] = $address;
    }
  }

  if($contact['address'] && $contact['address']['use_shared_address'] == 1) {
    $master_id = $contact['address']['master_id'];
    $query = "SELECT c.id, c.display_name, c.contact_sub_type, a.street_address, a.supplemental_address_1, a.city, a.state_province_id, a.postal_code, a.country_id FROM civicrm_address AS a LEFT JOIN civicrm_contact AS c ON a.contact_id = c.id WHERE a.id = $master_id;";
    $result = db_query($query);
    $contact['address']['shared'] = db_fetch_object($result);
  }

  if($contact['address_os1'] && $contact['address_os1']['use_shared_address'] == 1) {
    $master_id = $contact['address_os1']['master_id'];
    $query = "SELECT c.id, c.display_name, c.contact_sub_type, a.street_address, a.supplemental_address_1, a.city, a.state_province_id, a.postal_code, a.country_id FROM civicrm_address AS a LEFT JOIN civicrm_contact AS c ON a.contact_id = c.id WHERE a.id = $master_id;";
    $result = db_query($query);
    $contact['address_os1']['shared'] = db_fetch_object($result);
  }

  if($contact['address_os2'] && $contact['address_os2']['use_shared_address'] == 1) {
    $master_id = $contact['address_os2']['master_id'];
    $query = "SELECT c.id, c.display_name, c.contact_sub_type, a.street_address, a.supplemental_address_1, a.city, a.state_province_id, a.postal_code, a.country_id FROM civicrm_address AS a LEFT JOIN civicrm_contact AS c ON a.contact_id = c.id WHERE a.id = $master_id;";
    $result = db_query($query);
    $contact['address_os2']['shared'] = db_fetch_object($result);
  }

  if(is_array($contact['email'])) {
    $contact['email'] = NULL;
  }

  return $contact;
}

// Get a Civi Contacts Website
function _artspan_get_artist_websites($contact_id) {
  $contact_id = mysql_real_escape_string($contact_id);
  $query = "SELECT w.url, w.id, t.name FROM civicrm_website AS w LEFT JOIN (SELECT name, value FROM civicrm_option_value AS v WHERE v.option_group_id = (SELECT id FROM civicrm_option_group WHERE name='website_type')) AS t ON (w.website_type_id = t.value) WHERE w.contact_id = '$contact_id';";
  $result = db_query($query);
  $sites = array();
  while($website = db_fetch_object($result)) {
    $sites[] = $website;
  }
  return $sites;
}

function _artspan_load_civi_group_studios() {
  _artspan_load_civi_api();
  $params = array(
      'sort' => 'organization_name',
      'contact_type' => 'Organization',
      'contact_sub_type' => 'Group_Studio_Site',
      'rowCount' => 100
      );
  return civicrm_contact_get($params);
}

// Checks to see if the user is an event participant margot@rd **SFOS-UPDATE** change event id
function _artspan_get_openstudios_information($contact_id) {
  _artspan_load_civi_api();
  $params = array(
    'contact_id' => $contact_id,
    'event_title' => 'SF Open Studios - 2013'
    );
  $results =& civicrm_participant_search($params);
  if(count($results) > 0) {
    foreach($results as $result) {
    	if ($result['event_id'] == 69) {
      	//if(strstr($result['participant_fee_level'], 'Artist') || strstr($result['participant_fee_level'], 'Combo')) {
      	return $result;
      	//}
    	}
    }
  }
  return false;
}

// Get the states from the Civi list based on USA country Id
function _artspan_load_state_options($country_id=1228, $field='name') {
  $field = mysql_real_escape_string($field);
  $country_id = mysql_real_escape_string($country_id);
  $query = "SELECT $field, id FROM civicrm_state_province WHERE country_id = '$country_id';";
  $result = db_query($query);
  $states = array();
  while($state = db_fetch_object($result)) {
    $states[$state->id] = $state->{$field};
  }
  return $states;
}

function _artspan_sync_artist_role($user, $civi_contact) {
  $is_artist = FALSE;
  foreach($user->roles as $role) {
    if($role == 'artist') $is_artist = TRUE;
  }
  if($is_artist != ($civi_contact['contact_sub_type'] == 'Artist')) {
    _artspan_load_civi_api();
    $params = array(
      'contact_id' => $civi_contact['contact_id'],
      'contact_type' => 'Individual',
      'contact_sub_type' => $is_artist ? 'Artist' : ''
    );
    $result = civicrm_contact_update($params);
  }
}

function _artspan_openstudios_register($user, $event, $values) {
  if($event['title'] == 'SF Open Studios - 2013') {
    $PUBLIC_LOCATION_ID = 7;
    // Get form results
    foreach($values as $value) {
      if(is_array($value)) {
        $results = $value;
      }
    }

    // Is a public/studio location already set?
    $public = FALSE;
    $contact = _artspan_get_civi_contact($user);
    foreach(array_merge($contact['phone'], $contact['email']) as $i) {
      if($i['location_type_id'] == $PUBLIC_LOCATION_ID) {
        $public = TRUE;
      }
    }

    // Save the user's publishable email and phone
    $phone_type = ($results['custom_46'] == 'phone-2-2') ? 2 : 1;
    $params = array(
      'version' => '3.0',
      'contact_id' => $contact['contact_id'],
      'email' => array(
        array(
          'email' => $results['custom_29'],
          'location_type_id' => $PUBLIC_LOCATION_ID
        )
      ),
      'phone' => array(
        array(
          'phone' => $results['custom_30'],
          'location_type_id' => $PUBLIC_LOCATION_ID,
          'phone_type_id' => $phone_type
        )
      )
    );

    if($public) {
      $response =& civicrm_location_update($params);
    }
    else {
      $response =& civicrm_location_add($params);
    }

    // Save .tiffs to a custom directory
    $tiff_dest = '/web/artspan.org/www/sites/default/files/openstudios_tiffs/';

    $firstname = urlencode($results['first_name']);
    $lastname = urlencode($results['last_name']);
    $parent = array_pop(taxonomy_get_parents(intval($results['studio_weekend_no'])));
    if(isset($parent) && isset($results['custom_16']['name'])) {
      $weekend_no = array_pop(explode(" ", trim(array_shift(explode("-", $parent->name)))));
      $filename = $tiff_dest . $firstname . '_' . $lastname . '_' . $weekend_no . '.tiff';
      while (file_exists($filename)) {
        $next_number = (is_int($next_number)) ? $next_number + 1 : 0;
        $filename = $tiff_dest . $firstname . '_' . $lastname . '_' . $weekend_no . '_' . $next_number . '.tiff';
      }
      copy($results['custom_16']['name'], $filename);
    }

    $parent_2 = array_pop(taxonomy_get_parents(intval($results['studio_weekend_no_2'])));
    if(isset($parent_2) && isset($results['custom_56']['name'])) {
      $weekend_no_2 = array_pop(explode(" ", trim(array_shift(explode("-", $parent_2->name)))));
      $filename = $tiff_dest . $firstname . '_' . $lastname . '_' . $weekend_no_2 . '.tiff';
      $next_number = -1;
      while (file_exists($filename)) {
        $filename = $tiff_dest . $firstname . '_' . $lastname . '_' . $weekend_no_2 . '_' . ++$next_number . '.tiff';
      }
      copy($results['custom_56']['name'], $filename);
    }
  }
}


function artspan_custom_menu() {
  return array (
    'artspan/js/addwebsite' => array (
      'title' => 'Javascript Add Website to List',
      'page callback' => 'artspan_addwebsite_js',
      'access arguments' => array('access content'),
      'type' => MENU_CALLBACK,
    ),
  );
}

function artspan_addwebsite_js($uid) {
  $civi_user = _artspan_get_civi_contact($uid);
  $form = artspan_addwebsite_form($uid, $civi_user);
  $form[] = array('#type' => 'textfield', '#name' => 'website_new');
  $rendered = drupal_render($form);
  print drupal_json(array('status' => TRUE, 'data' => $rendered));
}

function artspan_addwebsite_form($uid, $civi_user, &$twitter = NULL, &$facebook = NULL) {
  $siteform = array(
    '#title' => 'Web Site',
    '#prefix' => '<div id="field-website-items">',
    '#suffix' => '</div>',
    '#weight' => -5
  );

  $websites = _artspan_get_artist_websites($civi_user['contact_id']);

  foreach($websites as $website) {
    if($website->name == 'Twitter') {
      $twitter = $website;
      continue;
    }
    else if($website->name == 'Facebook') {
      $facebook = $website;
      continue;
    }
    else {
      $siteform['website_' . $website->id] = array(
        '#type' => 'textfield',
        '#value' => $website->url,
        '#name' => 'website_'. $website->id
      );
    }
  }

  $siteform['website_new_1'] = array(
    '#type' => 'textfield',
    '#name' => 'website_new_1'
    );

  return $siteform;
}

// Adds form fields to the Profile Edit form - margot@rd 
function artspan_custom_form_artist_node_form_alter(&$form) {
  $uid = $form['uid']['#value'];
  $civi_user = _artspan_get_civi_contact_public_location_info($uid, array('return_custom_45' => 1, 'return_custom_3' => 1, 'return_custom_4' => 1));
  $civi_user_event = _artspan_get_openstudios_information($civi_user['contact_id']);
  _artspan_sync_artist_role(user_load($form['#node']->uid), $civi_user);
  $form['_civi_contact'] = array(
    '#type' => 'value',
    '#value' => $civi_user
  );

  $form['phone'] = array(
    '#type' => 'textfield',
    '#title' => 'Public Phone Number',
    '#value' => $civi_user['phone'],
  );

  $form['email'] = array(
    '#type' => 'textfield',
    '#title' => 'Public Email Address',
    '#value' => $civi_user_event['custom_29'],
  ); 
  
  $form['websites'] = array(
    '#title' => 'Web Site',
    '#type' => 'fieldset',
    'field_websites_add_more' => array(
      '#type' => 'submit',
      '#name' => 'field_websites_add_more',
      '#value' => 'Add another item',
      '#submit' => array('content_add_more_submit_proxy'),
      '#ahah' => array(
        'path' => 'artspan/js/addwebsite/'. $uid,
        'wrapper' => 'field-website-items',
        'method' => 'replace',
        'effect' => 'fade',
        'event' => 'mousedown',
        'keypress' => TRUE
      ),
      '#button_type' => 'submit'
    )
  );

  $form['websites']['list'] = artspan_addwebsite_form($uid, $civi_user, $twitter, $facebook);

  if(isset($twitter)) {
    $twitter_url = $twitter->url;
    $twitter_name = 'website_' . $twitter->id;
  }
  else {
    $twitter_name = 'website_Twitter';
  }

  $form['twitter'] = array(
    '#type' => 'textfield',
    '#title' => 'Twitter URL',
    '#value' => $twitter_url,
    '#name' => $twitter_name,
    '#size' => 60
  );

  if(isset($facebook)) {
    $facebook_url = $facebook->url;
    $facebook_name = 'website_' . $facebook->id;
  }
  else {
    $facebook_name = 'website_Facebook';
  }
  
  $form['facebook'] = array(
    '#type' => 'textfield',
    '#title' => 'Facebook URL',
    '#value' => $facebook_url,
    '#name' => $facebook_name,
    '#size' => 60
  );


  $group_studios = array(0=>'- Select A Studio -');
  foreach(_artspan_load_civi_group_studios() as $studio) {
    $group_studios[$studio['contact_id']] = $studio['display_name'];
  }
  $states = _artspan_load_state_options();
  drupal_add_js(drupal_get_path('module', 'artspan_custom') . '/studio.js');
  $group_studio = isset($civi_user['address']['shared']) && $civi_user['address']['shared']->contact_sub_type == 'Group_Studio_Site';

  /*$form['studio'] = array(
    '#type' => 'fieldset',
    '#title' => 'Studio Information',
    '#attributes' => array(
      'class' => 'studio_information'
      ),
    'name' => array(
      '#type' => 'textfield',
      '#title' => 'Title',
      '#name' => 'studio_title',
      '#default_value' => $civi_user['custom_45']
      ),
    'groupsite_question' => array(
      '#type' => 'radios',
      '#title' => 'Is your studio part of a group site?',
      '#options' => array('No', 'Yes'),
      '#default_value' => $group_studio ? 1 : 0,
      ),
    'groupsite_no' => array(
      '#type' => 'fieldset',
      '#attributes' => array(
        'class' => 'groupsite groupsite_no'
        ),
      'address' => array(
        '#type' => 'textfield',
        '#title' => 'Studio Address',
        '#name' => 'studio_address',
        '#default_value' => $group_studio ? '' : $civi_user['address']['street_address']
        ),
      'city' => array(
        '#type' => 'textfield',
        '#title' => 'Studio City',
        '#name' => 'studio_city',
        '#default_value' => ($group_studio || !$civi_user['address']['city']) ? 'San Francisco' : $civi_user['address']['city']
        ),
      'state' => array(
        '#type' => 'select',
        '#title' => 'State',
        '#name' => 'state',
        '#options' => $states,
        '#default_value' => ($group_studio || !$civi_user['address']['state_province_id']) ? array_search('California', $states) : $civi_user['address']['state_province_id']
        ),
      'zip' => array(
        '#type' => 'textfield',
        '#title' => 'Zip Code',
        '#name' => 'zip',
        '#default_value' => $group_studio ? '' : $civi_user['address']['postal_code']
        ),
      ),
    'groupsite_yes' => array(
      '#type' => 'fieldset',
      '#attributes' => array(
        'class' => 'groupsite groupsite_yes'
        ),
      'location' => array(
        '#type' => 'select',
        '#title' => 'Group Location Reference',
        '#name' => 'group_location_reference',
        '#options' => $group_studios,
        '#default_value' => $group_studio && array_key_exists($civi_user['address']['shared']->id, $group_studios) ? $civi_user['address']['shared']->id : 0
        ),
      'building_no' => array(
        '#type' => 'textfield',
        '#title' => 'Bldg. #',
        '#name' => 'building_no',
        '#default_value' => $group_studio ? $civi_user['custom_3'] : ''
        ),
      'studio_no' => array(
        '#type' => 'textfield',
        '#title' => 'Studio #',
        '#name' => 'studio_no',
        '#default_value' => $group_studio ? $civi_user['custom_4'] : ''
        )
      )
    );*/

  $form['#validate'] = array('_artspan_artist_profile_validate');
  $form['#submit'] = array('_artspan_artist_profile_submit');
  $form['buttons']['submit']['#submit'][] = '_artspan_artist_profile_redirect';
}

function artspan_custom_civicrm_buildForm( $formName, &$form ) {
  if($formName == 'CRM_Event_Form_Registration_Register') {
    civicrm_initialize();
    require_once 'api/v2/Event.php';
    $params = array('id' => $form->_eventId);
    $event = civicrm_event_get( $params );
    if($event['event_type_id'] == 2) {
      //$form->add('text', 'studio_artist_name', 'Artist Name', true, true);
      $form->add('select', 'studio_weekend_no', 'Weekend/Location 1', true);
      $form->add('select', 'studio_weekend_no_2', 'Weekend/Location 2', true);
      $form->add('text', 'group_site', 'Is this studio part of a group site?', true);
      $form->add('text', 'studio_address', '', true);
      $form->add('text', 'studio_city', '', true);
      $form->add('text', 'studio_state', '', true);
      $form->add('text', 'studio_zip', '', true);
      $form->add('select', 'studio_group_location', '', true);
      $form->add('text', 'studio_no', '', true);
      $form->add('text', 'studio_bldg_no', '', true);
      $form->add('text', 'group_site_2', 'Is this studio part of a group site?', true);
      $form->add('text', 'studio_address_2', '', true);
      $form->add('text', 'studio_city_2', '', true);
      $form->add('text', 'studio_state_2', '', true);
      $form->add('text', 'studio_zip_2', '', true);
      $form->add('select', 'studio_group_location_2', '', true);
      $form->add('text', 'studio_no_2', '', true);
      $form->add('text', 'studio_bldg_no_2', '', true);
    }
  }
}

// Runs after the Civic Registration takes place - margot@rd
function artspan_custom_civicrm_postProcess( $formName, &$form ) {
  if ($formName == 'CRM_Event_Form_Registration_Confirm') {
    global $user;
    $form_data = array();
    
    // Here they add the artist role to the drupal profile - margot@rd
    $user_roles = $user->roles;
    $user_roles[3] = 'artist';
    user_save($user, array('roles' => $user_roles));
    
		// load all the form values into into $form_data - margot@rd
    foreach($form->_values['params'] as $key => $form_values) {
      if (is_numeric($key)) $form_data = $form_values;
    }

		// Add civi event info to the artist profile node or make a new one for them - margot@rd
    $profile_node = node_load(array('uid' => $user->uid, 'type' => 'artist'), NULL, true);

    if (!is_object($profile_node)) {
      $profile_node = new stdClass();
      $profile_node->uid = $user->uid;
    
      $profile_node->type = 'artist';
      $profile_node->created = strtotime("now");
      $profile_node->changed = strtotime("now");
      $profile_node->status = 1;
      $profile_node->comment = 0;
      $profile_node->promote = 0;
      $profile_node->moderate = 0;
      $profile_node->sticky = 0;
    }

    if ($form_data['studio_weekend_no']) {
      $profile_node->field_sf_open_studios = array();

      $term = taxonomy_get_term($form_data['studio_weekend_no']);
      $parents = taxonomy_get_parents($form_data['studio_weekend_no']);
      foreach($parents as $getparent) {
        $parent = $getparent;
      }
      $profile_node->field_sf_open_studios[] = array('value' => $parent->tid);
      $profile_node->field_sf_open_studios[] = array('value' => $term->tid);
      $profile_node->field_sf_open_studios_wk1 = array(array('value' => $term->tid));
    }

    if ($form_data['studio_weekend_no_2']) {
      $term = taxonomy_get_term($form_data['studio_weekend_no_2']);
      $parents = taxonomy_get_parents($form_data['studio_weekend_no_2']);
      foreach($parents as $getparent) {
        $parent = $getparent;
      }

      $profile_node->field_sf_open_studios[] = array('value' => $parent->tid);
      $profile_node->field_sf_open_studios[] = array('value' => $term->tid);
      $profile_node->field_sf_open_studios_wk2 = array(array('value' => $term->tid));
    }

    //$profile_node->field_content_title[0]['value'] = trim($form_data['studio_artist_name']);
    $profile_node->field_content_title[0]['value'] = trim($form_data['custom_10']);
    /*
 		if ($primary_medium = taxonomy_get_term_by_name($form_data['custom_21'])) {
    	$tid = key($primary_medium );
 			$profile_node->field_primary_medium['value'] = array('tid' => $tid);
		} */
		
		// Then save the Node out with the new data - margot@rd
    node_save($profile_node);
    _node_index_node($profile_node);

    // Save address and phone to civi contact
    $contact = _artspan_get_civi_contact_public_location_info($user->uid);
    $STUDIO_LOCATION_ID = 6;
    $STUDIO_LOCATION_ID_OS1 = 8;
    $STUDIO_LOCATION_ID_OS2 = 9;

    // Is a group site set for weekend 1? If yes then
    if($form_data['custom_70'] == 1) {
      // Get group site
      $post_group_location_reference = db_escape_string($form_data['custom_83_id']);
      $query = "SELECT id, street_address, supplemental_address_1, city, state_province_id, postal_code, country_id FROM civicrm_address WHERE contact_id = $post_group_location_reference AND location_type_id = $STUDIO_LOCATION_ID;";
		
      $result = db_query($query);

      $groupsite = db_fetch_object($result);

      /* $studio_params = array(
        'version' => '3.0',
        'contact_id' => $contact['contact_id'],
        'address' => array(
          1 => array(
            'location_type_id' => $STUDIO_LOCATION_ID_OS1,
            'street_address' => $groupsite->street_address,
            'supplemental_address_1' => $groupsite->supplemental_address_1,
            'supplemental_address_2' => '',
            'city' => $groupsite->city,
            'state_province_id' => $groupsite->state_province_id,
            'postal_code' => $groupsite->postal_code,
            'country_id' => $groupsite->country_id,
            'master_id' => ''
            )
          )
        ); */
  		/* Not sure what this did but it can't work anyway margot@rd
   		if($form_data['studio_bldg_no']) {
        $studio_params['address'][1]['supplemental_address_2'] .= 'Bldg Number: ' . $form_data['studio_bldg_no'] . ' ';
      }
      if($form_data['studio_no']) {
        $studio_params['address'][1]['supplemental_address_2'] .= 'Studio Number: ' . $form_data['studio_no'] . ' ';
      }
			// Studio bldg and number details
      $contact_params['custom_73'] = $form_data['custom_73'];
      $contact_params['custom_74'] = $form_data['custom_74'];
      */
      
      // Get the civi participant record - margot@rd
			$params = array(
    		'contact_id' => $contact['contact_id'],
   			'event_title' => 'SF Open Studios - 2013'
   		 );
  		$participant =& civicrm_participant_search($params);
  		
			foreach ($participant as $part) {
				$participant_id = $part['participant_id'];
			}

			// we need this participant id so that we can use it to update the participant record
			
      $contact_params['custom_25'] = $groupsite->street_address;
      $contact_params['custom_71'] = $groupsite->city;
      $contact_params['custom_28'] = $groupsite->postal_code;
      $contact_params['custom_72'] = $groupsite->state;
      $contact_params['version'] = 3;
      $contact_params['event_id'] = 69;
      $contact_params['id'] = $participant_id;
      
    } else {
    	// runs if is not group studio? what the hell did this do since if that's the case then there is no need for it? - margot@rd
      /* $studio_params = array(
        'version' => '3.0',
        'contact_id' => $contact['contact_id'],
        'address' => array(
          1 => array(
            'location_type_id' => $STUDIO_LOCATION_ID_OS1,
            'street_address' => $form_data['studio_address'],
            'city' => $form_data['studio_city'],
            'state_province_id' => $form_data['studio_state'],
            'postal_code' => $form_data['studio_zip'],
            'country_id' => 1228,
            'master_id' => ''
            )
          )
        );
      $contact_params['custom_73'] = '';
      $contact_params['custom_74'] = ''; */
    }

		// what does this stuff do!!!
    /* if($contact['address_os1']['location_type_id'] == $STUDIO_LOCATION_ID_OS1) {
      $response =& civicrm_location_update($studio_params);
    } else {
      $response =& civicrm_location_add($studio_params);
    } */

    //print_r($contact_params);
    // This was the old code that from what I can tell was adding the address info to the contact addresses so weird! - margot@rd 
    //$response =& civicrm_location_update($studio_params);
    //$response =& civicrm_contact_update($contact_params);
    
		// Ok now that we have what we need lets actually use the api to update (arrg create?) the participant - margot@rd
		$response =& civicrm_api('participant','create', $contact_params);

    // Do we process locations for a second weekend?
   // if(stripos($form_data['amount'], '2 weekends')) {
      // Yes we do:

      // Is a group site set for weekend 2?
      if($form_data['custom_76'] == 1) {
      $contact_params = array();
				print('ok we are trying to save!');
        // Get group site
        $post_group_location_reference = mysql_real_escape_string($form_data['custom_84_id']);
        $query = "SELECT id, street_address, supplemental_address_1, city, state_province_id, postal_code, country_id FROM civicrm_address WHERE contact_id = $post_group_location_reference AND location_type_id = $STUDIO_LOCATION_ID;";
        $result = db_query($query);
        $groupsite = db_fetch_object($result);
        print_r($groupsite);
       /* $studio_params = array(
          'version' => '3.0',
          'contact_id' => $contact['contact_id'],
          'address' => array(
            1 => array(
              'location_type_id' => $STUDIO_LOCATION_ID_OS2,
              'street_address' => $groupsite->street_address,
              'supplemental_address_1' => $groupsite->supplemental_address_1,
              'supplemental_address_2' => '',
              'city' => $groupsite->city,
              'state_province_id' => $groupsite->state_province_id,
              'postal_code' => $groupsite->postal_code,
              'country_id' => $groupsite->country_id,
              'master_id' => ''
              )
            )
          );
				
        if($form_data['studio_bldg_no_2']) {
          $studio_params['address'][1]['supplemental_address_2'] .= 'Bldg ' . $form_data['studio_bldg_no_2'] . ' ';
        }
        if($form_data['studio_no_2']) {
          $studio_params['address'][1]['supplemental_address_2'] .= 'Studio ' . $form_data['studio_no_2'] . ' ';
        }
        
        $contact_params['custom_53'] = $form_data['studio_bldg_no_2'];
        $contact_params['custom_55'] = $form_data['studio_no_2'];
        */
        
        // Get the civi participant record - margot@rd
			$params = array(
    		'contact_id' => $contact['contact_id'],
   			'event_title' => 'SF Open Studios - 2013'
   		 );
  		$participant =& civicrm_participant_search($params);

			foreach ($participant as $part) {
				$participant_id = $part['participant_id'];
			}
			print_r($participant);
			
			// we need this participant id so that we can use it to update the participant record
			
      $contact_params['custom_81'] = $groupsite->street_address;
      $contact_params['custom_77'] = $groupsite->city;
      $contact_params['custom_78'] = $groupsite->state;
      $contact_params['custom_82'] = $groupsite->postal_code;
      $contact_params['version'] = 3;
      $contact_params['event_id'] = 69;
      $contact_params['id'] = $participant_id;
			print_r($contact_params);
      }
      else {
       /*  $studio_params = array(
          'version' => '3.0',
          'contact_id' => $contact['contact_id'],
          'address' => array(
            1 => array(
              'location_type_id' => $STUDIO_LOCATION_ID_OS2,
              'street_address' => $form_data['studio_address_2'],
              'city' => $form_data['studio_city_2'],
              'state_province_id' => $form_data['studio_state_2'],
              'postal_code' => $form_data['studio_zip_2'],
              'country_id' => 1228,
              'master_id' => ''
              )
            )
          );
        $contact_params['custom_53'] = '';
        $contact_params['custom_55'] = '';
        */
      }
			
      /* if($contact['address_os2']['location_type_id'] == $STUDIO_LOCATION_ID_OS2) {
        $response =& civicrm_location_update($studio_params);
      } else {
        $response =& civicrm_location_add($studio_params);
      }
      $response =& civicrm_contact_update($contact_params);
    */
    	$response =& civicrm_api('participant','create', $contact_params);
    	$participant =& civicrm_participant_search($params);
			print_r($participant);

    	exit;
    }
  //}
}


function _artspan_artist_profile_validate($form, &$form_state) {
  drupal_add_js(drupal_get_path('module', 'artspan') . '/studio.js');
  foreach($form['#post'] as $field => $value) {
    if(strstr($field, 'website')) {
      if($value && (!valid_url($value) || !valid_url($value, TRUE))) {
        form_set_error($field, "One of the urls below is not valid.");
      }
    }
  }

  /*if($form['#post']['groupsite_question'] == 1) {
    if(!array_key_exists($form['#post']['group_location_reference'], _artspan_load_civi_group_studios())) {
      form_set_error('group_location_reference', "Please choose a group location.");
    }
  }*/
}

function _artspan_artist_profile_redirect($form, &$form_state) {
  $form_state['redirect'] = 'user/'.$form['uid']['#value'].'/edit/artwork';
}

// updated Civi when the profile is submitted margot@rd
function _artspan_artist_profile_submit($form, &$form_state) {
  $PUBLIC_LOCATION_ID = 7;
  $STUDIO_LOCATION_ID = 6;
  
  $post = $form['#post'];
  $user = _artspan_get_civi_contact($form['#node']->uid);
  $contact_id = $user['contact_id'];

  _artspan_sync_artist_role(user_load($form['#node']->uid), $user);

  $email = $post['email'];
  $phone = $post['phone'];

  // Is a public/studio location already set?
  $public = FALSE;
  $studio = FALSE;
  foreach(array_merge($user['phone'], $user['email']) as $i) {
    if($i['location_type_id'] == $PUBLIC_LOCATION_ID) {
      $public = TRUE;
    }
    if($i['location_type_id'] == $STUDIO_LOCATION_ID) {
      $studio = TRUE;
    }
  }

  // Civicrm Update
  $params = array(
    'version' => '3.0',
    'contact_id' => $contact_id,
    'phone' => array(
      array(
        'phone' => $post['phone'],
        'phone_type_id' => 1,
        'location_type_id' => $PUBLIC_LOCATION_ID
      )
    ),
    'email' => array(
      array(
        'email' => $post['email'],
        'location_type_id' => $PUBLIC_LOCATION_ID
      )
    )
  );

  if($public) {
    $response =& civicrm_location_update($params);
  }
  else {
    $response =& civicrm_location_add($params);
  }

  // Prepare contact params for studio
  $contact_params = array(
      'contact_id' => $contact_id,
      'contact_type' => 'Individual',
      'custom_45' => $post['studio_title'],
      );

  // Is a group site set?
  /*if($post['groupsite_question'] == 1) {
    // Get group site
    $post_group_location_reference = mysql_real_escape_string($post['group_location_reference']);
    $query = "SELECT id, street_address, supplemental_address_1, city, state_province_id, postal_code, country_id FROM civicrm_address WHERE contact_id = $post_group_location_reference AND location_type_id = $STUDIO_LOCATION_ID;";
    $result = db_query($query);
    $groupsite = db_fetch_object($result);
    $studio_params = array(
      'version' => '3.0',
      'contact_id' => $contact_id,
      'address' => array(
        1 => array(
          'location_type_id' => $STUDIO_LOCATION_ID,
          'street_address' => $groupsite->street_address,
          'supplemental_address_1' => $groupsite->supplemental_address_1,
          'city' => $groupsite->city,
          'state_province_id' => $groupsite->state_province_id,
          'postal_code' => $groupsite->postal_code,
          'country_id' => $groupsite->country_id,
          'master_id' => $groupsite->id
          )
        )
      );

    $contact_params['custom_3'] = $post['building_no'];
    $contact_params['custom_4'] = $post['studio_no'];
  }
  else {
    $studio_params = array(
      'version' => '3.0',
      'contact_id' => $contact_id,
      'address' => array(
        1 => array(
          'location_type_id' => $STUDIO_LOCATION_ID,
          'street_address' => $post['studio_address'],
          'city' => $post['studio_city'],
          'state_province_id' => $post['state'],
          'postal_code' => $post['zip'],
          'country_id' => 1228,
          'master_id' => ''
          )
        )
      );
    $contact_params['custom_3'] = '';
    $contact_params['custom_4'] = '';
  }
  if($studio) {
    $response =& civicrm_location_update($studio_params);
  }
  else {
    $response =& civicrm_location_add($studio_params);
  }
  $response =& civicrm_contact_update($contact_params);*/

  // Update the web sites
  foreach($post as $key => $value) {
    $args = explode('_', $key);
    if($args[0] == 'website') {
      $value = mysql_real_escape_string($value);
      if(is_numeric($args[1])) {
        $id = $args[1];
        if($value == '') {
          $query = "DELETE FROM civicrm_website WHERE contact_id='$contact_id' AND id='$id'";
        }
        else {
          $query = "UPDATE civicrm_website SET url='$value' WHERE contact_id='$contact_id' AND id='$id'";
        }
        $result = db_query($query);
      }
      else {
        if($args[1] == "Twitter") {
          $website_type = 4;
        }
        elseif($args[1] == "Facebook") {
          $website_type = 3;
        }
        else {
          $website_type = 1;
        }
        
        if($value != '')
        {
          $query = "INSERT INTO civicrm_website (contact_id, url, website_type_id) VALUES ('$contact_id', '$value', '$website_type');";
          $result = db_query($query);
        }
      }
    }
  }
}

function artspan_custom_form_user_profile_form_alter(&$form) {
  // Turn off autocomplete.
  $form['#attributes'] = array('autocomplete' => 'off');

  /* The first part of this function is borrowed from the ajax username_check module,
   * but now the functionality also works for the user edit page.
   */
  $mode = variable_get('username_check_mode', 'auto');
  _username_check_load_resources($mode);

  if (isset($form['account']) && $form['account']['#type'] == 'fieldset') {
    $form_group = &$form['account'];
  }
  else {
    $form_group = &$form;
  }
  
  if ($mode == 'manual') {
    $form_group['name']['#weight'] = -5;
    $form_group['name']['#prefix'] = '<div id="username-check-wrapper">';
    $form_group['name']['#suffix'] = '</div>';

    $form_group['username_check_button'] = array(
      '#value' => '<input type="button" name="op" id="edit-username-check-button" value="Check availability" class="form-button" style="display: inline-block">',
      '#prefix' => '<div id="username-check-message" class="username-check-message"></div>',
      '#weight' => -4,
    );
  }
  elseif ($mode == 'auto') {
    $module_path = drupal_get_path('module', 'username_check');
    $form_group['name']['#prefix'] = '<div id="username-check-wrapper">';
    $form_group['name']['#suffix'] = '</div><div id="username-check-message"></div><div id="username-check-informer" class="username-check-informer">&nbsp;</div>';
  }

  /* Now, we want to add the civicrm personal information profile to this form. Fun! */
  $curr_user = $form['_account']['#value'];
  $civi_user = _artspan_get_civi_contact($curr_user->uid);

  $form['_civi_contact'] = array(
    '#type' => 'value',
    '#value' => $civi_user
  );

  $form['personal'] = array(
    '#type' => 'fieldset',
    '#title' => 'Personal Information',
    '#weight' => -20,
    'profile' => array(
      '#value' => civicrm_uf_profile_html_get($civi_user['contact_id'], 'Personal Information')
    )
  );

  $form['demographics'] = array(
    '#type' => 'fieldset',
    '#title' => 'Demographic Information (Optional)',
    '#weight' => -19,
    'profile' => array(
      '#value' => civicrm_uf_profile_html_get($civi_user['contact_id'], 'Demographics (Optional)')
    )
  );
}

function artspan_custom_user($op, &$edit, $account, $category = NULL) {
  if($op == 'after_update' && $category == 'account') {
    _artspan_sync_artist_role($account, $account->_civi_contact);
  }
}  



// Prepopulate artist on new artwork form field
function artspan_custom_form_editview_node_form_new_alter(&$form) {
  $form['field_artist'][0]['#default_value']['uid'] = arg(1);
}

// What the hell does this function actually do?  It seems to be resonsible for removing the feilds that should be in the civi record
function artspan_custom_nodeapi(&$node, $op, $a3 = NULL, $a4 = NULL) {
  // If we're on the edit artwork page, make sure node's artist and uid are set.
  if($op == 'presave' && $node->type == 'artwork' && arg(0) == 'user' && arg(3) == 'artwork') {
    $user = user_load(arg(1));
    $node->uid = arg(1);
    $node->name = $user->name;
    $node->field_artist[0]['uid'] = arg(1);
    $node->title = $node->name . ' - ' . $node->field_content_title[0]['value'];
  }

  if($op == 'insert' && $node->type == 'artwork') {
    // If the current user has no thumbnail art, make this his thumbnail piece
    $profile = content_profile_load('artist', $node->uid);
    if($profile->field_ref_thumbnail_art[0]['nid'] == NULL) {
      $profile->field_ref_thumbail_art[0]['nid'] = $node->nid;
      $query = "UPDATE content_type_artist SET field_ref_thumbnail_art_nid = '{$node->nid}' WHERE nid='{$profile->nid}';";
      $result = db_query($query);
    }
  }

  if(($op == 'update' || $op == 'insert') && $node->type == 'artist') {

    function get_name($id) {
      $term = taxonomy_get_term($id);
      return $term->name;
    }

    function get_weekend_no($id) {
      $parents = taxonomy_get_parents($id);
      foreach($parents as $getparent) {
        $parent = $getparent;
      }
      return $parent->name;
    }

    _artspan_load_civi_api();
    $civi_uid = civicrm_uf_match_id_get($node->uid);
    $params = array(
      'contact_id' => $civi_uid,
      'event_title' => 'SF Open Studios - 2011'
      );

    $participant = civicrm_participant_search($params);

    if(count($participant) > 0) {
      $keys = array_keys($participant);
      $id = $keys[0];
      $participant = $participant[$id];
      $mediums = chr(1);
      foreach($node->field_mediums_list as $tid) {
        switch($tid['value']) {
          case 1:
            $mediums .= "book-arts" . chr(1);
            break;
          case 2:
            $mediums .= "ceramics" . chr(1);
            break;
          case 3:
            $mediums .= "drawing" . chr(1);
            break;
          case 4:
            $mediums .= "fiber" . chr(1);
            break;
          case 5:
            $mediums .= "glass" . chr(1);
            break;
          case 6:
            $mediums .= "installation" . chr(1);
            break;
          case 7:
            $mediums .= "mixed-media" . chr(1);
            break;
          case 8:
            $mediums .= "painting" . chr(1);
            break;
          case 9:
            $mediums .= "photography" . chr(1);
            break;
          case 10:
            $mediums .= "printmaking" . chr(1);
            break;
          case 11:
            $mediums .= "sculpture" . chr(1);
            break;
          case 12:
            $mediums .= "wearable" . chr(1);
            break;
        }
      }
      if($mediums == chr(1)) {
        $mediums = NULL;
      }
      $params = array(
        'id' => $id,
        'custom_10' => $node->field_content_title[0]['value'],
        //'custom_21' => $mediums,
        'custom_62' => get_name($node->field_primary_medium[0]['value']),
        'custom_63' => get_weekend_no($node->field_sf_open_studios_wk1[0]['value']),
        'custom_64' => get_weekend_no($node->field_sf_open_studios_wk2[0]['value']),
        'custom_65' => get_name($node->field_sf_open_studios_wk1[0]['value']),
        'custom_66' => get_name($node->field_sf_open_studios_wk2[0]['value'])
      );

      $query = "UPDATE civicrm_value_sf_open_studios_artist_info_4 SET " .
        "publishable_artist_name_10 = '" . db_escape_string($node->field_content_title[0]['value']) . "', " .
        //"guide_mediums_21 = '" . $mediums . "', " .
        "guide_primary_medium_62 = '" . db_escape_string(get_name($node->field_primary_medium[0]['value'])) . "', " .
        "first_os_weekend___63 = '" . db_escape_string(get_weekend_no($node->field_sf_open_studios_wk1[0]['value'])) . "', " .
        "second_os_weekend___64 = '" . db_escape_string(get_weekend_no($node->field_sf_open_studios_wk2[0]['value'])) . "', " .
        "first_os_neighborhood_65 = '" . db_escape_string(get_name($node->field_sf_open_studios_wk1[0]['value'])) . "', " .
        "second_os_neighborhood_66 = '" . db_escape_string(get_name($node->field_sf_open_studios_wk2[0]['value'])) . "' " .
        "WHERE entity_id = '" . $id . "';";

      //db_query($query);
    }

  }
}

function artspan_custom_views_data_alter(&$data) {

  $data['civicrm_contact']['first_name'] = array(
  'title' => t('Last Name'),
  'help' => t('Last Name'),
  'field' => array(
  'handler' => 'civicrm_handler_field_contact_link',
  'click sortable' => TRUE,
  ),
  'argument' => array(
  'handler' => 'views_handler_argument_string',
  ),
  'filter' => array(
  'handler' => 'views_handler_filter_string',
  'allow empty' => TRUE,
  ),
  'sort' => array(
  'handler' => 'views_handler_sort',
  ),
  );
//DISPLAY Name for the Contact (Full Name with Prefixes and Suffixes)
// see civicrm.views.inc around line 238
    $data['civicrm_contact']['display_name'] = array(
        'title' => t('Display Name'),
        'help' => t('Full Name of the Contact with prefixes and suffixes'),
      'field' => array(
         'handler' => 'civicrm_handler_field_contact_link',
         'click sortable' => TRUE,
        ),
        'argument' => array(
         'handler' => 'views_handler_argument_string'
      ),
      'filter' => array(
         'handler' => 'views_handler_filter_string',
         'allow empty' => TRUE,
      ),
      'sort' => array(
         'handler' => 'views_handler_sort',
        )
   );
}
