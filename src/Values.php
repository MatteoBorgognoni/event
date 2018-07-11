<?php

namespace Drupal\event;

class Values {

  public function set($name, $value) {
    $this->{$name} = $value;
  }

}