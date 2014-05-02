<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  define('K_TCPDF_EXTERNAL_CONFIG',true);
  if((!isset($_SERVER['DOCUMENT_ROOT'])) OR (empty($_SERVER['DOCUMENT_ROOT']))){
    if(isset($_SERVER['SCRIPT_FILENAME']))
      $_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0-strlen($_SERVER['PHP_SELF'])));
    else if(isset($_SERVER['PATH_TRANSLATED']))
      $_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0-strlen($_SERVER['PHP_SELF'])));
    else
      $_SERVER['DOCUMENT_ROOT'] = '/var/www';
  }
  define('K_PATH_MAIN',realpath(dirname(__FILE__))."/ext-tcpdf/");
  define('K_PATH_FONTS', K_PATH_MAIN.'fonts/');
  define('K_PATH_CACHE', K_PATH_MAIN.'cache/');
  define('K_PATH_IMAGES', K_PATH_MAIN.'images/');
  define('K_BLANK_IMAGE', K_PATH_IMAGES.'_blank.png');
  define('PDF_PAGE_FORMAT', 'A4');
  define('PDF_PAGE_ORIENTATION', 'P');
  define('PDF_CREATOR', 'TCPDF');
  define('PDF_AUTHOR', 'TCPDF');
  define('PDF_UNIT', 'mm');

  define('PDF_MARGIN_HEADER', 5);
  define('PDF_MARGIN_FOOTER', 10);
  define('PDF_MARGIN_TOP', 27);
  define('PDF_MARGIN_BOTTOM', 25);
  define('PDF_MARGIN_LEFT', 15);
  define('PDF_MARGIN_RIGHT', 15);

  define('PDF_FONT_NAME_MAIN', 'helvetica');
  define('PDF_FONT_SIZE_MAIN', 10);
  define('PDF_FONT_NAME_DATA', 'helvetica');
  define('PDF_FONT_SIZE_DATA', 8);
  define('PDF_FONT_MONOSPACED', 'courier');

  define('PDF_IMAGE_SCALE_RATIO', 1);

  define('HEAD_MAGNIFICATION', 1.1);
  define('K_CELL_HEIGHT_RATIO', 1.25);
  define('K_TITLE_MAGNIFICATION', 1.3);
  define('K_SMALL_RATIO', 2/3);

  require_once("ext-tcpdf/config/lang/eng.php");
  require_once("ext-tcpdf/tcpdf.php");
  require_once("common.inc.php");
  define("PDF_LOGO",$_SERVER["DOCUMENT_ROOT"]."/images/pdflogo.jpg");
  define("PDF_LOGO_WIDTH",50);
  define("PDF_LOGO_HEIGHT",50);

  class PDF_TEMPLATE extends TCPDF{
    const MAINFONT = "dejavusans";
    public $headerset;
    public $footerset;
    public $pageWidth;
    public $pageHeight;
    public $printableWidth;
    public $printableHeight;
    public $time_format = "F j, Y g:i A";
    public $lineHeight = 7;
    public $lineColor = array(58,107,122);
    public $headerLineStyle;

    public function __construct($orientation='P',$unit='mm',$format='A4'){
      parent::__construct($orientation,$unit,$format,true,'UTF-8',false);
      $this->SetFont($this->getFont(), '', 10);
      $this->lMargin = 10;
      $this->tMargin = 10;
      $this->rMargin = 5;
      $this->bMargin = 15.21;
      $this->SetAutoPageBreak(false, $this->bMargin);
      $this->pageWidth = $this->CurOrientation == 'P' ? $this->fw : $this->fh;
      $this->pageHeight = $this->CurOrientation == 'P' ? $this->fh : $this->fw;
      $this->printableWidth = $this->pageWidth - $this->rMargin - $this->lMargin;
      $this->printableHeight = $this->pageHeight - $this->tMargin - $this->bMargin;
      $this->headerLineStyle = array("width" => 0.2,"color" => $this->lineColor);
    }

    public function getTimeFormat(){return $this->time_format;}
    public function getLineHeight(){return $this->lineHeight;}
    public function getFont(){return PDF_TEMPLATE::MAINFONT;}

    public function Footer(){
      global $application_name;
      if(!isset($this->footerset[$this->page])){
        $this->SetY($this->bMargin * -1);
        $this->SetFont($this->getFont(), "", 9);
        $this->SetTextColor(0xAA, 0xAA, 0xAA);
        //$this->Cell(100,5,"PAGE ". $this->PageNo()."/{nb}", 0, 0, 'L');
        $this->Cell(100,5,"Страница ". $this->PageNo(), 0, 0, 'L');
        $this->Cell(0,5,strtoupper("Файл создан " . date("d/m/Y в H:i")), 0, 1, 'R');
        $this->Cell(0,5,date("Y")." ".$application_name, 0, 0, 'C');
        $this->footerset[$this->page] = 1;
      }
    }

    public function Header(){
      global $application_name,$application_addr,$application_phone,$application_email;
      $xOld = $this->GetX();
      $yOld = $this->GetY();
      if(!isset($this->headerset[$this->page])){
        $x = $this->lMargin;
        $y = $this->tMargin;
        $this->SetXY($x,$y);
        //BEGIN PRINT LOGO AND APPLICATION NAME
        $this->Image(PDF_LOGO,$x,$y,PDF_LOGO_WIDTH/$this->k,PDF_LOGO_HEIGHT/$this->k);
        $this->SetFont($this->getFont(), "B", 18);
        $this->SetTextColor(58,107,122);
        $xApplicationText = $x + PDF_LOGO_WIDTH/$this->k;
        $wApplicationText = $this->printableWidth - PDF_LOGO_WIDTH/$this->k;
        $this->SetXY($xApplicationText,$y+5);
        $this->MultiCell($wApplicationText, 6,$application_name, 0, "C");
        $y = $this->GetY();
        $this->SetFont($this->getFont(), "", 8);
        $this->SetXY($xApplicationText,$y);
        $this->MultiCell($wApplicationText, 3,$application_addr, 0, "C");
        $y = $this->GetY();
        $this->SetXY($xApplicationText,$y);
        $addrline = "";
        if(strlen($application_addr) > 0){
          if(strlen($addrline) > 0){$addrline .= ". ";}
          $addrline .= "Адрес: ".$application_addr;
        } 
        if(strlen($application_phone) > 0){
          if(strlen($addrline) > 0){$addrline .= ". ";}
          $addrline .= "Тел.: ".$application_phone;
        } 
        if(strlen($application_email) > 0){
          if(strlen($addrline) > 0){$addrline .= ". ";}
          $addrline .= "Email: ".$application_email;
        } 
        $this->MultiCell($wApplicationText, 3,$addrline, 0, "C");
        //
        if($this->tMargin + PDF_LOGO_HEIGHT/$this->k > $this->GetY())
          $this->SetY($this->tMargin + PDF_LOGO_HEIGHT/$this->k);
        else
          $y = $this->GetY();
        $x = $this->lMargin;
        $y += 2;
        $this->SetXY($x,$y);
        if($this->page == 1){
          $this->Line($this->lMargin,$y,$this->pageWidth-$this->rMargin,$y,$this->headerLineStyle);
          $this->Ln(2);
        }
        $y = $this->GetY();
        $this->SetXY($x,$y);
      }
    }
  }
?>