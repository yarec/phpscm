<?php

class Reader {
  private $fp;
  private $c;
  private $ungetc;

  public function __construct($fp) {
    $this->fp = $fp;
    $c = '';
    $ungetc = '';
  }

  public function read() {
    if ($this->ungetc) {
      $this->ungetc = false;
    } else {
      $this->c = fgetc($this->fp);
    }
    return $this->c;
  }

  public function unread() {
    $this->ungetc = true;
  }
}
