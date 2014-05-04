<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("common.inc.php");
  header("Content-type: text/css");
?>

.cmenu-container{
  position:relative;
  margin:0px;
  text-align:left;
  background-color:<?php maincolor()?>;
  height:26px;
  padding:2px 0px 0px 0px;
  cursor:default;
}

a.cmenu{
  position:relative;
  cursor:pointer;
  margin:3px;
  padding:2px 4px;
  color:#ffffff;
  text-decoration:none;
  font-family:<?php mainfont()?>;
  font-size:14px;
  font-weight:bold;
  border-bottom: 4px solid <?php maincolor()?>;
}

a.cmenu:hover {
  border-bottom:4px solid #95d233;
}

a.cmenu:hover,a.cmenu:active,a.cmenu:visited{text-decoration:none;color:#ffffff;}

.cmenu-sel{background-color:#95d233;}

.char-button{
  vertical-align:middle;
  text-align:center;
  cursor:pointer;
  font-weight:bold;
  font-size:11px;
  font-family:courier;
  float:left;
  width:22px;
  height:22px;
  line-height:22px;
  margin:auto 2px auto 0px;
  background:url(/images/btn-frame.gif) no-repeat;
}
.char-button-selected{background-position:0px -44px;}

.ndv{
  text-align:right;
  vertical-align:top;
  float:left;
  clear:left;
  line-height:18px;
}

.vdv{
  text-align:left;
  vertical-align:top;
  float:left;
}

.textHint {font-style: italic; color:#cacaca;}
.cursor-ptr{cursor:pointer;}
.cursor-def{cursor:default;}
.image{margin:0px;padding:0px;}

/* column order image */
a.ord-name {
  color: #ffffff;
  font-family: <?php print($mainfont)?>;
  font-weight: normal;
  font-size: 14px;
  /* padding-left: 3px; */
  padding-top: 2px;
  /* padding-bottom: 2px; */
  text-decoration: none;  
}
a.ord-img {
  text-decoration: none;  
  vertical-align: middle; 
  /* padding-left:2px; */ 
}
/* column order index in query */
.ord-coi {
  text-decoration: none;  
  vertical-align: super; 
  color: #ffffff; 
  font-family: Verdana; 
  font-size: 7px;
}

/*table row cell - first column(leftmost)*/
td.tdl {
  border-left: 1px solid <?php print($maincolor)?>; 
  border-right: 1px solid <?php print($maincolor)?>; 
  border-bottom: 1px solid <?php print($maincolor)?>; 
  padding: 5px 5px 2px 5px;
  vertical-align: top; 
}

/*table row cell*/
td.td{
  border-right: 1px solid <?php print($maincolor)?>; 
  border-bottom: 1px solid <?php print($maincolor)?>; 
  padding: 5px 5px 2px 5px;
  vertical-align: top; 
}

td.so-selected {
  background-color: <?php print($mainbkg2)?>;
}

/*table header cell - first column(leftmost)*/
td.thl {
  border-left: 1px solid <?php print($maincolor)?>; 
  border-right: 1px solid <?php print($maincolor)?>; 
  border-top: 1px solid <?php print($maincolor)?>; 
  border-bottom: 1px solid <?php print($maincolor)?>; 
  padding: 5px 5px 2px 5px;
}

/*table header cell*/
td.th{
  border-right: 1px solid <?php print($maincolor)?>; 
  border-top: 1px solid <?php print($maincolor)?>; 
  border-bottom: 1px solid <?php print($maincolor)?>; 
  padding: 5px 5px 2px 5px;
}


html,body{padding: 0px; margin: 0px;}
body {background-color: <?php print($mainbkg)?>;}

.nbborder{border-bottom:none;}
.noborder{border:none;}
.nomargin {margin:0px;}
.nopad {padding:0px;}

.w50pct {width:50%;}
.w90pct {width:90%;}
.w100pct {width:100%;}
.w450 {width:450px;}
.w400 {width:400px;}
.w350 {width:350px;}
.w300 {width:300px;}
.w250 {width:250px;}
.w200 {width:200px;}
.w150 {width:150px;}
.w100 {width:100px;}
.w50 {width:50px;}
.w40 {width:40px;}
.w30 {width:30px;}
.w25 {width:25px;}
.w20 {width:20px;}
.w15 {width:15px;}
.w10 {width:10px;}

.content{
  font-family:<?php print($mainfont)?>;
  color:<?php print($maincolor)?>;
  background-color:transparent;
  padding:0px 0px 15px 0px;
  width:100%;
}

.subcontent{
  font-family:<?php print($mainfont)?>;
  color:<?php print($maincolor)?>;
  background-color:transparent;
  width:100%;
}

.mainFont{font-family: <?php print($mainfont)?>;}
.mainColor{color: <?php print($maincolor)?>;}
.mainBkg{background-color: <?php print($mainbkg)?>;}

.solidLTB {border-left:1px solid <?php print($maincolor)?>;border-top:1px solid <?php print($maincolor)?>;border-bottom:1px solid <?php print($maincolor)?>;}
.solidL {border-left:1px solid <?php print($maincolor)?>;}
.solidT {border-top:1px solid <?php print($maincolor)?>;}
.solidR {border-right:1px solid <?php print($maincolor)?>;}
.solidB {border-bottom:1px solid <?php print($maincolor)?>;}
.solid {border:1px solid <?php print($maincolor)?>;}
.solid2 {border:2px solid <?php print($maincolor)?>;}
.solid3 {border:3px solid <?php print($maincolor)?>;}

.insetL {border-left:1px inset <?php print($maincolor)?>;}
.insetT {border-top:1px inset <?php print($maincolor)?>;}
.insetR {border-right:1px inset <?php print($maincolor)?>;}
.insetB {border-bottom:1px inset <?php print($maincolor)?>;}

.inset {border:1px inset <?php print($maincolor)?>;}

.nowrap {white-space:nowrap;}

.center {text-align:center;}
.left {text-align:left;}
.right {text-align:right;}

.vtop {vertical-align:top;}
.vmid {vertical-align:middle;}
.vbtm {vertical-align:bottom;}

.alert{
  background-color: #ffa500;
  color: #ffffff;
  font-family: <?php print($mainfont)?>;
  font-weight: normal;
  font-size: 18px;
  padding-left: 3px;
  padding-bottom: 3px;
  text-transform: none;
  vertical-align: middle;
}

.info{
  background-color: #019601;
  color: #ffffff;
  font-family: <?php print($mainfont)?>;
  font-weight: normal;
  font-size: 18px;
  padding-left: 3px;
  padding-bottom: 3px;
  text-transform: none;
  vertical-align: middle;
}

.pagename{
  background-color: <?php print($maincolor)?>;
  color: #ffffff;
  font-family: <?php print($mainfont)?>;
  font-weight: normal;
  font-size: 18px;
  padding-left: 0px;
  padding-bottom: 3px;
  text-transform: none;
  vertical-align: middle;
}

.hugeTitle{
  color: <?php print($maincolor)?>;
  background-color: <?php print($mainbkg)?>;
  font-family: <?php print($mainfont)?>;
  font-weight: bold;
  font-size: 34px;
  padding-left: 3px;
  padding-top: 2px;
  padding-bottom: 2px;
  /*text-transform: uppercase;*/
}

.subtitle {
  color: <?php print($maincolor)?>;
  background-color: <?php print($mainbkg)?>;
  font-family: <?php print($mainfont)?>;
  font-weight: normal;
  font-size: 14px;
  padding-left: 3px;
  padding-top: 2px;
  padding-bottom: 2px;
}

.topmenu {
  width: 242px;
  height: 32px;
  vertical-align: middle;
  color: <?php print($maincolor)?>;
  font-family: <?php print($mainfont)?>;
  font-weight: bold;
  font-size: 12px;
  text-decoration: none;
  letter-spacing: 1px;
}

.submenu {
  width: 242px;
  height: 22px;
  vertical-align: middle;
}

.menuitem {letter-spacing: 1px;color: <?php print($maincolor)?>; font-family: <?php print($mainfont)?>; font-weight: normal; text-decoration: none;}
A.menuitem {letter-spacing: 1px;color: <?php print($maincolor)?>; font-family: <?php print($mainfont)?>; font-weight: normal; text-decoration: none;}
A.menuitem:active {letter-spacing: 1px;color: <?php print($maincolor)?>; font-family: <?php print($mainfont)?>; font-weight: normal; text-decoration: none;}
A.menuitem:hover {letter-spacing: 1px;color: <?php print($maincolor)?>; font-family: <?php print($mainfont)?>; font-weight: normal; text-decoration: none;}
A.menuitem:visited {letter-spacing: 1px;color: <?php print($maincolor)?>; font-family: <?php print($mainfont)?>; font-weight: normal; text-decoration: none;}
A.menuitem:link {letter-spacing: 1px;color: <?php print($maincolor)?>; font-family: <?php print($mainfont)?>; font-weight: normal; text-decoration: none;}

/*
A{color: <?php print($maincolor)?>;}
A:active{color: <?php print($maincolor)?>;}
A:hover{color: <?php print($maincolor)?>;}
A:visited{color: <?php print($maincolor)?>;}
A:link{color: <?php print($maincolor)?>;}
*/

.ucase {text-transform: uppercase;}
.underline {text-decoration: underline;}

/*BEGIN BORDER*/
.border {border-width: 1px; border-style: solid; border-color: <?php print($maincolor)?>;}
.deb {border: 1px dotted red;}
/*END BORDER*/

/*BEGIN COLORS*/
.red{color: red;}
/*END COLORS*/

/*BEGIN COPYRIGHT STYLE*/
.copyright {FONT-SIZE:11px; COLOR:#788192; font-family: <?php print($mainfont)?>; font-weight:normal; color: <?php print($maincolor)?>;}
A.copyright {FONT-SIZE:11px; COLOR:#2e5598; font-family: <?php print($mainfont)?>; font-weight:normal; color: <?php print($maincolor)?>;}
A.copyright:hover {FONT-SIZE:11px; COLOR:#2e5598; font-family: <?php print($mainfont)?>; font-weight:normal; color: <?php print($maincolor)?>;}
A.copyright:visited {FONT-SIZE:11px; COLOR:#2e5598; font-family: <?php print($mainfont)?>; font-weight:normal; color: <?php print($maincolor)?>;}
A.copyright:link {FONT-SIZE:11px; COLOR:#2e5598; font-family: <?php print($mainfont)?>; font-weight:normal; color: <?php print($maincolor)?>;}
A.copyright:active {FONT-SIZE:11px; COLOR:#2e5598; font-family: <?php print($mainfont)?>; font-weight:normal; color: <?php print($maincolor)?>;}
/*END COPYRIGHT STYLE*/

/*BEGIN INPUT CONTROLS*/
.button{
  width: 130px;
}

.input{vertical-align: bottom; border: 1px solid #7F7F7F; font-family: <?php print($mainfont)?>; font-size:11px; padding:1px 1px 1px 1px;}
.text{vertical-align: bottom; height:18px;border: 1px solid #7F7F7F; font-family: <?php print($mainfont)?>; font-size:11px; padding:1px 1px 1px 1px;}
.textReadonly{vertical-align: bottom; border: 2px solid #7F7F7F; font-family: <?php print($mainfont)?>; font-size:11px; padding:1px 1px 1px 1px;}
.select{height: 20px; border: 1px solid #7F7F7F; font-family: <?php print($mainfont)?>; font-size:11px; padding: 1px 0px 1px 1px;}
/*END INPUT CONTROLS*/

/*BEGIN FONT SIZE*/
.S{font-size: 10px;}
.M{font-size: 11px;}
.L{font-size: 12px;}
.XL{font-size: 14px;}
.XXL{font-size: 16px;}
.XXXL{font-size: 18px;}
/*END FONT SIZE*/

/*BEGIN FONT FAMILY*/
.courier{font-family: Courier; text-decoration: none;}
.impact{font-family: Impact; text-decoration: none;}
.tahoma{font-family: Tahoma; text-decoration: none;}
.arial{font-family: Arial; text-decoration: none;}
.aribl{font-family: Arial Black; text-decoration: none;}
.times{font-family: Times New Roman; text-decoration: none;}
.bookman{font-family: Bookman Old Style; text-decoration: none;}
.verdana{font-family: Verdana; text-decoration: none;}
.garamond{font-family: Garamond; text-decoration: none;}
.ser{font-family: serif; text-decoration: none;}
.sser{font-family: sans-serif; text-decoration: none;}
.fanta{font-family: fantasy; text-decoration: none;}
.cur{font-family: cursive; text-decoration: none;}
.mono{font-family: monospace; text-decoration: none;}
/*END FONT FAMILY*/

/*BEGIN FONT WEIGHT/STYLE*/
.bold{font-weight: bold;}
.normal{font-weight: normal;}
.italic{font-style: italic;}
/*END FONT WEIGHT/STYLE*/

/*BEGIN PADDING*/
.tpad100 {padding-top: 100px;}
.tpad50 {padding-top: 50px;}
.tpad40 {padding-top: 40px;}
.tpad30 {padding-top: 30px;}
.tpad25 {padding-top: 25px;}
.tpad20 {padding-top: 20px;}
.tpad15 {padding-top: 15px;}
.tpad10 {padding-top: 10px;}
.tpad9 {padding-top: 9px;}
.tpad8 {padding-top: 8px;}
.tpad7 {padding-top: 7px;}
.tpad6 {padding-top: 6px;}
.tpad5 {padding-top: 5px;}
.tpad4 {padding-top: 4px;}
.tpad3 {padding-top: 3px;}
.tpad2 {padding-top: 2px;}
.tpad1 {padding-top: 1px;}

.bpad100 {padding-bottom: 100px;}
.bpad50 {padding-bottom: 50px;}
.bpad40 {padding-bottom: 40px;}
.bpad30 {padding-bottom: 30px;}
.bpad20 {padding-bottom: 20px;}
.bpad15 {padding-bottom: 15px;}
.bpad10 {padding-bottom: 10px;}
.bpad9 {padding-bottom: 9px;}
.bpad8 {padding-bottom: 8px;}
.bpad7 {padding-bottom: 7px;}
.bpad6 {padding-bottom: 6px;}
.bpad5 {padding-bottom: 5px;}
.bpad4 {padding-bottom: 4px;}
.bpad3 {padding-bottom: 3px;}
.bpad2 {padding-bottom: 2px;}
.bpad1 {padding-bottom: 1px;}

.rpad30 {padding-right: 30px;}
.rpad15 {padding-right: 15px;}
.rpad10 {padding-right: 10px;}
.rpad9 {padding-right: 9px;}
.rpad8 {padding-right: 8px;}
.rpad7 {padding-right: 7px;}
.rpad6 {padding-right: 6px;}
.rpad5 {padding-right: 5px;}
.rpad4 {padding-right: 4px;}
.rpad3 {padding-right: 3px;}
.rpad2 {padding-right: 2px;}
.rpad1 {padding-right: 1px;}

.lpad50 {padding-left: 50px;}
.lpad30 {padding-left: 30px;}
.lpad20 {padding-left: 20px;}
.lpad15 {padding-left: 15px;}
.lpad10 {padding-left: 10px;}
.lpad9 {padding-left: 9px;}
.lpad8 {padding-left: 8px;}
.lpad7 {padding-left: 7px;}
.lpad6 {padding-left: 6px;}
.lpad5 {padding-left: 5px;}
.lpad4 {padding-left: 4px;}
.lpad3 {padding-left: 3px;}
.lpad2 {padding-left: 2px;}
.lpad1 {padding-left: 1px;}

.pad50 {padding: 50px;}
.pad30 {padding: 30px;}
.pad20 {padding: 20px;}
.pad10 {padding: 10px;}
.pad9 {padding: 9px;}
.pad8 {padding: 8px;}
.pad7 {padding: 7px;}
.pad6 {padding: 6px;}
.pad5 {padding: 5px;}
.pad4 {padding: 4px;}
.pad3 {padding: 3px;}
.pad2 {padding: 2px;}
.pad1 {padding: 1px;}
/*END PADDING*/

m3{margin:3px;}



/*BEGIN PAGINATION STYLES*/
.pagenum_label {font-weight: normal;font-size: 11px; color: <?php print($maincolor);?>; font-family: <?php print($mainfont)?>;line-height : 16px;letter-spacing : 1px;}
.pagenum_text {font-weight: normal;font-size: 11px; color: <?php print($maincolor);?>;font-family: <?php print($mainfont)?>;line-height : 16px;letter-spacing : 1px;}
.pagenum_input{font-size: 11px;color: #008b96; border-color: <?php print($maincolor);?>;border-style: solid;border-width: 1px;font-family: <?php print($mainfont)?>;width: 50px;}
/*END PAGINATION STYLES*/

.calLabel {
  width:62px;
  border-color: #7F7F7F;
  border-style: solid;
  border-width: 1px;
  text-transform: uppercase;
  font-family: <?php print($mainfont)?>;
  font-size:11px;
  padding:1px 1px 1px 1px;
}

div.photo-legend{
	border-top:1px dashed #aaa;
}

table.photo{
  width:<?php printf("%d",$max_thumb_width+20)?>px;
  /*border:1px solid #acacac;*/
  padding:4px;
  border-radius:5px;
  -moz-border-radius:5px;
  -webkit-border-radius:5px;
}

div.photo-container{
	position:relative;
	float:left;
	margin:3px;
	padding:0;
	overflow:hidden;  
}

div.photo-container > div.xphoto{
	position:relative;
	width:<?php echo ($max_thumb_width+20)?>px;
	/*cursor:pointer;*/
	text-align:center;
	margin:2px;
	overflow:hidden;

	border:1px solid #acacac;
  border-radius:5px;
  -moz-border-radius:5px;
  -webkit-border-radius:5px;
}

div.photo-container > div.xpanel{
	position:relative;
	overflow:hidden;
}

div.photo-container div.le{
	margin:2px;
	float:left;
	text-align:left;
}
div.photo-container div.ri{
	margin:2px;
	float:right;
	text-align:right;
}

div.photo-container div.reserved,div.photo-container div.sold{
	height:4em;
	line-height:4em;
	vertical-align:middle;
	clear:both;
	width:100%;
	text-align:center;
	font-size:.8em;
	font-family:Arial;
	letter-spacing:.2em;
}

div.photo-container div.reserved{
	background:#cc1212;
	color:#fff;
}
div.photo-container div.sold{
	background:#84a2bf;
	color:#fff;
}