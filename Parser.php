<?php
require_once "Lexer.php";
require_once "ScmObjNumber.php";
require_once "ScmObjString.php";
require_once "ScmObjNull.php";
require_once "ScmObjCons.php";
require_once "ScmObjSymbol.php";

class Parser {
  private $lexer;

  public function __construct($lexer) {
    $this->lexer = $lexer;
  }

  public function parse() {
    if (false !== ($token = $this->lexer->getToken())) {
      if ($token->type === Token::STR)
        return new ScmObjString($token->value);
      if ($token->type === Token::NUM)
        return new ScmObjNumber($token->value);
      if ($token->type === Token::QUT)
        return $this->parseQuote();
      if ($token->type === Token::S_S)
        return $this->parseSexp();
      if ($token->type === Token::S_E)
        return $token;
      if ($token->type === Token::DOT)
        return $token;
      if ($token->type === Token::T)
        return new ScmObjBool(true);
      if ($token->type === Token::F)
        return new ScmObjBool(false);
      return new ScmObjSymbol($token->value);
    }
    return false;
  }

  private function parseQuote() {
    return new ScmObjCons(new ScmObjSymbol("quote"), $this->parse());
  }

  private function parseSexp() {
    $first = $this->parse();

    if ($first instanceof Token) {
      if ($first->type === Token::S_E)
        return new ScmObjNull();
      throw new Exception("Parse Error");
    }
    $second = $this->parse();

    if ($second instanceof Token) {
      if ($second->type === Token::DOT) {
        $third = $this->parse();
        if ($third instanceof Token)
          throw new Exception("Parse Error");
        $fourth = $this->parse();
        if (($fourth instanceof Token) && ($fourth->type === Token::S_E))
          return new ScmObjCons($first, $third);
        throw new Exception("Parse Error");
      } else if ($second->type === Token::S_E) {
        return new ScmObjCons($first, new ScmObjNull());
      } else
        throw new Exception("Parse Error");
    }

    return new ScmObjCons($first, new ScmObjCons($second, $this->parseList()));
  }

  private function parseList() {
    $obj = $this->parse();
    if ($obj instanceof Token) {
      if ($obj->type === Token::S_E)
        return new ScmObjNull();
      throw new Exception("Parse Error");
    }
    return new ScmObjCons($obj, $this->parseList());
  }
}