<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("settings.inc.php");
  require_once("User.class.php");
  require_once("common.inc.php");
  
  if(!User::isOperatorBuiltInAdmin()){
    User::logoff();
  }

  $param_name = getParameter("param_name");
  $param_title = "";
  $param_value = "";
  $command = getParameter("command");
  if(strcasecmp($command,"set") == 0){
    $param_title = getParameter("param_title");
    $param_value = getParameter("param_value");
    setSetting($param_name,$param_title,$param_value);
  }
  else if(strcasecmp($command,"select") == 0){
    $param_name = getParameter("param_name");
    $param_value = getSetting($param_name);
    $param_title = getSettingTitle($param_name);
  }

  checkSafePageRefresh("param_name=".$param_name);

  $db = getDatabase();
  $query = "select * from ".$dbprefix."_settings where exposable=1";
  $rs = $db->query($query,__FILE__.":".__LINE__);
  
  addJavaScript2HtmlHeader("/js/awt.js");
  require_once("html_header.inc.php");
?>
<form id="settings_form" method="post" action="<?php print($_SERVER["PHP_SELF"])?>">
<table width="100%" cellspacing="0" cellpadding="0">
  <tr><td style="text-align:left;" class="pagename">Настройки</td></tr>
  <!-- tr><td style="text-align:left;" class="subtitle">&nbsp;</td></tr -->
  <tr>
    <td>
      <table cellspacing="0" cellpadding="0">
        <tr>
          <td>
  <input type="hidden" name="command" value="set" />
            <table style="width:100%;" cellspacing="5" cellpadding="0">
              <tr>
                <td style="text-align:right;white-space:nowrap;" class="mainFont L rpad5">Параметр:</td>
                <td width="400">
  <select onChange="this.form.elements['command'].value='select'; this.form.submit();" id="param_name" name="param_name" class="select" style="width:400px;" title="select parameter name from the drop-down list">
    <option value=""></option>
<?php
  if($rs){
    while($row = $db->fetch_array($rs)){
      if(strcasecmp($row["name"],$param_name) == 0)
        $param_title = $row["title"];
?>
    <option <?php if(strcasecmp($row["name"],$param_name) == 0){print(" selected "); $param_value=$row["val"];}?> value="<?php hprint($row["name"]);?>"><?php hprint($row["title"]);?></option>
<?php
    }
    $db->free_result($rs);
  }
?>
  </select>
                </td>
              </tr>
              <tr>
                <td style="text-align:right;white-space:nowrap;" class="mainFont L rpad5">Новое название:</td>
                <td width="400">
  <input type="text" name="param_title" class="input" style="width:100%;" value="<?php hprint($param_title)?>" />
                </td>
              </tr>
              <tr>
                <td style="text-align:right;vertical-align:top;white-space:nowrap;" class="mainFont L rpad5 tpad5">Значение:</td>
                <td width="400">
  <textarea rows="4" cols="100" name="param_value" class="text" style="border: 1px solid #000000;width:400px;height:100px;padding: 2px 2px 2px 2px;"><?php hprint($param_value,false);?></textarea>
                </td>
              </tr>
              <tr>
                <td style="text-align:center;vertical-align:top;" colspan="2">
  <button type="submit" name="submit_btn" class="button">Установить<br />значение</button>
  <button type="button" class="button" onclick="this.form.reset(); return false;" name="reset_btn">Сброс<br />изменений</button>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>
<script type="text/javascript">
  function adjustComboBox(){
    var x = document.getElementById("param_name");
    if(x)
      setComboWidth(x,"<?php mainfont()?>","normal","11px");
  }
<?php addToOnloadChain("adjustComboBox");?> 
</script>
<?php require_once("html_footer.inc.php");?>
