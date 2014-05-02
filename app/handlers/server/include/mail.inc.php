<?php
  require_once("SMTPWrapper.class.php");
  function sendmail($contentType,$to,$cc,$bcc,$from,$sender,$subject,$txt,$is_debug_mode = false){
    if(strlen(trim($to)) > 0 && strlen(trim($txt)) > 0){
      if(is_null($contentType) || strlen($contentType) == 0)
        $contentType = "text/html; charset=iso-8859-1";
      else
        $contentType = trim($contentType);
      if(strlen($from) == 0)
        $from = getSuperuserEmail();
      else
        $from = trim($from);
      if(strlen($sender) == 0)
        $sender = $from;
      else
        $sender = "\"".trim($sender)."\" <".$from.">";
      if(strlen(trim($subject)) == 0)
        $subject = $from;
      else
        $subject = trim($subject);
      //
      $headers = "MIME-Version: 1.0\n"
                 ."Content-type: ".$contentType."\n"
                 ."X-Mailer: PHP/" . phpversion()."\n"
                 ."From: ".$sender."\n";
      if(strlen(trim($cc)) > 0)
        $headers .= "Cc: ".trim($cc)."\n";
      if(strlen(trim($bcc)) > 0)
        $headers .= "Bcc: ".trim($bcc)."\n";

      $headers .= "Reply-To: ".$from."\n";
      $headers .= "Return-Path: ".$from."\n";
      if(!$is_debug_mode)
        $result = mail($to, $subject, $txt, $headers, "-f".$from);
      else{
        hprint($to);
        print("<br />");
        hprint($subject); 
        print("<br />");
        hprint($txt); 
        print("<br /><br />");
        hprint($headers);
        print("<br />");
        hprint($from);
        print("<hr><br />");
      }
      
      return $result;
    }
    return false;
  }
?>
