<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("PDF_TEMPLATE.class.php");
  require_once("misc.inc.php");
  require_once("common.inc.php");

  abstract class PDF_TABLE extends PDF_TEMPLATE{
    protected $_initialized = false;
    protected $colName = null;
    protected $colWidth = null;
    protected $colAlign = null;
    protected $tableLineStyle = null;
    protected $miscInfo = array();

    public function __construct($orientation='P',$unit='mm',$format='A4'){
      parent::__construct($orientation, $unit, $format);
    }

    protected function _init($subject,$title){
      global $application_name;
      if(!$this->_initialized){
        if(is_null($this->colName))
          throw new Exception("Column names|widths|aligns should be initalized before PDF table file is created");
        if(is_null($this->colWidth)){
          $this->colWidth = array();
          $xf = 100.0/count($this->colName);
          for($i = 0; $i < count($this->colName); $i++)
            $this->colWidth[$i] = $xf;
        }
        if(is_null($this->colAlign)){
          $this->colAlign = array();
          for($i = 0; $i < count($this->colName); $i++)
            $this->colAlign[$i] = "C";
        }
        if(is_null($this->tableLineStyle))
          $this->tableLineStyle = array("width" => 0.1,"color" => $this->lineColor);
        
        $this->SetCreator($application_name);
        $this->SetAuthor(date($this->time_format) . " BY ".$application_name);
        $this->SetSubject($subject);
        $this->SetTitle($title);
        $this->SetKeywords($application_name." ".$title);
        $this->Open();
        $this->AddPage();
        $this->AliasNbPages();
        $this->_initialized = true;
      }
    }
    
    public abstract function getTitle();

    public function setColNames($arr){
      if(is_null($arr) || !is_array($arr) || count($arr) == 0)
        throw new Exception("Column names parameter should be array of strings");
      for($i = 0; $i < count($arr); $i++){
        if(!is_string($arr[$i]))
          throw new Exception(sprintf("Column name at %d is not a string",$i));
      }
      $this->colName = $arr;
    }
    
    public function setColWidths($arr){
      if(is_null($arr) || !is_array($arr) || count($arr) == 0)
        return;
      $total = 0;
      for($i = 0; $i < count($arr); $i++){
        if(!is_numeric($arr[$i]))
          throw new Exception(sprintf("Column width at %d is not a numeric value(should be non-zero positive value)",$i));
        $total += $arr[$i];
      }
      if($total > 100)
        throw new Exception("Total of the column widths exceeds 100");
      for($i = 0; $i < count($arr); $i++){
        $arr[$i] = $this->printableWidth*$arr[$i]/100;
      }
      $this->colWidth = $arr;
    }

    public function setColAligns($arr){
      if(is_null($arr) || !is_array($arr) || count($arr) == 0)
        return;
      for($i = 0; $i < count($arr); $i++){
        if(!($arr[$i] == 'L' || $arr[$i] == 'C' || $arr[$i] == 'R'))
          $arr[$i] = 'L';
      }
      $this->colAlign = $arr;
    }
    
    public function setTableLineStyle($arr){
      $this->tableLineStyle = $arr;
    }
    
    public function Header(){
      $xOld = $this->GetX();
      $yOld = $this->GetY();
      if(!isset($this->headerset[$this->page])){
        parent::Header();
        $x = $this->lMargin;
        $y = $this->GetY();
        $this->SetXY($x,$y);
        //BEGIN PRINT FIRST PAGE ELEMENTS
        if($this->page == 1){
          $this->SetFont($this->getFont(), "B", 15);
          $this->MultiCell($this->printableWidth, 4,$this->getTitle(), 0, "C");
          $this->SetFont($this->getFont(), "", 10);
          $this->Ln(2);
          $y = $this->GetY();
          $this->SetXY($x,$y);
          if(count($this->miscInfo) > 0){
            $this->SetTextColor(0xaa, 0x25, 0x50);
            foreach($this->miscInfo as $key => $val){
              $this->SetXY($this->lMargin,$y);
              if(strcmp($key,$val) == 0){
                $this->SetFont($this->getFont(), "B", 10);
                $this->MultiCell($this->printableWidth,4,$key,0,"C");
              }
              else{
                $this->SetFont($this->getFont(), "B", 10);
                $this->MultiCell($this->printableWidth/2,4,$key.":",0,"R");
                $y2 = $this->GetY();
                $this->SetXY($this->lMargin+$this->printableWidth/2,$y);
                $this->SetFont($this->getFont(), "", 10);
                $this->MultiCell($this->printableWidth/2,4,$val,0,"L");
                if($y2 > $this->GetY())
                  $this->SetXY($this->lMargin,$y2);
              }
              $y = $this->GetY();
            }
          }
          $this->SetXY($this->lMargin,$y+5);
          $this->headerset[$this->page] = $this->GetY();
        }
        //END PRINT FIRST PAGE ELEMENTS
        //BEGIN PRINT TABLE HEADER
        $this->SetTextColor(58,107,122);
        $this->headerset[$this->page] = $this->GetY();
        $this->SetFont($this->getFont(), "B", 10);
        $lineCount = 0;
        for($col = 0; $col < count($this->colName); $col++){
          $lineCount = $this->getNumLines($this->colName[$col],$this->colWidth[$col]) > $lineCount ? $this->getNumLines($this->colName[$col],$this->colWidth[$col]) : $lineCount;
        }
        $this->SetXY($x,$y);
        $this->SetFillColor(0xff, 0xff, 0xff);
        $this->MultiCell($this->printableWidth,$this->lineHeight*$lineCount,"",0,"C",1);
        $this->SetXY($x,$y);
        //$this->SetFillColor(0xff, 0xff, 0xff);
        for($col = 0; $col < count($this->colName); $col++){
          $txt = $this->colName[$col];
          $this->SetXY($x,$y);
          $this->MultiCell($this->colWidth[$col],$this->lineHeight,$txt,0,"C",0);
          $x += $this->colWidth[$col];
          if($this->GetY() > $this->headerset[$this->page])
            $this->headerset[$this->page] = $this->GetY();
        }
        $y2 = $this->headerset[$this->page];
        $x = $this->lMargin;
        $this->Line($x,$y,$this->printableWidth+$x,$y,$this->tableLineStyle);
        $this->Line($x,$y2,$this->printableWidth+$x,$y2,$this->tableLineStyle);
        $this->Line($x,$y,$x,$y2,$this->tableLineStyle);
        for($col = 0; $col < count($this->colWidth); $col++){
          $x += $this->colWidth[$col];
          $this->Line($x,$y,$x,$y2,$this->tableLineStyle);
        }
        $this->SetXY($this->lMargin,$y2+1);
        $this->headerset[$this->page] = $y2+1;
        //END PRINT TABLE HEADER
      }
    }
    
    protected function getLineCount($colValue){
      $lineCount = 0;
      for($i = 0; $i < count($colValue); $i++){
        //$x = $this->NbLines($this->colWidth[$i],$colValue[$i]);
        $x = $this->getNumLines($colValue[$i],$this->colWidth[$i]);
        if($lineCount < $x)
          $lineCount = $x;
      }
      return $lineCount;
    }

    protected function _printTableRow($tableRow){
      $lineCount = $this->getLineCount($tableRow);
      if($this->GetY() + $lineCount * $this->lineHeight > $this->pageHeight - $this->bMargin)
        $this->AddPage();
      $y = $this->GetY();
      $x = 0;
      for($j = 0; $j < count($tableRow); $j++){
        $this->SetXY($this->lMargin + $x,$y);
        $this->MultiCell($this->colWidth[$j],$this->lineHeight,$tableRow[$j], 0, $this->colAlign[$j], 0);
        $x += $this->colWidth[$j];
      }
      $this->SetXY($this->lMargin,$y + $lineCount * $this->lineHeight);
    }

    protected function _checkLastTableRow(){
      if($this->GetY() + $this->lineHeight + 1 > $this->pageHeight - $this->bMargin)
        $this->AddPage();
    }
  }
?>