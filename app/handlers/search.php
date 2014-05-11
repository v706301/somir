<?php
ini_set("include_path", ini_get("include_path") . ':' . __DIR__ . "/protected/include");
require_once("Localize.class.php");
require_once("PhotoAlbum.class.php");
require_once("CactiStatus.class.php");
require_once("Services_JSON.class.php");
require_once("ShoppingCart.class.php");
require_once("UserPreferences.class.php");
require_once("common.inc.php");

$json = new Services_JSON();
if (isset($_GET['text'])) {
    $limit = false;
    if (isset($_GET['limit'])) {
        if ($_GET['limit'] > 0) {
            $limit = $_GET['limit'];
        }
    }
    try {
        $list = PhotoAlbum::dbSearchPhotos($_GET['text'], 'name', $limit);
        $data = array();
        foreach ($list as $row) {
            $data[] = array(
                'image' => '/handlers/photo.php?photo_id=' . $row['id'],
                'text'  => $row['photo_desc'] ? $row['photo_desc'] : $row['photo_name'],
                'album_id' => $row['photoalbum_id'],
                'album_name' => $row['name'],
            );
        }
        header("Expires: Mon, 1 Jan 1970 00:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        header("Pragma: no-cache"); // HTTP/1.0
        header("Content-Type: text/json");
        echo $json->encode($data);
    } catch (Exception $e) {
        header ('HTTP/1.1 500 Server Error');
        echo $e->getMessage();
    }
} else {
    echo $json->encode(array());
}
