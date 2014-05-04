<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("http.inc.php");
  require_once("misc.inc.php");
  require_once("Localize.class.php");
  require_once("PhotoAlbum.class.php");
  require_once("PhotoAlbumList.class.php");
  require_once("common.inc.php");

  if(isCommand("search")){
    setSessionVar("search_photos", getParameter("search"));
  }

  //save page ref in session
  setSessionVar("LAST_VISITED_PHOTO_PAGE",SELF."?".$_SERVER["QUERY_STRING"]);

  checkSafePageRefresh();
  $search = getSessionVar("search_photos",false);
  $max_thumb_width     = intval(getSetting("max_item_thumb_width",ITEM_THUMB_W));
  $max_thumb_height    = intval(getSetting("max_item_thumb_height",ITEM_THUMB_H));
  //$photos = PhotoAlbum::dbSearchPhotos($search);
  $show_menu = false;
  $page_title = "Поиск фотографий по названию";

  addJavaScript2HtmlHeader("/js/awt.js");
  addJavaScript2HtmlHeader("/js/ajax.js.php");
  addJavaScript2HtmlHeader("/js/scart.js.php");
  addJavaScript2HtmlHeader("/js/ext-jquery/jquery-1.3.2.min.js");
  require_once("html_header.inc.php");
?>
<script type="text/javascript">
<!--
  $(document).ready(
    function(){
      loadPhotos();
    }
  );
//-->
</script>
<form action="" method="post" id="search_form" onsubmit="loadPhotos();return false;">
<table style="width:100%;" cellspacing="0" cellpadding="0">
  <tr>
    <td style="text-align:left;padding:10px;">
  <input name="command" value="search" type="hidden" />
      <div style="text-align:-moz-left;">
<?php PhotoAlbum::printStats()?>
<?php for($i = ord('A'); $i <= ord('Z'); $i++){?>
        <div class="vmid cursor-ptr bold courier M" onmouseover="this.style.backgroundPosition='0px -22px'" onmouseout="this.style.backgroundPosition='0px 0px'" onclick="this.style.backgroundPosition='0px -44px';setTimeout('window.location.href=\'/photobyfirstchar.php?char='+this.innerHTML+'\'',200)" style="float:left;width:22px;height:22px;line-height:22px;margin:auto 2px auto 0px;background:url(/images/btn-frame.gif) no-repeat;text-align:center;"><?php print(chr($i))?></div>
<?php }?>
      <div style="float:left;"><div id="slideshow-btn" onclick="startSlideShow()" style="background:url(/images/slideshow/29x29_slideshow.png) no-repeat;width:29px;height:29px;cursor:pointer;"></div></div>
      <div style="float:left;margin:3px;"><a class="mainFont mainColor M" href="/photoalbumlist.php"><?php hprint('Вернуться к списку фотоальбомов')?></a></div>
      <div class="mainFont mainColor L" style="clear:both;padding:10px 0px 10px 0px">
        <div class="bold" style="float:left;margin-right:5px;line-height:22px;vertical-align:middle;"><?php hprint('Поиск по названию')?></div>
        <div style="float:left;"><input type="text" style="width:300px" name="search" value="<?php hprint($search)?>" class="text"/></div>
        <div style="float:left;"><button id="search_btn" name="search_btn" type="submit" style="margin:0px 3px;width:22px;height:22px;border:none;background:url(/images/btn-search.gif) no-repeat transparent;" onmouseover="this.style.backgroundPosition='0px -22px'" onmouseout="this.style.backgroundPosition='0px 0px'" onclick="this.style.backgroundPosition='0px -44px';setTimeout('e$(\'search_btn\').style.backgroundPosition=\'0px 0px\'',200);"></button></div>
      </div>
      <div style="clear:both;text-align:-moz-left;">
      <table style="padding:5px;" cellspacing="0" cellpadding="0">
        <tr>
          <td id="photo-list" class="vtop">
          </td>
        </tr>
      </table>
      </div>
    </td>
  </tr>
</table>
</form>
<?php require_once("html_footer.inc.php");?>
  