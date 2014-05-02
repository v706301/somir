<?php
  define("DBTYPE_MYSQL", 1);
  class CDB{
    public $type;
    public $host;
    public $port;
    public $database;
    public $username;
    public $password;

    public $theConnection;
    //
    public $bConnected;
    public $bError;
    public $errno;
    public $error;

    /**
     * CDB Constructor
     * @package Database
     * 
     * 
     * @param string $type
     * @param string $host
     * @param string $database
     * @param string $username
     * @param string $password
     */
    function CDB($type, $host, $database, $username, $password){
      $this->type = $type;
      $this->host = $host;
      $this->database = $database;
      $this->username = $username;
      $this->password = $password;
      $this->theConnection = false;
      $this->bConnected = false;
      $this->bError = false;
      $this->errno = 0;
      $this->error = null;
    }

    /**
     * Connects to the database using parameters given in constructor
     * @package Database
     * 
     * 
     * @method connect
     * @param string $file_line Line in the file where this method called from
     */
    function connect($file_line = null){
      global $dbavailable;
      if($this->bConnected)
        return;
      $this->bError = false;
      $this->errno = 0;
      $this->error = null;
      $this->theConnection = @mysql_connect($this->host, $this->username, $this->password);
      if($this->theConnection !== false){
        if(@mysql_select_db($this->database)){
          mysql_query("SET NAMES 'utf8'",$this->theConnection);
          $this->bConnected = true;
        }
        else{
          $dbavailable = false;
          $this->bError = true;
          $this->errno = mysql_errno();
          $this->error = mysql_error();
          $this->close();
          if(defined("DEBUGMODE")){
            reportError("Fatal error","Database not available",techsupport(),$file_line."\r\n".$this->error);
          }
          else
            return false;
        }
      }
      else{
        $dbavailable = false;
        if(defined("DEBUGMODE")){
          reportError("Fatal error","Database not available",techsupport(),$file_line);
        }
        else
          return false;
      }
      return $this->bConnected;
    }
    function is_connected(){return $this->bConnected;}
    function close(){
      $this->bError = false;
      $this->errno = 0;
      $this->error = null;
      switch($this->type){
        case DBTYPE_MYSQL:
          @mysql_close($this->theConnection);
          $this->bConnected = false;
          break;

        case DBTYPE_PGSQL:
          pg_close($this->theConnection);
          $this->bConnected = false;
          break;
      }
    }
    function query($query,$file_line = null) {
      global $default_techsupport_email;
      if(substr($_SERVER["DOCUMENT_ROOT"],0,1) == "/")
        $chr = "/";
      else
        $chr = "\\";
      $file_line = substr(strrchr($file_line,$chr),1);
      global $SHOW_TIMING;
      if(isset($SHOW_TIMING) && $SHOW_TIMING == true){
        global $TIMING_TABLE;
        list($usec,$sec) = explode(" ", microtime());
        $enterQuery = floatval($usec) + floatval($sec);
      }
      $this->bError = false;
      $this->errno = 0;
      $this->error = null;
      $result = @mysql_query($query, $this->theConnection );
      $this->errno = mysql_errno();
      if($this->errno != null){
        $this->bError = true;
        $this->error = mysql_error();
        if(defined("DEBUGMODE"))
          reportError("Fatal error","Query failed",$default_techsupport_email,$file_line."\r\n".$this->error."\r\n".$query);
        else
          return false;
      }
      else{
        if(isset($SHOW_TIMING) && $SHOW_TIMING == true){
          list($usec,$sec) = explode(" ", microtime());
          $exitQuery = floatval($usec) + floatval($sec);
          $duration = $exitQuery - $enterQuery;
          if(defined("SHOW_QUERY_IN_TIMING"))
            $TIMING_TABLE[] = sprintf("QUERY<br />%s<br />[<span style=\"color: #aaaaaa;\">%s</span>]<br />started at %s.<br />Duration: %.3f sec<br />END<br />",$file_line,htmlize($query),$enterQuery,$duration);
          else
            $TIMING_TABLE[] = sprintf("QUERY<br />%s<br />started at %s.<br />Duration: %.3f sec<br />END<br />",$file_line,date("Y-m-d H:i:s",(int)$enterQuery),$duration);
        }
        return $result;
      }
    }

    function error(){return $this->error;}
    function affected_rows($result){return mysql_affected_rows($this->theConnection);}
    function free_result($result){return @mysql_free_result($result);}
    function fetch_row($result){return @mysql_fetch_array($result,MYSQL_NUM);}
    function fetch_assoc($result){return @mysql_fetch_array($result,MYSQL_ASSOC);}
    function fetch_array($result){return @mysql_fetch_array($result,MYSQL_BOTH);}
    function fetchFieldName($result,$i){return @mysql_field_name($result,$i);}
    function num_rows($result){return mysql_num_rows($result);}
    function insert_id(){return mysql_insert_id($this->theConnection);}
    function num_fields($result){
      if(is_resource($result))
        return mysql_num_fields($result);
      else
        return false;
    }
    function field_type($result,$field_offset){
      if(is_resource($result))
        return mysql_field_type($result, $field_offset );
      else
        return false;
    }
    function getLimitClause($items_per_page,$start_row){
      if($items_per_page > 0){
        if(!is_null($start_row) && intval($start_row) >= 0)
          return "\nLIMIT ".$start_row.",".$items_per_page;
        else
          return "\nLIMIT ".$items_per_page;
      }
      else
        return "";
    }
  }
?>