<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("http.inc.php");
  require_once("common.inc.php");

//  header("Content-Type: text/plain");
//  if(isParameter("lang"))
//    printLangTemplate();
//  else if(isParameter("mxl"))
//    printMaxlength();
//  else
//    printMaxlength();
  print("Forbidden");
?>