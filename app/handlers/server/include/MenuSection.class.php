<?php
  class MenuSection{
    private $name;
    private $link;
    private $items;

    function MenuSection($name,$link = null){
      $this->name = $name;
      $this->link = $link;
      if(strlen($this->link) == 0)
        $this->link = null;
      $this->items = array();
    }

    function getName(){return $this->name;}
    function getLink(){return $this->link;}

    function addItem($name,$link){
      if(strlen($name) > 0 && strlen($link) > 0){
        $this->items[] = array($name,$link);
      }
      else{
        $x = strlen($name) > 0 ? "name: ".$name : "link: ".$link;
        reportError("Error in menu item definition","Menu item name/link is empty\r\n".$x);
      }
    }

    function size(){
      return count($this->items);
    }

    function getItemAt($i){
      return $this->items[$i];
    }

    function getItemNameAt($i){
      $x = null;
      if(isset($this->items[$i])){
        $item = $this->items[$i];
        $x = $item[0];
      }
      return $x;
    }

    function getItemLinkAt($i){
      $x = null;
      if(isset($this->items[$i])){
        $item = $this->items[$i];
        $x = $item[1];
      }
      return $x;
    }
  }
?>