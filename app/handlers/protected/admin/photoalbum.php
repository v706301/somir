<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  ini_set("memory_limit",'64M');
  require_once("http.inc.php");
  require_once("misc.inc.php");
  require_once("Localize.class.php");
  require_once("PhotoAlbum.class.php");
  require_once("PhotoAlbumList.class.php");
  require_once("User.class.php");
  require_once("CactiStatus.class.php");
  require_once("common.inc.php");

  if(!User::isLoggedIn())
    redirect("/index.php" );
  if(!User::isOperatorAdmin(User::getOperatorId()))
    redirect("/index.php");
  if(!isParameter("photoalbum_id"))
    redirect("/admin/photoalbumlist.php");
  $photoalbum_id = getParameter("photoalbum_id");
  $album = new PhotoAlbum();
  try{
    $album->dbRead($photoalbum_id);
  }
  catch(Exception $e){
    redirectAlert("/admin/photoalbumlist.php",$e->getMessage());
  }

  //save page ref in session
  setSessionVar("LAST_VISITED_PHOTO_PAGE",SELF."?".$_SERVER["QUERY_STRING"]);

  if(isCommand("delete")){
    foreach($_POST as $key => $val){
      if(substr($key,0,strlen("photo_id")) == "photo_id" && $val != 0){
        $photo_id = substr($key,strlen("photo_id"));
        $album->dbDeletePhoto($photo_id);
      }
    }
  }
  else if(isCommand("show") || isCommand("hide")){
    foreach($_POST as $key => $val){
      if(substr($key,0,strlen("photo_id")) == "photo_id" && $val != 0){
        $photo_id = substr($key,strlen("photo_id"));
        $album->dbSetPhotoHidden($photo_id,isCommand("hide"));
      }
    }
  }
  else if(isCommand("move")){
    foreach($_POST as $key => $val){
      if(substr($key,0,strlen("photo_id")) == "photo_id" && $val != 0){
        $photo_id = substr($key,strlen("photo_id"));
        $album->dbMovePhoto($photo_id,getParameter("moveto_photoalbum_id"));
      }
    }
  }
  else if(isCommand("add_photo")){
    $photo = $album->httpReadPhoto();
    if($photo !== false && is_array($photo)){
      try{
        $photo_id = $album->dbInsertPhoto($photo);
        setSessionVar(SELF."_last_inserted_photo",$photo_id);
        setSessionVar(SELF."_last_updated_album",$album->getId());
      }
      catch(Exception $e){
        setAlert($e->getMessage());
      }
    }
  }

  checkSafePageRefresh(sprintf("photoalbum_id=%d",$album->getId()));

  $max_thumb_width     = intval(getSetting("max_item_thumb_width",ITEM_THUMB_W));
  $max_thumb_height    = intval(getSetting("max_item_thumb_height",ITEM_THUMB_H));
  $list = PhotoAlbumList::dbGetList();
  $photos = $album->dbReadPhotos("name");
  
  $page_title = str_replace("<br />"," ",htmlize($album->getName()));

  addCSS2HtmlHeader("/js/ext-jquery/jquery-ui.css");
  addJavaScript2HtmlHeader("/js/ext-jquery/jquery-1.3.2.min.js");
  addJavaScript2HtmlHeader("/js/ext-jquery/jquery-ui-1.7.2.min.js");
  addJavaScript2HtmlHeader("/js/ajax.js.php");
  addJavaScript2HtmlHeader("/js/awt.js");
  addJavaScript2HtmlHeader("/js/photoalbum.js.php");
  setFormFocus("photoalbum_form","photoalbum_name");
  require_once("html_header.inc.php");
?>
<script type="text/javascript">
<!--
  $(document).ready(
    function(){
      $('#add_photo_dialog').dialog({autoOpen:false,width:550,modal:true,resizable:false});
      $('#update_photo_dialog').dialog({autoOpen:false,width:550,modal:true,resizable:false});
//      $('#add_photo_dialog').dialog({autoOpen:false,width:550,height:300,modal:true,resizable:false});
//      $('#update_photo_dialog').dialog({autoOpen:false,width:550,height:270,modal:true,resizable:false});
      alignFloatingDivs();
    }
  );

  function startSlideShow(photoalbum_id){
    var arr = c$("photo-container");
    var post = "";
    for(var i = 0; i < arr.length; i++){
      if(post.length > 0)
        post += ',';
      post += arr[i].id.substring('div-photo'.length);
    }
    post = 'photos='+post+'&aw='+window.screen.availWidth+'&ah='+window.screen.availHeight;
    ajcall(
      '/ajaxcalls/save_slides.php',post,'Не удалось инициализировать слайдшоу',
      function(result){
        var w = window.open('/slideshow.php','slide_show','status=no,toolbar=no,menubar=no,location=no,resizable=no,scrollbars=no');
        if(w){w.opener = self;w.focus();}
      }
    );
  }
//-->
</script>
<div id="add_photo_dialog" class="mainFont mainColor L" style="display:none;overflow:hidden;" title="Добавить фото в альбом">
<form enctype="multipart/form-data" action="" method="post" id="add_photo_form">
<div class="ndv" style="width:20%;"><input type="hidden" name="command" value="add_photo" /><span class="pad3">Фото</span></div>
<div class="vdv" style="width:80%;"><input name="photo" type="file" style="margin:0px 3px 3px 3px;" /></div>

<div class="ndv" style="width:20%;"><span class="pad3">Название</span></div>
<div class="vdv" style="width:80%;"><textarea id="photo_name" name="photo_name" rows="1" cols="1" class="text" style="margin:0px 3px 3px 3px;width:99%;height:30px;"></textarea></div>

<div class="ndv" style="width:20%;"><span class="pad3">Описание</span></div>
<div class="vdv" style="width:80%;"><textarea id="photo_desc" name="photo_desc" rows="1" cols="1" class="text" style="margin:0px 3px 3px 3px;width:99%;height:60px;"></textarea></div>

<div class="ndv" style="width:20%;"><span class="pad3">Ключевые слова</span></div>
<div class="vdv" style="width:80%;"><textarea id="photo_keywords" name="photo_keywords" rows="1" cols="1" class="text" style="margin:0px 3px 3px 3px;width:99%;height:60px;"></textarea></div>

<div class="ndv" style="width:20%;"><span class="pad3">Цена</span></div>
<div class="vdv" style="width:80%;"><input id="photo_price" name="photo_price" class="text" style="margin:0px 3px 3px 3px;"/></div>

<div class="ndv" style="width:20%;"><span class="pad3">Продажа</span></div>
<div class="vdv" style="padding-left:3px"><?php CactiStatus::renderDropdown()?></div>

<div class="ndv" style="width:20%;line-height:25px;vertical-align:middle"><span style="font-size:18px;" class="pad3">&#x2300;</span></div>
<div class="vdv" style="width:80%;"><input id="diameter" name="diameter" class="text" style="margin:3px 3px 3px 3px;"/></div>

<div class="ndv" style="width:20%;"><span class="pad3">Заметки</span></div>
<div class="vdv" style="width:80%;"><textarea id="notes" name="notes" rows="1" cols="1" class="text" style="margin:0px 3px 3px 3px;width:99%;height:60px;"></textarea></div>

<div class="ndv" style="width:20%;">&nbsp;</div>
<div class="vdv" style="width:80%;margin-top:10px;">
  <div class="ndv" style="width:50%;line-height:20px;"><button name="submit_photo" onclick="ckPhotoUpload(this.form)" type="button" class="button">Добавить фото</button></div>
  <div class="vdv" style="width:50%;line-height:20px;"><button onclick="$('#add_photo_dialog').dialog('close')" name="close_dialog" type="button" class="button">Закрыть</button></div>
</div>
</form>
</div>

<div id="update_photo_dialog" class="mainFont mainColor L" style="display:none;overflow:hidden;" title="">
<form action="" id="update_photo_form" onsubmit="ajcall_UpdatePhotoText(this);return false;">

<div class="ndv" style="width:20%;"><input type="hidden" name="photo_id" value="" /><span class="pad3">Название</span></div>
<div class="vdv" style="width:80%;"><textarea id="photo_name" name="photo_name" rows="1" cols="1" class="text" style="margin:0px 3px 3px 3px;width:99%;height:30px;"></textarea></div>

<div class="ndv" style="width:20%;"><span class="pad3">Описание</span></div>
<div class="vdv" style="width:80%;"><textarea id="photo_desc" name="photo_desc" rows="1" cols="1" class="text" style="margin:0px 3px 3px 3px;width:99%;height:60px;"></textarea></div>

<div class="ndv" style="width:20%;"><span class="pad3">Ключевые слова</span></div>
<div class="vdv" style="width:80%;"><textarea id="photo_keywords" name="photo_keywords" rows="1" cols="1" class="text" style="margin:0px 3px 3px 3px;width:99%;height:60px;"></textarea></div>

<div class="ndv" style="width:20%;"><span class="pad3">Цена</span></div>
<div class="vdv" style="width:80%;"><input id="photo_price" name="photo_price" class="text" style="margin:0px 3px 3px 3px;"/></div>

<div class="ndv" style="width:20%;"><span class="pad3">Продажа</span></div>
<div class="vdv" style="padding-left:3px"><?php CactiStatus::renderDropdown()?></div>

<div class="ndv" style="width:20%;"><span class="pad3">&#x2300;</span></div>
<div class="vdv" style="width:80%;"><input id="diameter" name="diameter" class="text" style="margin:0px 3px 3px 3px;"/></div>

<div class="ndv" style="width:20%;"><span class="pad3">Заметки</span></div>
<div class="vdv" style="width:80%;"><textarea id="notes" name="notes" rows="1" cols="1" class="text" style="margin:0px 3px 3px 3px;width:99%;height:60px;"></textarea></div>

<div class="ndv" style="width:20%;">&nbsp;</div>
<div class="vdv" style="width:80%;margin-top:10px;">
  <div class="ndv" style="width:50%;line-height:20px;"><button name="submit_photo" onclick="SavePhotoText(this.form,<?php printf("photoalbum_id=%d",$album->getId())?>)" type="button" class="button">Сохранить</button></div>
  <div class="vdv" style="width:50%;line-height:20px;"><button onclick="$('#update_photo_dialog').dialog('close')" name="close_dialog" type="button" class="button">Закрыть</button></div>
</div>
</form>
</div>

<form enctype="multipart/form-data" action="" method="post" id="photoalbum_form" onsubmit="return ck(this)">
<table style="width:100%;" cellspacing="0" cellpadding="0">
  <tr><td style="text-align:left;" id="album_name" class="pagename"><?php print($page_title)?></td></tr>
  <tr>
    <td style="text-align:left;padding:10px;">
  <input name="command" value="" type="hidden" />
  <input name="photoalbum_id" value="<?php printf("%d",$album->getId())?>" type="hidden" />
<?php PhotoAlbum::printStats()?>
      <div style="text-align:-moz-left;">
      <table style="padding:5px;" cellspacing="0" cellpadding="0">
        <tr>
          <td class="vtop">
            <table id="savetext_container" class="mainFont mainColor L" cellspacing="0" cellpadding="2">
              <tr>
                <td class="right vtop"></td>
                <td style="height:22px;" class="left vmid">
                  <div 
                  id="ckb_hidden" 
                  class="cursor-ptr vmid center" 
                  onmouseover="this.style.backgroundPosition='0px -22px'" 
                  onmouseout="this.style.backgroundPosition='0px 0px'" 
                  onclick="this.style.backgroundPosition='0px -44px';setTimeout('ckHidden()',200)"
                  style="float:left;width:22px;height:22px;background-image:url(/images/btn-checkbox-<?php print($album->isHidden()?"on":"off")?>.gif);background-repeat:no-repeat;"><input type="hidden" name="is_hidden" value="<?php printf("%d",$album->isHidden() ? 1 : 0)?>" /></div>
                  <div 
                  class="vmid cursor-ptr"
                  onmouseover="document.getElementById('ckb_hidden').style.backgroundPosition='0px -22px'" 
                  onmouseout="document.getElementById('ckb_hidden').style.backgroundPosition='0px 0px'" 
                  onclick="document.getElementById('ckb_hidden').style.backgroundPosition='0px -44px';setTimeout('ckHidden()',200)"
                  style="float:left;height:22px;line-height:22px;margin:auto 0px auto 5px;">Не показывать альбом</div>
                </td>
                <td class="left vtop"></td>
              </tr>
              <tr>
                <td class="right vtop">Название альбома</td>
                <td class="left vtop"><textarea tabindex="1" name="photoalbum_name" rows="1" cols="1" class="text" style="width:400px;height:30px;"><?php hprint($album->getName(),false)?></textarea></td>
                <td class="left vtop"><button tabindex="3" type="button" name="btn_save_text" onclick="SaveAlbumInfo(this.form,<?php printf("%d",$album->getId())?>)" class="button">Сохранить изменения</button></td>
                <td class="vtop left" rowspan="4">
                  <img onclick="startSlideShow(<?php printf("%d",$album->getId())?>)" id="frontend_photo" src="/albumthumb.php?photoalbum_id=<?php print($album->getId());?>" style="width:<?php print($album->getThumbWidth())?>px;height:<?php print($album->getThumbHeight())?>px;" alt="" />
                </td>
              </tr>
              <tr>
                <td class="right vtop">Описание</td>
                <td colspan="2" class="left vtop"><textarea tabindex="2" name="photoalbum_desc" rows="1" cols="1" class="text" style="width:98%;height:60px;"><?php hprint($album->getDescription(),false)?></textarea></td>
              </tr>
              <tr>
                <td class="right vtop"></td>
                <td class="left vtop">
                  <select tabindex="4" disabled="disabled" name="moveto_photoalbum_id" class="select" style="width:400px;">
                    <option value="">-- Переместить отмеченные фото в другой альбом --</option>
<?php foreach($list as $other_album){
  if($other_album["id"] != $album->getId())
    printf("                    <option value=\"%d\">%s [Создан %s, %d фото]</option>\n"
    ,$other_album["id"]
    ,$other_album["name"]
    ,Localize::dbdate($other_album["date_created"])
    ,$other_album["photo_count"]);
}?>
                  </select>
                </td>
                <td class="left vtop"><button tabindex="5" type="submit" disabled="disabled" name="btn_move" onclick="ckMove(this.form)" class="button">Переместить</button></td>
              </tr>

              <tr>
                <td class="right vtop"></td>
                <td colspan="2" class="left vtop">
                  <table style="width:100%;" cellspacing="0" cellpadding="0">
                    <tr>
                      <td style="width:25%;" class="center vtop">
<button tabindex="6" type="button" name="btn_add" onclick="openAddPhotoDialog()" style="width:99%;height:65px;" class="button">Добавить<br />фото</button>
                      </td>
                      <td style="width:25%;" class="center vtop">
<button tabindex="7" type="button" name="btn_hide" disabled="disabled" onclick="submitForm(this.form,'hide')" style="width:99%;height:65px;" class="button">Скрыть<br />отмеченные<br />фото</button>
                      </td>
                      <td style="width:25%;" class="center vtop">
<button tabindex="8" type="button" name="btn_show" disabled="disabled" onclick="submitForm(this.form,'show')" style="width:99%;height:65px;" class="button">Открыть<br />отмеченные<br />фото</button>
                      </td>
                      <td style="width:25%;" class="center vtop">
<button tabindex="9" type="button" name="btn_delete" disabled="disabled" onclick="ckDelete(this.form)" style="width:99%;height:65px;" class="button">Удалить<br />отмеченные<br />фото</button>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
<?php
  foreach($photos as $photo){
    $descType = ($photo["photo_name"] > 0 || $photo["photo_desc"] > 0 || $photo["photo_keywords"] > 0) ? "active" : "grayed";
?>
            <div id="div-photo<?php printf("%d",$photo["id"])?>" class="photo-container" style="float:left;padding:3px;">
              <table cellspacing="0">
                <tr>
                  <td id="photo_html<?php echo $photo['id']?>" class="vtop">

                    <?php echo CactiStatus::renderPhoto($photo)?>
                  </td>
                  <td class="vtop left">
                    <div title="Отметить фото"
                    id="ckb<?php printf("%d",$photo["id"])?>"
                    class="cursor-ptr center" 
                    onmouseover="this.style.backgroundPosition='0px -22px'" 
                    onmouseout="this.style.backgroundPosition='0px 0px'" 
                    onclick="this.style.backgroundPosition='0px -44px';setTimeout('ckPhoto(<?php printf("%d",$photo["id"])?>)',200)"
                    style="width:22px;height:22px;background-image:url(/images/btn-checkbox-off.gif);background-repeat:no-repeat;"><input type="hidden" name="photo_id<?php printf("%d",$photo["id"])?>" /></div>

                    <div title="Редактировать текстовые описания (название, дополнительное описание, ключевые слова)"
                    class="cursor-ptr" 
                    onmouseover="this.style.backgroundPosition='0px -22px'" 
                    onmouseout="this.style.backgroundPosition='0px 0px'" 
                    onclick="this.style.backgroundPosition='0px -44px';setTimeout('openUpdatePhotoDialog(<?php printf("%d",$album->getId())?>,<?php printf("%d",$photo["id"])?>)',200)" 
                    style="margin-top:3px;width:22px;height:22px;background-image:url(/images/btn-desc-<?php print($descType)?>.gif);background-repeat:no-repeat;"></div>

                    <div title="Сделать фото заглавным"
                    class="cursor-ptr" 
                    onmouseover="this.style.backgroundPosition='0px -22px'" 
                    onmouseout="this.style.backgroundPosition='0px 0px'" 
                    onclick="this.style.backgroundPosition='0px -44px';
                    setTimeout('<?php printf("SetFrontendPhoto(\\'/photothumb.php?photo_id=%d\\',%d,%d,%d,%d)",$photo["id"],$photo["thumb_width"],$photo["thumb_height"],$photo["id"],$album->getId())?>',200)" 
                    style="margin-top:3px;width:22px;height:22px;background-image:url(/images/btn-frontend-photo.gif);background-repeat:no-repeat;"></div>
                  </td>
                </tr>
              </table>
            </div> 
<?php }?>
          </td>
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


