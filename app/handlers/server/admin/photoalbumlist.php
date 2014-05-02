<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
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

  if(isCommand("hide") || isCommand("show")){
    $album = new PhotoAlbum();
    $photoalbum_id = getParameter("photoalbum_id");
    if(strlen($photoalbum_id) > 0){
      try{
        $album->dbRead($photoalbum_id);
        $album->dbSetHidden(isCommand("show") ? false : true);
      }
      catch(Exception $e){
      	setAlert($e->getMessage());
      }
    }
  }
  else if(isCommand("delete")){
    $album = new PhotoAlbum();
    $photoalbum_id = getParameter("photoalbum_id");
    if(strlen($photoalbum_id) > 0){
      try{
        $album->dbRead($photoalbum_id);
        $album->dbDelete();
      }
      catch(Exception $e){
        setAlert($e->getMessage());
      }
    }
  }

  checkSafePageRefresh();

  $max_thumb_width     = intval(getSetting("max_item_thumb_width",ITEM_THUMB_W));
  $max_thumb_height    = intval(getSetting("max_item_thumb_height",ITEM_THUMB_H));
  $list = PhotoAlbumList::dbGetList();
  $page_title = "Фотоальбомы";
  addJavaScript2HtmlHeader("/js/ext-jquery/jquery-1.3.2.min.js");
  addJavaScript2HtmlHeader("/js/awt.js");
  require_once("html_header.inc.php");
?>
<script type="text/javascript">
<!--
  $(document).ready(
    function(){
      var arr = c$('album-container');
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
  );
//-->
</script>
<form enctype="multipart/form-data" action="" method="post" id="photoalbumlist_form">
<table style="width:100%;" cellspacing="0" cellpadding="0">
  <tr><td style="text-align:left;" class="pagename"><?php print($page_title)?></td></tr>
  <tr>
    <td style="text-align:left;padding:10px;">
  <input name="command" value="" type="hidden" />
<?php PhotoAlbum::printStats()?>
      <div style="text-align:-moz-left;">
      <table style="padding:5px;" cellspacing="0" cellpadding="0">
        <tr>
          <td>
<?php 
  foreach($list as $album){
    if($album["is_hidden"] == 1){
    	$showTitle = "Показывать альбом в фотогалерее";
      $showCommand = "show";
    }
    else{
      $showTitle = "Не показывать альбом в фотогалерее";
      $showCommand = "hide";
    }
?>
    <div class="album-container" style="float:left;padding:4px;">
      <table cellspacing="0" cellpadding="0">
        <tr>
          <td>
            <table onclick="window.location.href='/admin/photoalbum.php?photoalbum_id=<?php printf("%d",$album["id"])?>'" class="L arial mainColor cursor-ptr" style="width:<?php printf("%d",$max_thumb_width+20)?>px;border:1px solid #acacac;padding:3px;<?php print($album["is_hidden"]?"background-image:url(/images/blackmask.png);background-repeat:repeat;":"")?>" cellspacing="0" cellpadding="0">
              <tr><td class="bold center"><?php hprint($album["name"])?></td></tr>
              <tr><td class="center"><img src="/albumthumb.php?photoalbum_id=<?php print($album["id"]);?>" style="width:<?php print($album["thumb_width"])?>px;height:<?php print($album["thumb_height"])?>px;" alt="" /></td></tr>
              <tr>
                <td class="S italic left nowrap">
<?php
  if(strcmp($album["date_created"],$album["date_updated"]) != 0) 
    hprint(sprintf("Создан %s\nОбновлён %s\nСодержит %d фото (%.1fКб)",Localize::dbdate($album["date_created"]),Localize::dbdate($album["date_updated"]),$album["photo_count"],floatval($album["total_size"])/1024));
  else  
    hprint(sprintf("Создан %s\nСодержит %d фото (%.1fКб)",Localize::dbdate($album["date_created"]),$album["photo_count"],floatval($album["total_size"])/1024));
?>
                </td>
              </tr>
            </table>
          </td>
          <td class="vtop left">
            <div title="Открыть альбом для редактирования"
            class="cursor-ptr" 
            onmouseover="this.style.backgroundPosition='0px -22px'" 
            onmouseout="this.style.backgroundPosition='0px 0px'" 
            onclick="this.style.backgroundPosition='0px -44px';setTimeout('window.location.href=\'/admin/photoalbum.php?photoalbum_id=<?php printf("%d",$album["id"])?>\'',200)" 
            style="width:22px;height:22px;background-image:url(/images/btn-edit.gif);background-repeat:no-repeat;background-position:0px 0px;"></div>
            <div title="<?php hprint($showTitle)?>"
            class="cursor-ptr" 
            onmouseover="this.style.backgroundPosition='0px -22px'" 
            onmouseout="this.style.backgroundPosition='0px 0px'" 
            onclick="this.style.backgroundPosition='0px -44px';setTimeout('window.location.href=\'/admin/photoalbumlist.php?photoalbum_id=<?php printf("%d",$album["id"])?>&amp;command=<?php print($showCommand)?>\'',200)" 
            style="margin-top:3px;width:22px;height:22px;background-image:url(/images/btn-<?php print($showCommand)?>.gif);background-repeat:no-repeat;background-position:0px 0px;"></div>
            <div title="Удалить альбом"
            class="cursor-ptr" 
            onmouseover="this.style.backgroundPosition='0px -22px'" 
            onmouseout="this.style.backgroundPosition='0px 0px'" 
            onclick="this.style.backgroundPosition='0px -44px';if(confirm('Фотоальбом и все фотографии, которые в нём находятся, будут удалены.\nПродолжить?')){setTimeout('window.location.href=\'/admin/photoalbumlist.php?photoalbum_id=<?php printf("%d",$album["id"])?>&amp;command=delete\'',200);}" 
            style="margin-top:3px;width:22px;height:22px;background-image:url(/images/btn-delete.gif);background-repeat:no-repeat;background-position:0px 0px;"></div>
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
<?php require_once("html_footer.inc.php");?>
  