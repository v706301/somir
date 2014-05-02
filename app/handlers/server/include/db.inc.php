<?php
  require_once("db.class.php");
  require_once("xmlentity.inc.php");

  function getDatabase(){
    global $cdb;
    global $dbtype;
    global $dbhost;
    global $dbport;
    global $dbname;
    global $dbuser;
    global $dbpass;
    global $dbavailable;
    if(is_null($cdb)){
      $cdb = new CDB($dbtype, $dbhost, $dbname, $dbuser, $dbpass);
      $cdb->connect(__FILE__.":".__LINE__);
      if(!$cdb->bConnected){
        $dbavailable = false;
      }
    }
    return $cdb;
  }

  function closeDatabase(){
    global $cdb;
    global $dbavailable;
    if($cdb != null && $cdb->is_connected()){
      $cdb->close();
    }
    $dbavailable = false;
    $cdb = null;
  }

  function sqlsafe($value,$bQuotes = true, $bNull = true){
    if(is_null($value) || strlen(trim($value)) == 0){
      if($bNull) return "NULL";
      else $value = '';
    }
    $value = str_replace("'","''",quotemeta($value));
    if($bQuotes)
      $value = "'".$value."'";
    return $value;
  }

  function printMaxlength(){
    $query = sprintf("show tables");
    $db = getDatabase();
    $rs = $db->query($query,__FILE__.":".__LINE__);
    while($rs && $row = $db->fetch_row($rs)){
      print("//Table ".$row[0]."\n");
      $query = "select * from ".$row[0]." limit 1";
      $rs2 = $db->query($query,__FILE__.":".__LINE__);
      $fcount = mysql_num_fields($rs2);
      for($i = 0; $i < $fcount; $i++){
        $type = mysql_field_type($rs2, $i);
        $name = mysql_field_name($rs2, $i);
        $len = mysql_field_len($rs2, $i);
        $flags = mysql_field_flags($rs2, $i);
        $s = "\"".$row[0].".".$name."\" => array(".$len.",\"\"),\n";
        print($s);
      }
    }
    $db->free_result($rs);
  }
?>