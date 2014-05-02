<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("User.class.php");
  require_once("http.inc.php");
  require_once("misc.inc.php");
  require_once("common.inc.php");
  define("PROFILE_PAGE",1);
  if(!User::isLoggedIn() || !User::isOperatorAdmin(User::getOperatorId()))
    redirect("/index.php");
  $user_id = User::getOperatorId();
  $user = null;
  $db = getDatabase();
  $command = getParameter("command");
  if(isCommand("update")){
    $_POST["user_id"] = User::getOperatorId(); 
    $user = User::dbReadUser(User::getOperatorId());
    $user->httpRead();
    if($user->dbUpdate()){
      redirect(SELF);
    }
    else{
      setSessionVar(SELF."_uo",$user);
      checkSafePageRefresh("uo=1");
    }
  }
  checkSafePageRefresh();
  if(isParameter("uo")){
    $user = getSessionVar(SELF."_uo");
  }
  else{
    $user = User::dbReadUser(User::getOperatorId());
  }
  $page_title = "Данные учетной записи";
  require_once("html_header.inc.php");
?>
<script type="text/javascript">
  function ck(form){
    var msg = "";
<?php if(!User::isOperatorAdmin(User::getOperatorId())){?>
    if(isInputEmpty(form.elements["fname"],null)){
      msg = (msg.length > 0 ? "\r\n" : "") + "Необходимо указать имя";
    }
    if(isInputEmpty(form.elements["lname"],null)){
      msg = (msg.length > 0 ? "\r\n" : "") + "Необходимо указать фамилию";
    }
<?php }?>
    if(isInputEmpty(form.elements["email"],null)){
      msg = (msg.length > 0 ? "\r\n" : "") + "Необходимо указать адрес электронной почты";
    }
    if(msg.length > 0){
      alert(msg);
      return false;
    }
    else{
      disableButtons();
      return true;
    }
  }
</script>
<form id="ck_mod_profile_form" action="" method="post" onsubmit="return ck(this)">
  <input type="hidden" name="modified" value="" >
  <input type="hidden" name="command" value="update" >
<table width="100%" cellspacing="0" cellpadding="0">
  <tr><td style="text-align:left;" class="pagename"><?php print(User::isOperatorBuiltInAdmin() ? "САМЫЙ ГЛАВНЫЙ": $page_title)?></td></tr>
  <!-- tr><td style="text-align:left;" class="subtitle">Обновить данные учетной записи</td></tr-->
  <tr>
    <td style="text-align:center;">
      <table style="margin-top: 15px;margin-left:15px;" class="mainFont mainColor L" cellspacing="0" cellpadding="2">
<?php if(!User::isOperatorAdmin()){?>
        <tr>
          <td class="mainFont L" style="text-align:right;">Имя для логина:</td>
          <td>
  <input readonly <?php mxl($dbprefix."_user.username");?> type="text" name="username" value="<?php hprint($user->getUsername());?>" style="width:130px; text-transform:none;" class="input" />
          </td>
          <td></td><td></td>
        </tr>
<?php }?>
        <tr>
          <td class="mainFont L" style="text-align:right;">Пароль:</td>
          <td>
  <input <?php mxl($dbprefix."_user.password");?> type="password" name="password1" style="width:130px;" value="" class="input" />
          </td>
          <td style="text-align:right;padding-left: 10px;" class="mainFont L">Подтвердить пароль:</td>
          <td>
  <input <?php mxl($dbprefix."_user.password");?> type="password" name="password2" style="width:130px;" value="" class="input" />
          </td>
        </tr>
        <tr>
          <td class="mainFont L" style="text-align:right;">Адрес электронной почты:</td>
          <td>
  <input <?php mxl($dbprefix."_user.email");?> type="text" name="email" style="width:130px;" class="email" value="<?php hprint($user->getEmail())?>" />
          </td>
          <td></td><td></td>
        </tr>
<?php if(!User::isOperatorAdmin()){?>
        <tr>
          <td class="mainFont L" style="text-align:right;">Имя:</td>
          <td>
  <input <?php mxl($dbprefix."_user.fname");?> type="text" name="fname" value="<?php hprint($user->getFname());?>" style="width:130px;" class="input" />
          </td>
          <td></td><td></td>
        </tr>
        <tr>
          <td class="mainFont L" style="text-align:right;">Фамилия:</td>
          <td>
  <input <?php mxl($dbprefix."_user.lname");?> type="text" name="lname" value="<?php hprint($user->getLname());?>" style="width:130px;" class="input" />
          </td>
          <td></td><td></td>
        </tr>
        <tr>
          <td class="mainFont L" style="text-align:right;">Адрес(первая строчка):</td>
          <td>
  <input <?php mxl($dbprefix."_user.address1");?> type="text" name="address1" value="<?php hprint($user->getAddress1());?>" style="width:130px;" class="input" />
          </td>
          <td></td><td></td>
        </tr>
        <tr>
          <td class="mainFont L" style="text-align:right;">Адрес(вторая строчка):</td>
          <td>
  <input <?php mxl($dbprefix."_user.address2");?> type="text" name="address2" value="<?php hprint($user->getAddress2());?>" style="width:130px;" class="input" />
          </td>
          <td></td><td></td>
        </tr>
        <tr>
          <td class="mainFont L" style="text-align:right;">Город:</td>
          <td>
  <input <?php mxl($dbprefix."_user.city");?> type="text" name="city" value="<?php hprint($user->getCity());?>" style="width:130px;" class="input" />
          </td>
          <td></td><td></td>
        </tr>
        <tr>
          <td class="mainFont L" style="text-align:right;">Почтовый индекс:</td>
          <td>
  <input <?php mxl($dbprefix."_user.zip");?> type="text" name="zip" value="<?php hprint($user->getZip());?>" style="width:130px;" class="input" />
          </td>
          <td></td><td></td>
        </tr>
        <tr>
          <td class="mainFont L" style="text-align:right;">Страна:</td>
          <td>
  <input <?php mxl($dbprefix."_user.city");?> type="text" name="country" value="<?php hprint($user->getCountry());?>" style="width:130px;" class="input" />
          </td>
          <td></td><td></td>
        </tr>
<?php }?>
        <tr>
          <td></td>
          <td style="text-align:center;" class="tpad10">
  <button type="submit" name="submit_btn" style="width:130px;" class="button">Сохранить данные</button>
          </td>
          <td></td><td></td>
        </tr>
        <tr>
          <td></td>
          <td style="text-align:center;" class="tpad10">
  <button type="button" name="return_btn" onclick="return onPageUnload(this.form,'/index.php')" class="button">Выход без сохранения</button>
          </td>
          <td></td><td></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>
<?php require_once("html_footer.inc.php");?>
