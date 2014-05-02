<?php
  require_once("common.inc.php");
  require_once("settings.inc.php");
  require_once("Currency.class.php");

  class UserPreferences{
    private static $defaults = null;
    private $prefs = null;
    private $currency = null;
    
    public function UserPreferences(){
      $this->currency = new Currency();
      if(isset($_COOKIE[COOKIE_PREFS])){
        $this->prefs = UserPreferences::toarray($_COOKIE[COOKIE_PREFS]);
      }
      else{
        $this->prefs = UserPreferences::getDefaults();
        setcookie(COOKIE_PREFS,UserPreferences::tostring($this->prefs),mktime(0,0,0,date("m"),date("d"),date("Y")+1));
      }
    }

    public static function getDefaults(){
      if(is_null(UserPreferences::$defaults)){
        UserPreferences::$defaults = array(
        COOKIE_KEY_CURRENCY => getSetting(SETTINGS_KEY_CURRENCY,CURRENCY_UA),
        COOKIE_KEY_LANG => LANG_RU,
        );
      }
      return UserPreferences::$defaults;
    }
    
    private static function tostring($arr){
      if(is_null($arr) || !is_array($arr))
        throw new Exception("UserPrefs - Not an array");
      $s = "";
      foreach($arr as $key => $val){
        if(strlen($s) > 0)
          $s .= ",";
        $s .= $key."=".$val;
      }
      return $s;
    }
    
    private static function toarray($s){
      if(is_null($s) || strlen(trim($s)) == 0)
        return UserPreferences::getDefaults();
      $prefs = array();
      $arr = explode(",",$s);
      foreach($arr as $element){
        $px = explode("=",$element);
        if(count($px) == 2)
          $prefs[$px[0]] = $px[1];
      }
      if(count($prefs) == 0)
        $prefs = UserPreferences::getDefaults();
      return $prefs;
    }
    
    private function save(){
      setcookie(COOKIE_PREFS,UserPreferences::tostring($this->prefs),mktime(0,0,0,date("m"),date("d"),date("Y")+1));
    }
    
    public function get($key){
      if(!isset($this->prefs[$key])){
        $prefs = UserPreferences::getDefaults();
        if(!isset($prefs[$key]))
          throw new Exception("UserPrefs - unknown preference: ".$key);
        else{
          $this->prefs[$key] = $prefs[$key];
          $this->save();
        }
      }
      return $this->prefs[$key];
    }

		public static function setCurrency(){
		  if(isCommand("set_currency")){
		    $cval = getParameter("currency");
		    if(Currency::isSupported($cval)){
		      $prefs = new UserPreferences();
		      $prefs->set(COOKIE_KEY_CURRENCY,$cval);
		    }
		  }
		}

		public static function setLang(){
		  if(isCommand("set_lang")){
		    $cval = getParameter("lang");
	      $prefs = new UserPreferences();
	      $prefs->set(COOKIE_KEY_LANG,$cval);
		  }
		}

    public function set($key,$val){
      $prefs = UserPreferences::getDefaults();
      if(!isset($prefs[$key]))
        throw new Exception("UserPrefs - unknown preference: ".$key);
      $this->prefs[$key] = $val;
      $this->save();
    }

    public function getCurrency(){return $this->currency;}
    public function getLang(){return $this->lang;}

    public function getCurrencyCoef(){
      $userCoef = $this->currency->getCoef($this->get(COOKIE_KEY_CURRENCY));
      if($userCoef > 0.0)
        return $userCoef;
      //if coef not set - use system currency setting(coef=1)
      else
        return 1;
    }

    public function getCurrencyLabel(){
      $userCoef = $this->currency->getCoef($this->get(COOKIE_KEY_CURRENCY));
      if($userCoef > 0.0)
        return $this->currency->getLabel($this->get(COOKIE_KEY_CURRENCY));
      //if coef not set - use system currency setting
      else
        return $this->currency->getLabel($this->currency->getSelection());
    }

    public function printLangSelector(){
//    	print_r($_COOKIE);
//    	print_r($this->prefs);
      global $maincolor;
      $map = array('ru' => 'RU','en' => 'EN');
      $selection = $this->prefs[COOKIE_KEY_LANG];
      $bTable = false;
      $selection = $this->get(COOKIE_KEY_LANG) ? $this->get(COOKIE_KEY_LANG) : 'ru';
      $html = '';
      foreach($map as $key => $val){
          $href = ($selection != $key) ? sprintf(" href=\"?command=set_lang&amp;lang=%s\"",$key) : "";
          $style = sprintf("padding:0px 2px 0px 2px;text-decoration:none;%s",($selection == $key?"background-color:red;color:white;":"color:".$maincolor));
          $html .= <<<_HTML_
<a{$href} style="{$style}">{$val}</a>

_HTML_;
      }
      echo $html;
    }

    public function printCurrencySelector(){
      global $maincolor;
      $bTable = false;
      $map = Currency::getLabelMap();
      $selection = $this->currency->getCoef($this->get(COOKIE_KEY_CURRENCY)) > 0.0 ? $this->get(COOKIE_KEY_CURRENCY) : $this->currency->getSelection();
      foreach($map as $key => $val){
        if($this->currency->getCoef($key) > 0.0){
          $href = ($selection != $key) ? sprintf(" href=\"?command=set_currency&amp;currency=%s\"",$key) : "";
          $style = sprintf("padding:0px 2px 0px 2px;text-decoration:none;%s",($selection == $key?"background-color:red;color:white;":"color:".$maincolor));
          if(!$bTable){
            $bTable = true;
?>
                    <table class="mainFont mainColor L" cellspacing="0" cellpadding="0">
                      <tr>
<?php
          }
?>
                        <td style="padding-right:5px;">
                          <a<?php print($href)?> style="<?php print($style)?>"><?php hprint(Currency::getLabel($key))?></a>
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
    }
  }
