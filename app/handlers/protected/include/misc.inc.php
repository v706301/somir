<?php
  function csvsafe($s){
    if(strlen(trim($s)) == 0)
      return "";
    $s = preg_replace("/,/","",$s);
    return preg_replace("/\"/","'",$s);
    
  }
  
  function isProfilePage(){
    if(strcmp(basename($_SERVER["SCRIPT_FILENAME"]),"profile.php") == 0)
      return true;
    else
      return false;
  }
  
  function fullname($fname,$mname,$lname,$bStartWithFname = true){
    if($bStartWithFname)
      return $fname.(strlen($mname) == 0 ? "":" ".$mname)." ".$lname;
    else
      return $lname.", ".$fname.(strlen($mname) == 0 ? "":" ".$mname);
  }

  function name($name,$fname,$lname,$bStartWithFname = true){
    if(!$bStartWithFname){
      $x = $fname;
      $fname = $lname;
      $lname = $x;
    }
    if(strlen($name) == 0)
      return $fname." ".$lname;
    else
      return $name." (".$fname." ".$lname.")";
  }

  function isSSLCheckEnabled(){
    $val = getSetting("https_mode");
    if(strcasecmp($val,"ENABLED") == 0 || strcasecmp($val,"YES") == 0 || strcasecmp($val,"TRUE") == 0)
      return true;
    else
      return false;
  }

  function array2CSV($arr,$bSQLSafe = false,$separator = ""){
    $str = "";
    if(is_array($arr)){
      for($i = 0; $i < count($arr); $i++){
        if(strlen($str) > 0)
          $str .= ",";
        if($bSQLSafe)
          $str .= $separator.str_replace("'","''",quotemeta($arr[$i])).$separator;
        else
          $str .= $separator.$arr[$i].$separator;
      }
    }
    return $str;
  }

  function getElementIndex(&$arr,&$key){
    for($i = 0; $i < count($arr); $i++){
      if(strcasecmp($arr[$i],$key) == 0){
        return $i;
      } 
    }
    return -1;
  }

  function cknull($s){
    if(is_null($s) || strlen(trim($s)) == 0)
      return null;
    else
      return trim($s);
  }
  
  function makeFullPath($dir,$file,$delimiter = "/"){
    while(strcmp(substr($file,0,1),$delimiter) == 0)
      $file = substr($file,1);
    while(strcmp(substr($dir,strlen($dir)-1),$delimiter) == 0)
      $dir = substr($dir,0,strlen($dir)-1);
    return $dir . $delimiter . $file;
  }
  
  function trimLine($str,$len = 30){
    if(strlen($str)>$len) return substr($str,0,$len).'...';
    else return $str;
  }
  
  function isEmail($email){
    if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$",$email))
      return false;
    else
      return true;      
  }
    
  function htmlStar($misc = "class=\"times red XXXL\""){
    printf("<span %s>*</span>",$misc);
  }
  
  function ie($code,$version = null){
    $pos = strpos(strtoupper($_SERVER["HTTP_USER_AGENT"]),"MSIE");
    if($pos !== false){
      if(is_null($version))
        print($code);
      else{
        $i = intval(substr($_SERVER["HTTP_USER_AGENT"],$pos+5));
        if($i >= $version)
          print($code);
      }
    }
  }

  function generateCSVFile(/* array */ $aColumnNames, /* resource */ $oResult){
    if(!is_array($aColumnNames)){
      reportError("Colunm names not an array!", __FILE__ . ":" . __LINE__ );
      return false;
    }
    
    if(!is_resource($oResult)){
      // Resource is not resource!?
      reportError("Not a resource!", __FILE__ . ":" . __LINE__ );
      return false;
    }
    
    $cColumnNames = count($aColumnNames );
    if(count($aColumnNames)> CDB::num_fields($oResult)){
      reportError("Column count does not match!", __FILE__ . ":" . __LINE__ );
      return false;
    }
    
    $a = array();
    foreach($aColumnNames as $v){
      $a[] = $q = sprintf("\"%s\"", $v );
    }

    $s = implode(", ", $a );
    $db = getDatabase();
    
    while($row = $db->fetch_assoc($oResult)){
      $s .= "\r\n";
      for ($i = 0; $i < count($row); $i++){
        $name = $db->fetchFieldName($oResult, $i );
        $ftype = CDB::field_type($oResult, $i );
        if(in_array($name,array_keys($aColumnNames))){
          switch($ftype ){
            case "string":
            $value = preg_replace("/,/","",$row[$name]);
            $aColumnNames[$name] = sprintf("\"%s\"", $value );
            break;
            default:
            $aColumnNames[$name] = $row[$name];
            break;
          }
        }
      }
    $s .= implode(", ",$aColumnNames);
    }
    return $s;
  }
  
  function arrclean($arr){
    if(is_array($arr) && count($arr) > 0){
      $arr2 = array();
      $keys = array_keys($arr);
      for($i = 0; $i < count($keys); $i++){
        if(isset($arr[$keys[$i]]) && !is_null($arr[$keys[$i]]) && strlen($arr[$keys[$i]]) > 0)
          $arr2[$keys[$i]] = $arr[$keys[$i]];
      }
      return $arr2;
    }
    return null;
  }
  
?>