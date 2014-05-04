<?php
  $SETTINGS_CACHE = array();
  function isSetting($name){
    global $dbprefix;
    if(is_null($name) || strlen(trim($name)) == 0)
      return false;
    $db = getDatabase();
    $query = sprintf("select count(*) from ".$dbprefix."_settings where  upper(name)=%s",sqlsafe(strtoupper($name)));
    $rs = $db->query($query,__FILE__.":".__LINE__);
    $row = $db->fetch_array($rs);
    $db->free_result($rs);
    return intval($row[0]) > 0;
  }
  function isSettingExposable($name){
    global $dbprefix;
    $value = false;
    if(is_null($name) || strlen(trim($name)) == 0)
      return false;
    $db = getDatabase();
    $query = sprintf("select exposable from ".$dbprefix."_settings where upper(name)=%s",sqlsafe(strtoupper($name)));
    $rs = $db->query($query,__FILE__.":".__LINE__);
    if($rs && $row = $db->fetch_array($rs))
      $value = (intval($row[0]) != 0);
    $db->free_result($rs);
    return $value;
  }
  function getSetting($name,$default_value = null){
    global $dbprefix;
    global $SETTINGS_CACHE;
    $value = null;
    if(is_null($name) || strlen(trim($name)) == 0)
      return null;
    if(isset($SETTINGS_CACHE[$name])){
      $value = $SETTINGS_CACHE[$name];
    }
    else{
      $db = getDatabase();
      $query = sprintf("select val from ".$dbprefix."_settings where upper(name)=%s",sqlsafe(strtoupper($name)));
      $rs = $db->query($query,__FILE__.":".__LINE__);
      if($rs && $row = $db->fetch_array($rs))
        $value = $row[0];
      else
        $value = $default_value;
      $db->free_result($rs);
      $SETTINGS_CACHE[$name] = $value;
    }
    return $value;
  }
  function getSettingTitle($name){
    global $dbprefix;
    $title = null;
    if(is_null($name) || strlen(trim($name)) == 0)
      return null;
    $db = getDatabase();
    $query = sprintf("select title from ".$dbprefix."_settings where  upper(name)=%s",sqlsafe(strtoupper($name)));
    $rs = $db->query($query,__FILE__.":".__LINE__);
    if($rs && $row = $db->fetch_array($rs))
      $title = $row[0];
    $db->free_result($rs);
    return $title;
  }
  function setSetting($name,$title,$value = null){
    global $dbprefix;
    if(is_null($name) || strlen(trim($name)) == 0)
      return false;
    $db = getDatabase();
    if(isSetting($name)){
      if(strlen($title) > 0)
        $query = sprintf("update ".$dbprefix."_settings set title=%s,val=%s where exposable=1 and  name=%s",sqlsafe($title),sqlsafe($value),sqlsafe(strtoupper($name))); 
      else
        $query = sprintf("update ".$dbprefix."_settings set val=%s where name=%s",sqlsafe($value),sqlsafe(strtoupper($name))); 
    }
    else{
      $query = sprintf("insert into ".$dbprefix."_settings(name,title,val, exposable) values(%s,%s,%s, 1)",sqlsafe(strtoupper($name)),sqlsafe($title),sqlsafe($value)); 
    }
    $db->query($query,__FILE__.":".__LINE__);
    $SETTINGS_CACHE[$name] = null;
    unset($SETTINGS_CACHE[$name]);
  }
  
  function clearSettingsCache(){
    global $SETTINGS_CACHE;
    $SETTINGS_CACHE = array();  
  }
?>