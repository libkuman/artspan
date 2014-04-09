<?php

class GroupStudio extends Model {

  private $addressParts = array(
    1 => array('custom_24', 'custom_25', 'custom_71', 'custom_72', 'custom_28'),
    2 => array('custom_75', 'custom_81' , 'custom_77', 'custom_78', 'custom_82')
  );
  private $CAcode = '1004';

  public function __construct($args) {
    $data = array();
    $caller = $args['caller'];
    $callerData = $caller->getData();
    $elements = $caller->getElements();
    $extraNumber = $caller->get('civicrm_value_sf_open_studios_artist_info_4_id');
    foreach($caller->getGroupStudioElements() as $element) {
      $elementName = $element . $args['which'];
      if(strpos($element, 'address') !== false) {
        if(isset($this->addressParts[$args['which']])) {
          $addressVar = $this->addressParts[$args['which']];
        } else {
          $addressVar = array_shift($this->addressParts);
        }
        $itemToGet = array();
        foreach($addressVar as $key => $item) {
          $itemToGet[] = $item . '_' . $extraNumber;
        }
      } elseif(strpos($element, 'neighborhood')) {
        $data[$element] = $callerData[$elementName];
      } elseif(isset($elements[$elementName]) != null) {
        $itemToGet = $elements[$elementName];
      } else {
        $itemToGet = $elements[$elementName] . '_' . $extraNumber;
      }
      if(isset($itemToGet)) {
        if(!is_array($itemToGet)) {
          $itemToGet = array($itemToGet);
        }
        $data[$element] = array();
        foreach($itemToGet as $part) {
          $data[$element][] = $caller->get($part);
        }
        $replace_key = array_search($this->CAcode, $data[$element]);
        if($replace_key !== false) {
          $data[$element][$replace_key] = 'CA';
        }
        $data[$element] = implode(',', $this->dropBlanks($data[$element]));
        if($element == 'studio_address') {
          $data[$element] = $data[$element];
        }
      }
    }

    $geocode = new Geocode(explode(',', $data['studio_address']));
    $data['geocode'] = (string)$geocode;
    if($data['geocode'] == '[""]') {
      unset($data['geocode']);
    }
    $this->data = $data;
  }

  public function __toString() {
    $data = array();
    foreach($this->data as $key => $value) {
      if($key == 'geocode') {
        $data[] = '"' . $this->makeCamelCase($key) . '":' . $value;
      } elseif($value != '') {
        $data[] = '"' . $this->makeCamelCase($key) . '":"' . $this->escapeHTML($value) . '"';
      }
    }

    return '[{' . implode(',', $data) . '}]'; 
  }

}

?>
