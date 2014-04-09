<?php

class Website extends Model {

  protected $elements = array('url' => 'url');

  protected function processArgs($args) {
    if(!is_array($args) && (get_class($args) == 'Tokenizer')) {
      return parent::processArgs($args);
    } else {
      $this->queryString = '&contact_id=' . $args['contactID'];
      return $args['contactID'];
    }
  }

  public function __toString() {
    $data = $this->data;
    if(!is_array($data)) {
      $data = array($data);
    }

    if(!empty($data)) {
      return '["' . implode('","', $data) . '"]';
    }
    return null;
  }

}

?>
