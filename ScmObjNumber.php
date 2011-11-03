<?php
require_once "ScmObj.php";

class ScmObjNumber extends ScmObj
{
  public function __construct($value) {
    parent::__construct(intval($value));
  }

  public function __toString() {
    return sprintf("%d", $this->getValue());
  }
}
