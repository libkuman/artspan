<?php

class Phone extends Website {

  protected $elements = array('phone' => 'phone');

  protected function processArgs($args) {
    $id = parent::processArgs($args);
    $this->queryString .= '&location_type_id=7';
    return $id;
  }

}

?>
