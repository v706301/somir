<?php require_once("http.inc.php");?>
<?php require_once("User.class.php");?>
<?php print("<?xml version=\"1.0\" encoding=\"UTF-8\"?>");?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title><?php hprint($page_title);?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="author" content="Victor Orlov, v706301@gmail.com" />
    <meta name="keywords" content="кактус, суккулент, литопс, культивар, кактусы, суккуленты, литопсы, цветы, семена, экзотические растения, cactus, succulent, lithops, seeds, miroshnichenko, мирошниченко, харьков, kharkov, астрофитум, astrophytum, фото, photo, Cactaceae, Agavaceae, Aizoaceae, Apocynaceae, Asphodelaceae, Euphorbiaceae, Portulacaceae" />
    <meta name="description" content="Кактусы и суккуленты из Харькова от Оли и Сергея Мирошниченко, Cactuses and succulents from Olga and Sergey Miroshnichenko, Kharkov, Ukraine" />
    <meta name="robots" content="index,all" />
<?php printCSS2HtmlHeader();?>
    <link rel="shortcut icon" href="/favicon.ico" />
<?php
  if(!is_null($javascript_arr)){
    $jsfiles = array_keys($javascript_arr);
    for($xi = 0; $xi < count($jsfiles); $xi++){
      if(strcasecmp($jsfiles[$xi],"calendar") == 0){
        if(file_exists($_SERVER["DOCUMENT_ROOT"]."/js/jscalendar/lang/calendar-".strtolower($lang_key).".js"))
          $jscalendar_lang = "/js/jscalendar/lang/calendar-".strtolower($lang_key).".js";
        else
          $jscalendar_lang = "/js/jscalendar/lang/calendar-en.js";
?>
    <link rel="stylesheet" type="text/css" media="all" href="/js/jscalendar/css/calendar-win2k-cold-1.css" title="win2k-cold-1" />
    <script type="text/javascript" src="/js/cal.js"></script>
    <script type="text/javascript" src="/js/jscalendar/calendar.js"></script>
    <script type="text/javascript" src="<?php print($jscalendar_lang)?>"></script>
    <script type="text/javascript" src="/js/jscalendar/calendar-setup.js"></script>
<?php
      }
?>
    <script type="text/javascript" src="<?php print($jsfiles[$xi]);?>"></script>
<?php
    }
  }
?>
    <script type="text/javascript">
    <!--
      function onResize(){
        var content = null,header = null,footer = null;
        var container = false;
        if(document.documentElement)
          container = document.documentElement;
        else if(document.body)
          container = document.body;
        if(container){
          pageContent = document.getElementById("x_content");
          pageFooter = document.getElementById("x_footer");
          pageHeader = document.getElementById("x_header");
          if(container.clientHeight >= container.scrollHeight){
            pageContent.style.height = (container.clientHeight - pageHeader.clientHeight - pageFooter.clientHeight - 10) + "px";
          }
          if(container.clientWidth >= container.scrollWidth){
            pageContent.style.width = container.clientWidth + "px";
          }
        }
      }
      window.onresize = onResize;
    //-->
    </script>

    <script type="text/javascript">
    <!--
      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', 'UA-17783328-1']);
      _gaq.push(['_trackPageview']);
      (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
      })();
    //-->
    </script>
  </head>
  <body id="body" style="margin:0px;padding:0px;font-family:Tahoma;font-size:11px;">
    <div id="stringWidthMeter" style="position:absolute;visibility:hidden;z-index:1001;margin:0px;padding:0px;border:1px solid red;background-color:#ffffff;width:100px;height:100px;overflow:scroll;top:50px;left:50px;white-space:nowrap;"></div>
    <div id="x_fullpage1" style="z-index:1;">
      <table id="x_fullpage2" style="width:100%;text-align:center;" cellspacing="0" cellpadding="0">
        <tr><td id="x_header" style="height:100px;background-image:url(/images/top.jpg);background-repeat:repeat-x;">&nbsp;</td></tr>
        <tr>
          <td id="x_content" style="border-top:5px solid <?php maincolor()?>;border-bottom:5px solid <?php maincolor()?>;vertical-align:top;">
            <table style="width:100%;" cellspacing="0" cellpadding="0">
              <tr>
<?php if(User::isOperatorAdmin(User::getOperatorId())){?>
                <td id="x_menu" style="border-right:5px solid <?php maincolor()?>;vertical-align:top;text-align:right;padding-left:5px;padding-right:5px;padding-top:5px;width:<?php printf("%d",MENU_TOTAL_WIDTH)?>px;">
<?php require_once("menu.inc.php");?>
                </td>
<?php }?>
                <td style="vertical-align:top;text-align:<?php print($show_menu ? "left":"center")?>;">
                  <table cellspacing="0" cellpadding="3" style="width:100%;text-align:center;">
                    <tr>
                      <td style="margin:0px;padding:0px;text-align:center;">
                        <div style="text-align:-moz-center;margin:0px;padding:0px;">
<?php if($bClientNavigation){?>
<div class="cmenu-container">
  <a href="/photoshop.php" class="cmenu<?php print(SELF=="/photoshop.php"?" cmenu-sel":"")?>"><?php hprint('Взрослые растения')?></a>
  <a href="/index.php" class="cmenu<?php print(SELF=="/index.php"?" cmenu-sel":"")?>"><?php echo hprint('Семена и сеянцы')?></a>
  <a id="scart-menuitem" style="display:<?php print(isSessionVar(SHOPPING_CART_KEY) ? "inline":"none")?>" href="/scart.php" class="cmenu<?php print(SELF=="/scart.php"?" cmenu-sel":"")?>"><?php hprint('Корзинка')?></a>
  <a id="ckout-menuitem" style="display:<?php print(isSessionVar(SHOPPING_CART_KEY) ? "inline":"none")?>" href="/ckout.php" class="cmenu<?php print(SELF=="/ckout.php"?" cmenu-sel":"")?>"><?php hprint('Заказ')?></a>
  <a href="/photoalbumlist.php" class="cmenu<?php print((SELF=="/photoalbumlist.php"||SELF=="/photoalbum.php"||SELF=="/photobyfirstchar.php")?" cmenu-sel":"")?>"><?php hprint('Фотогалерея')?></a>
  <a href="/contacts.php" class="cmenu<?php print(SELF=="/contacts.php"?" cmenu-sel":"")?>"><?php hprint('Контакты')?></a>
</div>
<?php require_once '_currency_and_cart.inc.php'?>
<?php }?>

