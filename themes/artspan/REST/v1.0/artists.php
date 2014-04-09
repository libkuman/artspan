<?php

class Artists extends Model {

  private $groupID = GROUP_ID;
  private $eventID = EVENT_ID;
  protected $elements = array(
    'contact_id' => 'participant_id'
  );

  public function __construct($tokenizer) {
    $limit = ARTISTS_LIMIT;
    if($limit == -1) {
      $limit = 'all';
    }
    trigger_error('Artists API generation started. Gathering ' . $limit . ' artist contacts.');
    $this->specialTokens($tokenizer);
    parent::__construct($tokenizer);
    $participantIDs = $this->data;
    if(!is_array($participantIDs)) {
      $participantIDs = array($participantIDs => '');
    }
    $artists = array();
    $count = 0;
    trigger_error('Contacts acquired. Gathering data.');
    foreach($participantIDs as $id => $value) {
      $count++;
      trigger_error($count . ': Contact# ' . $id . ' is being generated.');
      $fakeToken = new Tokenizer();
      $fakeToken->fake('Participant', $value);
      $artists[$id] = new Participant($fakeToken);
      if($artists[$id]->getDrupalID() !== false) {
        $artists[$id] = (string)$artists[$id];
      } else {
        unset($artists[$id]);
      }
    }
    trigger_error('Processing complete.');
    $this->data = $artists;
  }

  public function __toString() {
    return '{"' . get_class($this) . '": [' . implode(',', $this->data) . ']}';
  }

  protected function seeker() {
    if(!$this->eventID) {
      $eventQuery = '';
    } else {
      $eventQuery = '&event_id=' . $this->eventID;
    }
    if(!$this->groupID) {
      $groupQuery = '';
    } else {
      $groupQuery = '&group=' . $this->groupID;
    }
    $data = file_get_contents(API_LOCATION . '?'. API_AUTH .'json=1&action=get&rowCount=' . ARTISTS_LIMIT . '&entity=Contact&contact_sub_type=Artist' . $eventQuery . $groupQuery);
    $data = json_decode($data, true);
    $this->rawData = $data;
    return $data['values'];
  }

  private function specialTokens($tokenizer) {
    $topToken = $tokenizer->peekToken(); 
    if(isset($topToken['Artists']) && ($topToken['Artists'] != '')) {
      $args = explode('-',$topToken['Artists']);
      $this->groupID = $args[0];
      $this->eventID = $args[1];
      if(isset($args[1]) || ($args[1] != '') || !is_numeric($args[1])) {
        $this->eventID = false;
      }
      if(isset($args[0]) || ($args[0] != '') || !is_numeric($args[0])) {
        $this->groupID = false;
      }
    }
  }

}

?>
