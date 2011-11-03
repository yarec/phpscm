<?php
require_once "ScmObj.php";

class ScmObjNull extends ScmObj
{
  public function __construct() {
    parent::__construct(null);
  }

  public function __toString() {
    return "()";
  }
}
