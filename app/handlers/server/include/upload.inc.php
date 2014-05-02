<?php
  function getIniSetting_MaxUploadSize(){
    $x = "".ini_get("upload_max_filesize");
    if(strlen($x) > 0){
      $lchar = substr($x,strlen($x)-1);
      if($lchar == 'M' || $lchar == 'm'){
        return floatval(substr($x,0,strlen($x)-1)) * 1024 * 1024;
      }
      else if($lchar == 'K' || $lchar == 'k')
        return floatval(substr($x,0,strlen($x)-1)) * 1024;
      else
        return floatval($x);
    }
    else
      return 0;
  }

  function getFormParameter_MaxUploadSize(){
    $x = getParameter("MAX_FILE_SIZE");
    if(strlen($x) > 0){
      $lchar = substr($x,strlen($x)-1);
      if($lchar == 'M' || $lchar == 'm'){
        return floatval(substr($x,0,strlen($x)-1)) * 1024 * 1024;
      }
      else if($lchar == 'K' || $lchar == 'k')
        return floatval(substr($x,0,strlen($x)-1)) * 1024;
      else
        return floatval($x);
    }
    else
      return 0;
  }

  function imageExt($type){
    if(strpos($type,"jpeg") !== false)
      $ext = ".jpg";
    else if(strpos($type,"gif") !== false)
      $ext = ".gif";
    else if(strpos($type,"png") !== false)
      $ext = ".png";
    else if(strpos($type,"bmp") !== false)
      $ext = ".bmp";
    else
      reportError("imageExt: Unsupported image type: ".$type);
    return $ext;
  }

  function isImageProcessingSupported($content_type){
    if(
        strcmp($content_type,"image/gif") == 0 || 
        strcmp($content_type,"image/jpg") == 0 || 
        strcmp($content_type,"image/jpeg") == 0 || 
        strcmp($content_type,"image/pjpeg") == 0 || 
        strcmp($content_type,"image/png") == 0 ||
        strcmp($content_type,"image/x-png") == 0
      ){
      if(strcmp($content_type,"image/gif") == 0 && supportsGIF()){
        ;
      }
      else if((strcmp($content_type,"image/jpg") == 0 || strcmp($content_type,"image/jpeg") == 0 || strcmp($content_type,"image/pjpeg") == 0) && supportsJPG()){
        ;
      }
      else if((strcmp($content_type,"image/png") == 0 || strcmp($content_type,"image/x-png") == 0) && supportsPNG()){
        ;
      }
      else{
        return false;
      }
    }
    else{
      return false;
    }
    if(!(function_exists("imagecreatetruecolor") && ($im = @imagecreatetruecolor(1,1)) != false)){
      return false;
    }
    if(!(function_exists("imagecopyresampled") && @imagecopyresampled($im,$im,0,0,0,0,1,1,1,1) != false)){
      return false;
    }
    $tmpname = tempnam("/tmp","image-processing-test".time());
    if(!(function_exists("imagejpeg") && @imagejpeg($im,$tmpname) != false)){
      return false;
    }
    unlink($tmpname);

    $arr_required_image_functions = array(
      "imagecreatetruecolor",
      "imagecopyresampled",
      "imagejpeg",
    );
    for($i = 0; $i < count($arr_required_image_functions); $i++){
      $f = $arr_required_image_functions[$i];
      if(!function_exists($f)){
        //print("Function not exists: ".$f); flush();
        return false;
      }
    }
    return true;
  }
  
  function supportsGIF(){return function_exists("imagecreatefromgif");}
  
  function supportsJPG(){return function_exists("imagecreatefromjpeg");}
  
  function supportsPNG(){return function_exists("imagecreatefrompng");}

  function getUploadError($param){
    if(!isset($_FILES[$param]) || strlen($_FILES[$param]["name"]) == 0){
      return false;
    }
    else if($_FILES[$param]["error"] == UPLOAD_ERR_OK || $_FILES[$param]["error"] == UPLOAD_ERR_NO_FILE){
      return false;
    }
    else{
      if($_FILES[$param]["error"] == UPLOAD_ERR_INI_SIZE){
        $upload_max_filesize = getIniSetting_MaxUploadSize();
        if($upload_max_filesize)
          $upload_max_filesize = " of ".$upload_max_filesize." bytes";
        return "Uploaded file '".$_FILES[$param]["name"]."' exceeds maximum allowed size".$upload_max_filesize;
      }
      else if($_FILES[$param]["error"] == UPLOAD_ERR_FORM_SIZE){
        $upload_max_filesize = getFormParameter_MaxUploadSize();
        if($upload_max_filesize)
          $upload_max_filesize = " of ".$upload_max_filesize." bytes";
        return "Uploaded file '".$_FILES[$param]["name"]."' exceeds maximum allowed size".$upload_max_filesize;
      }
      else if($_FILES[$param]["error"] == UPLOAD_ERR_PARTIAL){
        return "File ".$_FILES[$param]["name"]." was only partially uploaded";
      }
      else if($_FILES[$param]["error"] == UPLOAD_ERR_NO_TMP_DIR){
        return "Missing a temporary folder";
      }
      else if($_FILES[$param]["error"] == UPLOAD_ERR_CANT_WRITE){
        return "Failed to write file to disk";
      }
    }
  }
  
  function getUploadSize($param){
    if(!isset($_FILES[$param]) || strlen($_FILES[$param]["name"]) == 0)
      return 0;
    else if($_FILES[$param]["error"] != UPLOAD_ERR_OK){
      return 0;
    }
    else
      return $_FILES[$param]["size"];
  }

  function getUploadFilename($param){
    if(!isset($_FILES[$param]) || strlen($_FILES[$param]["name"]) == 0)
      return null;
    else 
      return $_FILES[$param]["name"];
  }

  function getUploadedMedia(&$upload,&$error = false,$param,$max_width = -1,$max_height = -1,$max_thumb_width = -1,$max_thumb_height = -1){
    if(!isset($_FILES[$param]) || strlen($_FILES[$param]["name"]) == 0){
      return null;
    }
    $wdir = getWriteableDirectory();
    $media = array();
    if(is_null($param))
      reportError("INVALID PARAMETER","image name parameter is empty",techsupport(),__FILE__.":".__LINE__);
    $media["name"] = $_FILES[$param]["name"];
    $media["type"] = $_FILES[$param]["type"];
    $media["size"] = intval($_FILES[$param]["size"]);
    $media["tmp_name"] = $_FILES[$param]["tmp_name"];
    $media["error"] = intval($_FILES[$param]["error"]);

    if($media["error"] == UPLOAD_ERR_INI_SIZE){
      $upload_max_filesize = getIniSetting_MaxUploadSize();
      if($upload_max_filesize)
        $upload_max_filesize = " of ".$upload_max_filesize." bytes";
      $error = "Uploaded file '".$media["name"]."' exceeds maximum allowed size".$upload_max_filesize;
      return false;
    }
    else if($media["error"] == UPLOAD_ERR_FORM_SIZE){
      $upload_max_filesize = getFormParameter_MaxUploadSize();
      if($upload_max_filesize)
        $upload_max_filesize = " of ".$upload_max_filesize." bytes";
      $error = "Uploaded file '".$media["name"]."' exceeds maximum allowed size".$upload_max_filesize;
      return false;
    }
    else if($media["error"] == UPLOAD_ERR_PARTIAL){
      $error = "File ".$media["name"]." was only partially uploaded";
      return false;
    }
    else if($media["error"] == UPLOAD_ERR_NO_TMP_DIR){
      $error = "Missing a temporary folder";
      return false;
    }
    else if($media["error"] == UPLOAD_ERR_NO_FILE){
      $error = false;
      return false;
    }
    else if($media["error"] == UPLOAD_ERR_CANT_WRITE){
      $error = "Failed to write file to disk";
      return false;
    }
    else if(is_uploaded_file($media["tmp_name"]) && $media["error"] == 0){
      $tmp = tempnam("/tmp","3512-upload");
      $result = move_uploaded_file($media["tmp_name"],$tmp);
      if((($max_width > 0 && $max_height > 0) || ($max_thumb_width > 0 && $max_thumb_height > 0)) && isImageProcessingSupported($media["type"])){
        $dim = getimagesize($tmp);
        $media["width"] = $dim[0];
        $media["height"] = $dim[1];
        if($dim[2] == 1){
          $fullImg = imagecreatefromgif($tmp);
        }
        else if($dim[2] == 2){
          $fullImg = imagecreatefromjpeg($tmp);
        }
        else if($dim[2] == 3){
          $fullImg = imagecreatefrompng($tmp);
        }
        if($max_width > 0 && $max_height > 0){
          if($media["width"] > $max_width || $media["height"] > $max_height){
            $media["width"]  = getScaledImageWidth($dim[0],$dim[1],$max_width,$max_height,__FILE__.":".__LINE__);
            $media["height"] = getScaledImageHeight($dim[0],$dim[1],$max_width,$max_height,__FILE__.":".__LINE__);
            $im = imagecreatetruecolor($media["width"],$media["height"]);
            imagecopyresampled($im,$fullImg,0, 0, 0, 0,$media["width"],$media["height"],$dim[0], $dim[1]);
            if($dim[2] == 1){
              imagegif($im, $tmp);
              $media["type"] = "image/gif";
            }
            else if($dim[2] == 2){
              imagejpeg($im, $tmp);
              $media["type"] = "image/jpeg";
            }
            else if($dim[2] == 3){
              imagepng($im, $tmp);
              $media["type"] = "image/png";
            }
          }
        }
        $fd = fopen($tmp,"rb");
        $content = fread($fd,filesize($tmp));
        fclose($fd);
        $media["content"] = $content;
        $media["size"] = strlen($content);

        if($max_thumb_width > 0 && $max_thumb_height > 0){
          if($media["width"] <= $max_thumb_width && $media["height"] <= $max_thumb_height){
            $media["thumb_type"] = $media["type"];
            $media["thumb_content"] = $media["content"];
            $media["thumb_size"] = $media["size"];
            $media["thumb_width"] = $media["width"];
            $media["thumb_height"] = $media["height"];
          }
          else{
            $media["thumb_width"]  = getScaledImageWidth($dim[0],$dim[1],$max_thumb_width,$max_thumb_height,__FILE__.":".__LINE__);
            $media["thumb_height"] = getScaledImageHeight($dim[0],$dim[1],$max_thumb_width,$max_thumb_height,__FILE__.":".__LINE__);
            $im = imagecreatetruecolor($media["thumb_width"],$media["thumb_height"]);
            imagecopyresampled($im,$fullImg,0, 0, 0, 0,$media["thumb_width"],$media["thumb_height"],$dim[0], $dim[1]);
            if($dim[2] == 1){
              imagegif($im, $tmp);
              $media["thumb_type"] = "image/gif";
            }
            else if($dim[2] == 2){
              imagejpeg($im, $tmp);
              $media["thumb_type"] = "image/jpeg";
            }
            else if($dim[2] == 3){
              imagepng($im, $tmp);
              $media["thumb_type"] = "image/png";
            }
            $fd = fopen($tmp,"rb");
            $content = fread($fd,filesize($tmp));
            $media["thumb_content"] = $content;
            $media["thumb_size"] = strlen($content);
            fclose($fd);
          }
        }
      }
      else{
        $fd = fopen($tmp,"rb");
        $content = fread($fd,filesize($tmp));
        $media["content"] = $content;
        $media["size"] = strlen($content);
        fclose($fd);
      }
      unlink($tmp);
    }
    else{
      $error = "Unknown error";
      $media = null;
    }
    
    $upload = $media;
    return true;
  }

  function getScaledImageWidth($width,$height,$maxwidth,$maxheight,$file_line = null){
    $arr = getScaledImageSize($width,$height,$maxwidth,$maxheight,$file_line);
    return intval($arr[0]);
  }

  function getScaledImageHeight($width,$height,$maxwidth,$maxheight,$file_line = null){
    $arr = getScaledImageSize($width,$height,$maxwidth,$maxheight,$file_line);
    return intval($arr[1]);
  }

  function getScaledImageSize($width,$height,$maxwidth,$maxheight,$file_line = null){
    $width = floatval($width);
    $height = floatval($height);
    $maxwidth = floatval($maxwidth);
    $maxheight = floatval($maxheight);

    if($width == 0.0 || $height == 0.0 || $maxwidth == 0.0 || $maxheight == 0.0){
      reportError("INCORRECT PARAMETER","One or more parameters are incorrect: ".$width."/".$height."/".$maxwidth."/".$maxheight,techsupport(),$file_line);
    }
    $rat = $width/$height;
    if($width > $maxwidth && $height > $maxheight){
      if($rat >= 1.0){
        $width = $maxwidth;
        $height = $width/$rat;
      }
      else{
        $height = $maxheight;
        $width = $height*$rat;
      }
    }
    else if($width > $maxwidth){
      $width = $maxwidth;
      $height = $width/$rat;
    }
    else if($height > $maxheight){
      $height = $maxheight;
      $width = $height*$rat;
    }

    if($width > $maxwidth || $height > $maxheight)
      $result = getScaledImageSize($width,$height,$maxwidth,$maxheight);
    else
      $result = array(intval($width),intval($height));
    return $result;
  }
  
?>