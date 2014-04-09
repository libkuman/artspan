<?php

class Geocode extends Website {

  public function __construct($address) {
    $this->data = $this->geoseeker($address);
  }

  protected function geoseeker($address) {
    unset($address[0]);
    $address = implode(',', $address);
    $address = str_replace(' ', '+', $address);
    $address = str_replace(' ', '+', $address);

    $data = file_get_contents(GEOCODE_API_LOCATION . '?address=' . $address . '&sensor=true'
    );
    $data = json_decode($data, true);
    $this->rawData = $data;
    if(!isset($data['results'][0]['geometry']['location'])) {
      return null;
    }
    return $data['results'][0]['geometry']['location'];
  }

}

?>
