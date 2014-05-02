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
    redirect("/photosearch.php");
  }

  if(!isParameter("char"))
    redirect("/photoalbumlist.php");
  $char = getParameter("char");
  $char = strtoupper($char);

  //save page ref in session
  setSessionVar("LAST_VISITED_PHOTO_PAGE",SELF."?".$_SERVER["QUERY_STRING"]);

  checkSafePageRefresh("char=".$char);

  $max_thumb_width     = intval(getSetting("max_item_thumb_width",ITEM_THUMB_W));
  $max_thumb_height    = intval(getSetting("max_item_thumb_height",ITEM_THUMB_H));
  $photos = PhotoAlbum::dbReadPhotosByChar($char);
  $show_menu = false;
  $page_title = "Фотографии на букву ".$char;

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
      loadPhotos('<?php echo $char?>');
    }
  );

  function selectCharButton(){
    var arr = c$('char-button');
    var form = e$('search_form');
    var char = form.elements['char'].value;
    for(var i = 0; i < arr.length; i++){
      var x = arr[i];
      x.style.backgroundPosition = x.innerHTML == char ? '0px -44px' : '0px 0px';
    }
  }

  function omo(el,tc){
    var fc = e$('search_form').elements['char'].value;
    el.style.backgroundPosition = (fc==tc ? '0px -44px' : '0px 0px');    
  }
  function oc(el,tc){
    var form = e$('search_form');
    form.elements['char'].value = tc;
    selectCharButton();
    //el.style.backgroundPosition='0px -44px';
    var code = 'loadPhotos(\'' + tc + '\')';
    setTimeout(code,200);    
  }
//-->
</script>
<form action="" method="post" id="search_form">
<table style="width:100%;" cellspacing="0" cellpadding="0">
  <tr>
    <td style="text-align:left;padding:10px;">
  <input name="command" value="" type="hidden" />
  <input name="char" value="<?php print($char)?>" type="hidden" />
      <div style="text-align:-moz-left;">
<?php PhotoAlbum::printStats()?>
<?php for($i = ord('A'); $i <= ord('Z'); $i++){?>
        <div 
        class="char-button<?php print(chr($i)==$char ? " char-button-selected":"")?>"
        onmouseover="this.style.backgroundPosition='0px -22px'"
        onmouseout="omo(this,'<?php print(chr($i))?>')" 
        onclick="oc(this,'<?php print(chr($i))?>')"
        ><?php print(chr($i))?></div>
<?php }?>
      <div style="float:left;">
        <div id="slideshow-btn" onclick="startSlideShow()" style="background:url(/images/slideshow/29x29_slideshow.png) no-repeat;width:29px;height:29px;cursor:pointer;"></div>
      </div>
      <div style="float:left;margin:3px;"><a class="mainFont mainColor M" href="/photoalbumlist.php">Вернуться к списку фотоальбомов</a></div>

<?php PhotoAlbum::printSearchByNameControl()?>
      
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
  