<?php
  define("URLENCODE_PREFIX","URLENCODED();");
  require_once("mail.inc.php");
  require_once("common.inc.php");
  define("FOCUSED_FORM_FIELD","-FOCUSED-FORM-FIELD-");

  function mkurl($resource,$params = null){
  	$qs = '';
    if(is_null($params) || !is_array($params))
      $params = array();
    $first = true;
    foreach($params as $name => $value){
    	if($first){
    		$first = false;
        $qs .= '?' . urlencode($name) . '=' . urlencode($value);
    	}
      else
        $qs .= '&' . urlencode($name) . '=' . urlencode($value);
    }
    return $resource . $qs;
  }

  function chkie(){
    if(strpos(strtoupper($_SERVER["HTTP_USER_AGENT"]),"MICROSOFT") !== false)
      return true;
    else if(strpos(strtoupper($_SERVER["HTTP_USER_AGENT"]),"MSIE") !== false)
      return true;
    else
      return false;
  }
  function chkmoz(){
    if(strpos(strtoupper($_SERVER["HTTP_USER_AGENT"]),"GECKO") !== false)
      return true;
    else
      return false;
  }
  function addHttpProtocol($url){
    if(strncasecmp($url,"http://",strlen("http://")) == 0)
      $href = $url;
    else if(strncasecmp($url,"https://",strlen("https://")) == 0)
      $href = $url;
    else
      $href = "http://".$url;
    return $href;
  }

  function append2Url($url,$paramstring){
    if(strlen($paramstring) > 0){
      if(strpos($url,"?") !== false)
        $url .= "&" . $paramstring;
      else
        $url .= "?" . $paramstring;
    }
    return $url;
  }

  function utf8_bad_replace($str, $replace = '?') {
    $UTF8_BAD = '([\x00-\x7F]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2}|(.{1}))';
    $matches = array();
    $result = "";
    while (preg_match('/'.$UTF8_BAD.'/S', $str, $matches)) {
      if(!isset($matches[2]))
        $result .= $matches[0];
      else
        $result .= $replace;
      $str = substr($str,strlen($matches[0]));
    }
    return $result;
  }

  function htmlize($s,$bInsertBreaks = true,$bCreateLinks = true, $bReplace = false){
    global $replacements;
    $s = lang($s);
    if(strlen(trim($s)) > 0){
      $s = utf8_bad_replace($s,'?');
      $s = htmlentities($s,ENT_QUOTES,"UTF-8");
      if($bInsertBreaks){
        $tmp = str_replace("\n","\r",$s);
        $tmp = str_replace("\r\r","\r",$tmp);
        $tmp = str_replace("\r","<br />",$tmp);
        $s = $tmp;
      }
      $s = str_replace("\t","<span style=\"font-family:monospace;\">&nbsp;&nbsp;</span>",$s);
      if($bCreateLinks){
        //!!!20090409 - attribute target="_blank" removed since it is not supported by XHTML strict
        $s = preg_replace("/(?:^|\b)((((http|https|ftp):\/\/))([\w\.]+)([,:%#&\/?~=\w+\.-]+))(?:\b|$)/is","<a class=\"link\" href=\"$1\">$1</a>",$s);
        $s = preg_replace("/(?<!http:\/\/)(?:^|\b)(((www\.))([\w\.]+)([,:%#&\/?~=\w+\.-]+))(?:\b|$)/is", "<a class=\"link\" href=\"http://$1\">$1</a>", $s);
        $s = preg_replace("/(([\w\.]+))(@)([\w\.]+)\b/i", "<a class=\"link\" href=\"mailto:$0\">$0</a>", $s);
      }
      
      //replace trademarks
      if($bReplace){
        $arr_words = array_keys($replacements);
        $s = str_replace($arr_words, $replacements, $s);
      }
    }
    return $s;
  }
  
  function hprint($s,$bInsertBreaks = true,$bCreateLinks = false, $bReplace = false){
    print(htmlize($s,$bInsertBreaks,$bCreateLinks, $bReplace));
  }
  function hprintmax($s,$charcount,$bInsertBreaks = true,$bCreateLinks = false, $bReplace = false){
    if($charcount > 0 && strlen($s) > $charcount)
      $s = substr($s,0,$charcount-3) . "...";
    print(htmlize($s,$bInsertBreaks,$bCreateLinks, $bReplace));
  }

	function lang($key){
	  if(function_exists('lang_Impl')){
	    return call_user_func('lang_Impl',$key);
	  } else {
	    return $key;
	  }
	} 
	

  function jstext($s){
    $s = str_replace("'","\'",$s);
    $s = str_replace("\r","\n",$s);
    $s = str_replace("\n\n","\n",$s);
    $s = str_replace("\n","\\"."n",$s);
    return $s;
  }

  function jswindow(
            $url
            ,$name = null
            ,$menubar = false
            ,$statusbar = false
            ,$toolbar = false
            ,$location = false
            ,$resizable = true
            ,$scrollbars = true){
    if(strlen($name) == 0)
      $name = "_blank";
    return "var w = window.open('".$url."'".
    ",'".$name."','".
    "status=".($statusbar ? "yes":"no").
    ",toolbar=".($toolbar ? "yes":"no").
    ",menubar=".($menubar ? "yes":"no").
    ",location=".($location ? "yes":"no").
    ",resizable=".($resizable ? "yes":"no").
    ",scrollbars=".($scrollbars ? "yes":"no").
    "'); w.opener = self; w.focus();";
  }
  
  function jsStretch($w = -1,$h = -1){
?>
<script type="text/javascript">
<!--
  function stretch(){
    var w = <?php print($w < 0 ? "window.screen.availWidth":$w)?>;
    var h = <?php print($h < 0 ? "window.screen.availHeight":$h)?>;
    var x = (window.screen.availWidth - w)/2;
    var y = (window.screen.availHeight - h)/2;
    window.moveTo(x,y);
    window.resizeTo(w,h);
  }
  setTimeout("stretch()",10);
//-->
</script>
<?php
  }

  function redirect($s){
    if(strlen($s) == 0)
      $s = $_SERVER["PHP_SELF"]."?redir=error";
    global $SHOW_TIMING;
    if(isset($SHOW_TIMING) && $SHOW_TIMING == true){
      global $startTimeMillis;
      list($usec,$sec) = explode(" ", microtime());
      $endTimeMillis = floatval($usec) + floatval($sec);
      $extime = $endTimeMillis-$startTimeMillis;
      if(strpos($s,"?") === false)
        $s .= "?extime=".$extime;
      else
        $s .= "&extime=".$extime;
    }
    header("Location: ".$s);
    exit;
  }

  function redirectAlert($s,$alert){
    setAlert($alert);
    redirect($s);
  }

  function curlReadFile($url){
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_POST,0);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch,CURLOPT_VERBOSE, 0);  // optional - verbose debug output
    $content = curl_exec($ch);
    //$curl_array = curl_getinfo($ch);
    curl_close($ch);
    return $content;
  }

  function isPrefixEncoded($s){
    return strncmp($s,URLENCODE_PREFIX,strlen(URLENCODE_PREFIX)) == 0;
  }

  function prefixEncode($s){
    if(is_null($s) || strlen($s) == 0)
      return "";
    else if(isPrefixEncoded($s))
      return $s;
    else
      return URLENCODE_PREFIX . urlencode($s);
  }

  function prefixDecode($s){
    if(is_null($s) || strlen($s) == 0)
      return "";
    else if(!isPrefixEncoded($s))
      return $s;
    else
      return urldecode(substr($s,strlen(URLENCODE_PREFIX)));
  }
  
  function parameterEquals($name,$value){
    $x = getParameter($name);
    if(!is_null($x) && strcasecmp($x,$value) == 0)
      return true;
    else
      return false;
  }

  function isParameter($name){
    if(isset($_POST[$name])){
      return true;
    }
    else if(isset($_GET[$name])){
      return true;
    }
    else{
      return false;
    }
  }

  function setParameter($name,$val){
    if(isset($_GET[$name]))
      unset($_GET[$name]);
    $_POST[$name] = $val;
  }

  function getParameter($name){
    $value = null;
    if(isset($_POST[$name])){
      $value = $_POST[$name];
    }
    else if(isset($_GET[$name])){
      $value = $_GET[$name];
    }
    else{
      return null;
    }
    if(is_null($value))
      $value = "";
    else if(is_array($value))
      return getParameterArray($name);
    if(isPrefixEncoded($value))
      $value = prefixDecode($value);
    else if(get_magic_quotes_gpc() != 0)
      $value = stripslashes($value);
    return strlen($value) == 0 ? null : trim($value);
  }

  function getParameterArray($name){
    $value = null;
    if(isset($_POST[$name]))
      $value = $_POST[$name];
    else if(isset($_GET[$name]))
      $value = $_GET[$name];

    if(!is_null($value)){
      if(!is_array($value))
        $value = array($value);
    }
    for($i = 0; $i < count($value); $i++){
      if(isPrefixEncoded($value[$i]))
        $value[$i] = prefixDecode($value[$i]);
      else if(get_magic_quotes_gpc() != 0)
        $value[$i] = stripslashes($value[$i]);
    }
    return $value;
  }

  function getParameterNamesByPrefix($prefix){
    $arr = array();
    $keys = array_keys($_POST);
    for($i = 0; $i < count($keys); $i++){
      $key = $keys[$i];
      if(strncasecmp($key,$prefix,strlen($prefix)) == 0)
        $arr[] = $key;
    }
    $keys = array_keys($_GET);
    for($i = 0; $i < count($keys); $i++){
      $key = $keys[$i];
      if(strncasecmp($key,$prefix,strlen($prefix)) == 0)
        $arr[] = $key;
    }
    return $arr;
  }

  function setAlert($s = null){
    if(is_null($s) && isSessionVar("js_alert",false)){
      clearSessionVar("js_alert");
    }
    else if(!is_null($s) && strlen(trim($s)) > 0){
      if(isSessionVar("js_alert"))
        $s = getSessionVar("js_alert")."\r\n".$s;
      setSessionVar("js_alert",$s);
    }
  }

  function getAlert(){
    return getSessionVar("js_alert",false);
  }

  function clearAlert(){
    return clearSessionVar("js_alert",true);
  }

  function showAlert(){
    $alert = getSessionVar("js_alert");
    if(isset($alert) && !is_null($alert) && strlen($alert) > 0){
      clearSessionVar("js_alert");
      $alert = str_replace("'","\'",$alert);
      $alert = str_replace("\r","\n",$alert);
      $alert = str_replace("\n\n","\n",$alert);
      $alert = str_replace("\n","\\"."n",$alert);
?>
<script type="text/javascript">
<!--
  var alertMessage = '<?php print($alert)?>';
  function showAlert(){alert(alertMessage);}
  actionId = setTimeout("showAlert()",10);
//-->
</script>
<?php
    }
  }

  function getSessionKeys(){
    $arr = array();
    $keys = array_keys($_SESSION);
    for($i = 0; $i < count($keys); $i++){
      $arr[] = substr($keys[$i],strlen("_S_"));
    }
    return $arr;
  }
  
  function setSessionVar($name,$value){
    $name = "_S_".strtoupper($name);
    if(strlen(trim($name)) > 0){
      if(!is_null($value))
        $value = serialize($value);
      $_SESSION[$name] = $value;
    }
  }

  function checkSessionVar($name){
    return getSessionVar($name,false);
  }
  
  function getSessionVar($name,$bClear = true){
    $value = null;
    if(strlen(trim($name)) > 0){
      $name = "_S_".strtoupper($name);
      if(isset($_SESSION[$name])){
        $value = $_SESSION[$name];
      }
    }
    if($bClear && isset($_SESSION[$name])){
      $_SESSION[$name] = null;
      unset($_SESSION[$name]);
    }
    if(!is_null($value))
      $value = unserialize($value);
    return $value;
  }

  function clearSessionVar($name){
    if(strlen(trim($name)) > 0){
      $name = "_S_".strtoupper($name);
      if(isset($_SESSION[$name])){
        $_SESSION[$name] = null;
        unset($_SESSION[$name]);
      }
    }
  }

  function clearPageVars($name = null){
    if(is_null($name))
      $name = $_SERVER["PHP_SELF"];
    $keys = getSessionKeys();
    for($i = 0; $i < count($keys); $i++){
      if(strncasecmp($keys[$i],$name,strlen($name)) == 0)
        clearSessionVar($keys[$i]);
    }
    ;
    $name = "_S_".strtoupper($name);
    if(strlen(trim($name)) > 0){
      unset($_SESSION[$name]);
      session_unregister($name);
    }
  }

  function isSessionVar($name){
    $name = "_S_".strtoupper($name);
    if(isset($_SESSION[$name]))
      return true;
    else
      return false;
  }

  function clearAllSessionVars(){
    $keys = array_keys($_SESSION);
    for($i = 0; $i < count($keys); $i++){
      unset($_SESSION[$keys[$i]]);
      session_unregister($keys[$i]);
    }
    session_unset();
    session_destroy();
  }

  function checkSafePageRefresh($paramstring = null,$anchor = ""){
    if(isParameter("command") || strcasecmp($_SERVER["REQUEST_METHOD"],"POST") == 0){
      $s = append2Url($_SERVER["PHP_SELF"],$paramstring);
      $s .= $anchor;
      redirect($s);
    }
  }

  function isCommand($command){
    global $command_arr;
    if(is_null($command_arr))
      $command_arr = explode(":",getParameter("command"));
    //trace_r($command_arr);
    for($i = 0; $i < count($command_arr); $i++){
      if(strcasecmp($command,$command_arr[$i]) == 0)
        return true;
    }
    return false;
  }

  function getCommandsByPrefix($prefix){
    global $command_arr;
    if(is_null($command_arr))
      $command_arr = explode(":",getParameter("command"));
    $arr = array();
    for($i = 0; $i < count($command_arr); $i++){
      if(strncasecmp($command_arr[$i],$prefix,strlen($prefix)) == 0)
        $arr[] = $command_arr[$i];
    }
    return $arr;
  }

  function setFormFocus($formName,$fieldName){
    if(!isSessionVar(FOCUSED_FORM_FIELD)){
      if(strlen($formName) > 0 && strlen($fieldName) > 0)
        setSessionVar(FOCUSED_FORM_FIELD,$formName.":".$fieldName);
    }
  }

  function addMeta2HtmlHeader($meta){
    global $meta_arr;
    $meta_arr[] = $meta;
  }

  function printMeta2HtmlHeader(){
    global $meta_arr;
    if(!is_null($meta_arr) && count($meta_arr) > 0){
      $tags = array_values($meta_arr);
      for($i = 0; $i < count($tags); $i++){
        print("    ".trim($tags[$i])."\r\n");
      }
    }
  }

  function addCSS2HtmlHeader($css){
    global $css_arr;
    if(!in_array($css,$css_arr)){
      $css_arr[$css] = $css;
    }
  }

  function printCSS2HtmlHeader(){
    global $css_arr;
    if(!is_null($css_arr)){
      $cssfiles = array_keys($css_arr);
      for($xi = 0; $xi < count($cssfiles); $xi++){
?>
    <link href="<?php print($cssfiles[$xi]);?>" rel="stylesheet" type="text/css" />
<?php
      }
    }
  }


  function addJavaScript2HtmlHeader($scriptName){
    global $javascript_arr;
    if(!in_array($scriptName,$javascript_arr)){
      $javascript_arr[$scriptName] = $scriptName;
      //hack - we know that function from this script should be executed on onload
      if($scriptName == "/js/form.js.php")
        addToOnloadChain("saveInitialState");
    }
  }
  
  function addToOnloadChain($jsFunctionName){
    global $javascriptOnloadChain;
    $javascriptOnloadChain[] = $jsFunctionName;
  }

  function print3512OnloadHandler(){
    global $javascriptOnloadChain;
    global $javascriptFocus;
    if(count($javascriptOnloadChain) > 0 || isSessionVar(FOCUSED_FORM_FIELD)){
?>
<script type="text/javascript">
<!-- 
  function _3512OnloadHandler(){
    //
<?php
      for($i = 0; $i < count($javascriptOnloadChain); $i++){
        print("    ".$javascriptOnloadChain[$i]."();\n");
      }
      if(isSessionVar(FOCUSED_FORM_FIELD)){
        $fff = explode(":",getSessionVar(FOCUSED_FORM_FIELD));
        if(count($fff) == 2 && strlen($fff[0]) > 0 && strlen($fff[1]) > 0){
          printf("    if(document.forms[\"%s\"]){\n",$fff[0]);
          printf("      var _fxel = document.forms[\"%s\"].elements[\"%s\"];\n",$fff[0],$fff[1]);
          print( "      if(_fxel && _fxel.focus){\n");
          print( "        _fxel.focus();\n");
          print( "        if(_fxel.select)\n");
          print( "          _fxel.select();\n");
          print( "      }\n");
          print( "    }\n");
        }
      }
?>
  }
  window.onload = _3512OnloadHandler;
//-->
</script>
<?php
    }
  }
?>