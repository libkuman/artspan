<?php

class OpenStudios extends Model {

  private $neighborhoods = array(
    "SOMArts Cultural Center" => 0,
    "Fort Mason" => 1,
    "Marina" => 1,
    "Pacific Heights" => 1,
    "Russian Hill" => 1,
    "North Beach" => 1,
    "Hayes Valley" => 1,
    "Haight" => 1,
    "Buena Vista" => 1,
    "Sunset" => 1,
    "Richmond" => 1,
    "Mission" => 2,
    "Castro" => 2,
    "Bernal Heights" => 2,
    "Noe Valley including Upper Market" => 2,
    "Upper Market" => 2,
    "Noe Valley" => 2,
    "West Portal" => 2,
    "Glen Park" => 2,
    "SOMA" => 3,
    "Tenderloin" => 3,
    "Potrero Hill" => 3,
    "Dogpatch" => 3,
    "Bayview" => 3,
    "Portola" => 3,
    "Excelsior" => 3,
    "Hunters Point Shipyard" => 4,
    "Islais Creek Studios" => 4 
  );
  private $weekends = array(
    0 => array(
      'start' => '2013-10-12', 
      'end' => '2013-10-13'
    ),
    1 => array('start' => '2013-10-19 11:00:00', 'end' => '2013-10-20 18:00:00'),
    2 => array('start' => '2013-10-26 11:00:00', 'end' => '2013-10-27 18:00:00'),
    3 => array('start' => '2013-11-02 11:00:00', 'end' => '2013-11-03 18:00:00'),
    4 => array('start' => '2013-11-09 11:00:00', 'end' => '2013-11-19 18:00:00'),
  );
  private $subtitles = array(
    0 => 'October 12 & 13 - Visit the SF Open Studio Exhibition at SOMArts Cultural Center!',
    1 => 'October 19 & 20, 11am to 6pm',
    2 => 'October 26 & 27, 11am to 6pm',
    3 => 'November 2 & 3, 11am to 6pm',
    4 => 'November 9 & 10, 11am to 6pm'
  );
  private $weekendNames = array(
    0 => 'Preview Weekend',
    1 => 'Weekend 1',
    2 => 'Weekend 2',
    3 => 'Weekend 3',
    4 => 'Weekend 4'
  );
  private $singleAccess = false;

  public function __construct($tokenizer) {
    if(get_class($tokenizer) == 'Tokenizer') {
      $token = $tokenizer->popToken();
      $id = $token[get_class($this)];
    } else {
      $id = $this->lookUpWeekend($tokenizer['neighborhood']);
    }

    if(isset($id) && ($id != '')) {
      $this->singleAccess = true;
      $listing = $this->createListing($id);
    } else {
      $listing = array();
      foreach(array_keys($this->weekends) as $weekendNumber) {
        $listing[$weekendNumber] = $this->createListing($weekendNumber);
      }
    }
    $this->data = $listing;
  }

  public function __toString() {
    return '{"' . get_class($this) . '":[{' . substr(parent::__toString(), 2) . '}';
  }

  private function createListing($weekendNumber) {
    $listing = array(
      'OpenStudioID' => $weekendNumber,
      'Name' => $this->weekendNames[$weekendNumber],
      'StartDate' => date(DATE_FORMAT, strtotime($this->weekends[$weekendNumber]['start'])),
      'EndDate' => date(DATE_FORMAT, strtotime($this->weekends[$weekendNumber]['end'])),
      'Subtitle' => $this->subtitles[$weekendNumber],
      'Neighborhoods' => $this->getNeighborhoods($weekendNumber)
    );
    return $listing;
  }

  private function getNeighborhoods($weekendNumber) {
    $neighborhoods = array();
    foreach($this->neighborhoods as $neighborhood => $weekend) {
      if($weekendNumber == $weekend) {
        $neighborhoods[] = $neighborhood; 
      }
    }
    if(!empty($neighborhoods)) {
      return $neighborhoods;
    }
    return null;
  }

  public function lookUpWeekend($neighborhood) {
    return $this->neighborhoods[$neighborhood];
  }

}

?>
