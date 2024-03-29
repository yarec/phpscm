<?php
require_once "Env.php";

abstract class ScmObj
{
  private $value;

  public function __construct($value) {
    $this->value = $value;
  }

  public function getValue() {
    return $this->value;
  }

  public function setValue() {
    $this->value = $value;
  }

  public function __toString() {
    return $this->value;
  }

  public function scmEval(Env &$env) {
    return $this;
  }
}
