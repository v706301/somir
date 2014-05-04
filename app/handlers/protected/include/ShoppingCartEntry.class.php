<?php
  require_once("Item.class.php");
  require_once("CactiStatus.class.php");

  class ShoppingCartEntry{
    private $item;
    private $quantity;

    public function ShoppingCartEntry($item,$quantity = 1){
      $this->item = $item;
      if(!($this->isItem() || $this->isPhoto()))
      	throw new Exception('Unexpected entry: '.print_r($this->item,true));
      if($quantity < 1)
        $quantity = 1;
      $this->quantity = $quantity;
      if($this->isPhoto() && $this->item['photo_status'] != 'available')
      	throw new Exception("№{$this->item['id']} не продается ({$this->item['photo_status']})");
    }
    
    public function isItem(){
      return is_object($this->item) && get_class($this->item) == 'Item';
    }
    
    public function isPhoto(){
      return is_array($this->item) && array_key_exists('photo_price',$this->item);
    }
    
    public function getSubtotal($currencyCoef = 1){
      if($this->isItem())
      	return $this->item->getPrice($currencyCoef) * $this->quantity;
      else if($this->isPhoto())
      	return floatval(sprintf("%.2f",$this->item['photo_price']*$currencyCoef));
    }
    
    public function formatSubtotal($currencyCoef,$currencyLabel){
      return sprintf("%.2f %s",$this->getSubtotal($currencyCoef),htmlize($currencyLabel));
    } 

		public function scartPhoto(){
			if($this->isItem()){
			  return $this->item->hasPhoto() ? 
'<a style="margin:1px;" onclick="'.
jswindow('/itemphotopopup.php?photo_id='.$this->item->getPhotoId()).
'" href="#"><img src="/itemthumb.php?photo_id='.$this->item->getPhotoId().
'" style="border:none;padding:0px;margin:0px;width:'.$this->item->getThumbWidth().'px;height:'.$this->item->getThumbHeight().
'px" alt="'.htmlize($this->item->getName()).
'" /></a>':'&nbsp;';
			}
			else{
			  return '<a style="margin:1px;" onclick="'.
jswindow('/photopopup.php?photo_id='.$this->item['id']).
'" href="#"><img src="/photothumb.php?photo_id='.$this->item['id'].
'" style="border:none;padding:0px;margin:0px;width:'.$this->item['thumb_width'].'px;height:'.$this->item['thumb_height'].
'px" alt="'.htmlize($this->item['photo_name']).
'" /></a>';
			}
		}

		public function scartPrice($prefs){
			if($this->isItem())
				return sprintf("%.2f %s",$this->item->getPrice($prefs->getCurrencyCoef()),htmlize($prefs->getCurrencyLabel()));
		  else
		  	return sprintf('%.2f %s',floatval(sprintf("%.2f",$this->item['photo_price']*$prefs->getCurrencyCoef())),htmlize($prefs->getCurrencyLabel()));
		}

		public function scartName(){
			if($this->isItem())
				return htmlize($this->getId().' - '.$this->item->getName());
			else
				return htmlize($this->getId().' - '.$this->item['photo_name']);
		}
		
		public function scartIcon(){
			if($this->isItem()){
				$imgAlt = $this->item->isSeed()?"Семена":"Растение";
				$imgSrc = $this->item->isSeed()?"seed-sign.png":"plant-sign.png";
			}
			else{
      	$imgAlt = "Индивидуальное растение, выбранное по фото";
      	$imgSrc = 'plant.png';
			}
			return '<img alt="'.$imgAlt.'" title="'.$imgAlt.'" style="border:none;" src="/images/'.$imgSrc.'" />';		  
		}

		public function scartDetails($bHtml = true){
			if($this->isItem())
				return Item::formatSizeOrQuantity(!$this->item->isSeed(),$this->item->getSize(),$this->item->getPackageQty());
			else{
				$null = $bHtml ? '&nbsp;' : '';
				return $this->item['diameter'] > 0.0 ? CactiStatus::formatDiameter($this->item['diameter'],$bHtml) : $null;
			}
		}

    public function getQuantity(){return $this->quantity;}
    public function setQuantity($newQuantity){
      if($this->isItem()){
	      if($newQuantity < 1)
	        $newQuantity = 1;
	      $this->quantity = $newQuantity;
      }
      else
	      $this->quantity = 1;
    }
    public function getItem(){return $this->item;}
    public function getName(){
    	return $this->isItem() ? $this->item->getName() : $this->item['photo_name'];
    }
    public function getId(){
    	return $this->isItem() ? $this->itemKey($this->item->getId()) : $this->photoKey($this->item['id']);
    }

		public function photoKey($item_id){
		  return PREF.$item_id;
		}

		public function itemKey($item_id){
		  return PREL.$item_id;
		}
  }
