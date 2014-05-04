<?php
  function trace($s){
    if(defined("COMMANDLINE"))
      print($s);
    else{
      print("\n<pre>\n");
      print($s);
      print("\n</pre>\n");
      flush();
    }
  }

  function trace_r($x){
    if(defined("COMMANDLINE"))
      print_r($x);
    else{
      print("\n<pre>\n");
      print_r($x);
      print("\n</pre>\n");
      flush();
    }
  }
?>