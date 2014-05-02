<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
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

  //save page ref in session
  setSessionVar("LAST_VISITED_PHOTO_PAGE",SELF);
  if(isCommand("search")){
    setSessionVar("search_photos", getParameter("search"));
    redirect("/photosearch.php");
  }

  checkSafePageRefresh();

  $max_thumb_width     = intval(getSetting("max_item_thumb_width",ITEM_THUMB_W));
  $max_thumb_height    = intval(getSetting("max_item_thumb_height",ITEM_THUMB_H));
  $list = PhotoAlbumList::dbGetList();
  $page_title = "Растения на продажу из фотоальбомов";
  $show_menu = false;

	$searchChar = getSessionVar('search_char');
	$searchStatus = getSessionVar('search_status');
	$searchText = getSessionVar('search_text');

  addCSS2HtmlHeader("/js/ext-jquery/jquery-ui.css");
  addJavaScript2HtmlHeader("/js/ext-jquery/jquery-1.3.2.min.js");
  addJavaScript2HtmlHeader("/js/ext-jquery/jquery-ui-1.7.2.min.js");
  addJavaScript2HtmlHeader("/js/ajax.js.php");
  addJavaScript2HtmlHeader("/js/awt.js");
  require_once("html_header.inc.php");
?>
<script type="text/javascript">
<!--
  function alignFloatingDivs(){
    var arr = c$('photo-container');
    var h = 0;
    for(var i = 0; i < arr.length; i++){
      var x = arr[i];
      if(x.offsetHeight > h)
        h = x.offsetHeight;
    }
    for(var i = 0; i < arr.length; i++){
      var x = arr[i];
      x.style.height = h + 'px';
    }
  }

  function openUpdatePhotoDialog(photoalbum_id,photo_id){
    var form = document.getElementById('update_photo_form');
    var dialog = document.getElementById('update_photo_dialog');
    form.elements['photo_id'].value = photo_id;
    var elPhotoId = form.elements['photo_id'];
    var elName = form.elements['photo_name'];
    var elDesc = form.elements['photo_desc'];
    var elKeywords = form.elements['photo_keywords'];
    var elPrice = form.elements['photo_price'];
    var elStatus = form.elements['photo_status'];
    var elDiameter = form.elements['diameter'];
    var elNotes = form.elements['notes'];
    var requestStr = "/ajaxcalls/photo_get_text.php?photo_id="+elPhotoId.value+'&photoalbum_id='+photoalbum_id;

    ajcall(requestStr,'',errmsg,
      function(result){
        $('#update_photo_dialog').dialog('open');
        $('#update_photo_dialog').dialog( "option", "title", result['photo_name'] );
        //workaround for Opera bug
        elName.style.visibility = 'hidden';
        elDesc.style.visibility = 'hidden';
        elKeywords.style.visibility = 'hidden';
        elPrice.style.visibility = 'hidden';
        elStatus.style.visibility = 'hidden';
        elDiameter.style.visibility = 'hidden';
        elNotes.style.visibility = 'hidden';
       
        elName.value = result['photo_name'] == null ? '' : result['photo_name'];
        elDesc.value = result['photo_desc'] == null ? '' : result['photo_desc'];
        elKeywords.value = result['photo_keywords'] == null ? '' : result['photo_keywords'];
        elPrice.value = result['photo_price'] == null ? '' : result['photo_price'];
        elStatus.value = result['photo_status'] == null ? '' : result['photo_status'];
        elDiameter.value = result['diameter'] == null ? '' : result['diameter'];
        elNotes.value = result['notes'] == null ? '' : result['notes'];
            
        elName.style.visibility = 'visible';
        elDesc.style.visibility = 'visible';
        elKeywords.style.visibility = 'visible';
        elPrice.style.visibility = 'visible';
        elStatus.style.visibility = 'visible';
        elDiameter.style.visibility = 'visible';
        elNotes.style.visibility = 'visible';
        elName.focus();elName.select();
      }
    );
  }

  function SavePhotoText(form,photoalbum_id){
    var elPhotoId = form.elements['photo_id'];
    var elName = form.elements['photo_name'];
    var elDesc = form.elements['photo_desc'];
    var elKeywords = form.elements['photo_keywords'];
    var elPrice = form.elements['photo_price'];
    var elStatus = form.elements['photo_status'];
    var elDiameter = form.elements['diameter'];
    var elNotes = form.elements['notes'];
    var postData = 
    '&photo_id='+elPhotoId.value+
    '&photo_name='+urlencode(elName.value)+
    '&photo_desc='+urlencode(elDesc.value)+
    '&photo_keywords='+urlencode(elKeywords.value)+
    '&photo_price='+urlencode(elPrice.value)+
    '&photo_status='+urlencode(elStatus.value)+
    '&diameter='+urlencode(elDiameter.value)+
    '&notes='+urlencode(elNotes.value)+
    ''
    ;
    ajcall('/ajaxcalls/photo_set_text.php',postData,errmsg,
      function(result){
        var el = document.getElementById('photo_html'+elPhotoId.value);
        if(el){el.innerHTML = result.success;}
        alignFloatingDivs();
      }
    );
    $('#update_photo_dialog').dialog('close');
  }
  
  $(document).ready(
    function(){
      $('#update_photo_dialog').dialog({autoOpen:false,width:550,modal:true,resizable:false});
      alignFloatingDivs();
    }
  );
//-->
</script>
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
  <div class="ndv" style="width:50%;line-height:20px;"><button name="submit_photo" onclick="SavePhotoText(this.form)" type="button" class="button">Сохранить</button></div>
  <div class="vdv" style="width:50%;line-height:20px;"><button onclick="$('#update_photo_dialog').dialog('close')" name="close_dialog" type="button" class="button">Закрыть</button></div>
</div>
</form>
</div>

<script type="text/javascript">
<!--
	var errmsg = 'Не удалось связаться с сервером для поиска фото';
	var searchChar = '<?php echo jstext($searchChar)?>';
	var searchStatus = '<?php echo jstext($searchStatus)?>';
	var searchText = '<?php echo jstext($searchText)?>';

  $(document).ready(
    function(){
    }
  );
  
  function searchPhotos(trigger){
    var f = document.forms['photoalbumsales_form'];
    searchStatus = f.elements['photo_status'].options[f.elements['photo_status'].selectedIndex].value;
    if(trigger.length == 1){
      searchChar = trigger;
      f.elements['search_text'].value = searchText = '';
    }
    else if(trigger == 'search_text' || trigger == 'search_btn'){
      searchChar = '';
      searchText = f.elements['search_text'].value;
    }
    var data = {
      char: searchChar,
      status: searchStatus,
      text: searchText
    };
    
    ajcall('/ajaxcalls/photo_sales.php',ajcallEncode(data),errmsg,
      function(result){
        var el = document.getElementById('photos');
        if(el){el.innerHTML = result;}
        alignFloatingDivs();
      }
    );
    
    
    //alert(ajcallEncode(data));
  }
//-->
</script>
<form action="" method="post" id="photoalbumsales_form" onsubmit="return false">
<table style="width:100%;" cellspacing="0" cellpadding="0">
  <tr>
    <td style="text-align:left;padding:10px;">
  <input name="command" value="" type="hidden" />
      <div style="text-align:-moz-left;">
<?php PhotoAlbum::printStats()?>
<?php for($i = ord('A'); $i <= ord('Z'); $i++){?>
      <div id="char_<?php echo chr($i)?>" class="vmid cursor-ptr bold courier M" 
      onmouseover="this.style.backgroundPosition='0px -22px'" 
      onmouseout="this.style.backgroundPosition='0px 0px'" 
      onclick="this.style.backgroundPosition='0px -44px';setTimeout(function(){searchPhotos('<?php echo chr($i)?>')},200)" 
      style="float:left;width:22px;height:22px;line-height:22px;margin:auto 2px auto 0px;background:url(/images/btn-frame.gif) no-repeat;text-align:center;"><?php print(chr($i))?></div>
<?php }?>

      <div class="mainFont mainColor L" style="clear:both;padding:5px 0px">
        <div class="bold" style="float:left;margin-right:5px;line-height:22px;vertical-align:middle;">Поиск</div>
        <div style="float:left;"><input onkeypress="if(keyCode(event)==13){searchPhotos(this.name)}" type="text" style="width:300px" name="search_text" value="" class="text"/></div>
        <div style="float:left;"><button name="search_btn" type="button" style="margin:0px 3px;width:22px;height:22px;border:none;background:url(/images/btn-search.gif) no-repeat transparent;" onmouseover="this.style.backgroundPosition='0px -22px'" onmouseout="this.style.backgroundPosition='0px 0px'" onclick="this.style.backgroundPosition='0px -44px';searchPhotos(this.name);"></button></div>
      	<div style="clear:both;height:5px"></div>
        <div class="bold" style="float:left;margin-right:5px;line-height:22px;vertical-align:middle;">Показывать фото</div>
        <div style="float:left;"><?php echo CactiStatus::renderDropdown(getSessionVar('photo_status'),'onchange="searchPhotos(this.name)"','photo_status')?></div>
      </div>

      <table style="clear:left;padding:5px;" cellspacing="0" cellpadding="0">
        <tr>
          <td id="photos"></td>
        </tr>
      </table>
      </div>
    </td>
  </tr>
</table>
</form>
<?php require_once("html_footer.inc.php");?>
  