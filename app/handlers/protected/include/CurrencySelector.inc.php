<?php
  $bTable = false;
  $map = Currency::getLabelMap();
  foreach($map as $key => $val){
    if($currency->getCoef($key) > 0.0){
      if(!$bTable){
        $bTable = true;
?>
                    <table class="mainFont mainColor L" cellspacing="0" cellpadding="0">
                      <tr>
<?php
      }
?>
                        <td style="padding-right:5px;">
                          <a <?php if($prefs->get(COOKIE_KEY_CURRENCY)!=$key){?>href="?command=set_currency&amp;currency=<?php hprint($key)?>"<?php }?> style="padding:0px 2px 0px 2px;text-decoration:none;<?php print($prefs->get(COOKIE_KEY_CURRENCY)==$key?"background-color:red;color:white;":"color:".maincolor())?>"><?php hprint(Currency::getLabel($key))?></a>
                        </td>                        
<?php
    }
  }
  if($bTable){
?>
                      </tr>
                    </table>
<?php
  }
?>
