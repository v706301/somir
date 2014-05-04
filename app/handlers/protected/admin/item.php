<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("Currency.class.php");
  require_once("User.class.php");
  require_once("Item.class.php");
  require_once("PlantFamily.class.php");
  require_once("http.inc.php");
  require_once("common.inc.php");
  
  if(!User::isLoggedIn())
    redirect("/index.php");
  if(!User::isOperatorAdmin())
    redirect("/index.php");

  $item_id = getParameter("item_id");
  $db = getDatabase();
  $item = null;
  $result = false;
  if(isCommand("add")){
    $item = new Item();
    $item->httpRead();
    if($item->isValid() && $item->dbInsert())
      redirect("/admin/item.php");
    else{
      setSessionVar(SELF."_uo",$item);
      checkSafePageRefresh("uo=1");
    }
  }
  else if(isCommand("update")){
    $item = new Item();
    $item->httpRead();
    if($item->isValid() && $item->dbUpdate())
      redirect("/admin/item.php?item_id=".$item->getId());
    else{
      setSessionVar(SELF . "_uo", $item);
      checkSafePageRefresh("uo=1&item_id=".$item->getId());
    }
  }
  else if(isCommand("delete")){
    $item = new Item();
    try{$item->dbRead($item_id);}catch(Exception $e){setAlert($e->getMessage());redirect("/admin/itemlist.php");}
    if($item->dbDelete()){
      setAlert("Item " . $item->getName() . " deleted.");
      redirect("/admin/itemlist.php");
    }
  }
  else if(isCommand("delphoto")){
    $photo_id = getParameter("photo_id");
    if(!is_null($photo_id)){
      $item = new Item();
      try{$item->dbRead($item_id);}catch(Exception $e){setAlert($e->getMessage());redirect("/admin/itemlist.php");}
      if($item->dbDeletePhoto($photo_id)){
        ;//setAlert("Item " . $item->name . " deleted.");
      }
    }
  }

  checkSafePageRefresh("item_id=".$item_id);
  
  $page_title = "Добавить в список";
  if(isParameter("uo")){
    $item = getSessionVar(SELF . "_uo");
    //trace_r($item);
  }
  if(is_null($item) && !is_null($item_id)){
    $item = new Item();
    try{$item->dbRead($item_id);}catch(Exception $e){setAlert($e->getMessage());redirect("/admin/itemlist.php");}
  }
  if(is_null($item))  
    $item = new Item();  
  $item_id = $item->getId();
  
  if(is_null($item_id)){
    $subtitle = "Добавить растение";
  }
  else if(!is_null($item_id)){
    $subtitle = "Обновить " . $item->getName();
  }
  
  $max_thumb_width     = intval(getSetting("max_item_thumb_width",ITEM_THUMB_W));
  $max_thumb_height    = intval(getSetting("max_item_thumb_height",ITEM_THUMB_H));
  $photo = $item->getPhotoInfo();
  if(!is_null($photo)){  
    $photo_width  = getScaledImageWidth($photo["thumb_width"],$photo["thumb_height"],$max_thumb_width,$max_thumb_height,__FILE__.":".__LINE__);
    $photo_height = getScaledImageHeight($photo["thumb_width"],$photo["thumb_height"],$max_thumb_width,$max_thumb_height,__FILE__.":".__LINE__);
    $bPhotoBorder = true;
  }
  else
    $bPhotoBorder = true;

  $javascript_arr = array("misc.js");
  
  addJavaScript2HtmlHeader("/js/awt.js");
  addJavaScript2HtmlHeader("/js/misc.js");
  addJavaScript2HtmlHeader("/js/form.js.php");
  addJavaScript2HtmlHeader("/js/numeric.js");
  setFormFocus("ck_mod_item_form","family");
  require_once("html_header.inc.php");
?>
<script type="text/javascript">
<!--
function ckDelete(form){
  if(window.confirm("Удалить <?php hprint($item->getName())?>?")){
    form.elements["command"].value = "delete";
    disableButtons();
    form.submit();
  }
}

function ck(f){
  if(isInputEmpty(f.elements["family"],"Не указано семейство, к которому принадлежит растение"))
    return false;
  if(isInputEmpty(f.elements["genus"],"Не указан род, к которому принадлежит растение"))
    return false;
  if(isInputEmpty(f.elements["species"],"Не указан вид, к которому принадлежит растение"))
    return false;
  if(isInputEmpty(f.elements["price"],"Не указана цена растения"))
    return false;
  if(isInputEmpty(f.elements["package_qty"],null)){
    f.elements["package_qty"].value = 1;
    alert("Продаваемое количество установлено равным 1");
  }

  if(isInputEmpty(f.elements["name"],null))
    generateFullname(f);
  disableButtons();
  return true;
}

function generateFullname(form){
  form.elements["name"].value = 
  form.elements["genus"].value+" "+
  form.elements["species"].value+
  (trim(form.elements["variety"].value).length > 0 ? " v. "+form.elements["variety"].value : "") + 
  (trim(form.elements["cultivar"].value).length > 0 ? " cv. "+form.elements["cultivar"].value : "");
}

function setSeed(form,bSeed){
  var color = null;
  var disabled = false;
  var labelSize = document.getElementById("idLabelSize");
  var controlSize = document.getElementById("idSize");
  var labelQuantity = document.getElementById("idLabelQuantity");
  var controlQuantity = document.getElementById("idQuantity");
  if(labelSize && controlSize && labelQuantity && controlQuantity){
    if(bSeed){
      labelQuantity.style.color = "<?php maincolor()?>";
      controlQuantity.disabled = false;
      labelSize.style.color = "<?php disabledcolor()?>"
      controlSize.disabled = true;
    }
    else{
      labelQuantity.style.color = "<?php disabledcolor()?>";
      controlQuantity.disabled = true;
      labelSize.style.color = "<?php maincolor()?>"
      controlSize.disabled = false;
    }
  }
}
//-->
</script>
<form id="ck_mod_item_form" enctype="multipart/form-data" action="" method="post" onsubmit="return ck(this)">
<table style="width:100%;" cellspacing="0" cellpadding="0">
  <tr><td colspan="3" style="text-align:left;" class="pagename"><?php print($page_title)?></td></tr>
  <tr>
    <td style="vertical-align:top;width:2%;padding-top:15px;">&nbsp;</td>
    <td style="text-align:left;vertical-align:top;padding-top:15px;">
  <input type="hidden" name="command" value="<?php print(is_null($item_id)? "add" : "update")?>" />
      <table class="mainColor mainFont XL" cellspacing="0" cellpadding="2">
        <tr>
          <td class="mainFont L" style="text-align:right;">Семейство:</td>
          <td style="text-align:left;">
  <select name="family" class="select">
<?php 
  for($i = 0; $i < PlantFamily::getCount(); $i++){
?>
    <option<?php print(strcasecmp(PlantFamily::get($i),$item->getFamily()) == 0 ? " selected=\"selected\"":"")?> value="<?php hprint(PlantFamily::get($i))?>"><?php hprint(PlantFamily::getName($i))?></option>
<?php
  }
?>
  </select>
          </td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td class="mainFont L" style="text-align:right;">&nbsp;</td>
          <td style="text-align:left;">
            <table cellspacing="0" cellpadding="0">
              <tr>
                <td><label for="id_plant">Растение</label></td>
                <td>
  <input onchange="setSeed(this.form,false)" type="radio" id="id_plant" name="seed" value="1" <?php print($item->isSeed()?"":"checked=\"checked\" ")?>/>
                </td>
                <td><label for="id_seed">Семена</label></td>
                <td>
  <input onchange="setSeed(this.form,true)" type="radio" id="id_seed" name="seed" value="0" <?php print($item->isSeed()?"checked=\"checked\" ":"")?>/>
                </td>
              </tr>
            </table>
          </td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td class="mainFont L" style="text-align:right;">Род:</td>
          <td style="text-align:left;">
  <input <?php mxl($dbprefix."_item.genus");?> type="text" name="genus" value="<?php hprint($item->getGenus()); ?>" style="width:300px; text-transform:none;" class="text" >
          </td>
          <td>&nbsp;</td><td>&nbsp;</td>
        </tr>
        <tr>
          <td class="mainFont L" style="text-align:right;">Вид:</td>
          <td style="text-align:left;">
  <input <?php mxl($dbprefix."_item.species");?> type="text" name="species" value="<?php hprint($item->getSpecies()); ?>" style="width:300px; text-transform:none;" class="text" />
          </td>
          <td>&nbsp;</td><td>&nbsp;</td>
        </tr>
        <tr>
          <td class="mainFont L" style="text-align:right;">Варитет:</td>
          <td style="text-align:left;">
  <input <?php mxl($dbprefix."_item.variety");?> type="text" name="variety" value="<?php hprint($item->getVariety()); ?>" style="width:300px; text-transform:none;" class="text" />
          </td>
          <td>&nbsp;</td><td>&nbsp;</td>
        </tr>
        <tr>
          <td class="mainFont L" style="text-align:right;">Культивар:</td>
          <td style="text-align:left;">
  <input <?php mxl($dbprefix."_item.cultivar");?> type="text" name="cultivar" value="<?php hprint($item->getcultivar()); ?>" style="width:300px; text-transform:none;" class="text" />
          </td>
          <td>&nbsp;</td><td>&nbsp;</td>
        </tr>
        <tr>
          <td class="mainFont L" style="text-align:right;">Полное название:</td>
          <td style="text-align:left;">
  <input <?php mxl($dbprefix."_item.name");?> type="text" name="name" value="<?php hprint($item->getName()); ?>" style="width:300px; text-transform:none;" class="text" />
          </td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td nowrap class="mainFont L" style="text-align:right;vertical-align:top;">Дополнительное описание:</td>
          <td style="text-align:left;">
  <textarea <?php mxl($dbprefix."_item.description");?> rows="4" cols="100" name="description" class="text" style="border: 1px solid #000000; width:300px; height:100px; padding: 2px 2px 2px 2px;"><?php hprint($item->getDescription(),false);?></textarea>
          </td>
          <td>&nbsp;</td><td>&nbsp;</td>
        </tr>
        <tr>
          <td class="mainFont L" style="text-align:right;">Цена:</td>
          <td style="text-align:left;">
            <table style="border:none;margin:0px;padding:0px;" cellspacing="0" cellpadding="0">
              <tr>
                <td>
  <input onkeydown="filterNum(this,event,2,false)" <?php mxl($dbprefix."_item.price");?> type="text" name="price" value="<?php hprint($item->getPrice()); ?>" style="width:60px; text-transform:none;" class="text" />
                </td>
                <td style="padding-left:3px;"><?php hprint(Currency::get(getSetting(SETTINGS_KEY_CURRENCY,CURRENCY_UA)))?></td>
              </tr>
            </table>
          </td>
          <td>&nbsp;</td><td>&nbsp;</td>
        </tr>
        <tr>
          <td id="idLabelQuantity" class="mainFont L" style="color:<?php $item->isSeed()?maincolor():disabledcolor()?>;text-align:right;white-space:nowrap;">Количество:</td>
          <td style="text-align:left;">
  <input id="idQuantity"<?php print($item->isSeed()?"":" disabled=\"disabled\"")?> onkeydown="filterNum(this,event,0,false)" <?php mxl($dbprefix."_item.package_qty");?> type="text" name="package_qty" value="<?php hprint($item->getPackageQty() < 2 ? 1 : $item->getPackageQty()); ?>" style="width:60px; text-transform:none;" class="text" />
          </td>
          <td>&nbsp;</td><td>&nbsp;</td>
        </tr>
        <tr>
          <td id="idLabelSize" class="mainFont L" style="color:<?php $item->isSeed()?disabledcolor():maincolor()?>;text-align:right;white-space:nowrap;">Размер(см):</td>
          <td style="text-align:left;">
  <input id="idSize"<?php print($item->isSeed()?" disabled=\"disabled\"":"")?> <?php mxl($dbprefix."_item.size");?> type="text" name="size" value="<?php hprint($item->getSize());?>" style="width:60px; text-transform:none;" class="text" />
          </td>
          <td>&nbsp;</td><td>&nbsp;</td>
        </tr>
        <tr>
          <td class="mainFont L" style="text-align:right;">Есть в наличии:</td>
          <td style="text-align:left;">
  <select name="available" class="select">
    <option<?php print($item->getAvailable()>0?" selected=\"selected\"":"")?> value="1">Да</option>
    <option<?php print($item->getAvailable()==0?" selected=\"selected\"":"")?>  value="0">Нет</option>
  </select>
          </td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td style="text-align:left;">
<script type="text/javascript">
  function setLabelNewColor(bChecked){
    var x = document.getElementById("idLabelNew");
    if(x){
      x.style.color = bChecked ? "red":"<?php maincolor()?>";
    }
  }
</script>
            <table class="mainColor mainFont L" cellspacing="0" cellpadding="0">
              <tr>
                <td>
  <input style="color:red;" onclick="setLabelNewColor(this.checked)" id="idFeature1"<?php print(strlen($item->getFeature1())>0 ? " checked=\"checked\"":"")?> type="checkbox" name="feature1" value="new" />
                </td>
                <td id="idLabelNew" style="color:<?php print(strlen($item->getFeature1())>0 ? "red":$maincolor)?>;"><label for="idFeature1">Новый</label></td>
              </tr>
            </table>
          </td>
          <td>&nbsp;</td><td>&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td class="tpad10">
  <button type="submit" name="submit_btn" class="button"><?php print(is_null($item_id)? "Добавить" : "Обновить")?></button>
          </td>
          <td>&nbsp;</td><td>&nbsp;</td>
        </tr>
<?php if(!is_null($item->getId())){?>
        <tr>
          <td>&nbsp;</td>
          <td class="tpad10">
  <button type="button" name="delete_btn" onclick="ckDelete(this.form)" class="button">Удалить</button>
          </td>
          <td>&nbsp;</td><td>&nbsp;</td>
        </tr>
<?php }?>
        <tr>
          <td>&nbsp;</td>
          <td class="tpad10">
  <button type="button" name="return_btn" onclick="return onPageUnload(this.form,'/admin/itemlist.php')" class="button">Вернуться в список</button>
          </td>
          <td>&nbsp;</td><td>&nbsp;</td>
        </tr>
      </table>
    </td>
    <td style="text-align:left;vertical-align:top;width:60%;padding-top:15px;">
      <div style="text-align:-moz-left;">
      <table cellspacing="0" cellpadding="0">
        <tr>
          <td class="normal" style="text-align:center;vertical-align:middle;font-size:10px;width:<?php printf("%d",$max_thumb_width+10)?>px; height: <?php printf("%d",$max_thumb_height+10)?>px;<?php if($bPhotoBorder){?> border: 2px dashed red;<?php }?>">
<?php if(!is_null($photo)){?><a target="_blank" style="margin: 5px 5px 5px 5px;" href="/itemphoto.php?photo_id=<?php print($photo["photo_id"]);?>"><img src="/itemthumb.php?photo_id=<?php print($photo["photo_id"]);?>" style="width:<?php print($photo["thumb_width"])?>px;height:<?php print($photo["thumb_height"])?>px;" alt="" /></a><?php }else{?><?php }?>
          </td>
        </tr>
<?php if(!is_null($photo)){?>
        <tr>
          <td style="text-align:center;">
<script type="text/javascript">
  function delPhoto(el){
    if(confirm("Удалить фото?")){
      window.location.href = "<?php printf("%s?item_id=%d&command=delphoto&photo_id=%d",SELF,$item_id,$photo["photo_id"])?>";
    }
  }
</script>
  <button onclick="delPhoto(this)" type="button" style="border: none; width:20px; height: 20px; background-color: transparent;" name="delphoto_button"><img alt="" title="Удалить фото" style="border:none;margin:0px;padding:0px;" src="/images/delete1.gif" /></button>
          </td>
        </tr>
<?php }?>
      </table>
      <table cellspacing="0" cellpadding="0">
        <tr>
          <td style="text-align:right;" class="tpad5 rpad5">
  <input type="file" name="photo" class="text" />
          </td>
        </tr>
        <tr>
          <td style="text-align:left;" class="mainFont mainColor L">Добавить фото</td>
        </tr>
      </table>
      </div>
    </td>
  </tr>
</table>
</form>
<?php
  require_once("html_footer.inc.php");
?>
