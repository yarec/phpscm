<?php
require_once "Reader.php";
require_once "Token.php";

class Lexer
{
  private $reader;

  public function __construct($fp) {
    $this->reader = new Reader($fp);
  }

  public function getToken() {
    while (false !== ($c = $this->reader->read())) {
      if (ctype_space($c))
        continue;
      $this->reader->unread();
      $token = false;

      if ($c === '(')
        $token = new Token(Token::S_S, $this->reader->read());
      else if ($c === ')')
        $token = new Token(Token::S_E,  $this->reader->read());
      else if ($c === '"')
        $token = $this->getTokenString();
      else if ($c === '.')
        $token = new Token(Token::DOT, $this->reader->read());
      else if ($c === "'")
        $token = new Token(Token::QUT, $this->reader->read());
      else if ($c === '-')
        $token = $this->getTokenMinus();
      else if (is_numeric($c))
        $token = $this->getTokenNumeric();
      else if (strlen($c))
        $token = $this->getTokenSymbol();
      return $token;
    }
    return false;
  }

  private function getTokenMinus() {
    $num = $this->reader->read();
    while ((false !== ($c = $this->reader->read())) && is_numeric($c)) $num .= $c;
    if (strlen($num) === 1)
      return new Token(Token::SYM, $num);
    $this->reader->unread();
    return new Token(Token::NUM, $num);
  }

  private function getTokenNumeric() {
    $num = '';
    while ((false !== ($c = $this->reader->read())) && is_numeric($c)) $num .= $c;
    $this->reader->unread();
    return new Token(Token::NUM, $num);
  }

  private function getTokenString() {
    $str = '';
    $this->reader->read();
    while ((false !== ($c = $this->reader->read())) && $c !== '"') $str .= $c;
    return new Token(Token::STR, $str);
  }

  private function getTokenSymbol() {
    $sym = '';
    while (false !== ($c = $this->reader->read())) {
      if (ctype_space($c)
          || $c === '('
          || $c === ')')
        break;
      $sym .= $c;
    }
    $this->reader->unread();
    if ($sym === '#t')
      return new Token(Token::T, $sym);
    if ($sym === '#f')
      return new Token(Token::F, $sym);
    return new Token(Token::SYM, $sym);
  }
}
