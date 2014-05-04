<?php
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once("common.inc.php");
?>

function trim(s){
  s = ""+s;
  if(s.length == 0)
    return s;
  var ws = "\u0009\u000A\u000B\u000C\u000D\u0020\u0085\u00A0\u1680\u180E\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200A\u200B\u2028\u2029\u202F\u205F\u2060\u3000";
  var i = 0;
  //ltrim
  for(i = 0; i < s.length; i++){
    if(ws.indexOf(s.charAt(i)) < 0)
      break;
  }
  if(i > 0)
    s = s.substring(i);
  //rtrim
  for(i = s.length - 1; i >= 0; i--){
    if(ws.indexOf(s.charAt(i)) < 0)
      break;
  }
  if(i < s.length - 1)
    s = s.substring(0,i+1);
  return s;
}

function onPageUnload(form,url){
  if(typeof(INITIAL_STATE) != "undefined"){
    if(!INITIAL_STATE){
      disableButtons(form);
      window.location.href = url;
    }
    else{
      if(confirmExit()){
        disableButtons(form);
        window.location.href = url;
      }
    }
  }
  else{
    disableButtons(form);
    window.location.href = url;
  }
  return false;
}

function disableButtons(form){
  if(form){
    for(var i = 0; i < form.elements.length; i++){
      var type = form.elements[i].type ? form.elements[i].type.toUpperCase() : "";
      if(type == "SUBMIT" || type == "BUTTON" || type == "RESET") 
        form.elements[i].disabled = true;
    }
    return true;
  }
  return false;
}

function setCommand(form,command,bClear){
  if(form && form.elements['command']){
    if(bClear || form.elements['command'].value.length == 0)
      form.elements['command'].value = command;
    else
      form.elements['command'].value += ":" + command;
    return true;
  }
  return false;
}

function submitForm(form,command,bClear){
  if(setCommand(form,command,bClear)){
    form.submit();
    return true;
  }
  return false;
}

function isInputEmpty(form_element,message){
  if(form_element){
    var x = form_element.value;
    x = x.replace(/^\s+|\s+$/g,'');
    if(x == null || x == ""){
      if(message != null){
        alert(message);
        form_element.select();
        form_element.focus();
      }
      return true;
    }
    return false;
  }
  else{
    return true;
  }
}

function isSelectEmpty(form_element,message){
  if(form_element && form_element.options){
    if(form_element.length > 0){
      var x = form_element.options[form_element.selectedIndex].value;
      x = x.replace(/^\s+|\s+$/g,'');
      if(x == null || x == ""){
        if(message != null){
          alert(message);
          form_element.focus();
        }
        return true;
      }
      return false;
    }
    else{
      return false;
    }
  }
  else
    return true;
}

function isEmail(form_element,message){
  if(form_element){
    var x = form_element.value;
    var filter  = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    var ax = x.split(",");
    
    var result = true;
    for(i = 0; i < ax.length; i++){
      if(filter.test(ax[i]) == false){
        if(message != null){
          if(ax[i] != "")
            message = message+" ["+ax[i]+"]";
          alert(message);
          form_element.focus();
        }
        result = false;
        break;
      }
    }
    return result;
  }
  else
    return false;
}

var MODIFIED_WARNING = 'Значения формы были изменены. Выйти без сохранения?';
var INITIAL_STATE = false;

function saveInitialState(){
  var forms = document.forms;
  for(var x = 0; x < forms.length; x++){
    var f = forms[x];
    if(f.id && f.id.substring && f.id.substring(0,"ck_mod_".length) == "ck_mod_"){
      INITIAL_STATE = new Array();
      for(var i = 0; i < f.elements.length; i++){
        var el = f.elements[i];
        if(!el.id || (el.id && el.id.substring(0,"xfc_".length) != "xfc_")){
          if(el.tagName.toLowerCase() == "select"){
            INITIAL_STATE[i] = el.selectedIndex;
          }
          else if(el.tagName.toLowerCase() == "textarea"){
            INITIAL_STATE[i] = el.value;
          }
          else if(el.tagName.toLowerCase() == "input" && (el.type.toLowerCase() == "text" || el.type.toLowerCase() == "password")){
            INITIAL_STATE[i] = el.value;
          }
          else if(el.tagName.toLowerCase() == "input" && el.type.toLowerCase() == "radio"){
            INITIAL_STATE[i] = el.checked;
          }
          else if(el.tagName.toLowerCase() == "input" && el.type.toLowerCase() == "checkbox"){
            INITIAL_STATE[i] = el.checked;
          }
        }
      }
      break;
    }
  }
}

function formUpdated(){
  var modified = false;
  if(INITIAL_STATE){
    var forms = document.getElementsByTagName("form");
    for(var x = 0; x < forms.length; x++){
      var f = forms[x];
      if(f.id && f.id.substring(0,"ck_mod_".length) == "ck_mod_"){
        for(var i = 0; i < f.elements.length; i++){
          var el = f.elements[i];
          if(!el.id || (el.id && el.id.substring(0,"xfc_".length) != "xfc_")){
            if(el.tagName.toLowerCase() == "select" && INITIAL_STATE[i] != el.selectedIndex){
              modified = true;
              break;
            }
            else if(el.tagName.toLowerCase() == "textarea" && INITIAL_STATE[i] != el.value){
              modified = true;
              break;
            }
            else if(el.tagName.toLowerCase() == "input" && (el.type.toLowerCase() == "text" || el.type.toLowerCase() == "password") && INITIAL_STATE[i] != el.value){
              modified = true;
              break;
            }
            else if(el.tagName.toLowerCase() == "input" && el.type.toLowerCase() == "radio" && INITIAL_STATE[i] != el.checked){
              modified = true;
              break;
            }
            else if(el.tagName.toLowerCase() == "input" && el.type.toLowerCase() == "checkbox" && INITIAL_STATE[i] != el.checked){
              modified = true;
              break;
            }
          }
        }
        break;
      }
    }
  }
  return modified;
}

function confirmExit(){
  var modified = formUpdated();
  if(!modified || (modified && confirm(MODIFIED_WARNING)))
    return true;
  else
    return false;
}
