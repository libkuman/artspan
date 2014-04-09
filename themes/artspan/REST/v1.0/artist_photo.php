<?php

class ArtistPhoto extends Model {
  protected $useDB = true;

  public function __construct($info) {
    $db = new Database();
    $this->setEntity();
    if(is_array($info) && isset($info['name'])) {
      $drupalID = $db->getDrupalId($info['contactID']);
      $vid = $db->getSingle('SELECT `vid` FROM `node` WHERE `uid` = ' . $drupalID . ' AND `type` = "artist" LIMIT 1;');
    } else {
      $vid = $this->processArgs($info);
    }

    $fid = $db->getSingle('SELECT `field_image_fid` FROM `content_field_image` WHERE `vid` = "' . $vid . '" LIMIT 1;');
    $image = $db->getSingle('SELECT `filepath` FROM `files` WHERE `fid` = "' . $fid . '" LIMIT 1;');
    $this->data = '';
    if($image !== false) {
      $this->data = FILE_PATH . $image;
    }

    $db->close();
  }

}

?>
