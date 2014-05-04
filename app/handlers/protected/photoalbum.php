<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("misc.inc.php");
  require_once("Localize.class.php");
  require_once("PhotoAlbum.class.php");
  require_once("PhotoAlbumList.class.php");
  require_once("CactiStatus.class.php");
  require_once("Currency.class.php");
  require_once("Item.class.php");
  require_once("ShoppingCart.class.php");
  require_once("ShoppingCartEntry.class.php");
  require_once("UserPreferences.class.php");
  require_once("common.inc.php");

  if(isCommand("search")){
    setSessionVar("search_photos", getParameter("search"));
    redirect("/photosearch.php");
  }

  if(!isParameter("photoalbum_id"))
    redirect("/photoalbumlist.php");
  $photoalbum_id = getParameter("photoalbum_id");
  $album = new PhotoAlbum();
  try{
    $album->dbRead($photoalbum_id);
  }
  catch(Exception $e){
    redirectAlert("/photoalbumlist.php",$e->getMessage());
  }

  //save page ref in session
  setSessionVar("LAST_VISITED_PHOTO_PAGE",SELF."?".$_SERVER["QUERY_STRING"]);


  checkSafePageRefresh(sprintf("photoalbum_id=%d",$album->getId()));

//  $max_thumb_width     = intval(getSetting("max_item_thumb_width",ITEM_THUMB_W));
//  $max_thumb_height    = intval(getSetting("max_item_thumb_height",ITEM_THUMB_H));
  $photos = $album->dbReadPhotos("name");
  $show_menu = false;
  $page_title = lang("Кактусы и суккуленты из Харькова")." - " . str_replace("<br />"," ",htmlize($album->getName()));

  addJavaScript2HtmlHeader("/js/awt.js");
  addJavaScript2HtmlHeader("/js/ajax.js.php");
  addJavaScript2HtmlHeader("/js/scart.js.php");
  addJavaScript2HtmlHeader("/js/ext-jquery/jquery-1.3.2.min.js");
  
  $prefs = new UserPreferences();
  $scart = getSessionVar(SHOPPING_CART_KEY,false);
  if(!is_null($scart)){
    if($scart->getTotal($prefs->getCurrencyCoef()) <= 0.0){
      clearSessionVar(SHOPPING_CART_KEY);
      $scart = null;
    }
  }
  
  //setFormFocus("photoalbum_form","photoalbum_name");
  require_once("html_header.inc.php");
?>
<script type="text/javascript">
<!--
  $(document).ready(
    function(){
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
  );

//-->
</script>



<form action="" method="post" id="photoalbum_form">
<table style="width:100%;" cellspacing="0" cellpadding="0">
  <tr>
    <td style="text-align:left;padding:10px;">
  <input name="command" value="" type="hidden" />
  <input name="photoalbum_id" value="<?php printf("%d",$album->getId())?>" type="hidden" />
      <div style="text-align:-moz-left;">
<?php PhotoAlbum::printStats()?>
<?php for($i = ord('A'); $i <= ord('Z'); $i++){?>
        <div class="vmid cursor-ptr bold courier M" onmouseover="this.style.backgroundPosition='0px -22px'" onmouseout="this.style.backgroundPosition='0px 0px'" onclick="this.style.backgroundPosition='0px -44px';setTimeout('window.location.href=\'/photobyfirstchar.php?char='+this.innerHTML+'\'',200)" style="float:left;width:22px;height:22px;line-height:22px;margin:auto 2px auto 0px;background:url(/images/btn-frame.gif) no-repeat;text-align:center;"><?php print(chr($i))?></div>
<?php }?>
      <div style="float:left;"><div id="slideshow-btn" onclick="startSlideShow()" style="background:url(/images/slideshow/29x29_slideshow.png) no-repeat;width:29px;height:29px;cursor:pointer;"></div></div>
      <div style="float:left;margin:3px;"><a class="mainFont mainColor M" href="/photoalbumlist.php">Вернуться к списку фотоальбомов</a></div>
<?php PhotoAlbum::printSearchByNameControl()?>

      <div style="clear:left;float:left;padding:4px;"><img src="/albumthumb.php?photoalbum_id=<?php print($album->getId());?>" style="width:<?php print($album->getThumbWidth())?>px;height:<?php print($album->getThumbHeight())?>px;" alt="" /></div>
      <div style="float:left;" class="L arial mainColor cursor-ptr">
        <div class="bold left XXL"><?php hprint($album->getName())?></div>
        <div class="left L" style="margin-bottom:10px;"><?php hprint($album->getDescription())?></div>
        <div class="italic left S" style="margin-bottom:10px;">
<?php
  if(strcmp($album->getDateCreated(),$album->getDateUpdated()) != 0) 
    hprint(sprintf("Создан %s\nОбновлён %s\nСодержит %d фото (%.1fКб)",Localize::dbdate($album->getDateCreated()),Localize::dbdate($album->getDateUpdated()),$album->getPhotoCount(),floatval($album->getTotalSize())/1024));
  else  
    hprint(sprintf("Создан %s\nСодержит %d фото (%.1fКб)",Localize::dbdate($album->getDateCreated()),$album->getPhotoCount(),floatval($album->getTotalSize())/1024));
?>
        </div>
      </div>

      <div style="clear:both;text-align:-moz-left;">
      <table style="padding:5px;" cellspacing="0" cellpadding="0">
        <tr>
          <td class="vtop">
<?php 
  foreach($photos as $photo){
  	echo CactiStatus::renderForSale($photo,$prefs);
	}
?>
          </td>
        </tr>
      </table>
      </div>
    </td>
  </tr>
</table>
</form>
<?php require_once("html_footer.inc.php");?>
  