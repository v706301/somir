<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  ini_set("memory_limit",'64M');
  require_once("http.inc.php");
  require_once("misc.inc.php");
  require_once("Localize.class.php");
  require_once("PhotoAlbum.class.php");
  require_once("PhotoAlbumList.class.php");
  require_once("User.class.php");
  require_once("common.inc.php");

  if(!User::isLoggedIn())
    redirect("/index.php" );
  if(!User::isOperatorAdmin(User::getOperatorId()))
    redirect("/index.php");

  //save page ref in session
  setSessionVar("LAST_VISITED_PHOTO_PAGE",SELF);

  if(isCommand("add_photo")){
    $album = new PhotoAlbum();
    $photoalbum_id = getParameter("photoalbum_id");
    $photoalbum_name = getParameter("photoalbum_name");
    if(strlen($photoalbum_id) > 0 && strlen($photoalbum_name) > 0)
      $photoalbum_id = null;
    $photo = $album->httpReadPhoto();
//header("Content-type: ".$photo["type"]);
//header("Content-length: ".$photo["size"]);
//print($photo["content"]);
//exit;
    if($photo !== false && is_array($photo)){
      try{
        if(is_null($photoalbum_id)){
          $album->setName($photoalbum_name);
          $album->setDescription(getParameter("photoalbum_desc"));
          if(!$album->dbInsert())
            throw new Exception("Не удалось создать новый фотоальбом");
        }
        else
          $album->dbRead($photoalbum_id);
        $photo_id = $album->dbInsertPhoto($photo);
        setSessionVar(SELF."_last_inserted_photo",$photo_id);
        setSessionVar(SELF."_last_updated_album",$album->getId());
        redirect(sprintf("/admin/photoalbum.php?photoalbum_id=%d",$album->getId()));
      }
      catch(Exception $e){
        setAlert($e->getMessage());
      }
    }
  }

  checkSafePageRefresh();

  $list = PhotoAlbumList::dbGetList();
  $last_photo_id = getSessionVar(SELF."_last_inserted_photo",false);
  $last_album_id = getSessionVar(SELF."_last_updated_album",false);
  $page_title = "Загрузка фото в альбомы";
  addJavaScript2HtmlHeader("/js/ext-jquery/jquery-1.3.2.min.js");
  addJavaScript2HtmlHeader("/js/ext-jquery/jquery.example.min.js");
  setFormFocus("photoupload_form","photo");
  require_once("html_header.inc.php");
?>
<script type="text/javascript">
<!--
  function ckNewPhotoAlbum(form){
    if(form.elements['photoalbum_id'].selectedIndex == 0){
      form.elements['photoalbum_name'].focus();
    }
  }
  
  var new_album_hint = 'Название нового альбома\r\n(Если не указать, альбом назовётся "Фотоальбом")';
  var new_album_desc_hint = 'Описание нового альбома (не обязательно)';
  var photo_name_hint = 'Название фотографии\r\n(Если не указать, названием станет имя файла)';
  var photo_desc_hint = 'Описание фотографии (не обязательно)';
  var photo_keywords_hint = 'Ключевые слова для поисковых систем (не обязательно)';

  function setHintsVisible(b){
  	var form = document.getElementById('photoupload_form');
    if(b){
      jqhint(form.elements['photoalbum_name'],new_album_hint);
      jqhint(form.elements['photoalbum_desc'],new_album_desc_hint);
      jqhint(form.elements['photo_name'],photo_name_hint);
      jqhint(form.elements['photo_desc'],photo_desc_hint);
      jqhint(form.elements['photo_keywords'],photo_keywords_hint);
  	}
    else{
      jqhint(form.elements['photoalbum_name']);
      jqhint(form.elements['photoalbum_desc']);
      jqhint(form.elements['photo_name']);
      jqhint(form.elements['photo_desc']);
      jqhint(form.elements['photo_keywords']);
    }
  }

  function ck(form){
  	if(trim(form.elements['photo'].value).length == 0){
      setTimeout('setHintsVisible(true)',10);
      return false;
    }
    else{
      setHintsVisible(false);
      return true;
    }
  }

  $(window).load(function(){
    setHintsVisible(true);
  });
  
//-->
</script>
<form enctype="multipart/form-data" action="" method="post" id="photoupload_form" onsubmit="return ck(this)">
<table style="width:100%;" cellspacing="0" cellpadding="0">
  <tr><td style="text-align:left;" class="pagename"><?php print($page_title)?></td></tr>
  <tr>
    <td style="text-align:left;padding:10px;">
  <input name="command" value="add_photo" type="hidden" />
<?php PhotoAlbum::printStats()?>
      <div style="text-align:-moz-left;">
      <table style="padding:5px;" cellspacing="0" cellpadding="0">
        <tr>
          <td style="width:700px;padding-top:20px;" class="mainFont mainColor L">
            <div style="clear:both;line-height:18px;margin-bottom:20px;" class="vmid">
              <span style="line-height:18px;" class="rpad4">Выбрать фото</span>
              <span style="line-height:18px;"><input name="photo" type="file" /></span>
              <span style="line-height:18px;"><button name="submit_photo" type="submit" class="button">Сохранить</button></span>
            </div>

            <fieldset class="mainFont mainColor L italic" style="width:95%;float:left;">
              <legend>Выбор фотоальбома</legend>
              <div style="width:100%;" class="left vmid pad5">
                <select onchange="ckNewPhotoAlbum(this.form)" name="photoalbum_id" class="select" style="width:99%;">
                  <option value="">&laquo;&laquo; Создать новый &raquo;&raquo;</option>
                  <?php foreach($list as $album){printf("<option %svalue=\"%d\">%s [Создан %s, %d фото]</option>\n",($album["id"] == $last_album_id ? "selected=\"selected\" ":""),$album["id"],$album["name"],Localize::dbdate($album["date_created"]),$album["photo_count"]);}?>
                </select>
              </div>
              <div style="width:100%;" class="left vtop pad5">
                <textarea onfocus="jqhint(this,false)" onblur="jqhint(this,new_album_hint)" id="photoalbum_name" name="photoalbum_name" rows="1" cols="1" class="text" style="width:99%;height:30px;" ></textarea>
              </div>
              <div style="width:100%;" class="left vtop pad5">
                <textarea onfocus="jqhint(this,false)" onblur="jqhint(this,new_album_desc_hint)" id="photoalbum_desc" name="photoalbum_desc" rows="1" cols="1" class="text" style="width:99%;height:60px;"></textarea>
              </div>
            </fieldset>

            <fieldset class="mainFont mainColor L italic" style="width:95%;float:left;margin-top:20px;">
              <legend>Сведения о загружаемой фотографии</legend>
              <div style="width:100%;" class="left vtop pad5">
                <textarea onfocus="jqhint(this,false)" onblur="jqhint(this,photo_name_hint)" id="photo_name" name="photo_name" rows="1" cols="1" class="text" style="width:99%;height:30px;"></textarea>
              </div>
              <div style="width:100%;" class="left vtop pad5">
                <textarea onfocus="jqhint(this,false)" onblur="jqhint(this,photo_desc_hint)" id="photo_desc" name="photo_desc" rows="1" cols="1" class="text" style="width:99%;height:60px;"></textarea>
              </div>
              <div style="width:100%;" class="left vtop pad5">
                <textarea onfocus="jqhint(this,false)" onblur="jqhint(this,photo_keywords_hint);this.form.elements['submit_photo'].focus()" id="photo_keywords" name="photo_keywords" rows="1" cols="1" class="text" style="width:99%;height:60px;"></textarea>
              </div>
            </fieldset>
          </td>
        </tr>
      </table>
      </div>
    </td>
  </tr>
</table>
</form>
<?php require_once("html_footer.inc.php");?>
  