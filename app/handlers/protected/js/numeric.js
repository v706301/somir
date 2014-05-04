function filterNum(el,event,iDecimals,bAcceptNegativeValues){
  var evt = getEvent(event);
  var code = keyCode(event);
  var value = el.value;
  var pos = getCaretPos(el);
  if(isNaN(iDecimals) || iDecimals < 0)
    iDecimals = 0;
  var chr = null;
  if(!value)
    value = "";

  var debugWindow = document.getElementById("idCopyright");
  if(debugWindow){
    debugWindow.innerText = code;
  }
  if((code == 109 || code == 189) && pos == 0 && value.charAt(0) != '-' && bAcceptNegativeValues)
    chr = '-';
  else if(iDecimals > 0 && (code == 110 || code == 190) && value.indexOf(".") < 0)
    chr = '.';
  else if(code == 48 || code == 96)
    chr = '0';
  else if(code == 49 || code == 97)
    chr = '1';
  else if(code == 50 || code == 98)
    chr = '2';
  else if(code == 51 || code == 99)
    chr = '3';
  else if(code == 52 || code == 100)
    chr = '4';
  else if(code == 53 || code == 101)
    chr = '5';
  else if(code == 54 || code == 102)
    chr = '6';
  else if(code == 55 || code == 103)
    chr = '7';
  else if(code == 56 || code == 104)
    chr = '8';
  else if(code == 57 || code == 105)
    chr = '9';
  else if(code == 8){return true;}
  else if(code == 9){return true;}
  else if(code == 27){return true;}
  else if(code >= 33 && code <= 36){return true;}
  else if(code >= 37 && code <= 40){return true;}
  else if(code == 46){return true;}
  else if(code >= 112 && code <= 123){return true;}
  if(iDecimals > 0){
    if(el && el.value && el.value.indexOf(".") >= 0){
      var mantis = el.value.substring(el.value.indexOf(".")+1);
      if(pos > el.value.indexOf(".") && mantis && mantis.length >= iDecimals){
        chr = null;
      }
    }
  }
  if(chr != null){
    if(value){
      var pre = value.substring(0,pos);
      var post = value.substring(pos);
      value = pre + chr + post;
    }
    else
      value = chr;
    el.value = value;
    setCaretPos(el,pos+1);
  }
  cancelEvent(evt);  
  return false;
}

  function checkIntegerLimits(form_element,lower,upper,ck_lower,ck_upper,ck_empty,message){
    var x = form_element.value;
    x = x.replace(/^\W+/,'');
    if((x == null || x == "")){
      if(ck_empty == true){
        if(message != null){alert(message); form_element.select(); form_element.focus();}
        return false;
      }
      else
        return true;
    }
    y = parseFloat(x);
    x = parseInt(x);
    if(isNaN(x)){
      if(message != null){alert(message); form_element.select(); form_element.focus();}
      return false;
    }
    if(x > y || x < y){
      if(message != null){alert(message); form_element.select(); form_element.focus();}
      return false;
    }
    if(ck_lower && x < lower){
      if(message != null){alert(message); form_element.select(); form_element.focus();}
      return false;
    }
    if(ck_upper && x > upper){
      if(message != null){alert(message); form_element.select(); form_element.focus();}
      return false;
    }
    return true;
  }

  function checkFloatLimits(form_element,lower,upper,ck_lower,ck_upper,ck_empty,message){
    var x = form_element.value;
    x = x.replace(/^\W+/,'');
    if((x == null || x == "")){
      if(ck_empty == true){
        if(message != null){alert(message); form_element.select(); form_element.focus();}
        return false;
      }
      else
        return true;
    }
    x = parseFloat(x);
    if(isNaN(x)){
      if(message != null){alert(message); form_element.select(); form_element.focus();}
      return false;
    }
    if(ck_lower && x < lower){
      if(message != null){alert(message); form_element.select(); form_element.focus();}
      return false;
    }
    if(ck_upper && x > upper){
      if(message != null){alert(message); form_element.select(); form_element.focus();}
      return false;
    }
    return true;
  }

  function floatValueWithinRange(x,lower,upper,ck_lower,ck_upper,ck_empty,message){
    x = x.replace(/^\W+/,'');
    if((x == null || x == "")){
      if(ck_empty == true){
        if(message != null)
          alert(message);
        return false;
      }
      else
        return true;
    }
    x = parseFloat(x);
    if(isNaN(x)){
      if(message != null)
        alert(message);
      return false;
    }
    if(ck_lower && x < lower){
      if(message != null)
        alert(message);
      return false;
    }
    if(ck_upper && x > upper){
      if(message != null)
        alert(message);
      return false;
    }
    return true;
  }


