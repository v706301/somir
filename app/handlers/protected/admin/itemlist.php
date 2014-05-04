<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("http.inc.php");
  require_once("misc.inc.php");
  require_once("pagination.inc.php");
  require_once("Currency.class.php");
  require_once("User.class.php");
  require_once("Item.class.php");
  require_once("common.inc.php");

  if(!User::isLoggedIn())
    redirect("/index.php" );
  if(!User::isOperatorAdmin(User::getOperatorId()))
    redirect("/index.php");

  $_SORT_COLUMNS = array(
    "i.genus,i.species,i.variety,i.cultivar" => "a",
    "i.price" => "a",
  );
  require_once("sort.inc.php");
  setSortOrder();

  if(isCommand("search") || isCommand("csv")){
    setSessionVar(SELF . "_search_item", getParameter("search_item"));
    setSessionVar(SELF . "_search_showfilter1", getParameter("showfilter1"));
    setSessionVar(SELF . "_search_showfilter2", getParameter("showfilter2"));
    setSessionVar(SELF . "_search_showfilter3", getParameter("showfilter3"));
    setSessionVar(SELF . "_search_showfilter4", getParameter("showfilter4"));
    setSessionVar(SELF . "_search_showfilter5", getParameter("showfilter5"));
  }

  if(isCommand("csv")){
    $db = getDatabase();
    $search_item = getSessionVar(SELF . "_search_item", false);
    $showfilter1 = getSessionVar(SELF . "_search_showfilter1", false);
    $showfilter2 = getSessionVar(SELF . "_search_showfilter2", false);
    $showfilter3 = getSessionVar(SELF . "_search_showfilter3", false);
    $showfilter4 = getSessionVar(SELF . "_search_showfilter4", false);
    $showfilter5 = getSessionVar(SELF . "_search_showfilter5", false);
    $items_per_page = -1;
    $rs = Item::searchItems($search_item,$showfilter1,$showfilter2,$showfilter3,$showfilter4,$showfilter5);
    $s = generateCSVFile(
      array(
      "id" =>  "ID",
      "type" =>  "Растение(1) или семена(0)",
      "family" =>   "Семейство",
      "genus" =>   "Род",
      "species" =>   "Вид",
      "variety" =>   "Варитет",
      "name" =>   "Полное название",
      "price" =>   "Цена",
      "package_qty" => "Количество",
      ), 
      $rs);
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=\"catalog.csv\""); 
    print("\xEF\xBB\xBF");
    print($s);
    exit;
  }

  savePaginationVars($items_per_page);
  checkSafePageRefresh();

  $db = getDatabase();
  $search_item = getSessionVar(SELF . "_search_item", false);
  $showfilter1 = getSessionVar(SELF . "_search_showfilter1", false);
  $showfilter2 = getSessionVar(SELF . "_search_showfilter2", false);
  $showfilter3 = getSessionVar(SELF . "_search_showfilter3", false);
  $showfilter4 = getSessionVar(SELF . "_search_showfilter4", false);
  $showfilter5 = getSessionVar(SELF . "_search_showfilter5", false);
  $total = Item::getSearchItemCount($search_item,$showfilter1,$showfilter2,$showfilter3,$showfilter4,$showfilter5);
  setPaginationVars($items_per_page,$total);
  $rs = Item::searchItems($search_item,$showfilter1,$showfilter2,$showfilter3,$showfilter4,$showfilter5);
  
  
  if(basename($_SERVER["SCRIPT_FILENAME"]) == "itemlist.php")
    $page_title = "Список(прайс-лист)";
  require_once("html_header.inc.php");
?>
<table style="width:100%;" cellspacing="0" cellpadding="0">
  <tr><td style="text-align:left;" class="pagename"><?php print($page_title)?></td></tr>
  <tr>
    <td style="text-align:left;padding:10px;">
      <div style="text-align:-moz-left;">
      <table style="padding:5px;" cellspacing="0" cellpadding="0">
        <tr>
          <td style="width:700px;">
<form id="itemlist_form" action="">
            <table cellspacing="0" cellpadding="0">
              <tr>
                <td style="vertical-align:top;padding:5px 0px 5px 0px;text-align:left;"><input type="hidden" name="command" value="search" /><button onclick="window.location.href='/admin/item.php';" type="button" style="width:130px;" class="button">Добавить новое<br />растение</button></td>
                <td style="vertical-align:top;padding:5px 0px 5px 0px;text-align:left;"><button onclick="this.form.elements['command'].value='csv'; this.form.submit(); this.form.elements['command'].value='search';" type="button" style="width:140px;" class="button">Сгрузить<br />весь список</button></td>
              </tr>
            </table>
</form>
<?php require_once("ItemSearchForm.inc.php")?>
<?php
  $i = $start_row;
  $bTableOpened = false;
  while($rs && $row = $db->fetch_assoc($rs)){
    if(!$bTableOpened){
      $bTableOpened = true;
?>
            <table style="margin-top:20px;width:700px;" class="mainFont mainColor L" cellspacing="0" cellpadding="2">
              <tr class="subtitle ucase">
                <td style="width:15px;text-align:center;" class="thl"><?php sreset("#")?></td>
                <td style="width:25px;text-align:left;" class="th">&nbsp;</td>
                <td style="width:400px;text-align:left;" class="th <?php sstyle(0,"so-selected","");?>"><?php slink(0,"Название")?></td>
                <td style="width:100px;text-align:left;" class="th">Размер/<br />Кол-во семян</td>
                <td style="width:40px;text-align:left;" class="th <?php sstyle(1,"so-selected","");?>"><?php slink(1,"Цена")?></td>
                <td style="width:100px;text-align:left;" class="th">Фото</td>
                <td style="width:20px;text-align:left;" class="th">&nbsp;</td>
              </tr>
<?php
    }
?>
              <tr>
                <td style="text-align:right;" class="tdl vtop"><?php print($i+1)?>.</td>
                <td style="text-align:center;" class="td vtop">
                  <img alt="<?php print($row["type"]==0?"Семена":"Растение")?>" title="<?php print($row["type"]==0?"Семена":"Растение")?>" style="border:none;" src="/images/<?php print($row["type"]==0?"seed-sign.png":"plant-sign.png")?>" />
                  <?php if($row["available"]==0){?><img alt="НЕТ В НАЛИЧИИ" title="НЕТ В НАЛИЧИИ" src="/images/notavailable.png" /><?php }?>
                  <?php if(strlen($row["feature1"])>0){?><img alt="Новый" title="Новый" src="/images/newitem.png" /><?php }?>
                </td>
                <td style="text-align:left;" class="td vtop"><?php print(Item::formatDescription($row["name"],$row["description"]))?></td>
                <td style="text-align:right;" class="td vtop rpad10"><?php print(Item::formatSizeOrQuantity($row["type"],$row["size"],$row["package_qty"]))?></td>
                <td style="text-align:left;" class="td vtop"><?php printf("%.2f",$row["price"])?>&nbsp;<?php hprint(Currency::get(getSetting(SETTINGS_KEY_CURRENCY,CURRENCY_UA)))?></td>
                <td style="text-align:center;" class="td vtop"><?php if(isset($row["photo_id"])){?><a style="margin:1px;" onclick="<?php print(jswindow("/itemphotopopup.php?photo_id=".$row["photo_id"]))?>" href="#"><img alt="<?php hprint($row["name"])?>" src="/itemthumb.php?photo_id=<?php print($row["photo_id"]);?>" style="border:none;padding:0px;margin:0px;width:<?php printf("%d",$row["thumb_width"])?>px;height:<?php printf("%d",$row["thumb_height"])?>px;" /></a><?php }else{?>&nbsp;<?php }?></td>
                <td style="text-align:center;" class="td vtop"><a class="mainFont mainColor L" href="/admin/item.php?item_id=<?php printf("%d",$row["id"])?>"><img alt="Редактировать" style="border:none;" src="/images/edit.gif"/></a></td>
              </tr>
<?php
    $i++;
  }
  $db->free_result($rs);
  if($bTableOpened){
?>
            </table>
<?php
  }
?>
<?php if($items_per_page > 0){?>
<?php printPaginationControl($_SERVER["PHP_SELF"]);?>
<?php }?>
          </td>
        </tr>
      </table>
      </div>
    </td>
  </tr>
</table>
<?php require_once("html_footer.inc.php");?>
  