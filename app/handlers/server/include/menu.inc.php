<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("User.class.php");
  require_once("MenuSection.class.php");
  require_once("PhotoAlbumList.class.php");

  function printMenuSection($menu){
    if(is_null($menu->getLink())){
      $menulink = $menu->getName();
    }
    else{
      $menuname = strncmp($menu->getLink(),SELF,strlen(SELF)) == 0 ? "<span class=\"red\">".$menu->getName()."</span>" : $menu->getName();
      $menulink = sprintf("<a class=\"menuitem\" style=\"white-space:nowrap;font-weight: bold;\" href=\"#\" onclick=\"return onPageUnload(null,'%s');\">%s</a>",$menu->getLink(),$menuname);
    }
?>
                    <tr>
                      <td style="white-space:nowrap;text-align:left;margin-left:5px;width: <?php printf("%d",MENU_TOTAL_WIDTH-MENU_DOT_WIDTH)?>px;"><?php print($menulink)?></td>
                    </tr>
<?php
    for($i = 0; $i < $menu->size(); $i++){
      $menuname = strncmp($menu->getItemLinkAt($i),SELF,strlen(SELF)) == 0 ? "<span class=\"red\">".$menu->getItemNameAt($i)."</span>" : $menu->getItemNameAt($i);
      $itemlink = sprintf("<a class=\"menuitem\" href=\"#\" onclick=\"return onPageUnload(null,'%s');\">%s</a>",$menu->getItemLinkAt($i),$menuname);
?>
                    <tr>
                      <td style="white-space:nowrap;text-align:left;margin-left:5px;width: <?php printf("%d",MENU_TOTAL_WIDTH-MENU_DOT_WIDTH)?>px;">&nbsp;&nbsp;<?php print($itemlink)?></td>
                    </tr>
<?php
    }
  }

  function printMenu($menuarray){
    for($i = 0; $i < count($menuarray); $i++){
      $msection = $menuarray[$i];
      printMenuSection($msection);
    }
  }

  $menuarray = array();
  if(User::isOperatorAdmin()){
    require_once("Item.class.php");
    $arr = Item::getItemCountByType();
    $legend = 
    "<br />(<span title=\"".Item::getItemTypeName(Item::TYPE_PLANT)."\" class=\"mainFont mainColor L\">" .$arr[Item::TYPE_PLANT]."<img alt=\"\" style=\"border:none;margin:0px;padding:0px;width:16px;height:16px;\" src=\"/images/plant-sign.png\" /></span>\n" .
    "<span class=\"mainFont mainColor L\">|</span>\n" .
    "<span title=\"".Item::getItemTypeName(Item::TYPE_SEED)."\" class=\"mainFont mainColor L\">" .$arr[Item::TYPE_SEED]."<img alt=\"\" style=\"border:none;margin:0px;padding:0px;width:16px;height:16px;\" src=\"/images/seed-sign.png\" /></span>)\n" .
    "";
    $msection = new MenuSection("Растения на продажу");
    $msection->addItem("Добавить","/admin/item.php");
    $msection->addItem("Прайс-лист".$legend,"/admin/itemlist.php");
    $msection->addItem("В фотоальбомах","/admin/photoalbumsales.php");
    $menuarray[] = $msection;

    $msection = new MenuSection("Добавить фото в альбом","/admin/photoupload.php");
    $menuarray[] = $msection;
    if(PhotoAlbumList::dbGetCount() > 0){
      $msection = new MenuSection("Фотоальбомы","/admin/photoalbumlist.php");
      $menuarray[] = $msection;
    }
    $msection = new MenuSection("Денежные единицы","/admin/currency_settings.php");
    $menuarray[] = $msection;
    $msection = new MenuSection("Настройки","/admin/settings.php");
    $menuarray[] = $msection;
    $msection = new MenuSection("Личные данные","/admin/profile.php");
    $menuarray[] = $msection;
    $msection = new MenuSection("Выход","/admin/logoff.php");
    $menuarray[] = $msection;
  }
?>
                  <table style="width:<?php printf("%d",MENU_TOTAL_WIDTH)?>px;white-space:nowrap;text-align:right;" class="content bold L" cellspacing="0" cellpadding="4">
<?php printMenu($menuarray);?>
                  </table>
