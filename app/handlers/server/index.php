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
      "size" =>   "Размер",
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
  
  $show_menu = false;
  if(basename($_SERVER["SCRIPT_FILENAME"]) == "index.php")
    $page_title = "Кактусы и суккуленты из Харькова";
  
  $prefs = new UserPreferences();
  $scart = getSessionVar(SHOPPING_CART_KEY,false);
  if(!is_null($scart)){
    if($scart->getTotal($prefs->getCurrencyCoef()) <= 0.0){
      clearSessionVar(SHOPPING_CART_KEY);
      $scart = null;
    }
  }
  
  //trace_r($_SESSION);
  
  addJavaScript2HtmlHeader("/js/awt.js");
  addJavaScript2HtmlHeader("/js/ajax.js.php");
  require_once("html_header.inc.php");
?>
<script type="text/javascript">
  function ajcall_SetSelected(el,item_id){
    var monitor,monitorId = null,responseData = null;
    var counter = 0;
    var href = document.getElementById("idShoppingCart");
    var img = document.getElementById("idShoppingCartImg");
    var imgSrc = img.src;
    img.src = "/images/loading26.gif";
    var req = createRequest();
    if(req != null){
      req.onreadystatechange = function(){
        if(req.readyState == 4){
          clearInterval(monitorId);
          if(req.status != 200){
            alert("Не удалось связаться с сервером для добавления/удаления элемента\r\n"+req.status);
            el.checked = !el.checked;
          }
          else{
            if(req.responseText.substring(0,1) != "{")
              alert(req.responseText);
            else{
              var result = eval("("+req.responseText+")");
              if(typeof(result["setselected_error"]) == 'string'){
                img.src = imgSrc;
                alert(result["setselected_error"]);
              }
              else{
                if(result["total"] == ""){
                  img.src = "/images/basket-empty.png";
                  img.title = "В тележке пусто.";
                  e$('scart-menuitem').style.display = 'none';
                  e$('ckout-menuitem').style.display = 'none';
                }
                else{
                  img.src = "/images/basket-full.png";
                  img.title = result["total"];
                  e$('scart-menuitem').style.display = 'inline';
                  e$('ckout-menuitem').style.display = 'inline';
                }
              }
            }
          }
        }
      };
    }
    monitor = function(){
      counter++;
      if(counter > 10){
        img.src = imgSrc;
        req.abort();
        clearInterval(monitorId);
        alert("Не удалось связаться с сервером для добавления/удаления элемента\r\nПроцедура прервана");
      }
    };
    monitorId = setInterval(monitor,1000);
    requestStr = "/ajaxcalls/setselected.php?item_id="+item_id+"&selected="+(el.checked ? 1:0);
    sendRequest(req,requestStr,"");
  }
</script>
<table style="width:100%;" cellspacing="0" cellpadding="0">
  <tr>
    <td style="padding:10px;">
      <div style="margin-left:0">
      <table style="padding:5px;" cellspacing="0" cellpadding="0">
        <tr>
          <td style="width:700px;">
<?php require_once("ItemSearchForm.inc.php")?>
<?php
  $i = $start_row;
  $bTableOpened = false;
  while($rs && $row = $db->fetch_assoc($rs)){
    if(!$bTableOpened){
      $bTableOpened = true;
?>
<form id="select_item_form" action="" method="get">
            <table style="width:700px;" class="mainFont mainColor L" cellspacing="0" cellpadding="2">
              <tr class="subtitle ucase">
                <td style="width:15px;text-align:center;" class="thl"><?php sreset("#")?></td>
                <td style="width:25px;text-align:left;" class="th">&nbsp;</td>
                <td style="width:400px;text-align:left;" class="th <?php sstyle(0,"so-selected","");?>"><?php slink(0,htmlize('Название'))?></td>
                <td style="width:100px;text-align:center;" class="th"><?php hprint('Размер/Кол-во семян')?></td>
                <td style="width:40px;text-align:left;" class="th <?php sstyle(1,"so-selected","");?>"><?php slink(1,htmlize('Цена'))?></td>
                <td style="width:100px;text-align:left;" class="th"><?php hprint('Фото')?></td>
                <td style="width:20px;text-align:left;" class="th">&nbsp;</td>
              </tr>
<?php
    }
?>
              <tr>
                <td style="text-align:right;" class="tdl vtop"><?php print($i+1)?>.</td>
                <td style="text-align:center;" class="td vtop">
                  <img alt="<?php hprint($row["type"]==0?"Семена":"Растение")?>" title="<?php hprint($row["type"]==0?"Семена":"Растение")?>" style="border:none;" src="/images/<?php print($row["type"]==0?"seed-sign.png":"plant-sign.png")?>" />
                  <?php if($row["available"]==0){?><img alt="<?php hprint('НЕТ В НАЛИЧИИ')?>" title="<?php hprint('НЕТ В НАЛИЧИИ')?>" src="/images/notavailable.png" /><?php }?>
                  <?php if(strlen($row["feature1"])>0){?><img alt="Новый" title="<?php hprint('Новый')?>" src="/images/newitem.png" /><?php }?>
                </td>
                <td style="text-align:left;" class="td vtop"><?php print(Item::formatDescription($row["name"],$row["description"]))?></td>
                <td style="text-align:right;" class="td vtop rpad10"><?php print(Item::formatSizeOrQuantity($row["type"],$row["size"],$row["package_qty"]))?></td>
                <td style="text-align:left;" class="td vtop"><?php print(Item::formatPrice($row["price"],$prefs->getCurrencyCoef(),$prefs->getCurrencyLabel()));?></td>
                <td style="text-align:center;" class="td vtop"><?php if(isset($row["photo_id"])){?><a style="margin:1px;" onclick="<?php print(jswindow("/itemphotopopup.php?photo_id=".$row["photo_id"]))?>;return false;" href="<?php print(mkurl('/itemphotopopup.php',array('photo_id'=>$row["photo_id"],'n'=>$row["name"],'f'=>$row["family"])))?>"><img alt="<?php hprint($row["name"])?>" src="/itemthumb.php?photo_id=<?php print($row["photo_id"]);?>" style="border:none;padding:0px;margin:0px;width:<?php printf("%d",$row["thumb_width"])?>px;height:<?php printf("%d",$row["thumb_height"])?>px;" /></a><?php }else{?>&nbsp;<?php }?></td>
                <td style="text-align:center;" class="td vtop"><input<?php if($scart && $scart->isEntry($row["id"])){?> checked="checked"<?php }?> title="<?php hprint('Добавить в корзину')?>" type="checkbox" name="ck<?php printf("%d",$row["id"])?>" onclick="ajcall_SetSelected(this,<?php printf("%d",$row["id"])?>)" /></td>
              </tr>
<?php
    $i++;
  }
  $db->free_result($rs);
  if($bTableOpened){
?>
            </table>
</form>
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
