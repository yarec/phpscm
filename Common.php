<?php
require_once "Lexer.php";
require_once "Parser.php";
require_once "ScmObj.php";
require_once "Env.php";
require_once "ScmObjCons.php";

class Common
{
  public static function repl(Env &$env) {
    print "phpscm> ";
    $lexer = new Lexer(STDIN);
    $parser = new Parser($lexer);
    try {
      while (false !== ($scmObj = $parser->parse())) {
        print(self::toString($scmObj->scmEval($env)));
        print "\nphpscm> ";
      }
      return 0;
    } catch (Exception $e) {
      print $e->getMessage() . PHP_EOL;
    }
  }

  public static function scmRequire(Env &$env, $filename) {
    try {
      if (false === ($f = fopen($filename, "r")))
        throw new Exception("ERROR: fopen");
      $lexer = new Lexer($f);
      $parser = new Parser($lexer);
      while (false !== ($scmObj = $parser->parse())) {
        $scmObj->scmEval($env);
      }
      return 0;
    } catch (Exception $e) {
      print $e->getMessage() . PHP_EOL;
    }
  }

  public static function toString($scmObj) {
    $h = '';
    if ($scmObj instanceof ScmObjCons)
      $h = '(';
    return $h . $scmObj;
  }

  public static function toPhpArray(ScmObjCons $scmObj) {
    $itr = $scmObj;
    $ary = array();
    while ($itr instanceof ScmObjCons) {
      if ($itr->car() instanceof ScmObjCons)
        $ary[] = self::toPhpArray($itr->car());
      else
        $ary[] = $itr->car()->getValue();
      $itr = $itr->cdr();
    }
    return $ary;
  }
}