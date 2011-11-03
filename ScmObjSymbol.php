<?php
require_once "ScmObj.php";

class ScmObjSymbol extends ScmObj
{
  public function scmEval(Env &$env) {
    return $env->lookup($this, $env);
  }
}
