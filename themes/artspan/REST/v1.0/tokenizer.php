<?php

class Tokenizer {

  private $path;

  public function __construct($argv = null) {
    $request = array();
    if(isset($argv)) {
      if(class_exists(ucwords($argv[1]))) {
        $item = null;
        if(isset($argv[2])) {
          $item = $argv[2];
        }
        $requestArray = array(array($argv[1] => $item));
      } else {
        die("Bad arguments\n");
      }
    } else {
      if(isset($_SERVER['REQUEST_URI'])) {
        $request = explode('/', substr($_SERVER['REQUEST_URI'], 1));
        if((ucwords($request[0]) == 'Artists') || (ucwords($request[0]) == 'Events') || (ucwords($request[0]) == 'OpenStudios')) {
          die();
        }
      }
      $requestArray = array();
      for($i = 0; $i <= count($request); $i += 2) {
        $item = null;
        if(isset($request[($i + 1)])) {
          $item = $request[($i + 1)];
        }
        if(isset($request[$i])) {
          $requestArray[] = array($request[$i] => $item);
        }    
      }
    }
    $this->path = $requestArray;
  }

  public function fake($type, $id) {
    $this->path = array(array($type => $id));
  }

  public function popToken() {
    return array_pop($this->path);
  }

  public function peekToken() {
    $tokens = $this->path;
    return array_pop($tokens);
  }

  public function getTokens() {
    return $this->path;
  }

}

?>
