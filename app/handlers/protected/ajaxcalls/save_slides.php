<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("Localize.class.php");
  require_once("PhotoAlbum.class.php");
  require_once("Services_JSON.class.php");
  require_once("common.inc.php");

  $data = null;
  $json = new Services_JSON();
  if(isParameter("photos")){
    setSessionVar("slideshow-photos",getParameter("photos"));
    setSessionVar("avail-width",getParameter("aw"));
    setSessionVar("avail-height",getParameter("ah"));
    $data = $json->encode(array("success" => "Список фото для слайдшоу инициализирован"));
  }
  else{
    $data = $json->encode(array("error" => "Отсутствует необходимый параметр - список фото для слайдшоу"));
  }

  header("Expires: Mon, 1 Jan 1970 00:00:00 GMT");
  header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
  header("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
  header("Pragma: no-cache");                          // HTTP/1.0
  print($data);
  flush();
?>
