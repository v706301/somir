<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("http.inc.php");
  require_once("User.class.php");
  require_once("common.inc.php");

  $command = getParameter("command");
  $username = null;
  if(strcasecmp($command,"login") == 0){
    $username = getParameter("username");
    $password = getParameter("password");
    if(User::authenticate($username,$password))
      redirect("/admin/itemlist.php");
  }
  if(User::isLoggedIn()){
    
  }
  else{
    $show_menu = false;
  }
  checkSafePageRefresh($username);
  setFormFocus("login_frm","username");
  require_once("html_header.inc.php");
?>
<?php
  if(!User::isLoggedIn()){
?>
<script type="text/javascript">
  function ck(form){
    disableButtons();
  }
</script>
<form id="login_frm" method="post" action="">
  <input type="hidden" name="command" value="login" />
            <table cellspacing="0" cellpadding="3" style="text-align:center;">
              <tr><td colspan="2" style="text-align:right;padding-top: 30px;" class="XL mainFont mainColor">Вход в административную часть сайта</td></tr>
              <tr>
                <td style="width:45%;text-align:right" class="XL mainFont mainColor bold">Учётная запись:</td>
                <td style="text-align:left;">
  <input type="text" name="username" class="text" style="width:130px;" value="<?php print(isParameter("username") ? htmlize(getParameter("username")) : "")?>" >
                </td>
              </tr>
              <tr>
                <td class="XL mainFont mainColor bold" style="text-align:right;">Пароль:</td>
                <td style="text-align:left;">
  <input type="password" name="password" class="text" style="width:130px;" >
                </td>
              </tr>
              <tr>
                <td></td>
                <td style="text-align:left;">
  <button name="submit_btn" type="submit" class="button">Вход</button>
                </td>
              </tr>
            </table>
</form>
<?php
  }
  else{
?>
<?php
  }
?>
<?php require_once("html_footer.inc.php");?>
