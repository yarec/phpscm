<?php
require_once "ScmObj.php";

class ScmObjLambda extends ScmObj
{
  public function __construct($args, $body, Env &$env) {
    parent::__construct(array('args' => $args, 'body' => $body, 'env' => $env));
  }

  public function __toString() {
    $value = $this->getValue();
    return '(lambda (' . $value['args'] . ' (' . $value['body'] . ')';
  }

  public function scmApply($args) {
    $value = $this->getValue();

    $env = new Env($value['env']);
    $argSym = $value['args'];
    $argVal = $args;
    while (!($argSym instanceof ScmObjNull)) {
      $env->define($argSym->car(), $argVal->car());
      $argSym = $argSym->cdr();
      $argVal = $argVal->cdr();
    }

    return $value['body']->scmEval($env);
  }
}