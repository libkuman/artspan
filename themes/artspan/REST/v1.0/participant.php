<?php

class Participant extends Model {

  private $drupalID;
  protected $elements = array(
    'artist_id' => 'contact_id',
    'email' => 'Email', 
    'name' => 'display_name', 
    'phone' => 'Phone', 
    'open_studios' => 'display_name', //placeholder 
    'open_studios2' => 'display_name', //placeholder 
    //'statement' => 'Statement', 
    'website' => 'Website', 
    'artwork' => 'Artwork',
    'artist_photo' => 'ArtistPhoto',
    'neighborhood' => 'custom_94',
    'group_studio_id' => 'custom_83_id',
    'studio_name' => 'custom_83',
    'studio_building' => 'custom_72',
    'studio_number' => 'custom_73',
    'studio_address' => 'custom_25',
    'geocode' => 'Geocode',
    'neighborhood2' => 'custom_96',
    'group_studio_id2' => 'custom_84_id',
    'studio_name2' => 'custom_84',
    'studio_building2' => 'custom_79',
    'studio_number2' => 'custom_80',
    'studio_address2' => 'custom_81',
    'geocode2' => 'Geocode'
  );
  private $groupStudioElements = array(
    'group_studio_id',
    'studio_name',
    'studio_building',
    'studio_number',
    'studio_address',
    'neighborhood',
    'geocode'
  );

  public function __construct($tokenizer) {
    parent::__construct($tokenizer);
    $this->getNeighborhood(); 
    $this->getNeighborhood(2);
    $this->getGroupStudio();
    $this->getGroupStudio(2);
    $this->combineGroupStudios();
  }

  private function combineGroupStudios() {
    $data = $this->data;
    if(isset($data['open_studios']) && isset($data['open_studios2'])) {
      $data['open_studios'] = substr($data['open_studios'], 0, -1) . ',' . substr($data['open_studios2'], 1);
      unset($data['open_studios2']);
    }
    $this->data = $data;
  }

  private function getGroupStudio($which = '') {
    $data = $this->data;
    if(isset($data['neighborhood' . $which])) {
      $groupStudio = new GroupStudio(array('which' => $which, 'caller' => $this));
      foreach($this->groupStudioElements as $element) {
        $elementName = $element . $which;
        unset($data[$elementName]);
      }
      $data['open_studios' . $which] = (string)$groupStudio;
    }
    $this->data = $data;
  }

  public function getGroupStudioElements() {
    return $this->groupStudioElements;
  }

  public function getNeighborhood($which = '') {
    $data = $this->data;
    $extraNumber = $this->get('civicrm_value_sf_open_studios_artist_info_4_id');
    $dataItem = $this->get($this->elements['neighborhood' . $which] . '_' . $extraNumber);
    if(isset($dataItem) && ($dataItem !== false) && ($dataItem != '')) {
      $data['neighborhood' . $which] = $dataItem;
    }
    $this->data = $data;
  }

  public function getDrupalID() {
    if(!isset($this->drupalID)) {
      $this->db = new Database();
      $this->drupalID = $this->db->getDrupalId($this->get('contact_id'));  
      $this->db->close();
    }
    return $this->drupalID;
  }

}

?>
