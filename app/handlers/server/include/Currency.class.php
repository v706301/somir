<?php
  require_once("common.inc.php");

  class Currency{
    private static $keymap = array(
      CURRENCY_UA => "currency_coef_uah",
      CURRENCY_RU => "currency_coef_rr",
      CURRENCY_US => "currency_coef_usd",
    );

    private static $labelmap = array(
      CURRENCY_UA => CURRENCY_LABEL_UA,
      CURRENCY_RU => CURRENCY_LABEL_RU,
      CURRENCY_US => CURRENCY_LABEL_US,
    );
    
    private $coef = array(
      CURRENCY_UA => 1,
      CURRENCY_RU => 1,
      CURRENCY_US => 1,
    );
    
    private $selection = CURRENCY_UA;
    
    public static function isSupported($key){
      if(isset(Currency::$labelmap[$key]))
        return true;
      else
        return false;
    }

    public static function get($key){
      if(!isset(Currency::$labelmap[$key]))
        throw new Exception("Пересчет в валюту ".$key." не производится");
      return Currency::$labelmap[$key];
    }
    
    public static function getLabelMap(){return Currency::$labelmap;}
    public static function getLabel($key){
      if(isset(Currency::$labelmap[$key]))
        return Currency::$labelmap[$key];
      else
        throw new Exception("Пересчет в валюту ".$key." не производится");
    }

    public function Currency(){
      global $dbprefix;
      $db = getDatabase();
      $query = "select * from ".$dbprefix."_settings where exposable=0 and section='currency'";
      $rs = $db->query($query,__FILE__.":".__LINE__);
      while($rs && $row = $db->fetch_assoc($rs)){
        if($row["name"] == "currency")
          $this->selection = $row["val"];
        else if($row["name"] == Currency::$keymap[CURRENCY_US])
          $this->coef[CURRENCY_US] = floatval($row["val"]);
        else if($row["name"] == Currency::$keymap[CURRENCY_UA])
          $this->coef[CURRENCY_UA] = floatval($row["val"]);
        else if($row["name"] == Currency::$keymap[CURRENCY_RU])
          $this->coef[CURRENCY_RU] = floatval($row["val"]);
      }
    }

    public function getSelection(){return $this->selection;}
    public function setSelection($key){
      if(isset(Currency::$labelmap[$key]))
        $this->selection = $key;
      else
        throw new Exception("Пересчет в валюту ".$key." не производится");
    }

    public function getSelectionLabel(){
      return Currency::$labelmap[$this->selection];
    }
    

    public function getCoef($key){
      if(isset(Currency::$labelmap[$key]))
        return $this->coef[$key];
      else
        throw new Exception("Пересчет в валюту ".$key." не производится");
    }

    public function setCoef($key,$cval){
      if(!isset(Currency::$labelmap[$key]))
        throw new Exception("Пересчет в валюту ".$key." не производится");
      if(is_numeric($cval)){
        $cval = abs(floatval($cval));
        $this->coef[$key] = $cval;
      }
      else
        $this->coef[$key] = null;
    }

    public function httpReadCoefs(){
      $result = true;
      $cua = getParameter("cua");
      $cus = getParameter("cus");
      $cru = getParameter("cru");
      if($this->selection == CURRENCY_UA)
        $cua = 1;
      else if($this->selection == CURRENCY_US)
        $cus = 1;
      else if($this->selection == CURRENCY_RU)
        $cru = 1;
      if(!is_null($cua) && !is_numeric($cua)){
        setAlert(sprintf("Указан неверный коэффициент пересчета %s в %s",Currency::getLabel($this->selection),Currency::getLabel(CURRENCY_UA)));
        $result = false;
      }
      if(!is_null($cus) && !is_numeric($cus)){
        setAlert(sprintf("Указан неверный коэффициент пересчета %s в %s",Currency::getLabel($this->selection),Currency::getLabel(CURRENCY_US)));
        $result = false;
      }
      if(!is_null($cru) && !is_numeric($cru)){
        setAlert(sprintf("Указан неверный коэффициент пересчета %s в %s",Currency::getLabel($this->selection),Currency::getLabel(CURRENCY_RU)));
        $result = false;
      }
      if($result){
        $this->setCoef(CURRENCY_UA,$cua);
        $this->setCoef(CURRENCY_US,$cus);
        $this->setCoef(CURRENCY_RU,$cru);
        return true;
      }
      else{
        setAlert("Указан неверный коэффициент пересчета валют");
        setSessionVar(SELF."_errorinfo",
          array(
          "cua" => $cua,
          "cus" => $cus,
          "cru" => $cru,
          )
        );
        return false;
      }
    }

    public function httpReadCurrency(){
      $selection = getParameter("currency");
      if(!isset(Currency::$labelmap[$selection])){
        setAlert("Пересчет в валюту ".$selection." не производится");
        return false;
      }
      else{
        if($this->selection != $selection){
          $this->selection = $selection;
          $this->setCoef(CURRENCY_UA,0);
          $this->setCoef(CURRENCY_US,0);
          $this->setCoef(CURRENCY_RU,0);
        }
      }
      return true;
    }

    public function save(){
      global $dbprefix;
      $db = getDatabase();
      $query = sprintf("update ".$dbprefix."_settings set val=%s where name=%s",sqlsafe($this->selection),sqlsafe(SETTINGS_KEY_CURRENCY));
      $db->query($query,__FILE__.":".__LINE__);
      foreach(Currency::$keymap as $key => $val){
        $query = sprintf("update ".$dbprefix."_settings set val=%s where name=%s",sqlsafe(sprintf("%f",$this->coef[$key])),sqlsafe(Currency::$keymap[$key]));
        $db->query($query,__FILE__.":".__LINE__);
      }
      return true;
    }
  }
?>