<?php
  ini_set("include_path", ini_get("include_path") . ":" . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("User.class.php");
  require_once("common.inc.php");

  $photo_id = getParameter("photo_id");
  if(is_null($photo_id))
    exit;
  $db = getDatabase();
  $query = sprintf("select photo,photo_type,photo_size,is_hidden from ".$dbprefix."_photo where id=%d",$photo_id);
  $rs = $db->query($query,__FILE__.":".__LINE__);
  if(!$rs)
    exit;
  $row = $db->fetch_assoc($rs);
  $db->free_result($rs);  
  if(is_null($row["photo"]))
    exit;
  $bShow = true;
  if($row["is_hidden"] == 1){
    if(!(User::isLoggedIn() && User::isOperatorAdmin(User::getOperatorId())))
      $bShow = false;
  }
  if($bShow){
    header("Content-Type: ".$row["photo_type"]);
    header("Content-Length: ".$row["photo_size"]);
    print($row["photo"]);
  }
  exit;
?>