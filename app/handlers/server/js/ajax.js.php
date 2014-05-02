  //var callRegistry = new Array();
  var ajcall_Progress_Div = 'ajcall_Progress_Div';
  
  function createRequest(){
    var req = false;
    if(typeof(XMLHttpRequest) != "undefined")
      req = new XMLHttpRequest();
    else if(typeof ActiveXObject != "undefined"){
      try{
        req = new ActiveXObject("Msxml2.XMLHTTP");
      }
      catch(e){
        try{
          req = new ActiveXObject("Microsoft.XMLHTTP");
        }
        catch(e2){
          try{
            req = new ActiveXObject("Msxml2.XMLHTTP.4.0");
          }
          catch(e3){
            req = null;
          }
        }
      }
    }
    if(!req && window.createRequest)
      req = window.createRequest();
    return req;
  }
  
  function sendRequest(req,url,data){
    req.open("POST",url,true);
    req.setRequestHeader("Method", "POST " + url + " HTTP/1.1");
    req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    req.send(data);
  }
  
  function isAjaxCallFailed(arr){
    for(x in arr){
      if(x == "error")
        return arr[x];
    }
    return false;
  }
  
  function getResponse(req){
	//we always respond with array
	if(req.responseText.charAt(0) == '{' || req.responseText.charAt(0) == '[')
	  return eval("("+req.responseText+")");
	//otherwise we have some [PHP] error
	else{
      return {"error" : req.responseText};
	}
  }

  function isArray(a){return a.sort ? true : false;}

  function urlencode(s){
    var ux = s.toString();
    ux = encodeURIComponent(ux);
    ux = ux.replace("!","%21");
    ux = ux.replace("\'","%27");
    ux = ux.replace("(","%28");
    ux = ux.replace(")","%29");
    ux = ux.replace("*","%2A");
    ux = ux.replace("~","%7E");
    ux = ux.replace("%20","+");
    return ux;
  }

  function ajcallEncode(data){
  	var s = '';
    for(var property in data){
      if(s.length > 0) s += '&';
      s += property + '=' + urlencode(data[property]);
    }
    return s;
  }
  
  
  function ajcallProgress_Start(){
    if(document.documentElement)
      container = document.documentElement;
    else if(document.body)
      container = document.body;
    var progress = document.getElementById('ajcall_Progress_Div');
    if(!progress){
      progress = document.createElement('div');
      progress.id = 'ajcall_Progress_Div';
      progress.style.position = 'absolute';
      progress.style.visibility = 'hidden';
      progress.style.background = 'url(/images/loading51.gif) no-repeat center center';
      progress.style.left = '0px';
      progress.style.top = '0px';
      progress.style.width = container.clientWidth + 'px';
      progress.style.height = container.clientHeight + 'px';
      progress.style.zIndex = '500';
      container.appendChild(progress);
    }
    progress.style.visibility = 'visible';
  }

  function ajcallProgress_Stop(){
    var progress = document.getElementById('ajcall_Progress_Div');
    progress.style.visibility = 'hidden';
  }
  
  function ajcall(requestStr,postData,errorMessage,successCallback){
    var monitor,monitorId = null,counter = 0;
    ajcallProgress_Start();
    var req = createRequest();
    if(req != null){
      req.onreadystatechange = function(){
        if(req.readyState == 4){
          ajcallProgress_Stop();
          clearInterval(monitorId);
          if(req.status != 200){
            alert(errorMessage+':\r\nОшибка №'+req.status+'('+req.statusText+')');
          }
          else{
            /**
             * To simplify processing we always return object or array.
             * If responseText doesn't start with [ or { - we have server-side error
               !!!CHANGED 20120510 - it can return plain html now
             */
/*
            if(!(req.responseText == null || req.responseText == 'null' || req.responseText.substring(0,1) == "[" || req.responseText.substring(0,1) == "{")){
              alert('От сервера получен неверный ответ:\r\n'+req.responseText+'\r\nВозможно произошла ошибка.');
            }
            else{
*/
            
            if(req.responseText.substring(0,1) == "[" || req.responseText.substring(0,1) == "{"){
              //alert(req.responseText);
              var result = eval("("+req.responseText+")");
              if(result != null && typeof(result["error"]) == 'string'){
                alert(result["error"]);
              }
              else{
                if(typeof successCallback != 'undefined'){
                  successCallback(result);
                }
              }
            }
            else{
              if(typeof successCallback != 'undefined'){
                successCallback(req.responseText);
              }
            }
          }
        }
      };
    }
    monitor = function(){
      counter++;
      if(counter > 10){
        ajcallProgress_Stop();
        req.abort();
        clearInterval(monitorId);
        alert(errorMessage+'\r\nПроцедура прервана');
      }
    };
    monitorId = setInterval(monitor,1000);
    //alert(requestStr);
    sendRequest(req,requestStr,postData);
  }
