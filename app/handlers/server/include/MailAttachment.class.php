<?php
  class MailAttachment{
    private $body;
    private $name;
    
    public function MailAttachment($b,$n){
      $this->body = $b;
      $this->name = $n;
    }
    
    public function setBody($b){
      $this->body = $b;
    }
    
    public function getBody(){
      return $this->body;
    }
    
    public function setName($n){
      $this->name = $n;
    }
    
    public function getName(){
      return $this->name;
    }
  }
?>