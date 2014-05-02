<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("http.inc.php");
  require_once("misc.inc.php");
  require_once("pagination.inc.php");
  require_once("Currency.class.php");
  require_once("Item.class.php");
  require_once("Localize.class.php");
  require_once("ShoppingCart.class.php");
  require_once("ShoppingCartEntry.class.php");
  require_once("UserPreferences.class.php");
  require_once("CactiStatus.class.php");
  require_once("common.inc.php");

  $prefs = new UserPreferences();
  $scart = getSessionVar(SHOPPING_CART_KEY,false);
  if(is_null($scart) || $scart->getTotal($prefs->getCurrencyCoef()) == 0.0){
    clearSessionVar(SHOPPING_CART_KEY);
    redirect("/index.php");
  }

  if(isCommand("remove")){
    $cmd = getParameter("command");
    $arr = explode(":",$cmd);
    if(count($arr) > 1){
      for($i = 0; $i < count($arr); $i++){
        //if(is_numeric($arr[$i]))
        $scart->removeEntry($arr[$i]);
      }
      setSessionVar(SHOPPING_CART_KEY,$scart);
    }
  }
  else if(isCommand("removeall")){
    clearSessionVar(SHOPPING_CART_KEY);
    setAlert("Корзинка очищена");
  }
  else if(isCommand("recalculate") || isCommand("proceed")){
    $keys = array_keys($_POST);
    for($i = 0; $i < count($keys); $i++){
      if(strncmp($keys[$i],"quantity",strlen("quantity")) == 0){
        $item_id = substr($keys[$i],strlen("quantity"));
        $entry = $scart->getEntry($item_id);
        $quantity = intval(getParameter($keys[$i]));
        if($quantity < 0)
          $quantity = 0;
        if($quantity == 0)
          $scart->removeEntry($item_id);
        else{
          $entry->setQuantity($quantity);
          $scart->addEntry($entry);
        }
      }
    }
    setSessionVar(SHOPPING_CART_KEY,$scart);
    if(isCommand("proceed"))
      redirect("/ckout.php");
  }
//  else if(isCommand("csv")){
//    $scart->export2Csv($prefs->getCurrencyCoef(),$prefs->getCurrencyLabel());
//  }
//  else if(isCommand("pdf")){
//    require_once("ReportScart.class.php");
//    $report = new ReportScart();
//    $report->create($scart,$prefs);
//    $report->Output("zakaz-miroshnichenko.pdf",'D');
//  }

  savePaginationVars($items_per_page);
  checkSafePageRefresh();

  $show_menu = false;
  $page_title = "Корзинка";
  addJavaScript2HtmlHeader("/js/form.js.php");
  require_once("html_header.inc.php");
?>
<script type="text/javascript">
<!--
  function ck(form){
    if(form.elements["command"].value != "csv" && form.elements["command"].value != "pdf")
      disableButtons(form);
    return true;
  }
  
  function ckRemove(form,id){
		if(confirm('<?php hprint('Удалить из заказа?')?>')){
			setCommand(form,'remove',true);
			submitForm(form,id,false);
			return true;
		}    
		return false;
  }
//-->
</script>
<form id="ck_mod_shopping_cart_form" action="" method="post" onsubmit="return ck(this)">
<table style="width:100%;" cellspacing="0" cellpadding="0">
  <tr>
    <td style="padding:10px;">
  <input type="hidden" name="command" value="TTT" />
      <div style="margin-left:0">
      <table style="padding:5px;" cellspacing="0" cellpadding="0">
        <tr>
          <td style="width:700px;">
            <table style="width:700px;" class="mainFont mainColor L" cellspacing="0" cellpadding="2">
              <tr class="subtitle ucase">
                <td style="width:15px;text-align:center;" class="thl">№</td>
                <td style="width:25px;text-align:left;" class="th">&nbsp;</td>
                <td style="width:380px;text-align:left;" class="th"><?php hprint('Название')?></td>
                <td style="width:100px;text-align:center;" class="th"><?php hprint('Размер/Кол-во семян')?></td>
                <td style="width:40px;text-align:left;" class="th"><?php hprint('Цена')?></td>
                <td style="width:100px;text-align:left;" class="th"><?php hprint('Фото')?></td>
                <td style="width:20px;text-align:left;" class="th"><?php hprint('Заказ(шт)')?></td>
                <td style="width:50px;text-align:left;" class="th"><?php hprint('Сумма')?></td>
                <td style="width:20px;text-align:left;" class="th">&nbsp;</td>
              </tr>
<?php
  $keys = $scart->getEntryKeys();
  for($i = 0; $i < count($keys); $i++){
    $entry = $scart->getEntry($keys[$i]);
    $item = $entry->getItem();
    if($entry->isItem()){
			$inputPieces = <<<_X_
<input name="quantity{$entry->getId()}" value="{$entry->getQuantity()}" style="width:30px;" onkeypress="this.form.elements['proceed_button'].disabled=true;this.form.elements['recalculate_button'].disabled=false;" type="text" />
_X_;
    }
    else if($entry->isPhoto()){
			$inputPieces = <<<_X_
1 шт<input name="quantity{$entry->getId()}" value="1" type="hidden" />
_X_;
    }
?>
              <tr>
                <td style="text-align:right;" class="tdl vtop"><?php print($i+1)?>.</td>
                <td style="text-align:left;" class="td vtop"><?php echo $entry->scartIcon()?></td>
                <td style="text-align:left;" class="td vtop"><?php echo $entry->scartName()?></td>
                <td style="text-align:right;" class="td vtop rpad10"><?php echo $entry->scartDetails()?></td>
                <td style="text-align:left;" class="td vtop"><?php echo $entry->scartPrice($prefs)?></td>
                <td style="text-align:center;" class="td vtop"><?php echo $entry->scartPhoto()?></td>
                <td style="text-align:center;" class="td vtop"><?php echo $inputPieces?></td>
                <td style="text-align:left;" class="td vtop"><?php print($entry->formatSubtotal($prefs->getCurrencyCoef(),$prefs->getCurrencyLabel()));?></td>
                <td style="text-align:center;" class="td vtop"><button type="submit" onclick="return ckRemove(this.form,'<?php echo $entry->getId()?>')" name="remove_item_id<?php echo $entry->getId()?>" style="cursor:pointer;width:32px;height:32px;border:none;background-color:transparent;"><img alt="Удалить" style="border:none;padding:0px;margin:0px;" src="/images/delete1.gif" /></button></td>
              </tr>
<?php
  }
?>
              <tr>
                <td class="tdl vtop">&nbsp;</td>
                <td colspan="8" style="text-align:right;" class="td vtop"><?php hprint($scart->formatLegend($prefs->getCurrencyCoef(),$prefs->getCurrencyLabel()))?></td>
              </tr>
            </table>
            <table style="width:700px;padding-top:20px;" class="mainFont mainColor L" cellspacing="0" cellpadding="2">
              <tr>
                <td style="width:25%;text-align:center;" class="vtop">
  <button type="button" onclick="onPageUnload(this.form,'/index.php')" style="width:150px;" name="back_button"><?php hprint("Перейти к списку\nсемян и сеянцев")?></button>
                </td>
                <td style="width:25%;text-align:center;" class="vtop">
  <button type="submit" onclick="if(confirm('Все удалить?')){this.form.elements['command'].value='removeall';return true;}else{return false;}" style="width:150px;" name="removeall_button"><?php hprint('Удалить все')?></button>
                </td>
                <td style="width:25%;text-align:center;" class="vtop">
  <button disabled="disabled" type="submit" onclick="this.form.elements['command'].value='recalculate'" style="width:150px;" name="recalculate_button"><?php hprint('Пересчитать')?></button>
                </td>
                <td style="width:25%;text-align:center;" class="vtop">
  <button type="submit" onclick="this.form.elements['command'].value='proceed'" style="width:150px;" name="proceed_button"><?php hprint('Далее')?></button>
                </td>
              </tr>
              <tr>
                <td style="width:25%;text-align:center;" class="vtop">
  <button type="button" onclick="onPageUnload(this.form,'/photoshop.php')" style="width:150px;" name="back_button"><?php hprint("Перейти к списку\nвзрослых растений")?></button>
                </td>
                <td style="width:25%;text-align:center;" class="vtop"></td>
                <td style="width:25%;text-align:center;" class="vtop"></td>
                <td style="width:25%;text-align:center;" class="vtop"></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
      </div>
    </td>
  </tr>
</table>
</form>
<?php require_once("html_footer.inc.php");?>
  