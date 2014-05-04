<?php 
  ini_set("include_path", ini_get("include_path") . (substr($_SERVER["DOCUMENT_ROOT"],0,1) != "/" ? ";" : ":") . $_SERVER["DOCUMENT_ROOT"] . "/include");
  require_once 'http.inc.php';
?>
  
  function alignPhotoContainers(className){
    var arr = c$(className);
    var h = 0;
    for(var i = 0; i < arr.length; i++){
      var x = arr[i];
      if(x.offsetHeight > h)
        h = x.offsetHeight;
    }
    for(var i = 0; i < arr.length; i++){
      var x = arr[i];
      x.style.height = h + 'px';
    }
  }

  function loadPhotos(selectedChar){
    var query_string = '';
    var form = e$('search_form');
    var search = form.elements['search'].value;
    if(typeof selectedChar != 'undefined')
      query_string = 'char='+selectedChar;
    else if(search.length > 1)
      query_string = 'search='+search;
    else if(search == 1)
      query_string = 'char='+search;
    
    var container = e$('photo-list');
    if(container){
      container.innerHTML = '';
      var requestURL = '/ajaxcalls/load_slides.php?photoshop='+(window.location.href.indexOf('photoshop') >=0 ? '1':'0')+'&'+query_string;
      ajcall(
        requestURL,'','Не удалось загрузить фотографии',
        function(result){
          $('#photo-list').html(result);
          alignPhotoContainers('photo-container');
          e$('slideshow-btn').style.display = result.length > 0 ? 'block':'none';
        }
      );
    }
  }

  function startSlideShow(){
    var arr = c$("photo-container");
    var post = "";
    for(var i = 0; i < arr.length; i++){
      if(post.length > 0)
        post += ',';
      post += arr[i].id.substring('div-photo'.length);
    }
    post = 'photos='+post+'&aw='+window.screen.availWidth+'&ah='+window.screen.availHeight;
    ajcall(
      '/ajaxcalls/save_slides.php',post,'Не удалось инициализировать слайдшоу',
      function(result){
        var w = window.open('/slideshow.php','slide_show','status=no,toolbar=no,menubar=no,location=no,resizable=no,scrollbars=no');
        if(w){w.opener = self;w.focus();}
      }
    );
  }

  function ajcall_SetSelected(el,photo_id){
    var monitor,monitorId = null,responseData = null;
    var counter = 0;
    var href = document.getElementById("idShoppingCart");
    var img = document.getElementById("idShoppingCartImg");
    var imgSrc = img.src;
    img.src = "/images/loading26.gif";
    var req = createRequest();
    if(req != null){
      req.onreadystatechange = function(){
        if(req.readyState == 4){
          clearInterval(monitorId);
          if(req.status != 200){
            alert("Не удалось связаться с сервером для добавления/удаления элемента\r\n"+req.status);
          }
          else{
            if(req.responseText.substring(0,1) != "{")
              alert(req.responseText);
            else{
              var result = eval("("+req.responseText+")");
              if(typeof(result["error"]) == 'string'){
                img.src = imgSrc;
                alert(result["error"]);
              }
              else{
                if(result["total"] == ""){
                  img.src = "/images/basket-empty.png";
                  img.title = '<?php echo lang('В тележке пусто')?>';
                  e$('scart-menuitem').style.display = 'none';
                  e$('ckout-menuitem').style.display = 'none';
                }
                else{
                  img.src = "/images/basket-full.png";
                  img.title = result["total"];
                  e$('scart-menuitem').style.display = 'inline';
                  e$('ckout-menuitem').style.display = 'inline';
                }
                if(el.rel == 0){
                  el.rel = 1;
                  el.innerHTML = '<?php echo lang('Из корзины')?>';
                }
                else{
                  el.rel = 0;
                  el.innerHTML = '<?php echo lang('В корзину')?>';
                }
              }
            }
          }
        }
      };
    }
    monitor = function(){
      counter++;
      if(counter > 10){
        img.src = imgSrc;
        req.abort();
        clearInterval(monitorId);
        alert("Не удалось связаться с сервером для добавления/удаления элемента\r\nПроцедура прервана");
      }
    };
    monitorId = setInterval(monitor,1000);
    requestStr = "/ajaxcalls/setselected.php?photo_id="+photo_id+"&selected="+(el.rel == 0 ? 1:0);
    sendRequest(req,requestStr,"");
  }
