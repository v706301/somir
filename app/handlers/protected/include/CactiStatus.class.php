<?php
	require_once 'Currency.class.php';
	
	class CactiStatus{
		public static $map = array(
		'available' => 'В продаже',
		'sold' => 'Уже продан',
		'reserved' => 'Зарезервирован',
		'disabled' => 'Не продается',
		);
		
		public static $bkgmap = array(
		'available' => '#689600',
		'sold' => '#7a74b6',
		'reserved' => '#930224',
		'disabled' => '#aaa',
		'is_hidden' => '#ccc',
		'default' => 'transparent',
		);
		
		public static $frgmap = array(
		'available' => '#fff',
		'sold' => '#fff',
		'reserved' => '#fff',
		'disabled' => '#fff',
		'is_hidden' => '#fff',
		'default' => '#000',
		);
		
		public $status = '';
	
    public function __construct($status){
    	$this->status = $status;
    }
    
    public function renderDropdown($status = false,$misc = '',$name = 'photo_status',$noDefault = true){
      if(!$status && isset($this))
      	$status = $this->status;
      $html = sprintf('<select id="%s" name="%s" %s>'."\n",$name,$name,$misc);
      if($noDefault)
      	$html .= '  <option value="">-= Выбрать =-</option>'."\n";
      foreach(self::$map as $key=>$val){
        $sel = $key == $status ? ' selected="selected" ':'';
        $html .= sprintf('  <option value="%s"%s>%s</option>'."\n",$key,$sel,htmlize($val));
      }
      $html .= "</select>\n";
      echo $html;
    }
    
    public function sqlStatus($status){
      if(!is_array($status))
      	$status = array($status);
      $checked = array();
      foreach($status as $st){
        if(self::status($st))
        	$checked[] = sprintf('p.status=%s',sqlsafe($st));
      }
      if(count($checked) > 0){
	      $clause = '('.implode(' or ',$checked).')';
      }
      else
      	$clause = false;
      return $clause;
    }
    
    public function status($key = false){
      if(!$key && isset($this))
      	$key = $this->status;
      return array_key_exists($key,self::$map) ? self::$map[$key] : false;
    }
    
    public function formatDiameter($diameter,$bHtml = true){
    	if($diameter > 0.0){
    	  $diameter - sprintf('%.2f',$diameter);
    	  if(substr($diameter,-3) == '.00')
    	  	$diameter = substr($diameter,0,-3);
    	  
    	  if($bHtml)
    	  	$diameter = "&#x2300; - {$diameter}см<br/>";
    	  else{
    	  	$diameter = "D - {$diameter}см";
    	    
    	  }
    	}
    	else
    		$diameter = '';
    	return $diameter;
    }
    
    public function legend($photo = array()){
      $legend = '';
      if(is_array($photo)){
        $hidden = $photo['is_hidden'];
        $status = $photo['photo_status'];
        $price = $photo['photo_price'];
        $diameter = $photo['diameter'];
	      if(array_key_exists($status,self::$map))
	      	$display_status = self::$map[$status];
	      else	
	      	$display_status = '';
	      	
    		$diameter = self::formatDiameter($diameter);
      
      	if($price > 0.0)
      	  $price = sprintf('%.2f %s',$price,htmlize(Currency::get(getSetting(SETTINGS_KEY_CURRENCY,CURRENCY_UA))));
      	else
      		$price = 'не указана';	

      	$legend = sprintf('<div id="photo_legend%d" style="%s%s" class="photo-legend"><div style="float:left"><b>Номер: %04d</b><br/>%sЦена: %s<br/>%s%s</div><div style="float:right;font-size:18px;margin:4px 6px 4px 2px">%s</div></div>'
      	,$photo['id']
      	,self::bkg($photo)
      	,self::frg($photo)
      	,$photo['id']
      	
      	,$diameter
      	,$price
      	,self::$map[$status]
      	,$hidden ? '<br/>Фото скрыто':''
      	,strlen($photo['notes']) > 0 ? 'NB':''
      	);
      }
      return $legend;
    }

    public function bkg($photo){
      $bkg = '';
    	if($photo["is_hidden"])
    		$bkg = 'background:'.self::$bkgmap['is_hidden'].';';
    	else{
	     	$status = $photo['photo_status'];
	      if(array_key_exists($status,self::$bkgmap))
	      	$bkg = 'background:'.self::$bkgmap[$status].';';
	      else
	      	$bkg = 'border:1px solid #ccc;';
    	}
    	return $bkg;
    }

		public function frg($photo){
      $frg = '';
    	if($photo["is_hidden"])
    		$frg = 'color:'.self::$frgmap['is_hidden'].';';
    	else{
	     	$status = $photo['photo_status'];
	      if(array_key_exists($status,self::$frgmap))
	      	$frg = 'color:'.self::$frgmap[$status].';';
	      else
	    		$frg = 'color:'.self::$frgmap['default'].';';
    	}
    	return $frg;
		}

		public function renderPhoto($photo){
			$id = $photo['id'];
			$jswindow = jswindow("/photopopup.php?photo_id=".$photo["id"]);
			$bkg = self::bkg($photo);
			$frg = self::frg($photo);
			$name = htmlize($photo["photo_name"]);
			$tw = $photo["thumb_width"];
			$th = $photo["thumb_height"];
			$desc = htmlize(
				sprintf("%s(%.1fКб)\nДата создания %s",$photo["photo_file"],floatval($photo["photo_size"])/1024,Localize::dbdate($photo["date_created"]))
			);
			$legend = CactiStatus::legend($photo);
			$html = <<<_HTML_
<table class="photo cursor-ptr mainFont M" onclick="{$jswindow}" style="{$bkg}{$frg}" cellspacing="0">
<tr><td id="photo_name{$id}" class="bold center">{$name}</td></tr>
<tr><td class="center"><img src="/photothumb.php?photo_id={$id}" style="width:{$tw}px;height:{$th}px;" alt="" /></td></tr>
<tr><td class="S italic left nowrap">{$desc}{$legend}</td></tr>
</table>

_HTML_;
			return $html;
		}

		public function renderPhotoWithControls($photo){
//        <div title="Отметить фото"
//        id="ckb{$id}"
//        class="cursor-ptr center" 
//        onmouseover="this.style.backgroundPosition='0px -22px'" 
//        onmouseout="this.style.backgroundPosition='0px 0px'" 
//        onclick="this.style.backgroundPosition='0px -44px';setTimeout('ckPhoto({$id})',200)"
//        style="width:22px;height:22px;background-image:url(/images/btn-checkbox-off.gif);background-repeat:no-repeat;"><input type="hidden" name="photo_id{$id}" /></div>

			$partial = self::renderPhoto($photo);
			$id = $photo['id'];
			$album_id = $photo['photoalbum_id'];
	    $descType = ($photo["photo_name"] > 0 || $photo["photo_desc"] > 0 || $photo["photo_keywords"] > 0) ? "active" : "grayed";
			$html = <<<_HTML_
<div id="div-photo{$id}" class="photo-container">
  <table cellspacing="0">
    <tr>
      <td id="photo_html{$id}" class="vtop">{$partial}</td>
      <td class="vtop left">
        <div title="Редактировать текстовые описания (название, дополнительное описание, ключевые слова)"
        class="cursor-ptr" 
        onmouseover="this.style.backgroundPosition='0px -22px'" 
        onmouseout="this.style.backgroundPosition='0px 0px'" 
        onclick="this.style.backgroundPosition='0px -44px';setTimeout('openUpdatePhotoDialog({$album_id},{$id})',200)" 
        style="margin-top:3px;width:22px;height:22px;background-image:url(/images/btn-desc-$descType.gif);background-repeat:no-repeat;"></div>
      </td>
    </tr>
  </table>
</div>
             
_HTML_;
			return $html;
		}

		public function renderForSale(&$photo,&$prefs){
			global $max_thumb_width;
			$status = $cart = $price = $diameter = $num = '';
			$id = $photo['id'];

  		$scart = getSessionVar(SHOPPING_CART_KEY,false);
  		if($scart)
  			$entry = $scart->getEntry(ShoppingCartEntry::photoKey($id));
  		else
  			$entry = false;
  		if($entry){
				$cartLabel = lang('Из корзины');
				$basket = 1;
  		}
			else{
				$cartLabel = lang('В корзину');
				$basket = 0;
			}

			$jswin = jswindow("/photopopup.php?photo_id={$id}");
			$width = $max_thumb_width+20;
			$name = htmlize($photo["photo_name"]);
			$href = mkurl('/photopopup.php',array('photo_id'=>$photo["id"],'n'=>$photo["photo_name"]));
			$thumb_width = $photo["thumb_width"];
			$thumb_height = $photo["thumb_height"];
			
			if($photo["photo_status"] == 'available' || $photo["photo_status"] == 'sold' || $photo["photo_status"] == 'reserved')
				$price = Item::formatPrice($photo["photo_price"],$prefs->getCurrencyCoef(),$prefs->getCurrencyLabel(),false);
			$diameter = self::formatDiameter($photo["diameter"]);
			$num = '№ '.$id;
			if($photo["photo_status"] == 'available')
				$cart = '<a rel="'.$basket.'" id="cartlink'.$id.'" href="#" onclick="javascript:ajcall_SetSelected(this,'.$id.')">'.$cartLabel.'</a>';
			if($photo["photo_status"] == 'reserved')
				$status = '<div class="reserved">'.htmlize('ЗАРЕЗЕРВИРОВАНО').'</div>';
			else if($photo["photo_status"] == 'sold')
				$status = '<div class="sold">'.htmlize('ПРОДАН').'</div>';
			
			$html = <<<_HTML_
<div id="div-photo{$id}" class="photo-container">
  <div class="xphoto">
    <div style="margin:3px;" class="bold center vtop">{$name}</div>
    <a href="#" onclick="{$jswin}" style="margin:auto;text-align:center;" class="center vmid"><img src="/photothumb.php?photo_id={$id}" 
    style="width:{$thumb_width}px;height:{$thumb_height}px;border:none;" 
    title="{$name} - Кактусы и суккуленты из Харькова от Оли и Сергея Мирошниченко" 
    alt="{$name} - Кактусы и суккуленты из Харькова от Оли и Сергея Мирошниченко" /></a>
  	<div class="le">{$price}</div>
  	<div class="ri">{$diameter}</div>
  	<div style="clear:both;"></div>
  	<div class="le">{$num}</div>
  	<div class="ri">{$cart}</div>
  	{$status}
  </div>
</div> 
             
_HTML_;
				return $html;
		}
	}
	