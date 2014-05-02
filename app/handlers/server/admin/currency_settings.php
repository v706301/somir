<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("settings.inc.php");
  require_once("Currency.class.php");
  require_once("User.class.php");
  require_once("common.inc.php");
  
  if(!User::isOperatorBuiltInAdmin()){
    User::logoff();
  }

  $command = getParameter("command");
  if(isCommand("reset_currency")){
    $cur = new Currency();
    if($cur->httpReadCurrency()){
      $cur->save();
    }
  }
  else if(isCommand("set")){
    $cur = new Currency();
    if($cur->httpReadCoefs()){
      $cur->save();
    }
  }

  checkSafePageRefresh();
  $cur = new Currency();
  if(isSessionVar(SELF."_errorinfo")){
    $einfo = getSessionVar(SELF."_errorinfo");
    $cua = $einfo["cua"];
    $cus = $einfo["cus"];
    $cru = $einfo["cru"];
  }
  else{
    $cua = $cur->getCoef(CURRENCY_UA) > 0.0 ? sprintf("%.4f",$cur->getCoef(CURRENCY_UA)) : "";
    $cus = $cur->getCoef(CURRENCY_US) > 0.0 ? sprintf("%.4f",$cur->getCoef(CURRENCY_US)) : "";
    $cru = $cur->getCoef(CURRENCY_RU) > 0.0 ? sprintf("%.4f",$cur->getCoef(CURRENCY_RU)) : "";
  }
  
  addJavaScript2HtmlHeader("/js/awt.js");
  $page_title = "Денежные единицы";
  require_once("html_header.inc.php");
?>
<form id="settings_form" method="post" action="<?php print($_SERVER["PHP_SELF"])?>">
<table width="100%" cellspacing="0" cellpadding="0">
  <tr><td style="text-align:left;" class="pagename"><?php hprint($page_title)?></td></tr>
  <!-- tr><td style="text-align:left;" class="subtitle">&nbsp;</td></tr -->
  <tr>
    <td>
      <table cellspacing="0" cellpadding="0">
        <tr>
          <td>
  <input type="hidden" name="command" value="set" />
            <table style="width:100%;" cellspacing="5" cellpadding="0">
              <tr>
                <td style="text-align:right;white-space:nowrap;" class="mainFont L rpad5">Денежная единица прайс-листа:</td>
                <td>
  <select onchange="if(confirm('При смене денежной единицы все коэффициенты будут сброшены.Продолжить?')){this.form.elements['command'].value='reset_currency';this.form.submit();}" name="currency" class="select">
<?php
  $map = Currency::getLabelMap();
  foreach($map as $key => $val){
    if($key == $cur->getSelection())
      $selected = " selected=\"selected\"";
    else
      $selected = "";
?>
    <option<?php print($selected);?> value="<?php hprint($key);?>"><?php hprint($val);?></option>
<?php
  }
?>
  </select>
                </td>
              </tr>
<?php
  if($cur->getSelection() != CURRENCY_UA){
?>
              <tr>
                <td style="text-align:right;white-space:nowrap;" class="mainFont L rpad5">Коэффициент перевода <?php hprint($cur->getSelectionLabel())?> в <?php hprint(Currency::getLabel(CURRENCY_UA))?>:</td>
                <td width="400">
  <input type="text" name="cua" class="input" value="<?php print($cua)?>" />
                </td>
              </tr>
<?php
  }
?>
<?php
  if($cur->getSelection() != CURRENCY_US){
?>
              <tr>
                <td style="text-align:right;white-space:nowrap;" class="mainFont L rpad5">Коэффициент перевода <?php hprint($cur->getSelectionLabel())?> в <?php hprint(Currency::getLabel(CURRENCY_US))?>:</td>
                <td width="400">
  <input type="text" name="cus" class="input" value="<?php print($cus)?>" />
                </td>
              </tr>
<?php
  }
?>
<?php
  if($cur->getSelection() != CURRENCY_RU){
?>
              <tr>
                <td style="text-align:right;white-space:nowrap;" class="mainFont L rpad5">Коэффициент перевода <?php hprint($cur->getSelectionLabel())?> в <?php hprint(Currency::getLabel(CURRENCY_RU))?>:</td>
                <td width="400">
  <input type="text" name="cru" class="input" value="<?php print($cru)?>" />
                </td>
              </tr>
<?php
  }
?>
              <tr>
                <td style="text-align:left;vertical-align:top;" colspan="2">
  <button type="submit" name="submit_btn" class="button">Сохранить<br />настройки</button>
  <button type="button" class="button" onclick="this.form.reset(); return false;" name="reset_btn">Сброс<br />изменений</button>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>
<?php require_once("html_footer.inc.php");?>
