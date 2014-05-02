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

  $scart = getSessionVar(SHOPPING_CART_KEY,false);
  if($scart){
  	header('Content-type: text/plain');
  	print_r($scart);
  	if(isParameter('clear')){
  		clearSessionVar(SHOPPING_CART_KEY);
  		echo "\nShopping cart cleared";
  	}
  }
  else
  	echo 'No scart';
