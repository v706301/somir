<?php
  ini_set("include_path", ini_get("include_path") . ":" . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("PhotoAlbum.class.php");
  require_once("common.inc.php");
  
  $ids = getSessionVar("slideshow-photos",false);
  $availWidth = getSessionVar("avail-width",false);
  $availHeight = getSessionVar("avail-height",false);
  $dim = PhotoAlbum::dbGetSlideShowDimensions($ids,$availWidth,$availHeight);
  $name = "Слайдшоу";
  //trace_r($dim);
?>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?php hprint($name);?></title>
    <style type="text/css">
      #slideshow-container{margin:0px;padding:0px;font-family:Arial;}
      #slideshow-container *{margin:0px;padding:0px;}
      #image img {position:absolute; z-index:25; width:auto}
      #slideleft:hover {background-color:#333333;}
      #slideright:hover {background-color:#333333;}
    </style>
    <script type="text/javascript" src="/js/ajax.js.php"></script>
    <script type="text/javascript" src="/js/awt.js"></script>
    <script type="text/javascript" src="/js/slideshow.js"></script>
    <script type="text/javascript">
    <!--
      function adjust(){
        var x = c$('asd');
        var container = document.getElementById('slideshow-container');
        var w = <?php printf("%d",$dim["width"])?>;
        var h = <?php printf("%d",$dim["height"])?>;
        container.style.width = w + 'px';
        container.style.height = h + 'px';
        window.resizeTo(w,h);
        var dim = dim$();
        while(container.offsetWidth > dim.windowWidth){
          //var s = "before: "+container.offsetWidth+" <=> "+dim.offsetWidth+"\r\n";
          window.resizeTo(++w,h);
          dim = dim$();
          //alert(s+"after: "+container.offsetWidth+" <=> "+dim.windowWidth);
        }
        while(container.offsetHeight > dim.windowHeight){
          window.resizeTo(w,++h);
          dim = dim$();
        }
        window.moveTo((window.screen.availWidth-w)/2,(window.screen.availHeight-h)/2);

        ajcall(
          '/ajaxcalls/load_slides.php',
          'photos='+'<?php print($ids)?>',
          'Не удалось загрузить фото для слайдшоу',
          function(result){
            var slides = [];
            for(var i = 0; i < result.length; i++){
              slides[i] = {};
              slides[i].name = result[i].photo_name;
              slides[i].desc = result[i].photo_desc;
              slides[i].photoURL = '/photo.php?photo_id='+result[i].id;
              slides[i].thumbURL = '/photothumb.php?photo_id='+result[i].id;
              slides[i].thumbWidth = result[i].thumb_width;
              slides[i].thumbHeight = result[i].thumb_height;
              slides[i].photoWidth = Math.floor(result[i].photo_width * <?php print($dim["ratio"])?>);
              slides[i].photoHeight = Math.floor(result[i].photo_height * <?php print($dim["ratio"])?>);
            }
            slideshow = new Slide_Show("slideshow");
            slideshow.init(slides,"image","imgprev","imgnext","information","thumbnails","slider","slideleft","slideright");
            slideshow.start();
          }
        );
      }
    //-->
    </script>
  </head>
<body style="overflow:hidden;margin:0px;padding:0px;" onload="adjust()">
<!--div id="slideshow-debug" style="position:absolute;width:100%;height:100%;vertical-align:top;font-weight:bold;font-size:16px;background-color:#00ff00;opacity:.50;filter:alpha(opacity=50)"></div-->
  <div id="slideshow-container" class="mainFont mainColor L" style="padding-top:2px;background-color:#000000;overflow:hidden;">
    <div id="fullsize" style="border-bottom:1px solid #cccccc;position:relative;width:<?php printf("%d",$dim["photo_width"])?>px; height:<?php printf("%d",$dim["photo_height"])?>px; padding:2px; background:#000000;">
      <div id="imgprev" style="left:0px;background:url(/images/slideshow/left.gif) left center no-repeat;position:absolute; width:15%; height:<?php printf("%d",$dim["photo_height"])?>px; cursor:pointer; z-index:150;" title="Previous Image"></div>
      <div id="imglink"></div>
      <div id="imgnext" style="right:0px; background:url(/images/slideshow/right.gif) right center no-repeat;position:absolute;width:15%;height:<?php printf("%d",$dim["photo_height"])?>px;cursor:pointer;z-index:150;" title="Next Image"></div>
      <div id="image" style="width:<?php printf("%d",$dim["photo_width"])?>px;height:<?php printf("%d",$dim["photo_height"])?>px;"></div>
      <div id="information" style=" position:absolute;bottom:0px;width:<?php printf("%d",$dim["photo_width"])?>px;height:0px;background:#000000;color:#ffffff;overflow:hidden;z-index:200;">
        <h3 style="padding:4px 8px 3px; font-size:14px;"></h3>
        <p style="padding:0px 4px 8px 8px;font-size:11px;"></p>
      </div>
    </div>
    <div id="thumbnails" style="vertical-align:top;margin-top:5px;width:<?php printf("%d",$dim["photo_width"])?>;height:<?php printf("%d",$dim["thumb_height"])?>px;">
      <div id="slideleft" style="float:left; width:20px; height:<?php printf("%d",$dim["thumb_height"])?>px; background:url(/images/slideshow/scroll-left.gif) center center no-repeat; background-color:#222222;" title="Slide Left"></div>
      <div id="slidearea" style="float:left; position:relative; width:<?php printf("%d",$dim["thumb_area"])?>px; margin:0px 5px;height:<?php printf("%d",$dim["thumb_height"])?>px;overflow:hidden;">
        <div id="slider" style="vertical-align:top;position:absolute; left:0px; height:<?php printf("%d",$dim["thumb_height"])?>px;"></div>
      </div>
      <div id="slideright" style="float:right;width:20px;height:<?php printf("%d",$dim["thumb_height"])?>px; background:url(/images/slideshow/scroll-right.gif) center center no-repeat; background-color:#222222;" title="Slide Right"></div>
    </div>
  </div>
</body>
</html>