<?php
require_once "Common.php";

main($argc, $argv);

function main($argc, $argv) {
  $env = new Env(null);
  if ($argc === 1) {
    Common::scmRequire($env, dirname(__FILE__) . "/init.scm");
    return Common::repl($env);
  }
  if ($argc >= 2) {
    Common::scmRequire($env, dirname(__FILE__) . "/init.scm");
    return Common::scmRequire($env, $argv[1]);
  }
}


