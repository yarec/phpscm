<?php
require_once "ScmObj.php";

class ScmObjBool extends ScmObj
{
  public function __toString() {
    if ($this->getValue())
      return '#t';
    return '#f';
  }
}
