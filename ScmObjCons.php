<?php
require_once "ScmObj.php";
require_once "ScmObjBool.php";
require_once "ScmObjLambda.php";
require_once "ScmObjSequence.php";
require_once "Common.php";

class ScmObjCons extends ScmObj
{
  public function __construct($car, $cdr) {
    parent::__construct(array('car' => $car, 'cdr' => $cdr));
  }

  public function car() {
    $value = $this->getValue();
    return $value['car'];
  }

  public function cdr() {
    $value = $this->getValue();
    return $value['cdr'];
  }

  public function __toString() {
    $h = '';
    if ($this->car() instanceof ScmObjCons)
      $h = '(';
    if ($this->cdr() instanceof ScmObjNull)
      return $h . $this->car() . ')';
    if (!($this->cdr() instanceof ScmObjCons))
      return $h . $this->car() .  ' . ' . $this->cdr() . ')';
    return $h . $this->car() . " " . $this->cdr();
  }

  public function scmEval(Env &$env) {
    $car = $this->car()->scmEval($env);
    if ($car instanceof ScmObjSymbol) {
      $sym = $car->getValue();
      if ($sym === 'quote')
        return $this->cdr();
      if ($sym === 'set!')
        return $this->evalAssign($env);
      if ($sym === 'define')
        return $this->evalDefine($env);
      if ($sym === 'if')
        return $this->evalIf($env);
      if ($sym === 'lambda')
        return $this->evalLambda($env);
      if ($sym === 'begin')
        return $this->evalSequence($env);
      if ($sym === 'cond')
        return $this->evalCond($env);
      if ($sym === 'let')
        return $this->evalLet($env);
      if ($sym === 'require')
        return $this->evalRequire($env);
    }
    return $this->scmApply($car, $this->evalArgs($this->cdr(), $env));
  }

  private function evalAssign(Env &$env) {
    $cdr = $this->cdr();
    if ($cdr->car() instanceof ScmObjSymbol)
      return $env->assign($cdr->car(), $cdr->cdr()->car());
    throw new Exception("ERROR: Eval set!");
  }

  private function evalDefine(Env &$env) {
    $cdr = $this->cdr();
    if ($cdr instanceof ScmObjCons) {
      if ($cdr->car() instanceof ScmObjSymbol) {
        $val = $cdr->cdr()->car()->scmEval($env);
        $env->define($cdr->car(), $val);
        return new ScmObjBool(true);
      }
    }
    throw new Exception("ERROR: Eval define");
  }

  private function evalIf(Env &$env) {
    $predicate = $this->cdr()->car()->scmEval($env);
    if (($predicate instanceof ScmObjBool && $predicate->getValue() === false)
        || $predicate instanceof ScmObjNull) {
      return $this->cdr()->cdr()->cdr()->car()->scmEval($env);
    }
    return $this->cdr()->cdr()->car()->scmEval($env);
  }

  private function evalLambda(Env &$env) {
    //    return new ScmObjLambda($this->cdr()->car(), $this->cdr()->cdr()->car(), $env);
    return new ScmObjLambda($this->cdr()->car(), new ScmObjSequence($this->cdr()->cdr()), $env);
  }

  private function evalSequence(Env &$env) {
    $seq = new ScmObjSequence($this->cdr());
    return $seq->scmEval($env);
  }

  private function evalCond(Env &$env) {
    $condlist = $this->cdr();
    while (!($condlist instanceof ScmObjNull)) {
      $cond = $condlist->car()->car()->scmEval($env);
      if (($cond instanceof ScmObjBool && $cond->getValue() === false)
          || $cond instanceof ScmObjNull) {
        $condlist = $condlist->cdr();
        continue;
      }
      return $condlist->car()->cdr()->car()->scmEval($env);
    }
  }

  private function evalLet(Env &$env) {
    $body = $this->cdr()->cdr()->car();
    $let = $this->cdr()->car();
    $args = '';
    $vals = '';
    while (!($let instanceof ScmObjNull)) {
      if ($args === '') {
        $args = new ScmObjCons($let->car()->car(), new ScmObjNull());
        $vals = new ScmObjCons($let->car()->cdr()->car(), new ScmObjNull());
      } else {
        $args = new ScmObjCons($args->car(), new ScmObjCons($let->car()->car(), new ScmObjNull()));
        $vals = new ScmObjCons($vals->car(), new ScmObjCons($let->car()->cdr()->car(), new ScmObjNull()));
      }
      $let = $let->cdr();
    }
    $lambda = new ScmObjCons(new ScmObjLambda($args, $body, $env), $vals);
    return $lambda->scmEval($env);
  }

  private function evalRequire(Env &$env) {
    Common::scmRequire($env, $this->cdr()->car()->getValue());
    return new ScmObjBool(true);
  }

  private function evalArgs($obj, Env &$env) {
    if ($obj instanceof ScmObjNull)
      return $obj;
    return new ScmObjCons($obj->car()->scmEval($env), $this->evalArgs($obj->cdr(), $env));
  }

  public function scmApply($proc, $args) {
    if ($proc instanceof ScmObjSymbol) {
      if (Primitive::isPrimitiveProc($proc))
        return Primitive::scmApply($proc, $args);
    }
    if ($proc instanceof ScmObjLambda) {
      return $proc->scmApply($args);
    }
    return $proc;
  }
}