<?php
  require_once("PlantFamily.class.php");
  $bSearching = (!is_null($search_item));
?>
<?php if($bSearching){?>
<script type="text/javascript">
  function clearSearchFields(form){
    form.elements['search_item'].value='';
  }
</script>
<?php }?>
<form id="search_form" action="" method="post">
            <table class="mainColor mainFont L" style="width:700px;background-color:#ffffff;" cellspacing="0" cellpadding="2">
              <tr>
                <td style="border-bottom:2px solid #008000;padding-bottom:4px;">
  <input type="hidden" name="command" value="search" />
                  <table class="mainColor mainFont L" cellspacing="0" cellpadding="1">
                    <tr>
                      <td id="lblShowfilter1" class="mainColor mainFont XL rpad2"><?php hprint('Показывать')?></td>
                      <td style="text-align:left;">
  <select class="select" name="showfilter1" onchange="this.form.submit()">
    <option<?php print($showfilter1 == "all"?" selected=\"selected\"":"")?> value="all"><?php hprint('Всё(Растения и семена)')?></option>
    <option<?php print($showfilter1 == "plant"?" selected=\"selected\"":"")?>  value="plant"><?php hprint('Только растения')?></option>
    <option<?php print($showfilter1 == "seed"?" selected=\"selected\"":"")?>  value="seed"><?php hprint('Только семена')?></option>
  </select>                
                      </td>
                      <td id="lblShowfilter4" class="lpad5 mainColor mainFont XL rpad2"><?php hprint('семейство')?></td>
                      <td style="text-align:left;">
  <select class="select" name="showfilter4" onchange="this.form.submit()">
    <option value=""><?php hprint('Все')?></option>
<?php 
  for($i = 0; $i < PlantFamily::getCount(); $i++){
?>
    <option<?php print(strcasecmp(PlantFamily::get($i),$showfilter4) == 0 ? " selected=\"selected\"":"")?> value="<?php hprint(PlantFamily::get($i))?>"><?php hprint(PlantFamily::getName($i))?></option>
<?php
  }
?>
  </select>
                      </td>
                    </tr>
                  </table>
                </td>
              <tr>
                <td>
                  <table class="mainColor mainFont L" cellspacing="0" cellpadding="1">
                    <tr>
                      <td style="padding-left:4px;padding-right:4px;"><?php hprint('Поиск')?></td>
                      <td><input type="text" class="input" style="width:200px;" name="search_item" value="<?php hprint($search_item)?>" /></td>
                      <td>
                        <table cellspacing="0" cellpadding="0">
                          <tr>
                            <td style="width:32px;vertical-align:top;">
        <button name="submit_btn" type="submit"
        style="margin:0px 2px;width:22px;height:22px;border:none;background:url(/images/btn-search.gif) no-repeat transparent;" 
        onmouseover="this.style.backgroundPosition='0px -22px'" 
        onmouseout="this.style.backgroundPosition='0px 0px'" 
        onclick="this.style.backgroundPosition='0px -44px'"
        ></button>                            
                            </td>
                            <td style="width:25px;">
        <button name="clear_btn" type="submit"
        style="visibility:<?php print($bSearching?"visible":"hidden")?>;margin:0px 2px;width:22px;height:22px;border:none;background:url(/images/btn-delete.gif) no-repeat transparent;" 
        onmouseover="this.style.backgroundPosition='0px -22px'" 
        onmouseout="this.style.backgroundPosition='0px 0px'" 
        onclick="this.style.backgroundPosition='0px -44px';clearSearchFields(this.form)"
        ></button>                            
                            </td>
                          </tr>
                        </table>
                      </td>
                      <td style="width:80%;">
                        <table class="mainColor mainFont L" cellspacing="0" cellpadding="0">
                          <tr>
                            <td style="text-align:left;">
                              <table class="mainColor mainFont L" cellspacing="0" cellpadding="0">
                                <tr>
                                  <td class="lpad5 mainColor mainFont XL tpad2" style="text-align:right;">
  <input id="idShowfilter3" type="checkbox" onclick="this.form.submit()" name="showfilter3"<?php print(strlen($showfilter3)>0?" checked=\"checked\"":"")?> />
                                  </td>
                                  <td id="lblShowfilter3" class="mainColor mainFont XL rpad2"><label for="idShowfilter3"><?php hprint('только новые')?></label></td>
                                  <td class="lpad5 mainColor mainFont XL tpad2" style="text-align:right;">
  <input id="idShowfilter5" type="checkbox" onclick="this.form.submit()" name="showfilter5"<?php print(strlen($showfilter5)>0?" checked=\"checked\"":"")?> />
                                  </td>
                                  <td id="lblShowfilter5" class="mainColor mainFont XL rpad2"><label for="idShowfilter5"><?php hprint('только с фото')?></label></td>
                                </tr>
                              </table>
                            </td>
                          </tr>
                          <tr>
                            <td style="text-align:left;">
                              <table class="mainColor mainFont L" cellspacing="0" cellpadding="0">
                                <tr>
                                  <td id="lblShowfilter2" class="lpad5 mainColor mainFont XL rpad2"><?php hprint('наличие')?></td>
                                  <td style="text-align:left;">
  <select class="select" name="showfilter2" onchange="this.form.submit()">
    <option<?php print($showfilter2 == "all"?" selected=\"selected\"":"")?> value="all"><?php hprint('Не имеет значения')?></option>
    <option<?php print($showfilter2 == "available"?" selected=\"selected\"":"")?>  value="available"><?php hprint('Те,которые есть в наличии')?></option>
    <option<?php print($showfilter2 == "notavailable"?" selected=\"selected\"":"")?>  value="notavailable"><?php hprint('Те,которых нет в наличии')?></option>
  </select>                
                                  </td>
                                </tr>
                              </table>
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
