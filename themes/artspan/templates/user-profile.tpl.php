<?php
// This file contains all the displays for the artist profile, all field id's need to be updated and checked. margot@rd 

// Display Function for showing weekend info (what the fuck is this doing here?) margot@rd 
 function display_openstudios_weekend($number, $neigh, $weekend, $studio, $contact) {
    print "<div class='weekend_$number'>";
    print '<a href="/sfopenstudios">' . $weekend . '</a><br /> <strong>Neighborhood: ' . $neigh . '</strong>';
    print '<div class="address">';
    print $studio;
    print '</div>';
    print '</div>';
  }


  // get the civic contact info for this display based on the arg 
  $contact = _artspan_get_civi_contact_public_location_info(arg(1));

  // get the node for this display
  $node = content_profile_load('artist', arg(1));
  
  // Check to see if this user is registered for the event 
  //-- see artspan_custom.module margot@rd
  // Get their Event info from civi 
  $openstudios = _artspan_get_openstudios_information($contact['contact_id']);

  // Check to see if they are attending margot@rd  
  //** SFOSUPDATE ** change this id to be the event id
  if($openstudios['event_id'] == ARTSPAN_NEXT_OPENSTUDIOS_CIVICRM_EVENT_ID) {
    $participant = true;
  }

  // Load up some variables for display 
  
  // if the profile node has a title use it, if not then use the record from civi 
  if (isset($node->field_content_title[0]['value'])) {
    $title = $node->field_content_title[0]['value'];
  } else { 
    $title = $contact['display_name'];
  }
  $phone = $contact['public_phone'];
  //$email = $openstudios['custom_29'];
  $email = $contact['public_email'];
  $statement = content_format('field_artist_statement', $node->field_artist_statement[0],'default', $node) ;
	$image = $node->field_image[0];

  $websites = _artspan_get_artist_websites($contact['contact_id']);

  $artist_sites = array();
  $uniq_artist_sites = array();

  foreach($websites as $type => $type_websites) {
    foreach($type_websites as $id=>$url) {
      if($type == 'twitter') {
	$twitter = $url;
	continue;
      }
      else if($type == 'facebook') {
	$facebook = $url;
	continue;
      }
      else {
	$artist_sites[$id] = $url;
      }
    }
  }

  // Check to see if there are no websites, if none set false
  if(count($artist_sites) == 0) {
    $websites = false;
  }


  // Debug used to print available info to screen margot@rd 
/*	if ($user->uid == 3720) {
  print_r($openstudios);
	print_r($contact);
	}*/
	
 ?>
 
<div class="header-area">
  <h1><?php print $title; ?></h1>
  <?php // check the address field in Civi info for value 
  	if($participant): ?>
    <div class="sf-open-studios">SF Open Studios</div>

  <?php endif; ?>

  <div class="header-links">
    <div class="backlink">
      <?php print l('Back to Artists', 'artists'); ?>
    </div>
  </div>
</div>

<?php // if any of these items exists create the left hand profile block margot@rd 
	if($image || $websites || $phone || $email || $statement || $twitter || $facebook): ?>
  <div class="profile">
    <?php if($image): ?>
      <div class="avatar">
        <?php print theme('imagecache', 'profile_photo', $image['filepath'], 'Picture of ' . $title); ?>
      </div>
    <?php endif; ?>
    <?php if($phone || $email || $websites): ?>
      <div class="contact_area">
        <h3>Contact</h3>
        <?php if($websites): ?>
          <ul>
            <?php $unique = array();
              foreach($artist_sites as $id=>$url) {
                 if (isset($unique[$id])) {
                   continue;
                 } 
                 $unique[$id] = $site;
            ?>
              <li><a href="<?php print $url; ?>" target="_blank">
                <?php print $url; ?></a>
            <?php } ?>
          </ul>
        <?php endif; ?>
        <?php if($phone): ?><div class="phone-number"><?php print $phone; ?></div><?php endif; ?>
        <?php if($email): ?><div class="email-address"><a href="mailto:<?php print $email; ?>"><?php print $email; ?></a></div><?php endif; ?>
      </div>
    <?php endif; ?>

    <?php  // If they are a participant lets show the weekends they are registered for margot@rd 
    	if ($participant): ?>
      <div class="sfos_area">
        <?php // Check what Neighborhood they in for First Weekend and then show it here ** SFOSUPDATE ** change this to be the custom feild for the new neighborhood select
        if (!empty($openstudios[ARTSPAN_FIRST_CHOICE_WEEKEND_CF])) {
        	print '<h3>SF Open Studios</h3>';			
          $term1 = $openstudios[ARTSPAN_FIRST_CHOICE_WEEKEND_CF];
          $term1_id = taxonomy_get_term_by_name($openstudios[ARTSPAN_FIRST_CHOICE_WEEKEND_CF]);
          
          foreach($term1_id as $term1id) {
          
            if ($term1id->vid == 5) {
              $tname1 = $term1id->tid;
            }
          }
          
          $parent1 = array_pop(taxonomy_get_parents($tname1));
          
          if ($openstudios['custom_83']) {
	    $gn = $openstudios['custom_83'];
	    $groupLocation = _artspan_get_group_studio_site_contact($gn);
	    $studio1 = $groupLocation['display_name'] . 
	      '<br />' .$groupLocation['street_address'] . '<br />';
	    
            if ($openstudios['custom_73']) {
              $studio1 .= 'Building: ' . $openstudios['custom_73'];
            }
            if ($openstudios['custom_74']) {
              $studio1 .= ' Studio: ' . $openstudios['custom_74'] . '<br />';
            }
            $studio1 .= $groupLocation['city'] . ', ' . 
	      $groupLocation['state_province'] . ' '  . 
	      $groupLocation['postal_code']   .   '<br />';
          } 
	  else {
	    $studio1 = $openstudios['custom_25'] . ', CA. ' . 
	    $openstudios['custom_28'];

          }
          $weekend1 = intval(array_pop(explode(' ', trim(array_shift(explode('-', $parent1->name))))));
          
          // Check what Neighborhood they in for Second Weekend and then show it here ** SFOSUPDATE ** change this to be the custom feild for the new neighborhood select

          if (!empty($openstudios[ARTSPAN_SECOND_CHOICE_WEEKEND_CF])) {
            $term2 = $openstudios[ARTSPAN_SECOND_CHOICE_WEEKEND_CF];
            $term2_id = taxonomy_get_term_by_name($openstudios[ARTSPAN_SECOND_CHOICE_WEEKEND_CF]);
            foreach($term2_id as $term2id) {
              if ($term2id->vid == 5) {
                $tname2 = $term2id->tid;
              }
            }
            $parent2 = array_pop(taxonomy_get_parents($tname2));
            if ($openstudios['custom_84']) {
	      $gn = $openstudios['custom_84'];
	      $groupLocation = _artspan_get_group_studio_site_contact($gn);
	      $studio2 = $groupLocation['display_name'] . '<br />' . 
		$groupLocation['street_address'] . '<br />';

              if ($openstudios['custom_73']) {
                $studio2 .= 'Building: ' . $openstudios['custom_79'];
              }
              if ($openstudios['custom_73']) {
                $studio2 .= ' Studio: ' . $openstudios['custom_80'] . '<br />';
              }
              $studio2 .= $groupLocation['city'] . ', ' . 
		$groupLocation['state_province'] . ' '  . 
		$groupLocation['postal_code']   . '<br />';
            } 
	    else {
	      $studio2 = $openstudios['custom_81'] . '<br />' . 
		$openstudios['custom_77'] . ', CA. ' . 
		$openstudios['custom_82'];
            }
            $weekend2 = intval(array_pop(explode(' ', 
	      trim(array_shift(explode('-', $parent2->name))))));
            if($weekend1 < $weekend2) {
              display_openstudios_weekend('1', $term1, 
					  $parent1->name, $studio1, $contact);
              display_openstudios_weekend('2', $term2, 
					  $parent2->name, $studio2, $contact);
            }
            else {
              display_openstudios_weekend('1', $term1, $parent1->name, 
					  $studio1, $contact);
              display_openstudios_weekend('2', $term2, $parent2->name, 
					  $studio2, $contact);
            }
          }
          else {
              display_openstudios_weekend('1', $term1, $parent1->name, 
					  $studio1, $contact);
          }
        }
        ?>
      </div>
    <?php endif; ?>
    <?php if(false)://($contact['address']['display']): ?>
      <div class="studio_area">
        <h3>Studio Address</h3>
        <?php print $contact['address']['display']; ?></p>
      </div>
    <?php endif; ?>
    <?php if($statement): ?>
      <div class="statement_area">
        <h3>Artist Statement</h3>
        <?php print $statement; ?>
      </div>
    <?php endif; ?>
    <?php if($twitter || $facebook): ?>
      <div class="social_area">
        <?php if($twitter): ?>
          <a href="<?php echo $twitter; ?>" class="twitter-link" target="_blank"><img src="<?php echo base_path() . path_to_theme(); ?>/images/twitter_profile_button.gif" alt="View this artist's Twitter account"></a>
        <?php endif; ?>
        <?php if($facebook): ?>
          <a href="<?php echo $facebook; ?>" class="facebook-link" target="_blank"><img src="<?php echo base_path() . path_to_theme(); ?>/images/facebook_profile_button.gif" alt="View this artist's Facebook account"></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<div class="artwork">
  <?php print views_embed_view('artwork', 'block_1', arg(1)); ?>
</div>
<?php //print_r($node); ?>
