<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("http.inc.php");
  require_once("common.inc.php");
  $alert = getAlert();
  clearAllSessionVars();
  session_start();
  setAlert($alert);
  setSessionVar("LOGOFF",true);
  setcookie(COOKIE_NAME,"",0);
  header("Location: /admin/index.php");
?>
