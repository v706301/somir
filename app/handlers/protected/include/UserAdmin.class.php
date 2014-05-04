<?php
  require_once("User.class.php");
  
  class UserAdmin extends User{
    public function UserAdmin($row = null){
      $this->init($row);
    }

    public function httpRead($checkDisabled = false){
      parent::httpRead($checkDisabled);
    }

    public function dbRead($id){
      global $dbprefix;
      $u = null;
      $row = null;
      $db = getDatabase();
      $query = sprintf(
      "select\n" .
      "u.*\n" .
      ",u.id as user_id\n" .
      "from ".$dbprefix."_user u\n" .
      "where u.id=%d and u.usertype_id=%d"
      ,$id
      ,USERTYPE_ADMIN
      );
      //trace($query);
      $rs = $db->query($query,__FILE__.":".__LINE__);
      if($rs && $row = $db->fetch_array($rs)){
        $db->free_result($rs);
        $this->init($row);
      }
      else
        throw new Exception(sprintf("Admin with ID#%d not found",$id));
      return true;
    }
    
    public function dbInsert(){
      global $dbprefix;
      global $application_name;
      if(!User::isOperatorAdmin()){
        setAlert("Недостаточно полномочий");
        return false;
      }
      if(!$this->check()){
        return false;
      }
      $db = getDatabase();
      $md5_pass = md5($this->password1);
      $mc_pass = mc($this->password1);
      $query = sprintf("insert into ".$dbprefix."_user(".
      "usertype_id".
      ",createdby_id".
      ",parent_id".
      ",date_created".
      ",disabled".
      ",username".
      ",password".
      ",mcpass".
      ",email".
      ",name".
      ",fname".
      ",mname".
      ",lname".
      ",address1".
      ",address2".
      ",city".
      ",state".
      ",country".
      ",zip".
      ",phone".
      ") values(".
      "%d".   //usertype_id
      ",%d".  //createdby_id
      ",%d".  //parent_id
      ",now()".  //date_created
      ",%d".  //disabled
      ",%s".  //username
      ",'%s'".//password
      ",'%s'".//mcpass
      ",%s".  //email
      ",%s".  //name
      ",%s".  //fname
      ",%s".  //mname
      ",%s".  //lname
      ",%s".  //address1
      ",%s".  //address2
      ",%s".  //city
      ",%s".  //state
      ",%s".  //country
      ",%s".  //zip
      ",%s".  //phone
      ")"
      ,USERTYPE_ADMIN
      ,User::getOperatorId()
      ,$this->parent_id
      ,0
      ,sqlsafe($this->username)
      ,$md5_pass
      ,$mc_pass
      ,sqlsafe($this->email)
      ,sqlsafe($this->name)
      ,sqlsafe($this->fname)
      ,sqlsafe($this->mname)
      ,sqlsafe($this->lname)
      ,sqlsafe($this->address1)
      ,sqlsafe($this->address2)
      ,sqlsafe($this->city)
      ,sqlsafe($this->state)
      ,sqlsafe($this->country)
      ,sqlsafe($this->zip)
      ,sqlsafe($this->phone)
      );
      //trace($query);
      $db->query($query,__FILE__.":".__LINE__);
      $this->id = $db->insert_id();
      
      $body = lang_EmailNewAccount($this->username,$this->password1);
      $mailer = new SMTPWrapper();
      if($mailer->sendHtmlMimeMail("text/html", $this->getEmail(),null,null,null,$application_name,lang_EmailNewAccountSubj($this->getName()),$body)){
        setAlert(lang_EmailNewAccountSuccess($this->getName(),$this->getEmail()));
      }
      else{
        setAlert(lang_EmailNewAccountFailure($this->getName(),$this->getEmail(),$mailer->getErrors()));
      }
      return true;
    }
    
    public function dbUpdate(){
      global $dbprefix;
      if(defined("PROFILE_PAGE") && $this->id == User::getOperatorId()){
        //access to own profile always granted
      }
      else if(!User::isOperatorAdmin()){
        setAlert("Недостаточно полномочий");
        return false;
      }
      if(!$this->check())
        return false;
      $db = getDatabase();
      if(User::isOperatorBuiltInAdmin() && defined("PROFILE_PAGE")){
        $query = sprintf("update ".$dbprefix."_user set ".
        "email=%s".
        ",phone=%s".
        (is_null($this->password1) ? "%s" : ",password='%s'").
        (is_null($this->password1) ? "%s" : ",mcpass='%s'").
        " where id=%d"
        ,sqlsafe($this->email)
        ,sqlsafe($this->phone)
        ,(is_null($this->password1) ? "" : md5($this->password1))
        ,(is_null($this->password1) ? "" : mc($this->password1))
        ,$this->id
        );
      }
      else{
        //trace_r($this);
        $query = sprintf("update ".$dbprefix."_user set\n".
        "name=%s\n".
        ",usertype_id=%d\n".
        (is_null($this->password1) ? "%s" : ",password='%s'\n").
        (is_null($this->password1) ? "%s" : ",mcpass='%s'\n").
        ",email=%s".
        ",disabled=%d".
        ",fname=%s".
        ",mname=%s".
        ",lname=%s".
        ",address1=%s".
        ",address2=%s".
        ",city=%s".
        ",state=%s".
        ",country=%s".
        ",zip=%s".
        ",phone=%s".
        " where id=%d"
        ,sqlsafe($this->name)
        ,USERTYPE_ADMIN
        ,(is_null($this->password1) ? "" : md5($this->password1))
        ,(is_null($this->password1) ? "" : mc($this->password1))
        ,sqlsafe($this->email)
        ,$this->disabled ? 1 : 0
        ,sqlsafe($this->fname)
        ,sqlsafe($this->mname)
        ,sqlsafe($this->lname)
        ,sqlsafe($this->address1)
        ,sqlsafe($this->address2)
        ,sqlsafe($this->city)
        ,sqlsafe($this->state)
        ,sqlsafe($this->country)
        ,sqlsafe($this->zip)
        ,sqlsafe($this->phone)
        ,$this->id
        );
      }
      //trace($query);
      $db->query($query,__FILE__.":".__LINE__);
      setAlert("Данные учетной записи обновлены");
      return true;
    }

    public function dbDelete(){
      global $dbprefix;
      if(is_null($this->id) || !User::isOperatorAdmin() || $this->id == User::getOperatorId() || $this->id == BUILTIN_ADMIN_ACCOUNT){
        setAlert("Учетную запись удалить невозможно");
        return false;
      }
      if(!is_null($this->id)){
        $db = getDatabase();
        $query = sprintf("delete from ".$dbprefix."_user where id=%d and usertype_id=%d",$this->id,USERTYPE_ADMIN);
        $db->query($query,__FILE__.":".__LINE__);
        return true;
      }
      else{
        return false;
      }
    }
  }
?>