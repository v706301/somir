<?php
ini_set("include_path", ini_get("include_path") . ':' . __DIR__ . "/protected/include");
require_once("Localize.class.php");
require_once("PhotoAlbum.class.php");
require_once("CactiStatus.class.php");
require_once("Services_JSON.class.php");
require_once("ShoppingCart.class.php");
require_once("UserPreferences.class.php");
require_once("common.inc.php");

//$prefs = new UserPreferences();
$data = null;
$json = new Services_JSON();
try {
    $list = PhotoAlbum::dbReadPhotosRandom(30);
    $data = $json->encode($list);
} catch (Exception $e) {
    $data = $json->encode(array("error" => $e->getMessage()));
}

header("Expires: Mon, 1 Jan 1970 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Pragma: no-cache"); // HTTP/1.0
header("Content-Type: text/json");
print($data);
flush();
?>
