<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("user.inc.php");
  require_once("f_http.inc.php");
  require_once("f_misc.inc.php");
  //require_once("f_upload.inc.php");
  require_once("ImportInventory.class.php");
  require_once("common.inc.php");
  
  if(!isLoggedIn())
    redirect("/index.php");
    
  if(!isAdmin(getId()))
    redirect("/index.php");

  
  $content_margin_top = 10;
  $content_width = "100%";
  
  $db = getDatabase();
  $result = false;
  if(isCommand("upload")){
    if(isset($_FILES["file_csv"])){
      $upload = $_FILES["file_csv"];
      if($upload["error"] == 1 || $upload["error"] == 2){
        $upload_max_filesize = ini_get("upload_max_filesize");
        if($upload_max_filesize)
          $upload_max_filesize = " of ".$upload_max_filesize."]";
        setAlert("Error processing ".$upload["name"]." - file size exceeds maximum allowed".$upload_max_filesize);
      }
      else if($upload["error"] == 3){
        setAlert("File ".$upload["name"]." was only partially uploaded");
      }
      else if(is_uploaded_file($upload["tmp_name"]) && $upload["error"] == 0){
        $tmp = tempnam("/tmp","file_csv");
        $result = move_uploaded_file($upload["tmp_name"],$tmp);
        if($result){
          $parser = new ImportInventory();
          $bIgnoreFirstLine = strlen(getParameter("ignore_firstline")) > 0;
          $parser->parse($tmp,"ITEM",$bIgnoreFirstLine);
        }
        else{
          setAlert("Error processing ".$upload["name"]." - cannot move file");
        }
        unlink($tmp);
        $alert = getAlert();
        if(!is_null($alert)){
          setSessionVar(SELF."_error_log",$alert);
          clearAlert();
        }
        if($parser->inserted > 0 || $parser->updated > 0){
          setAlert("".$parser->inserted." inventory records added.");
          setAlert("".$parser->updated." existing inventory records modified.");
          setAlert("Total inventory updated: ".($parser->inserted+$parser->updated));
        }
      }
    }
  }
  
  checkSafePageRefresh();

  $page_title = "INVENTORY UPLOAD";
  $subtitle = "UPLOAD INVENTORY AS CSV FILE";
  $javascript_arr = array("misc.js");
  
  require_once("html_header.inc.php");
?>
<script type="text/javascript">
  function ck(form){
    var msg = "";
    if(!checkInputNotEmpty(form.elements["file_csv"],"Please choose CSV file for upload")){
      return false;
    }
    else
      return true;
  }
</script>
<form id="ck_mod_upload_form" enctype="multipart/form-data" action="" method="post" onsubmit="return ck(this)">
  <input type="hidden" name="command" value="upload" >
<table width="100%" cellspacing="0" cellpadding="0">
  <tr><td style="text-align:left;" class="pagename"><?php print($page_title)?></td></tr>
  <tr><td style="text-align:left;" class="subtitle"><?php print($subtitle)?></td></tr>
  <tr>
    <td style="text-align:center;">
      <table style="margin-top: 15px;" cellspacing="0" cellpadding="2">
        <tr>
          <td class="mainFont L" style="text-align:right;">Upload inventory:</td>
          <td>
  <input type="file" name="file_csv" style="width:196px;" class="text" >
          </td>
        </tr>
        <tr>
          <td></td>
          <td>
            <table class="mainFont L" cellspacing="0" cellpadding="0">
              <tr>
                <td>
  <input type="checkbox" name="ignore_firstline" id="ignore_firstline" >
                </td>
                <td><label for="ignore_firstline">First line contains column names</label></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td></td>
          <td style="text-align:left;" class="tpad10">
  <button type="submit" name="submit_btn" class="button">Upload</button>
          </td><td></td><td></td>
        </tr>
        <tr>
          <td></td>
          <td style="text-align:left;" class="tpad10">
  <button type="button" name="return_btn" onclick="return ckExitDontSave('/index.php')" class="button">Exit - Do not upload</button>
          </td><td></td><td></td>
        </tr>
<?php 
  $error_log = getSessionVar(SELF."_error_log",true);
  if(!is_null($error_log)){
?>        
        <tr>
          <td style="vertical-align:top;">Error log:</td>
          <td style="text-align:left;" class="tpad10"></td>
          <td></td><td></td>
        </tr>
        <tr>
          <td colspan="4" style="text-align:left;" class="tpad10">
  <textarea readonly rows="100" cols="200" style="width:400px; height: 200px;" name="error_log" class="text"><?php hprint($error_log,false)?></textarea>
          </td>
        </tr>
<?php }?>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <br >
      <table style="width:100%" cellspacing="0" cellpadding="0">
        <tr><td style="width:100%;text-align:left;" colspan="2" class="subtitle">Note</td></tr>
        <tr><td style="text-align:right;" class="rpad10 mainFont mainColor L">File format:</td><td class="mainFont mainColor L">CSV</td></tr>
        <tr><td style="text-align:right;" class="rpad10 mainFont mainColor L">Fields:</td><td class="mainFont mainColor L">SKU,Name,Price,Description,Package Qty(Items Per Package),Unit of Measure</td></tr>
      </table>
    </td>
  </tr>
</table>
</form>
<?php require_once("html_footer.inc.php");?>