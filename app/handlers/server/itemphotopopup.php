<?php
  ini_set("include_path", ini_get("include_path") . ":" . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("common.inc.php");
  $error = "Фото не найдено";
  $photo_id = getParameter("photo_id");
  $bClose = false;
  if(strlen($photo_id) == 0){
    $bClose = true;
  }
  else{
    $db = getDatabase();
    $query = sprintf(
    "select\n" .
    "i.name\n" .
    ",ip.photo_width\n" .
    ",ip.photo_height\n" .
    "from ".$dbprefix."_item i\n" .
    "join ".$dbprefix."_photo ip on ip.item_id=i.id\n" .
    "where ip.id=%d"
    ,$photo_id);
    //trace($query);
    $rs = $db->query($query,__FILE__.":".__LINE__);
    if($rs && $row = $db->fetch_assoc($rs)){
      $name = $row["name"];
      $w = $row["photo_width"];
      $h = $row["photo_height"];
      if(intval($w) <= 0 || intval($h) <= 0){
        $error = "Поломаная фотка";
        $bClose = true;
      }
      $imageRatio = floatval($w)/floatval($h);
    }
    else{
      $bClose = true;
    }
    $db->free_result($rs);
  }
?>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?php hprint($name);?></title>
    <script type="text/javascript">
<?php
  if(!$bClose){
?>
      var imageRatio = <?php printf("%f",$imageRatio);?>;
      var width = <?php printf("%d",$w);?>;
      var height = <?php printf("%d",$h);?>;
      var maxwidth = window.screen.availWidth;
      var maxheight = window.screen.availHeight;
      var photoDiv = false;
      var dx = 0;
      var dy = 0;

      function getViewportDimensions(){
        var h = 0, w = 0;
        if(self.innerHeight){
          h = window.innerHeight;
          w = window.innerWidth;
        } 
        else{
          if(document.documentElement && document.documentElement.scrollHeight){
            h = document.documentElement.scrollHeight;
            w = document.documentElement.scrollWidth;
          }
          else{
            if(document.body){
              h = document.body.scrollHeight;
              w = document.body.scrollWidth;
            }
          }
        }
        return{height: parseInt(h,10),width: parseInt(w,10)};
      }

      function scale(width,height,maxwidth,maxheight){
        var result = null;
        if(width > maxwidth && height > maxheight){
          if(imageRatio >= 1.0){
            width = maxwidth; height = width/imageRatio;
          }
          else{
            height = maxheight; width = height*imageRatio;
          }
        }
        else if(width > maxwidth){
          width = maxwidth; height = width/imageRatio;
        }
        else if(height > maxheight){
          height = maxheight; width = height*imageRatio;
        }
        if(width > maxwidth || height > maxheight)
          result = scale(width,height,maxwidth,maxheight);
        else
          result = new Array(parseInt(width),parseInt(height));
        return result;
      }

      function adjust(){
        var xy = 450;
        window.resizeTo(xy,xy);
        window.moveTo((maxwidth-xy)/2,(maxheight-xy)/2);
        var dim = getViewportDimensions();
        dx = xy - dim["width"];
        dy = xy - dim["height"];

        maxwidth -= dx;
        maxheight -= dy;
        var wh = scale(width,height,maxwidth,maxheight);
        width = wh[0];
        height = wh[1];

        window.resizeTo(width+dx,height+dy);
        window.moveTo((window.screen.availWidth-(width+dx))/2,(window.screen.availHeight-(height+dy))/2);
        var dim = getViewportDimensions();
        setTimeout("loadMainImage()",1000);
        window.focus();
      }
      
      function loadMainImage(){
        var dim = getViewportDimensions();
        photoDiv = document.getElementById("photoDiv");
        photoDiv.style.width = dim["width"] + "px";
        photoDiv.style.height = dim["height"] + "px";
        photoDiv.style.backgroundImage = "url(/itemphoto.php?photo_id=<?php print($photo_id)?>)";
      }
<?php
  }
?>
    </script>
  </head>
<body style="overflow:hidden;margin:0px;" <?php if(!$bClose){print(" onload=\"adjust()\"");}?>>
<?php
  if(!$bClose){
?>
<div
onclick="window.close()"
id="photoDiv"
style="
width:100%;
height:100%;
background-image:url(/images/loading50x50.gif);
background-repeat:no-repeat;
background-position:center center;
padding:0px;
margin:0px;
border:none;
background-color:#f9d544;
font-family:Verdana;
font-size:11px;
overflow:hidden;
vertical-align:bottom;
text-align:center;
cursor:pointer;"
>
</div>

<?php
  }
  else{
?>
    <script LANGUAGE="JavaScript1.2">
      alert("<?php print($error);?>");
      window.close();
    </script>
<?php
  }
?>
</body>
</html>