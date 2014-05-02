<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("Item.class.php");
  require_once("Localize.class.php");
  require_once("ShoppingCart.class.php");
  require_once("ShoppingCartEntry.class.php");
  require_once("Services_JSON.class.php");
  require_once("UserPreferences.class.php");
  require_once("PhotoAlbum.class.php");
  require_once("common.inc.php");

  $data = null;
  $json = new Services_JSON();
  if(isParameter("item_id") && isParameter("selected")){
    $item_id = getParameter("item_id");
    $selected = intval(getParameter("selected")) != 0 ? true:false;

    $scart = getSessionVar(SHOPPING_CART_KEY);
    if(is_null($scart))
      $scart = new ShoppingCart();

    $item = new Item();
    try{
      $item->dbRead($item_id);
      if($selected){
        $scart->addEntry(new ShoppingCartEntry($item));
      }
      else{
        $scart->removeEntry(ShoppingCartEntry::itemKey($item_id));
      }
      $prefs = new UserPreferences();
      if($scart->getTotal($prefs->getCurrencyCoef()) == 0.0){
        clearSessionVar(SHOPPING_CART_KEY,$scart);
        $data = $json->encode(array("total" => ""));
      }
      else{
        $data = $json->encode(array("total" => $scart->formatLegend($prefs->getCurrencyCoef(),$prefs->getCurrencyLabel())));
        setSessionVar(SHOPPING_CART_KEY,$scart);
      }
    }
    catch(Exception $e){
      $scart->removeEntry(ShoppingCartEntry::itemKey($item_id));
      $data = $json->encode(array("error" => "Наименование №".getParameter("item_id")." в списке отсутствует.\r\n(Возможно, недавно удалено администратором)"));
    }
  }
  else if(isParameter("photo_id") && isParameter("selected")){
	  $photo_id = getParameter("photo_id");
    $selected = intval(getParameter("selected")) != 0 ? true:false;

    $scart = getSessionVar(SHOPPING_CART_KEY);
    if(is_null($scart))
      $scart = new ShoppingCart();

    try{
	    $photo = PhotoAlbum::dbGetPhoto($photo_id);
	    if(!$photo)
	    	throw new Exception("Растение, изображенное на фото №{$photo_id}, не найдено");
      if($selected){
        $scart->addEntry(new ShoppingCartEntry($photo));
      }
      else{
        $scart->removeEntry(ShoppingCartEntry::photoKey($photo_id));
      }
      $prefs = new UserPreferences();
      if($scart->getTotal($prefs->getCurrencyCoef()) == 0.0){
        clearSessionVar(SHOPPING_CART_KEY,$scart);
        $data = $json->encode(array("total" => ""));
      }
      else{
        $data = $json->encode(array("total" => $scart->formatLegend($prefs->getCurrencyCoef(),$prefs->getCurrencyLabel())));
        setSessionVar(SHOPPING_CART_KEY,$scart);
      }
    }
    catch(Exception $e){
      $scart->removeEntry(ShoppingCartEntry::photoKey($photo_id));
      $data = $json->encode(array("error" => sprintf(lang("Растение, изображенное на фото №%d не найдено.\r\n(Возможно, уже продано или зарезервировано)"),getParameter("item_id"))));
    }
  }
  else{
    $data = $json->encode(array("error" => "Unknown call type"));
  }

  header("Expires: Mon, 1 Jan 1970 00:00:00 GMT");
  header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
  header("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
  header("Pragma: no-cache");                          // HTTP/1.0
  print($data);
  flush();
