<?php
  $mxl = array(
  );

  function maxlength($key){
    if(isset($mxl[$key])){
      $arr = $mxl[$key];
      return intval($arr[0]);
    }
    else
      return 0;
  }

  function mxl($key){
    global $mxl;
    if(isset($mxl[$key])){
      $arr = $mxl[$key];
      $length = $arr[0];
      if($length > 0)
        printf(" maxlength=\"%d\" ",$length);
    }
  }

  function jmxl($key){
    global $mxl;
    if(isset($mxl[$key])){
      $arr = $mxl[$key];
      $length = $arr[0];
      if($length > 0)
        printf(" onchange=\"if(this.value.length >= %d){this.value = this.value.substring(0,%d);this.style.backgroundColor='#ffffff';alert('Слишком длинный текст. Делаем обрезание.');}\" onkeyup=\"if(this.value.length > %d){this.style.backgroundColor='#ff0000';}else{this.style.backgroundColor='#ffffff';}\"",$length,$length,$length);
    }
  }
?>