<?php
  ini_set("include_path", ini_get("include_path") . ":" . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("User.class.php");
  require_once("common.inc.php");

  $photoalbum_id = getParameter("photoalbum_id");
  if(is_null($photoalbum_id))
    exit;
  $db = getDatabase();
  $query = sprintf("select thumb,thumb_type,thumb_size from ".$dbprefix."_photoalbum where id=%d",$photoalbum_id);
  $rs = $db->query($query,__FILE__.":".__LINE__);
  if(!$rs)
    exit;
  $row = $db->fetch_assoc($rs);
  $db->free_result($rs);  
  if(is_null($row["thumb"]))
    exit;
  header("Content-Type: ".$row["thumb_type"]);
  header("Content-Length: ".$row["thumb_size"]);
  print($row["thumb"]);
  exit;
?>