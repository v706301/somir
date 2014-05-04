<?php
  require_once("Localize.class.php");
  
  if(!isset($items_per_page))
    $items_per_page = 10;
  $totalrows = 0;
  $totalpages = 0;
  $start_row = 0;
  $end_row = 0;
  $pagenum = 0;
  $prev_page = 0;
  $next_page = 0;
  $firstlink = 0;
  $lastlink = 0;
  $orderby = null;
  $ascdesc = null;

  function savePaginationVars($items_per_page){
    if(strlen(getParameter("start")) > 0)
      setSessionVar($_SERVER["PHP_SELF"]."_search_start",getParameter("start"));
    else if(strcasecmp(getParameter("command"),"pagenum") == 0)
      setSessionVar($_SERVER["PHP_SELF"]."_search_start",_private_calculateRequestedStartRow(getParameter("command_value"),$items_per_page));
    if(strlen(getSessionVar($_SERVER["PHP_SELF"]."_search_start",false)) == 0)
      setSessionVar($_SERVER["PHP_SELF"]."_search_start","0");
  }

  function setPaginationVars($items_per_page,$total){
    global $totalrows,$totalpages,$start_row,$end_row,$pagenum,$prev_page,$next_page,$firstlink,$lastlink;
    $totalrows = $total;
    if($items_per_page > 0){
      $totalpages =  _private_getTotalPages($totalrows,$items_per_page);
      $start_row =   _private_getStartRow($totalrows,$items_per_page,getParameter("start"),$_SERVER["PHP_SELF"]."_search_start");
      $end_row = _private_getEndRow($totalrows,$start_row,$items_per_page);
      $pagenum = _private_getPageNum($start_row,$items_per_page);
      $prev_page = $pagenum - 1;
      $next_page = $pagenum + 1;
      $firstlink = _private_getFirstLink($pagenum,$items_per_page);
      $lastlink = _private_getLastLink($pagenum,$items_per_page,$totalpages);
    }
  }

  function _private_calculateRequestedStartRow($value,$items_per_page){
    return $items_per_page * (intval($value)-1) >= 0 ? $items_per_page * (intval($value)-1) : 0;
  }

  function _private_getStartRow($total,$items_per_page,$request_value,$session_value_name){
    $row = 0;
    if(strlen($request_value) > 0)
      $row = intval($request_value);
    else if(strlen(getSessionVar($session_value_name,false)) > 0)
      $row = intval(getSessionVar($session_value_name,false));
    if($row < 0)
      $row = 0;

    //BEGIN adjust start number
    if($row >= $total)
      $row = $total - $items_per_page;
    if($row < 0)
      $row = 0;
    //END adjust start number

    setSessionVar($session_value_name,$row);
    return $row;
  }

  function _private_getEndRow($total,$start_row,$items_per_page){
    $row = 0;
    //BEGIN adjust end number
    $row = $start_row + $items_per_page - 1;
    if($row >= $total)
      $row = $total - 1;
    //END adjust end number
    return $row;
  }

  function _private_getTotalPages($total,$items_per_page){
    $totalpages = intval(floor($total/$items_per_page));
    if($total % $items_per_page != 0)
      $totalpages = $totalpages + 1;
    return $totalpages;
  }

  function _private_getPageNum($start_row,$items_per_page){
    return intval(floor($start_row/$items_per_page));
  }

  function _private_getFirstLink($pagenum,$items_per_page){
    return $pagenum - 5 > 0 ? $pagenum - 5 : 0;
  }

  function _private_getLastLink($pagenum,$items_per_page,$totalpages){
    return $pagenum + 5 < $totalpages ? $pagenum + 5 : $totalpages - 1;
  }

  function printPaginationControl($form_action = "",$form_params = array(),$page_width = "100%"){
    global $pagenum;
    global $totalrows;
    global $totalpages;
    global $items_per_page;
    global $firstlink;
    global $lastlink;
    global $prev_page;
    global $next_page;

    $lblPage = htmlize('Страница');
    $lblOf = htmlize('из');
    $lblTotal = htmlize('всего в списке');
    $lblItems = htmlize(Localize::numeric($totalrows,"наименование"));
    
    $href_params = "";
    print("<!-- BEGIN PAGE NUMBERS  -->\r\n");
    print("<table style=\"width:".$page_width.";text-align:center;\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\r\n");
    print("<tr>\r\n");
    print("<td style=\"text-align:left;white-space:nowrap;\">\r\n");
    print("<form id=\"x_pagination_form\" method=\"post\" action=\"".$form_action."\">\r\n");
    print("<table style=\"width:100%;\" border=\"0\">\r\n");
    print("<tr><td style=\"white-space:nowrap;\">");
    if(is_array($form_params)){
      $keys = array_keys($form_params);
      for($i = 0; $i < count($keys); $i++){
        print("<input type=\"hidden\" name=\"".$keys[$i]."\" value=\"".$form_params[$keys[$i]]."\" />\r\n");
        $href_params = $href_params . "&amp;" . $keys[$i] . "=" . $form_params[$keys[$i]];
      }
    }
    print("<input type=\"hidden\" name=\"command\" value=\"pagenum\" />\r\n");

		
    
    print("<span class=\"pagenum_label\"><b>{$lblPage}&nbsp;&nbsp;<input class=\"pagenum_input\" style=\"text-align: center;\" type=\"text\" name=\"command_value\" value=\"" . ($pagenum+1) . "\" />" . " {$lblOf} " . $totalpages . "</b> ({$lblTotal}: " . $totalrows . "&nbsp;".$lblItems.")</span></td></tr>\r\n");
    print("</table>\r\n");
    if($prev_page >= 0)
      print("<a href=\"" . $form_action . "?start=" . (($pagenum-1)*$items_per_page) . $href_params . "\"><img style=\"border:none;padding:0px;margin:0px;\" src=\"/images/prev.gif\" alt=\"Prev\" title=\"Предыдущая страница\" /></a>\r\n");
    if($firstlink != $lastlink){
      for($i = $firstlink; $i <= $lastlink; $i++){
        if($i == $pagenum)
          print("<span class=\"pagenum_text\">" . ($i+1) . "</span>&nbsp;\r\n");
        else{
          print("<span class=\"pagenum_text\">\r\n");
          print("<a style=\"text-decoration:underline;\" class=\"pagenum_text\" href=\"" . $form_action . "?start=" . ($i*$items_per_page) . $href_params . "\">" . ($i+1) . "</a>\r\n");
          print("</span>&nbsp;\r\n");
        }
      }
      if($next_page <= $lastlink)
        print("<a href=\"" . $form_action . "?start=" . (($pagenum+1)*$items_per_page) . $href_params . "\"><img style=\"border:none;padding:0px;margin:0px;\" src=\"/images/next.gif\" alt=\"Next\" title=\"Следующая страница\" /></a>\r\n");
    }
    print("</form>\r\n");
    print("</td>\r\n");
    print("</tr>\r\n");
    print("</table>\r\n");
    print("<!-- END PAGE NUMBERS  -->\r\n");
  }
?>
