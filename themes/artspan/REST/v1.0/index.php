#!/usr/bin/env php
<?php

include("config.php");
include("tokenizer.php");
include("model.php");
include("database.php");
include("artists.php");
include("artwork.php");
include("statement.php");
include("website.php");
include("group_studio.php");
include("geocode.php");
include("phone.php");
include("email.php");
include("participant.php");
include("open_studios.php");
include("events.php");
include("artist_photo.php");

if(!isset($argv)) {
  $argv = null;
}
$tokenizer = new Tokenizer($argv);

$firstToken = array_keys($tokenizer->peekToken());
$firstModelName = ucwords(array_shift($firstToken));
$firstModel = new $firstModelName($tokenizer);

echo $firstModel;

?>
