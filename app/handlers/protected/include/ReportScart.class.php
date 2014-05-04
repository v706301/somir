<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("PDF_TABLE.class.php");
  require_once("misc.inc.php");
  require_once("common.inc.php");

  class ReportScart extends PDF_TABLE{
    public function __construct($orientation='P',$unit='mm',$format='A4'){
      parent::__construct($orientation, $unit, $format);
      $this->setColNames(array("№","Код","Название","Размер/\nКол-во","Цена","Заказ\n(шт)","Сумма"));
      //$this->setColWidths(array(2,3,45,10,15,10,15));
      $this->setColWidths(array(5,10,37,10,15,8,15));
      $this->setColAligns(array('R','L','L','R','R','R','R'));
      $this->setTableLineStyle(array("width" => 0.1,"color" => $this->lineColor));
    }

    public function getTitle(){return "Заказ";}

    public function create($scart,$prefs,$name,$email,$phone,$address){
      if(is_null($prefs))
        $prefs = new UserPreferences();
      //
      if(strlen($name) > 0 || strlen($email) > 0 || strlen($phone) > 0 || strlen($address) > 0){
        $this->miscInfo["Данные заказчика"] = "Данные заказчика";
        if(strlen($name) > 0)
          $this->miscInfo["ФИО"] = $name;
        if(strlen($email) > 0)
          $this->miscInfo["Email"] = $email;
        if(strlen($phone) > 0)
          $this->miscInfo["Телефон"] = $phone;
        if(strlen($address) > 0)
          $this->miscInfo["Адрес"] = $address;
      }

      $this->_init("Кактусы от Мирошниченко","Заказ");
      $this->SetFont($this->getFont(), "", 10);
      $keys = $scart->getEntryKeys();
      for($i = 0; $i < count($keys); $i++){
        $entry = $scart->getEntry($keys[$i]);
        $tableRow = array(
        $i+1
        ,$entry->getId()
        ,$entry->getName()
        ,$entry->scartDetails(false)
        ,$entry->scartPrice($prefs)
        ,$entry->getQuantity()
        ,$entry->formatSubtotal($prefs->getCurrencyCoef(),$prefs->getCurrencyLabel())
        );
        $this->_printTableRow($tableRow);
      }
      $this->_checkLastTableRow();
      $y = $this->GetY();
      $this->Line($this->lMargin,$y,$this->pageWidth-$this->rMargin,$y,$this->tableLineStyle);
      $this->Ln(2);
      $y = $this->GetY();
      $this->SetXY($this->lMargin,$y+1);
      $this->MultiCell($this->getPrintableW(),$y+1,"Итого: ".$scart->formatLegend($prefs->getCurrencyCoef(),$prefs->getCurrencyLabel()),0, "R");
      
      $this->Close();
    }
  }
?>