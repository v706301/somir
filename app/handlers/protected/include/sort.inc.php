<?php
  require_once("db.inc.php");
  require_once("http.inc.php");
  $_SORT_COLUMNS_DISABLED = false;
  $piecesHint = "<span style=\"cursor:default;font-size:8px;color:#ffff00;\" title=\"Items in pieces\">&times;1000</span>";
  
  function disableSort(){
    global $_SORT_COLUMNS;
    global $_SORT_COLUMNS_DISABLED;
    $_SORT_COLUMNS_DISABLED = $_SORT_COLUMNS;
    $_SORT_COLUMNS = null;
  }
  
  function enableSort(){
    global $_SORT_COLUMNS;
    global $_SORT_COLUMNS_DISABLED;
    if($_SORT_COLUMNS_DISABLED !== false){
      $_SORT_COLUMNS = $_SORT_COLUMNS_DISABLED;
      $_SORT_COLUMNS_DISABLED = false;
    }
  }
  
  function setSortOrder($initialIndex = 0){
    global $_SORT_COLUMNS;
    if(is_null($_SORT_COLUMNS) || !is_array($_SORT_COLUMNS) || count($_SORT_COLUMNS) == 0)
      reportError("Invalid sort order","Sort columns not defined",null,__FILE__.":".__LINE__);
    else if(isParameter("sr")){
      clearSessionVar(SELF."_sort-order");
      clearSessionVar(SELF."_sort-last");
    }
    if(!isSessionVar(SELF."_sort-order")){
      $arr = array();
      $keys = array_keys($_SORT_COLUMNS);
      for($i = 0; $i < count($keys); $i++){
        $ascdesc = getDefaultAscdescForColumn($i); //$_SORT_COLUMNS[$keys[$i]];
        if($i == $initialIndex){
          //0-0 means: column #0 was clicked 0 times
          setSessionVar(SELF."_sort-last",sprintf("%d-0",$initialIndex));
          setParameter("so",$ascdesc."-".$initialIndex);
        }
        array_push($arr,$ascdesc."-".$i);
      }
    }
    else
      $arr = getSessionVar(SELF."_sort-order");
    
    if(isParameter("so")){
      $x = explode("-",getParameter("so"));
      $ascdesc = $x[0];
      $index = intval($x[1]);
      for($i = 0; $i < count($arr); $i++){
        $y = explode("-",$arr[$i]);
        if(intval($y[1]) == $index){
          $prev = array_splice($arr,$i,1);
          break;
        }
      }
      $last = getSessionVar(SELF."_sort-last");
      $last = explode("-",$last);
      if($index == $last[0]){
        $result = $ascdesc . "-" . $index;
        setSessionVar(SELF."_sort-last",$index."-".(intval($last[1])+1));
      }
      else{
        $result = getDefaultAscdescForColumn($index) . "-" . $index;
        setSessionVar(SELF."_sort-last",$index."-0");
      }
      array_unshift($arr,$result);
    }
    setSessionVar(SELF."_sort-order",$arr);
  }

  function getSortOrderClause($defaultClause = "", $additionClause = ""){
    global $_SORT_COLUMNS;
    $s = "";
    if(isset($_SORT_COLUMNS) && is_array($_SORT_COLUMNS)){
      $arr = getSessionVar(SELF."_sort-order",false);
      if(!is_null($arr) && is_array($arr) && count($arr) > 0){
        $keys = array_keys($_SORT_COLUMNS);
        for($i = 0; $i < count($arr); $i++){
          $x = explode("-",$arr[$i]);
          $ascdesc = strtolower($x[0]) == "d" ? "DESC\n" : "ASC\n";
          $index = intval($x[1]);
          $columnKey = $keys[$index];
          if(strlen($s) > 0)
            $s .= ",";
          $s .= (strpos($_SORT_COLUMNS[$columnKey],"binary") === false ? "":" binary ").applyAscDesc($columnKey,$ascdesc);
        }
      }
    }
    if(strlen($additionClause) > 0)
      $s = strlen($s) > 0 ? $additionClause.",".$s : $additionClause;
    if(strlen($s) > 0)
      $s = "\nORDER BY\n".$s;
    else if(strlen($defaultClause) > 0)
      $s = "\nORDER BY\n".$defaultClause;
    return $s;
  }
  
  function applyAscDesc($columnKey,$ascdesc){
    $arr = explode(",",$columnKey);
    $columnKey = "";
    foreach($arr as $sortColumn){
      if(strlen($columnKey) > 0)
        $columnKey .= ",";
      $columnKey .= $sortColumn." ".$ascdesc;
    }
    return $columnKey;
  }
  
  function getSortOrderAbbr($index){
    $arr = getSessionVar(SELF."_sort-order",false);
    for($i = 0; $i < count($arr); $i++){
      $x = explode("-",$arr[$i]);
      if($index == intval($x[1])){
        return $arr[$i];
      }
    }
    return "";
  }
  
  function getColumnOrderInSort($index){
    $arr = getSessionVar(SELF."_sort-order",false);
    for($i = 0; $i < count($arr); $i++){
      $x = explode("-",$arr[$i]);
      if($index == intval($x[1])){
        return $i;
      }
    }
    return -1;
  }

  function getDefaultAscdescForColumn($columnIndexInDefaultArray){
    global $_SORT_COLUMNS;
    $keys = array_keys($_SORT_COLUMNS);
    $val = $_SORT_COLUMNS[$keys[$columnIndexInDefaultArray]];
    return $val[0];
  }

  function invertSortOrderAbbr($so){
    global $_SORT_COLUMNS;
    if(strlen($so) > 0){
      $x = explode("-",$so);
      $last = explode("-",getSessionVar(SELF."_sort-last",false));
      if($x[1] != $last[0]){
        if(strtolower($x[0]) == "d")
          return "a-".$x[1];
        else
          return "d-".$x[1];
      }
      else{
        if($last[1] == 0){
          $ascdesc = getDefaultAscdescForColumn($x[1]);
          if(strtolower($ascdesc) == "d")
            return "a-".$x[1];
          else
            return "d-".$x[1];
        }
        else{
          if(strtolower($x[0]) == "d")
            return "a-".$x[1];
          else
            return "d-".$x[1];
        }
      }
    }
    else
      return "";
  }

  function sreset($name,$params = ""){
    $link = sprintf("<a title=\"Reset sort order\" href=\"%s?sr=1%s\" class=\"ord-name\">%s</a>",SELF,$params,$name);
    $link2 = sprintf("<a title=\"Reset sort order\" href=\"%s?sr=1%s\" class=\"ord-coi\">x</a>",SELF,$params);
    $s = "";
    $s .= "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr>";
    $s .= sprintf("<td class=\"ord-coi\">%s</td>",$link2);
    $s .= sprintf("<td>%s</td>",$link);
    $s .= "</tr></table>";
    print($s);
  }

  function sstyle($index,$styleSelected,$stylePlain){
    $last = explode("-",getSessionVar(SELF."_sort-last",false));
    if($index == $last[0])
      print($styleSelected);
    else
      print($stylePlain);
  }

  function slink($index,$name,$params = "",$bShowSortOrder = false){
    $so = getSortOrderAbbr($index);
    $x = explode("-",$so);
    $so2 = invertSortOrderAbbr($so);
    $ord = getColumnOrderInSort($index);
    if($ord < 0)
      $ord = "";
    else
      $ord = sprintf("%d",$ord+1);
    $link = sprintf("<a href=\"%s?so=%s%s\" class=\"ord-name\">%s</a>",SELF,$so2,$params,$name);
    $img = sprintf("<a href=\"%s?so=%s%s\" class=\"ord-name\">&%s;</a>",SELF,$so2,$params,strtolower($x[0]) == "d" ? "uarr":"darr");
    $s = "";
    $s .= "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr>";
    if($bShowSortOrder)
      $s .= sprintf("<td class=\"ord-coi\">%s</td>",$ord);
    $s .= sprintf("<td>%s</td>",$link);
    $s .= sprintf("<td>%s</td>",$img);
    $s .= "</tr></table>";
    print($s);
  }
?>
