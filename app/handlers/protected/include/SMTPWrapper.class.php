<?php
  require_once("http.inc.php");
  require_once("settings.inc.php");
  if($_SERVER["DOCUMENT_ROOT"]){
    ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include/mail");
  }
  else if(preg_match("|/|",$_SERVER['PHP_SELF'])){
    preg_match("|(.*/)\w|",$_SERVER["PHP_SELF"], $m);
    ini_set("include_path", ini_get("include_path") . ":" . $m[1] . "include/mail");
  }
  else{
    ini_set("include_path", ini_get("include_path") . ":" . $_ENV["PWD"] . "/include/mail");
  }
  require_once("mail/htmlMimeMail.php");

  class SMTPWrapper{
    private $errors = null;
    private $result = false;
    
    public function SMTPWrapper(){}
    
    public function failure(){return $this->result === false;}
    public function getErrors(){
      if($this->failure()){
        if(!is_null($this->errors) && count($this->errors) > 0)
          return implode("\n",$this->errors);
        else
          return "[Unknown error]";
      }
    }
    
    public function sendHtmlMimeMail($contentType,$to,$cc,$bcc,$from,$sender,$subject,$txt, $application_id = null, $attachments = null,$is_debug_mode = false){
      global $DOCUMENT_ROOT;
      $this->errors = null;
      $this->result = false;
      if(strlen(trim($to))> 0 && (strlen(trim($txt)) > 0 || !is_null($attachments))){
        if(!is_array($to)) $to = explode(",",$to);
        $mail = new htmlMimeMail();
        $mailType = getSetting("mail-type");
        if(strcasecmp($mailType,"smtp") == 0){
          $host = getSetting("smtp-host");
          $port = getSetting("smtp-port");
          $user = getSetting("smtp-user");
          $pass = getSetting("smtp-pass");
          if(strlen($user) > 0 && strlen($pass) > 0)
            $bAuth = true;
          else
            $bAuth = false;
          $mail->setSMTPParams($host,$port,null,$bAuth,$user,$pass);
        }
        else
          $mailType = "mail";
        $mail->setHeader('Date', date('D, d M y H:i:s O'));
        $contentType = (is_null($contentType) || strlen($contentType) == 0)? "text/html; charset=iso-8859-1" : trim($contentType );
        $from = (strlen($from) == 0)? getSuperuserEmail() : trim($from );
        $sender = (strlen($sender) == 0)? $from : "\"" . trim($sender). "\" <" . $from . ">";
        $mail->setFrom($sender);
        $mail->setReplyTo($from);
        $mail->setReturnPath($from);

        if(!is_null($attachments) && is_array($attachments)){
          for($xi = 0; $xi < count($attachments); $xi++){
            //object of class MailAttachment
            $a = $attachments[$xi];
            $mail->addAttachment($a->getBody(),$a->getName());
          }
        }
        else if(is_a($attachments,"MailAttachment"))
          $mail->addAttachment($attachments->getBody(),$attachments->getName());
        $subject = (strlen(trim($subject))== 0)? $from : trim($subject);
        $mail->setSubject($subject);
        if(strlen(trim($cc))> 0)$mail->setCc(trim($cc));
        if(strlen(trim($bcc))> 0) $mail->setBcc();
        
        if(strpos($contentType, "text/html")!== false){
          $html = $mail->getFile($DOCUMENT_ROOT . "/include/mail.header.x" );
          $html .= strlen($txt) > 0 ? $txt : "&nbsp;";
          
          $html .= $mail->getFile($DOCUMENT_ROOT . "/include/mail.footer.x" );
          
          $mail->setHtml($html);
          $mail->addHtmlImage($mail->getFile($DOCUMENT_ROOT . "/images/gradlogo.png" ), "gradlogo.png", "image/png");
        }
        else
          $mail->setText($txt);
        
        if($is_debug_mode){
          $this->result = true;
          if(!$mail->is_built)
            $mail->buildMessage($mail->build_params); 
          hprint($to);
          print("<br />");
          hprint($subject); 
          print("<br />");
          hprint($txt); 
          print("<br /><br />");
          print_r($mail->headers); 
          print("<br />");
          hprint($from);
          print("<hr><br />");
        }
        else{
          $this->result = $mail->send($to,$mailType);
          if(!$this->result){
            $this->errors = $mail->errors;
          }
        }
      }
      return $this->result;
    }
  }
?>