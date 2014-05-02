<?php
  require_once("common.inc.php");

  abstract class CsvParser{
    private $RECORD_LENGTH;
    private $fieldCount;
    public function __construct($fields,$rlength = 8192){
      $this->fieldCount = $fields;
      $this->RECORD_LENGTH = $rlength;
    }
    abstract public function update($values,$lineInCsv);
    public function parse($fpath){
      $start = time();
      $fh = fopen($fpath,"r");
      if($fh){
        $i = 0;
        while(!feof($fh)){
          $values = fgetcsv($fh,$this->RECORD_LENGTH);
          if(count($values) == $this->fieldCount){
            $this->update($values,$i+1);
          }
          else if($values !== false){
            print("Field count at ".$fpath.":".($i+1)." doesn't match the declared file structure(".count($values)." fields instead of ".$this->fieldCount.")\n");
          }
          else
            $i--;
          $i++;
          $values = null;
        }
        fclose($fh);
        if(defined("COMMANDLINE"))
          print("\r\n");
        print("".$i." records processed\n");
      }
      $end = time();
      print("END parsing ".$fpath." (in ".($end-$start)." sec.)\n");
    }
  }
?>