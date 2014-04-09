<?php

class Database {

  private $db;
  private $data;

  public function __construct() {
    $db = new mysqli(DATABASE_URL, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
    $db->set_charset('utf8');
    $this->db = $db;
  }

  private function arrange($data) {
    $info = array();
    foreach($data as $key => $value) {
      $keyArray = explode('_', $key);
      $numAlready = false;
      foreach($keyArray as $innerKey => $keyWord) {
        if(is_numeric($keyWord)) {
          if($numAlready) {
            $keyWord = '';
          } else {
            $numAlready = true;
          }
        }
        $keyArray[$innerKey] = ucwords($keyWord);
      }
      $info[$entryNum][implode($keyArray)] = $value;
    }

    return array_shift($info);
  }

  public function close() {
    $this->db->close();
  }

  public function get($dataItem) {
    $data = $this->data;
    return $data[$dataItem]; 
  }

  public function getDrupalId($contactID) {
    $drupalID = $this->getSingle("SELECT `uid` FROM `users` WHERE `data` LIKE '%\"contact_id\";s:" . strlen($contactID) . ":\"" . $contactID . "\";%' LIMIT 1;");
    if(is_numeric($drupalID)) {
      return $drupalID;
    }
    return false;
  }

  public function getMany($query) {
    $result = $this->db->query($query);
    $array = array();
    if($result !== false) {
      while($row = $result->fetch_row()) {
        $array[] = array_shift($row);
      }
    } else {
      return false;
    }
    return $array;
  }

  public function getManyRecords($query) {
    $result = $this->db->query($query);
    $array = array();
    if($result !== false) {
      while($row = $result->fetch_row()) {
        $array[] = $row;
      }
    } else {
      return false;
    }
    return $array;
  }

  public function getSingle($query) {
    $result = $this->db->query($query);
    if($result !== false) {
      while($row = $result->fetch_row()) {
        $row = array_shift($row);
        if($row != '') {
          return $row;
        }
      }
      return false;
    } else {
      return false;
    }
  }

  public function getSingleRecord($query) {
    $result = $this->db->query($query);
    if($result !== false) {
      while($row = $result->fetch_row()) {
        $row = $row;
        if(($row != '') && !empty($row)) {
          return $row;
        }
      }
      return false;
    } else {
      return false;
    }
  }

  public function getArtwork($artwork) {
    $artwork = array();

    //DIGGING UP ART
    $query = 'SELECT `vid` FROM `node` WHERE `uid` = ' . $drupalID . ' AND type = "artwork`" LIMIT 1;';
    $result = $this->db->query($query);
    $idNum = $result->fetch_row();
    $idNum = array_shift($idNum);
    $query = 'SELECT `field_artist_statement_value` FROM `content_type_artist` WHERE `vid` = "' . $idNum . '" LIMIT 1;';
    $result = $this->db->query($query);
    while($row = $result->fetch_row()) {
      $artwork[] = array_shift($row);
    }

    $this->close();
  }

  private function processDrupalData($metadata) {
    $splode = explode('{', str_replace('}', '', $metadata));

    $data = array();
    foreach($splode as $key => $value) {
      $splode[$key] = explode('"', $value);
      if(count($splode[$key]) < 4) {
        unset($splode[$key]);
        continue;
      }
      while((count($splode[$key]) % 4) !=  0) {
        array_pop($splode[$key]);    
      }
      $data = array_merge($data, $splode[$key]);
    }

    $finalData = array();
    for($i = 0; $i < count($data); $i += 4) {
      if(!isset($data[($i + 3)])) {
        continue;
      }
      $finalData[$data[($i + 1)]] = $data[($i + 3)];
    }

    $this->data = $this->arrange($finalData);
    $data = array();
    foreach($splode as $key => $value) {
      $splode[$key] = explode('"', $value);
      if(count($splode[$key]) < 4) {
        unset($splode[$key]);
        continue;
      }
      while((count($splode[$key]) % 4) !=  0) {
        array_pop($splode[$key]);    
      }
      $data = array_merge($data, $splode[$key]);
    }

    $finalData = array();
    for($i = 0; $i < count($data); $i += 4) {
      if(!isset($data[($i + 3)])) {
        continue;
      }
      $finalData[$data[($i + 1)]] = $data[($i + 3)];
    }

    $this->data = $this->arrange($finalData);
  }
}

?>
