function e$(id){return document.getElementById(id);}
function p$(tagName,parent){
  parent = parent || document; 
  return parent.getElementsByTagName(tagName);
}
function s$(element,styleAttr){
  element = (typeof element == 'object') ? element : e$(element); 
  return element.currentStyle ? element.currentStyle[styleAttr] : document.defaultView.getComputedStyle(element,null).getPropertyValue(styleAttr);
}
function c$(clsName){
  var retVal = new Array();
  var elements = document.getElementsByTagName("*");
  var j = 0;
  for(var i = 0; i < elements.length; i++){
    if(elements[i].className.indexOf(" ") >= 0){
      var classes = elements[i].className.split(" ");
      for(var j = 0;j < classes.length;j++){
        if(classes[j] == clsName)
          retVal.push(elements[i]);
      }
    }
    else if(elements[i].className == clsName)
      retVal.push(elements[i]);
  }
  return retVal;
}

function dim$(){
  var ww,wh,sw,sh,cw,ch,ow,oh;
  if(document.documentElement){
    ww = document.documentElement.offsetWidth;
    wh = document.documentElement.offsetHeight;
    cw = document.documentElement.clientWidth;
    ch = document.documentElement.clientHeight;
    ow = document.documentElement.offsetWidth;
    oh = document.documentElement.offsetHeight;
    sw = document.documentElement.scrollWidth;
    sh = document.documentElement.scrollHeight;
//alert(
//    "client: "+cw+"x"+ch+"\r\n"+
//    "offset: "+ow+"x"+oh+"\r\n"+
//    "scroll: "+sw+"x"+sh+"\r\n"
//    );
  }
  else{
    var body = document.body || document.getElementsByTagName('body')[0];
    ww = body.clientWidth;
    wh = body.clientHeight;
    cw = body.clientWidth;
    ch = body.clientHeight;
    ow = body.offsetWidth;
    oh = body.offsetHeight;
    sw = body.scrollWidth;
    sh = body.scrollHeight;
  }
  if(window.innerHeight){
    ww = window.innerWidth;
    wh = window.innerHeight;
  }
  return {
    windowWidth: ww,
    windowHeight: wh,
    clientWidth: cw,
    clientHeight: ch,
    offsetWidth: ow,
    offsetHeight: oh,
    scrollWidth: sw,
    scrollHeight: sh
  };
}

function xpos(obj){
  var curleft = 0;
  if(obj.offsetParent){
    while (obj.offsetParent){
      curleft += obj.offsetLeft
      obj = obj.offsetParent;
    }
  }
  else if(obj.x)
    curleft += obj.x;
  return curleft;
}
  
function ypos(obj){
  var curtop = 0;
  if(obj.offsetParent){
    while(obj.offsetParent){
      curtop += obj.offsetTop
      obj = obj.offsetParent;
    }
  }
  else if(obj.y)
    curtop += obj.y;
  return curtop;
}

function setCaretPos(ctrl, pos){
  if(ctrl.setSelectionRange){
    ctrl.focus();
    ctrl.setSelectionRange(pos,pos);
  }
  else if(ctrl.createTextRange){
    var range = ctrl.createTextRange();
    range.collapse(true);
    range.moveEnd('character', pos);
    range.moveStart('character', pos);
    range.select();
  }
}

function getCaretPos(ctrl){
  var pos = 0;
  if(ctrl.selectionStart || ctrl.selectionStart == '0'){
    pos = ctrl.selectionStart;
  }
  else if(document.selection){
    ctrl.focus();
    var Sel = document.selection.createRange();
    Sel.moveStart('character', -ctrl.value.length);
    pos = Sel.text.length;
  }
  return (pos);
}

function keyCode(e){
  var code = 0;
  if(window.event)
    code = window.event.keyCode;
  else if(e)
    code = e.which;
  return code;
}

function getEvent(e){
  if(window.event)
    return window.event;
  else
    return e;
}

function cancelEvent(evt){
  if(evt){
    if(evt.preventDefault)
      evt.preventDefault();
    if(evt.stopPropagation)
      evt.stopPropagation();
  }
  if(window.event){
    window.event.cancelBubble = true;
    window.event.returnValue = false;
  }
  return false;
}

function getMousePosition(event){
  var evt = getEvent(event);
  if(document.documentElement){
    x = evt.clientX + document.documentElement.scrollLeft + document.body.scrollLeft;
    y = evt.clientY + document.documentElement.scrollTop + document.body.scrollTop;
  }
  else if(window.scrollX){
    x = evt.clientX + window.scrollX;
    y = evt.clientY + window.scrollY;
  }
  return {x: parseInt(x,10),y: parseInt(y,10)};
}

function getViewportDimensions(){
  var h = 0, w = 0;
  if(self.innerHeight){
    h = window.innerHeight;
    w = window.innerWidth;
  } 
  else{
    if(document.documentElement && document.documentElement.clientHeight){
      h = document.documentElement.clientHeight;
      w = document.documentElement.clientWidth;
    }
    else{
      if(document.body){
        h = document.body.clientHeight;
        w = document.body.clientWidth;
      }
    }
  }
  return{height: parseInt(h,10),width: parseInt(w,10)};
}

function centerElement(el){
  var dim = getViewportDimensions();
  var left = (dim.width == 0) ? 50 : (dim.width - el.offsetWidth)/2;
  var top = (dim.height == 0) ? 50 : (dim.height - el.offsetHeight)/2;
  if(document.documentElement){
    left += document.documentElement.scrollLeft + document.body.scrollLeft;
    top += document.documentElement.scrollTop + document.body.scrollTop;
  }
  else if(window.scrollX){
    left += window.scrollX;
    top += window.scrollY;
  }
  el.style.left = left + 'px';
  el.style.top = top + 'px';
}

function stringWidth(meter,s,fontFamily,fontWeight,fontSize){
  if(meter){
    meter.style.width = "30px";
    meter.innerHTML = s;
    return meter.scrollWidth;
  }
  return result;
}

function setComboWidth(combo,fontFamily,fontWeight,fontSize,extraSize){
  var l = -100;
  var meter = document.getElementById("stringWidthMeter");
  if(meter){
    meter.style.fontFamily = fontFamily;
    meter.style.fontWeight = fontWeight;
    meter.style.fontSize = fontSize;
    for(i = 0; i < combo.options.length; i++){
      var x = parseInt(stringWidth(meter,combo.options[i].text,fontFamily,fontWeight,fontSize));
      if(x > l)
        l = x;
    }
    if(l > 0){
      extraSize = parseInt(extraSize);
      if(isNaN(extraSize) || extraSize < 27)
        extraSize = 27;
      combo.style.width = (l + extraSize) + "px";
    }
  }
}

function isCharKey(event){
  if(isControlKey(event))
    return false;
  var c = keyCode(event);
  if(c == 8)
    return false;
  //delete
  else if(c == 46)
    return false;
  return true;
}

function isControlKey(event){
  var c = keyCode(event);
  if(c == 9)
    return true;
  else if(c == 16)
    return true;
  else if(c == 17)
    return true;
  else if(c == 18)
    return true;
  else if(c == 19)
    return true;
  else if(c == 20)
    return true;
  else if(c == 27)
    return true;
  else if(c == 33)
    return true;
  else if(c == 34)
    return true;
  else if(c == 35)
    return true;
  else if(c == 36)
    return true;
  else if(c == 37)
    return true;
  else if(c == 38)
    return true;
  else if(c == 39)
    return true;
  else if(c == 40)
    return true;
  else if(c == 44)
    return true;
  else if(c == 45)
    return true;
  else if(c == 91)
    return true;
  else if(c == 92)
    return true;
  else if(c == 93)
    return true;
  else if(c == 112)
    return true;
  else if(c == 113)
    return true;
  else if(c == 114)
    return true;
  else if(c == 115)
    return true;
  else if(c == 116)
    return true;
  else if(c == 117)
    return true;
  else if(c == 118)
    return true;
  else if(c == 119)
    return true;
  else if(c == 120)
    return true;
  else if(c == 121)
    return true;
  else if(c == 122)
    return true;
  else if(c == 123)
    return true;
  else if(c == 144)
    return true;
  else if(c == 145)
    return true;
  return false;
}
