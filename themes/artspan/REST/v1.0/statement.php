<?php

class Statement extends Model {
  protected $useDB = true;

  public function __construct($info) {
    $db = new Database();
    $this->setEntity();
    if(is_array($info)) {
      $drupalID = $info['caller']->getDrupalID();
      if($drupalID !== false) {
        $idNum = $db->getSingle('SELECT `vid` FROM `node` WHERE `uid` = ' . $drupalID . ' AND type = "artist" LIMIT 1;');
      }
    } else {
      $idNum = $this->processArgs($info);
    }

    if(isset($idNum)) {
      $query = 'SELECT `field_artist_statement_value` FROM `content_type_artist` WHERE `vid` = "' . $idNum . '" LIMIT 1;';
      $statement = $db->getSingle($query);
      $this->data = '';
      if($statement !== false) {
        $this->data = '<html>' . strip_tags($statement) . '<\/html>';
      }
    } else {
      $this->data = null;
    }

    $db->close();
  }

  public function __toString() {
    return $this->escapeHTML($this->data);
  }

}

?>
