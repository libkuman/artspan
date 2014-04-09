<?php

if(arg(0) == 'node') {
  drupal_goto('user/' . $node->uid);
}

