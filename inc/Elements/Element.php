<?php

class Element
{
  public function __construct()
  { }

  public function __set($name, $value)
  {
    if (is_string($value))
      $this->{$name} = $value;
  }

  public function __get($name)
  {
    if (isset($this->{$name}))
      return $this->{$name};

    return false;
  }
}
