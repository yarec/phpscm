<?php
require_once "Primitive.php";
require_once "ScmObjBool.php";

class Env
{
  private $parent;
  private $env;

  public function __construct($parent) {
    $this->parent = $parent;
    $this->env = array();
  }

  public function lookup(ScmObjSymbol $sym) {
    if (array_key_exists($sym->getValue(), $this->env))
      return $this->env[$sym->getValue()];
    if ($this->parent === null) {
      if (Primitive::isPrimitiveSymbol($sym))
        return $sym;
      throw new Exception("ERROR: Undefined symbol \"" . $sym->getValue() . "\"");
    }
    return $this->parent->lookup($sym);
  }

  public function assign($sym, $value) {
    if (array_key_exists($sym->getValue(), $this->env)) {
      $this->env[$sym->getValue()] = $value;
      return new ScmObjBool(true);
    }
    if ($this->parent === null)
      return new ScmObjBool(false);
    if ($parent->assign($sym, $value)->getValue() === false) {
      $env[$sym->getValue()] = $value;
    }
    return new ScmObjBool(true);
  }

  public function define($sym, $value) {
    $this->env[$sym->getValue()] = $value;
  }
}