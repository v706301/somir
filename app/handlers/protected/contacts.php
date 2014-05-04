<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("http.inc.php");
  require_once("misc.inc.php");
  require_once("settings.inc.php");
  require_once("common.inc.php");

  $show_menu = false;
  $page_title = "Контакты";
  $p = new UserPreferences();
  $ru = $p->get(COOKIE_KEY_LANG) == 'ru';
  $addr = getSetting("contacts-address".($ru ? '':'-en'));
  $phone = getSetting("contacts-phone");
  $email = getSetting("contacts-email");
  require_once("html_header.inc.php");
  
?>
<table style="width:100%;" cellspacing="0" cellpadding="0">
  <tr>
    <td style="text-align:center;padding:10px;">
      <div style="text-align:-moz-center;">
      <table style="padding:25px;" cellspacing="0" cellpadding="0">
        <tr>
          <td style="width:700px;">
            <table class="mainFont mainColor XXL" cellspacing="0" cellpadding="0">
<?php if(strlen($addr) > 0){?>
              <tr>
                <td style="padding:0px 5px 20px 0px;text-align:right;font-weight:bold;vertical-align:top;"><?php echo $ru ? 'Почтовый адрес':'Postal address'?>:</td>
                <td style="padding:0px 5px 20px 0px;text-align:left;"><?php hprint($addr)?></td>
              </tr>
<?php }?>
<?php if(strlen($phone) > 0){?>
              <tr>
                <td style="padding:0px 5px 20px 0px;text-align:right;font-weight:bold;vertical-align:top;"><?php echo $ru ? 'Телефон':'Phone'?>:</td>
                <td style="padding:0px 5px 20px 0px;text-align:left;"><?php hprint($phone)?></td>
              </tr>
<?php }?>
<?php if(strlen($email) > 0){?>
              <tr>
                <td style="padding:0px 5px 20px 0px;text-align:right;font-weight:bold;vertical-align:top;"><?php echo $ru ? 'Электронная почта':'Email'?>:</td>
                <td style="padding:0px 5px 20px 0px;text-align:left;"><?php hprint($email)?></td>
              </tr>
<?php }?>
            </table>
          </td>
        </tr>
      </table>
      </div>
    </td>
  </tr>
</table>
<?php require_once("html_footer.inc.php");?>
  