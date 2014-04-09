<?php

class Artwork extends Model {

  protected $elements = array('name', 'image_url', 'description');
  protected $useDB = true;

  public function __construct($info) {
    $db = new Database();
    $this->setEntity();
    if(is_array($info)) {
      $drupalID = $info['caller']->getDrupalID();

      if($drupalID !== false) {
        $idNums = $db->getMany('SELECT `vid` FROM `node` WHERE `uid` = ' . $drupalID . ' AND type = "artwork" ORDER BY `vid`;');
      }
    } else {
      $idNums = array($this->processArgs($info));
    }

    if(isset($idNums)) {
      $data = array();

      $artIDs = '`vid` = ' . implode(' OR `vid` = ', $idNums);
      $query = 'SELECT `field_content_title_value` FROM `content_field_content_title` WHERE ' . $artIDs . ' ORDER BY `vid`;';
      $titles = $db->getMany($query);

      $images = array();
      foreach($idNums as $idNum) {
        $query = 'SELECT `field_image_fid` FROM `content_field_image` WHERE `vid` = ' . $idNum . ' LIMIT 1;';
        $fid = $db->getSingle($query);

        $query = 'SELECT `filepath` FROM `files` WHERE `fid` = ' . $fid . ' LIMIT 1;';
        $images[$idNum] = $db->getSingle($query);
      }

      //$query = 'SELECT `field_art_detail_value` FROM `content_type_artwork` WHERE ' . $artIDs . ' ORDER BY `vid`;';
      //$media = $db->getMany($query);

      $query = 'SELECT `field_dimensions_value` FROM `content_field_dimensions` WHERE ' . $artIDs . ' ORDER BY `vid`, `delta`;';
      $dimensions = $db->getMany($query);

      foreach($idNums as $idNum) {
        $itemDimensions = array();
        for($i = 0; $i <3; $i++) {
          $dimension = array_shift($dimensions);
          if(($dimension != 0) && isset($dimension)) {
            $itemDimensions[] = $dimension;
          }
        }
        $data[$idNum] = $this->dropBlanks(array(
          'Name' => $this->escapeHTML(array_shift($titles)),
          'ImageURL' => FILE_PATH . $this->escapeHTML(array_shift($images)),
          'Description' => $this->escapeHTML(/*array_shift($media) . */' (' . implode(' x ', $itemDimensions) . ')')
        ));
      }

      $this->data = $data;
    } else {
      $this->data = null;
    }

    $db->close();
  }

}

?>
