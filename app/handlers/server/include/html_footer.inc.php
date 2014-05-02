<?php
  $extime = "";
  $xs = "";
  if(isset($SHOW_TIMING) && $SHOW_TIMING === true){
    list($usec,$sec) = explode(" ", microtime());
    $endTimeMillis = floatval($usec) + floatval($sec);
    $extime .= ($endTimeMillis - $startTimeMillis + floatval(getParameter("extime")));
    $extime = " [".sprintf("%.3f",$extime)."]";
    $TIMING_TABLE[] = "Exit: ".date("Y-m-d H:i:s",(int)$endTimeMillis);
    $xs .= "<div id=\"timing_table\" ondblclick=\"this.style.display='none';\" style=\"width: ".($page_width+10)."px; position: absolute; display: none; z-index: 100; font-family: Tahoma; font-size: 11px; background-color: #ffffff;\">\r\n";
    for($xi = 0; $xi < count($TIMING_TABLE); $xi++)
      $xs .= $TIMING_TABLE[$xi] . "<br />\r\n";
    $xs .= "</div>\r\n";
  }
?>
                        </div>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td id="x_footer">
            <table style="background-color: <?php print($mainbkg)?>; width:100%;" cellspacing="0" cellpadding="0">
              <tr>
                <td 
                  <?php if(isset($SHOW_TIMING) && $SHOW_TIMING === true){?>
                  ondblclick="var x = document.getElementById('timing_table'); if(x){x.style.display='block';}"
                  <?php }?>
                  id="idCopyright" 
                  class="copyright" 
                  style="text-align: right; vertical-align: middle; height: 35px;"><?php print($copyright.$extime)?>
                </td>
                <td style="text-align:left;padding-left:20px;">
                </td>
              </tr>
              <tr><td style="height: 1px; background-color: #ffffff;"></td></tr>
            </table>
          </td>
        </tr>
      </table>
    </div>
    <?php print($xs);?>
<?php showAlert();?>
<?php 
  print3512OnloadHandler();
?>
<?php
  if(!is_null($cdb))
    closeDatabase();
?>
  </body>
</html>
