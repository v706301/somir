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
  require_once("common.inc.php");

  $prefs = new UserPreferences();
  $scart = getSessionVar(SHOPPING_CART_KEY,false);
  if(is_null($scart) || $scart->getTotal($prefs->getCurrencyCoef()) == 0.0){
    clearSessionVar(SHOPPING_CART_KEY);
    redirect("/index.php");
  }

  function setProfileData(){
    setSessionVar(SELF."_cname",getParameter("cname"));
    setSessionVar(SELF."_cemail",getParameter("cemail"));
    setSessionVar(SELF."_cphone",getParameter("cphone"));
    setSessionVar(SELF."_caddress",getParameter("caddress"));
  }

	if(isParameter('command'))
    setProfileData();

  if(isCommand("backto_list")){
    redirect("/index.php");
  }
  else if(isCommand("backto_scart")){
    redirect("/scart.php");
  }
  else if(isCommand("csv")){
    $scart->export2Csv(getSessionVar(SELF."_cname",false),getSessionVar(SELF."_cemail",false),getSessionVar(SELF."_cphone",false),getSessionVar(SELF."_caddress",false),$prefs);
  }
  else if(isCommand("pdf")){
    require_once("ReportScart.class.php");
    $report = new ReportScart();
    $report->create($scart,$prefs,getSessionVar(SELF."_cname",false),getSessionVar(SELF."_cemail",false),getSessionVar(SELF."_cphone",false),getSessionVar(SELF."_caddress",false));
    $report->Output("zakaz-miroshnichenko.pdf",'D');
  }

  savePaginationVars($items_per_page);
  checkSafePageRefresh();

  $show_menu = false;
  $page_title = "Оформить заказ";
  addJavaScript2HtmlHeader("/js/form.js.php");
  require_once("html_header.inc.php");
?>
<script type="text/javascript">
  function ck(form){
    if(form.elements["command"].value != "csv" && form.elements["command"].value != "pdf")
      disableButtons(form);
    return true;
  }
</script>
<form id="ck_mod_ckout_form" action="" method="post" onsubmit="return ck(this)">
<table style="width:100%;" cellspacing="0" cellpadding="0">
  <tr>
    <td style="padding:10px;">
  <input type="hidden" name="command" value="" />
      <div style="margin-left:0">
      <table style="padding:5px;" cellspacing="0" cellpadding="0">
        <tr>
          <td style="width:700px;">
            <table style="width:700px;" class="mainFont mainColor L" cellspacing="0" cellpadding="2">
              <tr><td colspan="2" style="text-align:center;border-bottom:1px solid <?php maincolor()?>" class="XXL"><?php hprint('В корзине')?> <?php hprint($scart->formatLegend($prefs->getCurrencyCoef(),$prefs->getCurrencyLabel()))?></td></tr>
              <tr>
                <td style="padding-top:20px;width:170px;text-align:right;"><?php hprint('Ваши фамилия, имя и отчество')?>:</td>
                <td style="padding-top:20px;width:530px;text-align:left;">
  <input type="text" class="input" name="cname" style="width:350px;" value="<?php hprint(getSessionVar(SELF."_cname",false))?>" />
                </td>
              </tr>
              <tr>
                <td style="text-align:right;"><?php hprint('Ваш email')?>:</td>
                <td style="text-align:left;">
  <input type="text" class="input" name="cemail" style="width:350px;" value="<?php hprint(getSessionVar(SELF."_cemail",false))?>" />
                </td>
              </tr>
              <tr>
                <td style="text-align:right;"><?php hprint('Ваш телефон')?>:</td>
                <td style="text-align:left;">
  <input type="text" class="input" name="cphone" style="width:350px;" value="<?php hprint(getSessionVar(SELF."_cphone",false))?>" />
                </td>
              </tr>
              <tr>
                <td style="text-align:right;vertical-align:top;"><?php hprint("Ваш адрес:\n(не забудьте указать\nпочтовый индекс)")?></td>
                <td style="width:530px;text-align:left;">
  <textarea class="input" rows="1" cols="1" style="width:350px;height:150px;" name="caddress"><?php hprint(getSessionVar(SELF."_caddress",false),false)?></textarea>
                </td>
              </tr>
            </table>
            <table style="width:700px;padding-top:20px;" class="mainFont mainColor L" cellspacing="0" cellpadding="2">
              <tr>
                <td style="width:25%;text-align:center;" class="vtop">
  <button type="submit" onclick="this.form.elements['command'].value='backto_list';return true;" style="width:150px;" name="backtolist_button"><?php hprint("Перейти к списку\nсемян и сеянцев")?></button>
                </td>
                <td style="width:25%;text-align:center;" class="vtop">
  <button type="submit" onclick="this.form.elements['command'].value='backto_scart';return true;" style="width:150px;" name="backtoscart_button"><?php hprint("Вернуться\nв корзинку")?></button>
                </td>
                <td style="width:25%;text-align:center;" class="vtop">
  <button type="submit" onclick="this.form.elements['command'].value='csv'" style="width:150px;" name="csv_button"><?php hprint("Сгрузить список\nвыбранного\nв формате Excel")?></button>
                </td>
                <td style="width:25%;text-align:center;" class="vtop">
  <button type="submit" onclick="this.form.elements['command'].value='pdf'" style="width:150px;" name="pdf_button"><?php hprint("Сгрузить список\nвыбранного\nв формате PDF")?></button>
                </td>
              </tr>
              <tr>
                <td style="width:25%;text-align:center;" class="vtop">
  <button type="submit" onclick="this.form.elements['command'].value='backto_list';return true;" style="width:150px;" name="backtolist_button"><?php hprint("Перейти к списку\nвзрослых растений")?></button>
                </td>
                <td style="width:25%;text-align:center;" class="vtop">
                </td>
                <td style="width:25%;text-align:center;" class="vtop">
                </td>
                <td style="width:25%;text-align:center;" class="vtop">
                </td>
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
  