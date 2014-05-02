<?php
  require_once("Localize.class.php");
  require_once("ShoppingCartEntry.class.php");
  require_once("misc.inc.php");

  class ShoppingCart{
    private $entries = array();
    private $entryNames = array();

    public function __construct(){}

    public function addEntry($scartEntry,$bAddToExisting = false){
      if(!is_null($scartEntry) && is_a($scartEntry,"ShoppingCartEntry")){
        if($bAddToExisting && isset($this->entries[$scartEntry->getId()])){
          $existingScartEntry = $this->entries[$scartEntry->getId()];
          $existingScartEntry->setQuantity($existingScartEntry->getQuantity()+$scartEntry->getQuantity());
          $this->entries[$scartEntry->getId()] = $existingScartEntry;
        }
        else{
          $this->entries[$scartEntry->getId()] = $scartEntry;
          $this->entryNames[$scartEntry->getId()] = $scartEntry->getName();
          asort($this->entryNames);
        }
        return true;
      }
      return false;
    }

    public function removeEntry($item_id){
      if(!is_null($item_id) && isset($this->entries[$item_id])){
        unset($this->entries[$item_id]);
        unset($this->entryNames[$item_id]);
        return true;
      }
      return false;
    }

    public function getEntry($item_id){
      if(!is_null($item_id) && isset($this->entries[$item_id]))
        return $this->entries[$item_id];
      else
        return false;
    }

    public function getEntryKeys(){
      return array_keys($this->entryNames);
    }

    public function getEntryCount(){
      return count($this->entries);
    }

    public function getTotal($currencyCoef){
      $total = 0.0;
      $values = array_values($this->entries);
      for($i = 0; $i < count($values); $i++)
        $total += $values[$i]->getSubtotal($currencyCoef);
      return $total;
    }
    
    public function formatTotal($currencyCoef,$currencyLabel){
      $total = 0.0;
      $values = array_values($this->entries);
      for($i = 0; $i < count($values); $i++)
        $total += $values[$i]->getSubtotal($currencyCoef);
      return sprintf("%.2f %s",$total,$currencyLabel);
    }
    
    public function formatLegend($currencyCoef,$currencyLabel){
      $total = $this->getTotal($currencyCoef);
      $count = $this->getEntryCount();
      $prefs = new UserPreferences();
      if($prefs->get(COOKIE_KEY_LANG) == 'ru'){
        $legend = sprintf("%d %s на общую сумму %.2f %s",$count,Localize::numeric($count,"наименование"),$total,$currencyLabel);
      } else {
        $legend = sprintf("Total %.2f %s (%d item(s))",$total,htmlize($currencyLabel),$count);
      }
      return $legend;
    }
    
    public function isEntry($item_id){
      if(!is_null($item_id) && isset($this->entries[$item_id]))
        return true;
      else
        return false;
    }
    
    public function export2Csv($name,$email,$phone,$address,$prefs){
      //$header = 
      
      if($this->getEntryCount() > 0){
        //header("Content-type: text/csv; charset=UTF8");
        header("Content-type: text/csv; charset=Cp1251");
        header("Content-Disposition: attachment; filename=\"zakaz-miroshnichenko.csv\"");
        $csv = ''; 
        //$csv .= "\xEF\xBB\xBF";
        $csv .=  xconv("\"№\",\"Код\",\"Название\",\"Размер/Кол-во\",\"Цена\",\"Заказано(шт)\",\"Сумма\"\r\n");
        $keys = $this->getEntryKeys();
        for($i = 0; $i < count($keys); $i++){
          $entry = $this->getEntry($keys[$i]);
          $line =
          xconv( 
          sprintf(
          "\"%d\"" .  //№
          ",\"%s\"" . //Код
          ",\"%s\"" . //Название
          ",\"%s\"" . //Размер/Кол-во
          ",\"%s\"" . //Цена
          ",\"%s\"" . //Заказано(шт)
          ",\"%s\"" . //Итого
          "\r\n"
          ,$i+1
          ,$entry->getId()
          ,csvsafe($entry->getName())
	        ,csvsafe($entry->scartDetails(false))
	        ,csvsafe($entry->scartPrice($prefs))
	        ,csvsafe($entry->getQuantity())
	        ,csvsafe($entry->formatSubtotal($prefs->getCurrencyCoef(),$prefs->getCurrencyLabel()))
          ));
          //echo $line;
          $csv .= $line;
        }
        $csv .= xconv(sprintf("\r\n\r\nИтого: %s",$this->formatLegend($prefs->getCurrencyCoef(),$prefs->getCurrencyLabel())));
        
        if(strlen($name) > 0 || strlen($email) > 0 || strlen($phone) > 0 || strlen($address) > 0){
          $csv .= xconv("\r\n\r\n\r\nДанные заказчика:\r\n");
          if(strlen($name) > 0)
            $csv .= xconv("ФИО: ".$name."\r\n");
          if(strlen($email) > 0)
            $csv .= xconv("Email: ".$email."\r\n");
          if(strlen($phone) > 0)
            $csv .= xconv("Телефон: ".$phone."\r\n");
          if(strlen($address) > 0)
            $csv .= xconv("Адрес:\r\n".$address."\r\n");
        }
      }
      echo $csv;
      exit;
    }
  }
  
  function xconv($s){
    //return $s;
    return iconv("UTF-8","cp1251",$s);
  }
?>