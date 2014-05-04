<?php
  error_reporting(E_ALL);
  set_time_limit(180);
  $SHOW_TIMING = true;
  if(isset($SHOW_TIMING) && $SHOW_TIMING === true){
    list($usec,$sec) = explode(" ", microtime());
    $startTimeMillis = floatval($usec) + floatval($sec);
    $TIMING_TABLE = array("Enter: ".date("Y-m-d H:i:s",(int)$startTimeMillis));
  }
  define("SELF",$_SERVER["PHP_SELF"]);
  define("DEBUGMODE",true);
  define("DISABLE_CACHING",false);
  define("LOG_LEVEL_VERBOSE",2);
  define("LOG_LEVEL_DEBUG",1);
  define("LOG_LEVEL_RELEASE",0);
  define("LOG_LEVEL",LOG_LEVEL_DEBUG);
  define("BUILTIN_ADMIN_ACCOUNT",0);
  define("BUILTIN_ADMIN_ACCOUNT_TITLE","Администратор сайта");
  define("COOKIE_NAME","somir-01");
  define("MENU_TOTAL_WIDTH",200);
  define("MENU_DOT_WIDTH",25);

  //ПРЕФИКС ДЛЯ НОМЕРОВ РАСТЕНИЙ ИЗ ПРАЙС-ЛИСТА
  define("PREL",'M');
  //ПРЕФИКС ДЛЯ НОМЕРОВ РАСТЕНИЙ ИЗ ФОТОАЛЬБОМОВ
  define("PREF",'U');


  if(!defined("NO_SESSION")){
    session_start();
  }
  $writable_directory = "/tmp";
  $default_admin_email = "v706301@gmail.com";
  $default_admin_phone = "XXX-XXX-XXXX";
  $default_techsupport_email = "v706301@gmail.com";
  $admin_email = null;
  $techsupport_email = null;
  $application_name = "Кактусы и суккуленты из Харькова";
  $application_email = "astrophytum.som@gmail.com";
  $application_addr = "";
  $application_phone = "+380979102555";
  $application_fax = "";
  $page_title = (isset($page_title) && strlen($page_title) > 0 ? $page_title : $application_name);
  $page_width         = "100%";
  $show_menu          = true;
  $maincolor          = "#3ba83b";
  function maincolor(){global $maincolor;print($maincolor);}
  $mainfont           = "Tahoma";
  function mainfont(){global $mainfont;print($mainfont);}
  $mainbkg            = "#ffffff";
  $mainbkg2           = "#eeeeff";
  function mainbkg(){global $mainbkg;print($mainbkg);}
  $disabledcolor      = "#dddddd";
  function disabledcolor(){global $disabledcolor;print($disabledcolor);}
  $content_width = "100%";
  require_once("db.inc.php");
  $cdb    = null;
  $dbtype = DBTYPE_MYSQL;
  $dbhost = "localhost";
  $dbport = "";
  $dbname = "cactusmi_db";
  $dbuser = 'somir';//"cactusmi_user";
  $dbpass = '123';//"123456";

  $dbavailable = true;
  $dbprefix = "so";
  $command_arr = null;
  $javascript_arr = array();
  $css_arr = array("/style.css.php" => "/style.css.php");
  $command_arr = null;
  $meta_arr = array();
  $javascriptOnloadChain = array("onResize");
  $copyright = "&copy; 2009 <a class=\"copyright\" href=\"mailto:astrophytum.som@gmail.com\">(S+O)xM</a>.";
  $items_per_page = 10;
  require_once("debug.inc.php");
  require_once("const.inc.php");
  require_once("http.inc.php");
  require_once("settings.inc.php");
  require_once("maxlength.inc.php");
  require_once("replacements.inc.php");
  function reportError($title,$text = null,$email = null,$file_line = null){
    global $maincolor;
    if(defined("COMMANDLINE")){
      print("\r\n".$title."\r\n".$text."\r\n".$file_line."\r\n");
    }
    else{
      global $admin_email;
      if(strlen($email) == 0)
        $email = $admin_email;
      if(!isset($text) || is_null($text) || strlen(trim($text)) == 0){
        $text = "[Unknown error]";
      }
      if(!is_null($email) && strlen(trim($email)) > 0)
        $contact = "Please, contact <a class=\"alert\" href=\"mailto: ".$email."?Subject=".htmlize($text,false)."\">administrator</a>";
      else
        $contact = "";
      if(strlen($title) > 0)
        print("<div title=\"".htmlize($file_line,false)."\" style=\"font-size: 24px;\" class=\"alert\"><br /><br />".$title."<br /><br /></div>");
      print("<div style=\"font-size: 18px;\" class=\"alert\"><br /><br />".$text."<br />".$contact."<br /><br /></div>");
      if(defined("DEBUGMODE")){
        if(strlen($file_line) > 0){
          print("\r\n\r\n<a style=\"cursor: pointer;\" onclick=\"var di = document.getElementById('debuginfo'); if(di.style.display=='none'){di.style.display='block';}else{di.style.display='none';}\"><img src=\"/images/dsign.jpg\" alt=\"DEBUG\"></a>\r\n");
          print("\r\n\r\n<div id=\"debuginfo\" style=\"display: none; border: 1px solid black; background-color: ".$maincolor."; color: #ffffff;\">\r\n<p>".htmlize($file_line)."</p>\r\n</div>\r\n");
        }
      }
      flush();
    }
    exit;
  }
  function getSuperuserEmail(){
    global $default_admin_email;
    global $admin_email;
    global $dbavailable;
    global $dbprefix;
    if(is_null($admin_email)){
      $db = getDatabase();
      if($dbavailable){
        $rs = $db->query(sprintf("select email from ".$dbprefix."_user where id=%d",BUILTIN_ADMIN_ACCOUNT),__FILE__.":".__LINE__);
        if($rs && $row = $db->fetch_array($rs))
          $admin_email = $row[0];
        $db->free_result($rs);
      }
      if(is_null($admin_email))
        $admin_email = $default_admin_email;
    }
    return $admin_email;
  }
  function techsupport(){
    global $default_techsupport_email;
    global $techsupport_email;
    global $dbavailable;
    global $dbprefix;
    if(is_null($techsupport_email)){
      $db = getDatabase();
      if($dbavailable){
        $rs = $db->query("select val from ".$dbprefix."_settings where name='techsupport'",__FILE__.":".__LINE__);
        if($rs && $row = $db->fetch_array($rs))
          $techsupport_email = $row[0];
        $db->free_result($rs);
      }
      if(is_null($techsupport_email))
        $techsupport_email = $default_techsupport_email;
    }
    return $techsupport_email;
  }
  function dblog($level,$type,$message,$failure = false){
    global $dbprefix;
    if(defined("COMMANDLINE")){
      print($message."\n");
      flush();
    }
    if($level <= LOG_LEVEL){
      global $dbavailable;
      $query = sprintf("insert into ".$dbprefix."_log(type,message,time) values(%s,%s,%s);",sqlsafe($type),sqlsafe($message),sqlsafe(date("Y-m-d H:i:s")));
      $db = getDatabase();
      if($dbavailable)
        $db->query($query,__FILE__.":".__LINE__);
      if($failure)
        exit;
    }
  }
  function catchError($errno, $errstr, $errfile, $errline){
    switch($errno) {
      case E_USER_ERROR:
        $xml = "<user_error>\n<id>%d</id><description>%s</description>\n</user_error>\n";
        break;
      case E_USER_WARNING:
        $xml = "<user_warning>\n<id>%d</id><description>%s</description>\n</user_warning>\n";
        break;
      case E_USER_NOTICE:
        $xml = "<user_notice>\n<id>%d</id><description>%s</description>\n</user_notice>\n";
        break;
      case E_ERROR:
        $xml = "<error>\n<id>%d</id><description>%s</description>\n</error>\n";
        break;
      case E_WARNING:
        $xml = "<warning>\n<id>%d</id><description>%s</description>\n</warning>\n";
        break;
      case E_PARSE:
        $xml = "<parse>\n<id>%d</id><description>%s</description>\n</parse>\n";
        break;
      case E_NOTICE:
        $xml = "<notice>\n<id>%d</id><description>%s</description>\n</notice>\n";
        break;
      case E_CORE_ERROR:
        $xml = "<core_error>\n<id>%d</id><description>%s</description>\n</core_error>\n";
        break;
      case E_CORE_WARNING:
        $xml = "<core_warning>\n<id>%d</id><description>%s</description>\n</core_warning>\n";
        break;
      case E_COMPILE_ERROR:
        $xml = "<compile_error>\n<id>%d</id><description>%s</description>\n</compile_error>\n";
        break;
      case E_COMPILE_WARNING:
        $xml = "<compile_warning>\n<id>%d</id><description>%s</description>\n</compile_warning>\n";
        $s = "Warning: ";
        break;
      case E_ALL:
        $xml = "<all>\n<id>%d</id><description>%s</description>\n</all>\n";
        break;
      case E_STRICT:
        $xml = "<strict>\n<id>%d</id><description>%s</description>\n</strict>\n";
        break;
      case E_RECOVERABLE_ERROR:
        $xml = "<recoverable_error>\n<id>%d</id><description>%s</description>\n</recoverable_error>\n";
        break;
    }
    $s = htmlize($errstr."\r\n[".$errfile.":".$errline."]",false,false,false);
    $xml = sprintf($xml,$errno,$s);
    print($xml);
  }

  function getWriteableDirectory($bReportError = true){
    global $writable_directory;
    if(isset($writable_directory) && is_writeable($writable_directory))
      return $writable_directory;
    else if(is_writeable("/tmp"))
      return "/tmp";
    else{
      if($bReportError)
        reportError("Directory not writable","Directory: '".$writable_directory."' is not writeable",getSuperuserEmail(),__FILE__.":".__LINE__);
      else
        return null;
    }
  }

  $bClientNavigation = strpos(SELF,'/admin') === false;
//  SELF == "/index.php" ||
//  SELF == "/ckout.php" ||
//  SELF == "/contacts.php" ||
//  SELF == "/index.php" ||
//  SELF == "/photoalbum.php" ||
//  SELF == "/photoshop.php" ||
//  SELF == "/photoalbumlist.php" ||
//  SELF == "/photobyfirstchar.php" ||
//  SELF == "/scart.php" ||
//  SELF == "/slideshow.php";
  
  if(defined("DISABLE_CACHING") && DISABLE_CACHING == true){
    header("Expires: Tue, 1 Jan 1970 0:0:1 GMT");
    header("Pragma: no-cache");
    header("Cache-Control: no-cache");
  }
  addJavaScript2HtmlHeader("/js/form.js.php");

  $max_thumb_width     = intval(getSetting("max_item_thumb_width",ITEM_THUMB_W));
  $max_thumb_height    = intval(getSetting("max_item_thumb_height",ITEM_THUMB_H));

  require_once 'UserPreferences.class.php';
  UserPreferences::setCurrency();
  UserPreferences::setLang();
  $prefs = new UserPreferences();
  $langFile = $_SERVER['DOCUMENT_ROOT'].'/include/lang/'.$prefs->get(COOKIE_KEY_LANG).'.php';
  if(file_exists($langFile)){
    require_once $langFile;
  }
  