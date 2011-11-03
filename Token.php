<?php

class Token
{
  const S_S = 1; // (
  const S_E = 2; // )
  const DQT = 3; // "
  const DOT = 4; // .
  const QUT = 5; // '
  const NUM = 6;
  const STR = 7;
  const SYM = 8;
  const NIL = 9;
  const T   = 10;
  const F   = 11;

  public $type;
  public $value;

  public function __construct($type, $value) {
    $this->type = $type;
    $this->value = $value;
  }
}
