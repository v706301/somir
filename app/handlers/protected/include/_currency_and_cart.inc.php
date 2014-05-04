<?php
/*
 * Created on Jun 9, 2012
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
  require_once 'UserPreferences.class.php';
  require_once 'ShoppingCart.class.php';
  if(!isset($prefs))
  	$prefs = new UserPreferences();
  if(!isset($scart)){
	  $scart = getSessionVar(SHOPPING_CART_KEY,false);
	  if(!is_null($scart)){
	    if($scart->getTotal($prefs->getCurrencyCoef()) <= 0.0){
	      clearSessionVar(SHOPPING_CART_KEY);
	      $scart = null;
	    }
	  }
  }
  $scart_title = htmlize(is_null($scart) ? "В тележке пусто" : $scart->formatLegend($prefs->getCurrencyCoef(),$prefs->getCurrencyLabel()));
  $scart_img = '/images/basket-'.(is_null($scart) ? "empty":"full").'.png';
?>
<div style="margin:2px auto 0 5px;width:700px;overflow:hidden;">
	<div style="float:left;height:47px;padding-top:20px"><?php $prefs->printCurrencySelector()?></div>
	<div style="float:left;"><a id="idShoppingCart" title="<?php echo $scart_title?>" href="/scart.php" style="height:44px;"><img id="idShoppingCartImg" alt="" style="width:44px;height:44px;border:none;margin:0px;padding:0px;" src="<?php echo $scart_img?>" /></a></div>
	<div style="float:right;height:47px;padding-top:20px"><?php $prefs->printLangSelector()?></div>
</div>
