<?php
require_once "ScmObjCons.php";

class Primitive
{
  static private $primitiveSyms = array('define',
                                        'if',
                                        'lambda',
                                        'set!',
                                        'quote',
                                        'begin',
                                        'else',
                                        'cond',
                                        'let',
                                        'require');

  static private $primitiveProcs = array('cons' => 'cons',
                                         '+' => 'add',
                                         '-' => 'sub',
                                         '*' => 'mul',
                                         '/' => 'div',
                                         '=' => 'equal',
                                         '<' => 'gt',
                                         '>' => 'lt',
                                         '<=' => 'ge',
                                         '>=' => 'le',
                                         'string-length' => 'string_length',
                                         'string=?' => 'string_equal',
                                         'var_dump' => '_var_dump',
                                         'format' => 'format',
                                         'php' => 'phpFunction',
                                         'car' => 'car',
                                         'cdr' => 'cdr');

  public static function isPrimitiveSymbol(ScmObjSymbol $sym) {
    if (in_array($sym->getValue(), self::$primitiveSyms))
      return true;
    return self::isPrimitiveProc($sym);
  }

  public static function isPrimitiveProc(ScmObjSymbol $sym) {
    return array_key_exists($sym->getValue(), self::$primitiveProcs);
  }

  public static function scmApply($proc, $args) {
    $proc = self::$primitiveProcs[$proc->getValue()];
    return self::$proc($args);
  }

  private static function cons($args) {
    $cdr = $args->cdr();
    if (!($cdr instanceof ScmObjNull))
      $cdr = $args->cdr()->car();
    return new ScmObjCons($args->car(), $cdr);
  }

  private static function calc($func, $args) {
    $ans = $args->car()->getValue();
    $arg = $args->cdr();
    while (!($arg instanceof ScmObjNull)) {
      $ans = $func($ans, $arg->car()->getValue());
      $arg = $arg->cdr();
    }
    return new ScmObjNumber($ans);
  }

  private static function add($args) {
    return self::calc(function($a,$b){return $a+$b;}, $args);
  }

  private static function sub($args) {
    return self::calc(function($a,$b){return $a-$b;}, $args);
  }

  private static function mul($args) {
    return self::calc(function($a,$b){return $a*$b;}, $args);
  }

  private static function div($args) {
    return self::calc(function($a,$b){return $a/$b;}, $args);
  }

  private static function compare($func, $args) {
    $value = $args->car()->getValue();
    $itr = $args->cdr();
    while (!($itr instanceof ScmObjNull)) {
      if (!$func($value, $itr->car()->getValue()))
        return new ScmObjBool(false);
      $value = $itr->car()->getvalue();
      $itr = $itr->cdr();
    }
    return new ScmObjBool(true);
  }

  private static function equal($args) {
    return self::compare(function($a,$b){return $a===$b;}, $args);
  }

  private static function gt($args) {
    return self::compare(function($a,$b){return $a<$b;}, $args);
  }

  private static function lt($args) {
    return self::compare(function($a,$b){return $a>$b;}, $args);
  }

  private static function ge($args) {
    return self::compare(function($a,$b){return $a<=$b;}, $args);
  }

  private static function le($args) {
    return self::compare(function($a,$b){return $a>=$b;}, $args);
  }

  public static function string_length($args) {
    if ($args->car() instanceof ScmObjString)
      return new ScmObjNumber(strlen($args->car()->getValue()));
    throw new Exception("ERROR: string-length");
  }

  public static function string_equal($args) {
    return self::compare(function($a,$b){return $a===$b;}, $args);
  }

  private static function _var_dump($args) {
    var_dump($args->car());
    return new ScmObjBool(true);
  }

  private static function format($args) {
    $fmt = str_replace('\n', "\n", $args->car()->getValue());
    $fmt = str_replace('\t', "\t", $fmt);
    $arg = array();
    if (!($args->cdr() instanceof ScmObjNull))
      $arg = self::toPhpArray($args->cdr());
    vprintf($fmt, $arg);
    return new ScmObjBool(true);
  }

  private static function toPhpArray($args) {
    return Common::toPhpArray($args);
  }

  private static function phpFunction($args) {
    $func = $args->car()->__toString();
    $phpArgs = array();
    $arg = $args->cdr();
    while (!($arg instanceof ScmObjNull)) {
      $car = $arg->car();
      if ($car instanceof ScmObjCons)
        $car = self::toPhpArray($car);
      else
        $car = $car->getValue();
      $phpArgs[] = $car;
      $arg = $arg->cdr();
    }
    $ans = null;
    switch (count($phpArgs)) {
    case 0: $ans = $func(); break;
    case 1: $ans = $func($phpArgs[0]); break;
    case 2: $ans = $func($phpArgs[0], $phpArgs[1]); break;
    case 3: $ans = $func($phpArgs[0], $phpArgs[1], $phpArgs[2]); break;
    case 4: $ans = $func($phpArgs[0], $phpArgs[1], $phpArgs[2], $phpArgs[3]); break;
    case 5: $ans = $func($phpArgs[0], $phpArgs[1], $phpArgs[2], $phpArgs[3], $phpArgs[4]); break;
    default: throw new Exception("ERROR: phpFunction");
    }
    if ($ans === null)
      return new ScmObjBool(true);
    return self::toSexp($ans);
  }

  private static function toSexp($phpval) {
    if (is_bool($phpval))
      return new ScmObjBool($phpval);
    if (is_numeric($phpval))
      return new ScmObjNumber($phpval);
    if (is_string($phpval))
      return new ScmObjString($phpval);
    if (is_array($phpval)) {
      $sexp = '';
      foreach($phpval as $val) {
        if ($sexp = '')
          $sexp = self::toSexp($val);
        else
          $sexp = new ScmObjCons($sexp, self::toSexp($val));
      }
      return $sexp;
    }
    throw new Exception("ERROR: toSexp");
  }

  private static function car($args) {
    return $args->car()->car();
  }

  private static function cdr($args) {
    return $args->car()->cdr();
  }
}
