<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("http.inc.php");
  require_once("misc.inc.php");
  require_once("Localize.class.php");
  require_once("PhotoAlbum.class.php");
  require_once("PhotoAlbumList.class.php");
  require_once("User.class.php");
  require_once("common.inc.php");

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
  $page_title = "Кактусы и суккуленты из Харькова - Фотоальбомы";
  $show_menu = false;
  
  addJavaScript2HtmlHeader("/js/awt.js");
  addJavaScript2HtmlHeader("/js/ext-jquery/jquery-1.3.2.min.js");
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
<form action="" method="post" id="photoalbumlist_form">
<table style="width:100%;" cellspacing="0" cellpadding="0">
  <tr>
    <td style="text-align:left;padding:10px;">
  <input name="command" value="" type="hidden" />
      <div style="text-align:-moz-left;">
<?php PhotoAlbum::printStats()?>
<?php for($i = ord('A'); $i <= ord('Z'); $i++){?>
      <div class="vmid cursor-ptr bold courier M" onmouseover="this.style.backgroundPosition='0px -22px'" onmouseout="this.style.backgroundPosition='0px 0px'" onclick="this.style.backgroundPosition='0px -44px';setTimeout('window.location.href=\'/photobyfirstchar.php?char='+this.innerHTML+'\'',200)" style="float:left;width:22px;height:22px;line-height:22px;margin:auto 2px auto 0px;background:url(/images/btn-frame.gif) no-repeat;text-align:center;"><?php print(chr($i))?></div>
<?php }?>
<?php PhotoAlbum::printSearchByNameControl()?>
      <table style="clear:left;padding:5px;" cellspacing="0" cellpadding="0">
        <tr>
          <td>
<?php 
  foreach($list as $album){
?>
            <div class="album-container" style="float:left;padding:4px;">
              <table cellspacing="0" cellpadding="0">
                <tr>
                  <td>
                    <table onclick="window.location.href='/photoalbum.php?photoalbum_id=<?php printf("%d",$album["id"])?>'" class="L arial mainColor cursor-ptr" style="width:<?php printf("%d",$max_thumb_width+20)?>px;border:1px solid #acacac;padding:3px;" cellspacing="0" cellpadding="0">
                      <tr><td class="bold center"><?php hprint($album["name"])?></td></tr>
                      <tr><td class="center"><a href="<?php print(mkurl('/photoalbum.php',array('photoalbum_id'=>$album["id"],'n'=>$album["name"])))?>" onclick="return false"><img src="/albumthumb.php?photoalbum_id=<?php print($album["id"]);?>" style="border:0;width:<?php print($album["thumb_width"])?>px;height:<?php print($album["thumb_height"])?>px;" alt="" /></a></td></tr>
                      <tr>
                        <td class="S italic left nowrap">
<?php
  if(strcmp($album["date_created"],$album["date_updated"]) != 0) 
    hprint(sprintf("Создан %s\nОбновлён %s\nСодержит %d фото (%.1fКб)",Localize::dbdate($album["date_created"]),Localize::dbdate($album["date_updated"]),$album["exposed_count"],floatval($album["exposed_size"])/1024));
  else  
    hprint(sprintf("Создан %s\nСодержит %d фото (%.1fКб)",Localize::dbdate($album["date_created"]),$album["exposed_count"],floatval($album["exposed_size"])/1024));
?>
                        </td>
                      </tr>
                    </table>
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
  