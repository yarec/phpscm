<?php
require_once "ScmObj.php";

class ScmObjSequence extends ScmObj
{
  public function __toString() {
    $str = '';
    $value = $this->getValue();
    if ($value instanceof ScmObjCons) {
      $f = '';
      while (!($value instanceof ScmObjNull)) {
        $str .= $f . $value->car()->__toString();
        $value = $value->cdr();
        $f = ' (';
      }
    } else {
      $str = $value->__toString();
    }
    return $str;
  }

  public function scmEval(&$env) {
    $seq = $this->getValue();
    $ret = null;
    while (!($seq instanceof ScmObjNull)) {
      $ret = $seq->scmEval($env);
      $seq = $seq->cdr();
    }
    return $ret;
  }
}