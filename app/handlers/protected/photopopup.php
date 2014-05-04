<?php
  ini_set("include_path", ini_get("include_path") . ":" . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("User.class.php");
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
    "photo_name\n" .
    ",photo_file\n" .
    ",photo_desc\n" .
    ",photo_keywords\n" .
    ",photo_width\n" .
    ",photo_height\n" .
    ",is_hidden\n" .
    "from ".$dbprefix."_photo p\n" .
    "where p.id=%d"
    ,$photo_id);
    //trace($query);
    $rs = $db->query($query,__FILE__.":".__LINE__);
    if($rs && $row = $db->fetch_assoc($rs)){
      $name = $row["photo_name"];
      $filename = $row["photo_file"];
      $desc = $row["photo_desc"];
      $keywords = $row["photo_keywords"];
      $bHidden = $row["is_hidden"] == 1;
      $w = $row["photo_width"];
      $h = $row["photo_height"];
      if(intval($w) <= 0 || intval($h) <= 0){
        $error = "Поломанная фотка";
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
    <title><?php hprint('Кактусы и суккуленты из Харькова - '.$name);?></title>
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
      var infoHeight = 0;
      var info = null;
      var animateInfo = null;
       
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

      function showInfo(){
        //alert(infoHeight + "/" + info.offsetHeight);
        if(info.offsetHeight >= infoHeight){
          clearTimeout(animateInfo);
        }
        else{
        	var h = info.offsetHeight + 5;
          if(h > infoHeight)
            h = infoHeight;
          info.style.height = h + 'px';
        }
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
        setTimeout("loadMainImage()",200);
        window.focus();
      }
      function loadMainImage(){
<?php if(!$bHidden || (User::isLoggedIn() && User::isOperatorAdmin(User::getOperatorId()))){?>      
        var dim = getViewportDimensions();
        photoDiv = document.getElementById("photoDiv");
        photoDiv.style.width = dim["width"] + "px";
        photoDiv.style.height = dim["height"] + "px";
        photoDiv.style.backgroundImage = "url(/photo.php?photo_id=<?php print($photo_id)?>)";
        info = document.getElementById('info');
        info.style.height = 'auto';
        infoHeight = parseInt(info.offsetHeight);
        info.style.height = '1px';
        animateInfo = setInterval('showInfo()',25);
<?php }else{?>
        photoDiv.innerHTML = "Фото скрыто от показа";
<?php }?>
      }
<?php
  }
?>
    </script>
  </head>
<body style="vertical-align:bottom;overflow:hidden;margin:0px;" <?php if(!$bClose){print(" onload=\"adjust()\"");}?>>
<?php
  if(!$bClose){
?>
<div id="info"
class="mainFont cursor-ptr" 
style="
z-index:100;
height:1px;
overflow:hidden;
background-color:#669966;
width:100%;
color:#ffffff;
vertical-align:top;
bottom:0px;
position:absolute;
opacity:.6;
filter:alpha(opacity=60);" 
onclick="window.close()">
<h3 class="cursor-ptr" style="margin:0px;"><?php hprint($name)?></h3>
<p class="cursor-ptr"><?php hprint($desc)?></p>
</div>

<div
onclick="window.close()"
id="photoDiv"
style="
width:100%;
height:100%;
<?php if(!$bHidden || (User::isLoggedIn() && User::isOperatorAdmin(User::getOperatorId()))){?>      
background-image:url(/images/loading50x50.gif);
background-repeat:no-repeat;
background-position:center center;
<?php }?>
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