<?php

abstract class Model {

  protected $entity;
  protected $prefix;
  protected $data;
  protected $rawData;
  protected $db;
  protected $elements = null;
  protected $useDB = false;
  protected $infoType = 'Double';
  private $tokens = array();
  protected $queryString = null;

  public function __construct($tokenizer) {
    $this->setEntity();
    $this->processArgs($tokenizer);
    $this->data = $this->matchElements($this->seeker());
  }

  public function __toString() {
    if(is_array($this->data)) {
      return $this->makeList($this->data);
    } else {
      return '"' . $this->data . '"';
    }
  }

  private function collapseArray($array) {
    if(count($array) == 1) {
      return array_shift($array);
    }
    return $array;
  }

  //This function written by seriousdev
  //http://stackoverflow.com/questions/5258543/remove-all-the-line-breaks-from-the-html-source
  //Retrieved 2013-08-23 17:00:00
  private function collapseLineBreaks($input) {
    $input = str_replace(array("\r\n", "\r"), "\\n", $input);
    $lines = explode("\n", $input);
    if($lines == $input) {
      return $input;
    }
    $newLines = array();

    foreach ($lines as $i => $line) {
      if(!empty($line)) {
        $newLines[] = trim($line);
      }
    }
    return implode("\\n", $newLines);
  }

  protected function dropBlanks($array) {
    if(!is_array($array)) {
      return $array;
    }
    foreach($array as $key => $value) {
      if(!isset($value) || empty($value) || ($value == '')) {
        unset($array[$key]);
      }
    }
    return $array;
  }

  protected function escapeHTML($string) {
    return str_replace('&quot;', '\"', htmlentities($string, ENT_COMPAT, "UTF-8"));
    //array('"', '”', '“', 'â€™', '’', '–', 'â€“', 'â€¨', '‘', 'â€˜', '•', 'â€¢', '—', 'â€”', '…', 'â€¦', '″', 'â€³', 'â€‹', 'â€¨'), 
    //array('\"', '\"', '\"', "'", "'", '-', '-', '', "'", "'", '', '', '-', '-', '...', '...', '"', '"', '', ''), 
    /*return str_replace(
      array('"',  '”',  '“',  'â€', '™', '’', '–', '“', '¨', '‘', '˜', '•', '¢', '—', '”', '…',   '¦', '″', '³', '‹', '¨'), 
      array('\"', '\"', '\"', '',   '',  "'", '-', '',  "'", "'", '',  '',  '-', '-', '"', '...', '',  '"', '',  '',  ''), 
      $this->collapseLineBreaks($string)
    );*/
  }

  public function get($dataItem, $id = null) {
    $data = $this->rawData;

    if(!isset($id) || !is_numeric($id)) {
      $entry = $data['values'];
      if(is_array($entry)) {
        $entry = array_pop($entry);
      }
    } else {
      $entry = $data['values'][$id];
    }
    if(!isset($entry[$dataItem])) {
      if(!isset($this->data[$dataItem])) {
        return null;
      }
      return $this->data[$dataItem];
    }
    return $entry[$dataItem]; 
  }

  public function getData() {
    return $this->data;
  }

  public function getElements() {
    return $this->elements;
  }

  public function getPrefix() {
    return $this->prefix;
  }

  public function getQueryString() {
    return $this->queryString;
  }

  protected function getTokens() {
    return $this->tokens;
  }

  protected function makeCamelCase($word) {
    $allCapsWords = array('id', 'url');
    if(is_numeric($word)) {
      return $word;
    }
    $camelCased = array();
    $wordArray = explode('_', $word);
    $numAlready = false;
    foreach($wordArray as $subword) {
      if(is_numeric($subword)) {
        if($numAlready) {
          $subword = '';
        } else {
          $numAlready = true;
        }
      } elseif(in_array($subword, $allCapsWords) !== false) {
        $subword = strtoupper($subword);
      } else {
        $subword = ucwords($subword);
      }
      $camelCased[] = $subword;
    }
    return implode($camelCased);
  } 

  protected function makeList($array) {
    $return = array();
    $array = $this->dropBlanks($array);
    foreach($array as $name => $item) {
      if($item =='') {
        continue;
      } elseif (!is_array($item)) {
        $item = array($name => $item);
      }
      $instance = array();
      foreach($item as $key => $value) {
        if(!is_array($value)) {
          $value = trim($value);
          if(isset($value) && ($value != '') && ($value[0] != '"') && ($value[0] != '{') && ($value[0] != '[')) {
            $value = '"' . $value . '"';
          }
        } else {
          $value = '["' . implode('","', $value) . '"]';
        }

        $instance[] = '"' . $this->makeCamelCase($key) . '":' . $value;
      }
      $return[] = implode(',', $instance);
    }
    if(is_array($this->data) && (get_class($this) != 'Participant')) {
      return '[{' . implode('},{', $return) . '}]';
    }
    return '{' . implode(',', $return) . '}';
  }

  private function matchElements($data = null) {
    if(isset($this->elements) && is_array($this->elements) && isset($data)) {
      $newData = array();
      foreach($data as $idNum => $item) {
        $newItem = array();
        foreach($this->elements as $key => $element) {
          //Corrects address2 and neighborhood2 elements
          if($key[(strlen($key) - 1)] == 2) {
            $key = substr($key, 0, -1);
          }
          if(isset($item[$element]) || isset($item[$element . '_' . $this->get('civicrm_value_sf_open_studios_artist_info_4_id')])) {
            if(isset($item[$element])) {
              $tempKey = $element;
            } else {
              $tempKey = $element . '_' . $this->get('civicrm_value_sf_open_studios_artist_info_4_id');
            }
            if($item[$tempKey] == '') {
              unset($newItem[$key]);
            } else { 
              $newItem[$key] = $item[$tempKey];
            }
          } elseif(class_exists($element)) {
            $newItem[$key] = new $element(array('contactID' => $this->get('contact_id'), 'name' => $this->get('display_name'), 'caller' => $this));
            if(empty($newItem[$key]->data)) {
              unset($newItem[$key]);
            } else {
              $newItem[$key] = (string)$newItem[$key];
            }
          } else {
            if(!$this->useDB || !isset($newItem[$key]) || ($newItem[$key] == '')) {
              $newItem[$key] = false;
            }
          }
        }
        $newData[$idNum] = $this->collapseArray($newItem);
      }
      return $this->collapseArray($newData);
    } else {
      return $data;
    }
  }

  protected function processArgs($args) {
    if(!is_array($args) && (get_class($args) == 'Tokenizer')) {
      $id = $args->peekToken();
      $id = array_shift($id);
      $query = '&';
      if(isset($id)) {
        $query .= $this->getPrefix() . 'id=' . $id;
      }
      $this->queryString = $query;
      return $id;
    } else {
      $this->queryString = $args['caller']->getQueryString();
      return $args['contactID'];
    }
  }

  protected function seeker() {
    $data = file_get_contents(API_LOCATION . '?'. API_AUTH .'json=1&action=get&entity=' . $this->entity . $this->queryString);
    $data = json_decode($data, true);
    $this->rawData = $data;
    return $data['values'];
  }

  protected function setEntity() {
    $this->entity = get_class($this);
  }

  public function setTokens($tokens) {
    $this->tokens = $tokens;
  }

  public function unsetElement($elementName) {
    $data = $this->data;
    if(!isset($data[$elementName])) {
      return false;
    }
    unset($data[$elementName]);
    $this->data = $data;
    return true;
  }

  public function goDie($message) {
    die('<pre>' . print_r($message, true) . '</pre>');
  }

}

?>
