<?php

class Events extends OpenStudios {

  public function __construct($tokenizer) {
    $listing = array();
    $db = new Database();
    $token = $tokenizer->popToken();
    if($token['Events'] == null) {
      $events = $db->getManyRecords("SELECT `vid`,`title` FROM `node` WHERE `type` = 'event';");
    } elseif (is_numeric($token['Events'])) {
      $events = array($db->getSingleRecord("SELECT `vid`,`title` FROM `node` WHERE `type` = 'event' AND `vid` = '" . $token['Events'] . "' LIMIT 1;"));
    }
    foreach($events as $nodeData) {
      $viewData = $db->getSingleRecord('SELECT `field_date_value`, `field_date_value2`, `field_event_link_url`, `field_event_contact_email_value`, `field_event_location_lid` FROM `content_type_event` WHERE `vid` = "' . $nodeData[0] . '";');
      if($viewData) {
        $fid = $db->getSingle('SELECT `field_image_fid` FROM `content_field_image` WHERE `vid` = "' . $nodeData[0] . '" LIMIT 1;');
        $image = $db->getSingle('SELECT `filepath` FROM `files` WHERE `fid` = "' . $fid . '" LIMIT 1;');
        $location = $db->getSingleRecord('SELECT `name`, `street`, `additional`, `city`, `province`, `postal_code`, `country` FROM `location` WHERE `lid` = ' . $viewData[4] . ';');
        if(!is_array($location)) {
          $location = array($location);
        }
        $description = $db->getSingle('SELECT `field_body_value` FROM `content_field_body` WHERE `vid` = ' . $nodeData[0] . ';');
        $listing[$nodeData[0]] = $this->dropBlanks(array(
          'event_id' => $nodeData[0],
          'name' => $this->escapeHTML($nodeData[1]),
          'start_date' => date(DATE_FORMAT, strtotime($viewData[0])),
          'end_date' => date(DATE_FORMAT, strtotime($viewData[1])),
          'image_url' => FILE_PATH . $image,
          'address' => implode(',', $this->dropBlanks($location)),
          'website' => $viewData[2],
          'contact' => $viewData[3],
          'description' => '<html><body>' . $this->escapeHTML(strip_tags(str_replace('</p><p>', '\\n\\n', $description))) . '<\/body><\/html>'
        ));
        if($image == '') {
          preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $description, $matches);
          if(isset($matches[1])) {
            $listing[$nodeData[0]]['image_url'] = $matches[1];
            if($matches[1][0] == '/') {
              $listing[$nodeData[0]]['image_url'] = FILE_PATH . substr($matches[1], 1);
            }
          } else {
            unset($listing[$nodeData[0]]['image_url']);
          }
        }
      }
    }
    $db->close();

    $this->data = $listing;
  }

}

?>
