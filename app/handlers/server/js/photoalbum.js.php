  var errmsg = 'Не удалось связаться с сервером для сохранения изменений';
  var errloadmsg = 'Не удалось связаться с сервером для чтения текстовой информации о фото';
  var errsaveslidesmsg = 'Не удалось связаться с сервером для инициализации слайдшоу';

  function alignFloatingDivs(){
    var arr = c$('photo-container');
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
  
  function SaveAlbumInfo(form,album_id){
    var elName = form.elements['photoalbum_name'];
    var elDesc = form.elements['photoalbum_desc'];
    var elHidden = form.elements['is_hidden'];
    if(trim(elName.value).length == 0){
      alert('Нужно указать название фотоальбома');
      return false;
    }
    var postData = 'photoalbum_id='+album_id+'&photoalbum_name='+urlencode(elName.value)+'&photoalbum_desc='+urlencode(elDesc.value)+'&is_hidden='+(elHidden.value);
    ajcall('/ajaxcalls/photoalbum_saveinfo.php',postData,errmsg);
  }

  function SetFrontendPhoto(src,w,h,id,album_id){
    ajcall('/ajaxcalls/photoalbum_set_frontend.php?photo_id='+id+'&photoalbum_id='+album_id,'',errmsg,
      function(result){
        var frontend = document.getElementById('frontend_photo');
        frontend.src = src;
        frontend.style.width = w + 'px';
        frontend.style.height = h + 'px';
      }
    );
  }

  function SavePhotoText(form,photoalbum_id){
    var elPhotoId = form.elements['photo_id'];
    var elName = form.elements['photo_name'];
    var elDesc = form.elements['photo_desc'];
    var elKeywords = form.elements['photo_keywords'];
    var elPrice = form.elements['photo_price'];
    var elStatus = form.elements['photo_status'];
    var elDiameter = form.elements['diameter'];
    var elNotes = form.elements['notes'];
    var postData = 
    'photoalbum_id='+photoalbum_id+
    '&photo_id='+elPhotoId.value+
    '&photo_name='+urlencode(elName.value)+
    '&photo_desc='+urlencode(elDesc.value)+
    '&photo_keywords='+urlencode(elKeywords.value)+
    '&photo_price='+urlencode(elPrice.value)+
    '&photo_status='+urlencode(elStatus.value)+
    '&diameter='+urlencode(elDiameter.value)+
    '&notes='+urlencode(elNotes.value)+
    ''
    ;
    ajcall('/ajaxcalls/photo_set_text.php',postData,errmsg,
      function(result){
        var el = document.getElementById('photo_html'+elPhotoId.value);
        if(el){el.innerHTML = result.success;}
        alignFloatingDivs();
      }
    );
    $('#update_photo_dialog').dialog('close');
  }

  function openUpdatePhotoDialog(photoalbum_id,photo_id){
    var form = document.getElementById('update_photo_form');
    var dialog = document.getElementById('update_photo_dialog');
    form.elements['photo_id'].value = photo_id;
    var elPhotoId = form.elements['photo_id'];
    var elName = form.elements['photo_name'];
    var elDesc = form.elements['photo_desc'];
    var elKeywords = form.elements['photo_keywords'];
    var elPrice = form.elements['photo_price'];
    var elStatus = form.elements['photo_status'];
    var elDiameter = form.elements['diameter'];
    var elNotes = form.elements['notes'];
    var requestStr = "/ajaxcalls/photo_get_text.php?photo_id="+elPhotoId.value+'&photoalbum_id='+photoalbum_id;

    ajcall(requestStr,'',errmsg,
      function(result){
        $('#update_photo_dialog').dialog('open');
        $('#update_photo_dialog').dialog( "option", "title", result['photo_name'] );
        //workaround for Opera bug
        elName.style.visibility = 'hidden';
        elDesc.style.visibility = 'hidden';
        elKeywords.style.visibility = 'hidden';
        elPrice.style.visibility = 'hidden';
        elStatus.style.visibility = 'hidden';
        elDiameter.style.visibility = 'hidden';
        elNotes.style.visibility = 'hidden';
       
        elName.value = result['photo_name'] == null ? '' : result['photo_name'];
        elDesc.value = result['photo_desc'] == null ? '' : result['photo_desc'];
        elKeywords.value = result['photo_keywords'] == null ? '' : result['photo_keywords'];
        elPrice.value = result['photo_price'] == null ? '' : result['photo_price'];
        elStatus.value = result['photo_status'] == null ? '' : result['photo_status'];
        elDiameter.value = result['diameter'] == null ? '' : result['diameter'];
        elNotes.value = result['notes'] == null ? '' : result['notes'];
            
        elName.style.visibility = 'visible';
        elDesc.style.visibility = 'visible';
        elKeywords.style.visibility = 'visible';
        elPrice.style.visibility = 'visible';
        elStatus.style.visibility = 'visible';
        elDiameter.style.visibility = 'visible';
        elNotes.style.visibility = 'visible';
        elName.focus();elName.select();
      }
    );
  }

  function countCheckedPhotos(form){
    var checkedCount = 0;
    for(var i = 0; i < form.elements.length; i++){
      if(form.elements[i].name.substring(0,'photo_id'.length).toLowerCase() == 'photo_id' && form.elements[i].value != 0)
        checkedCount++;
    }
    return checkedCount;
  }

  function ckHidden(){
    var form = document.getElementById('photoalbum_form');
    var el = form.elements['is_hidden'];
    var ckb = document.getElementById('ckb_hidden');
    if(el.value == 0){
      ckb.style.backgroundImage = 'url(/images/btn-checkbox-on.gif)';
      ckb.style.backgroundPosition = '0px 0px';
      el.value = 1;
    }
    else{
      ckb.style.backgroundImage = 'url(/images/btn-checkbox-off.gif)';
      ckb.style.backgroundPosition = '0px 0px';
      el.value = 0;
    }
    SaveAlbumInfo(form,form.elements['photoalbum_id'].value);
  }

  function ckPhoto(id){
    var form = document.getElementById('photoalbum_form');
    var el = form.elements['photo_id'+id];
    var ckb = document.getElementById('ckb'+id);
    if(el.value == 0){
      ckb.style.backgroundImage = 'url(/images/btn-checkbox-on.gif)';
      ckb.style.backgroundPosition = '0px 0px';
      el.value = 1;
    }
    else{
      ckb.style.backgroundImage = 'url(/images/btn-checkbox-off.gif)';
      ckb.style.backgroundPosition = '0px 0px';
      el.value = 0;
    }
    var disabled = countCheckedPhotos(form) == 0;
    form.elements['moveto_photoalbum_id'].disabled = disabled;
    form.elements['btn_move'].disabled = disabled;
    form.elements['btn_hide'].disabled = disabled;
    form.elements['btn_show'].disabled = disabled;
    form.elements['btn_delete'].disabled = disabled;
  }

  function ckDelete(form){
    var checkedCount = countCheckedPhotos(form);
    if(checkedCount == 0)
      return false;
    else{
      var warn = null;
      if(checkedCount > 1)
        warn = ''+checkedCount+' фото будут удалены без возможности восстановления.\r\nПродолжить?';
      else
        warn = 'Фото будет удалено без возможности восстановления.\r\nПродолжить?';
      if(confirm(warn))
        return submitForm(form,'delete');
      else
        return false;
    }
  }

  function ckShow(form){
    var checkedCount = countCheckedPhotos(form);
    if(checkedCount == 0)
      return false;
    else
      return submitForm(form,'show');
  }

  function ckHide(form){
    var checkedCount = countCheckedPhotos(form);
    if(checkedCount == 0)
      return false;
    else
      return submitForm(form,'hide');
  }

  function ckMove(form){
    var checkedCount = countCheckedPhotos(form);
    if(checkedCount == 0)
      return false;
    else
      return submitForm(form,'move');
  }
  
  function openAddPhotoDialog(){$('#add_photo_dialog').dialog('open');}

  function ckPhotoUpload(form){
    if(trim(form.elements['photo'].value).length > 0){
      return submitForm(form,'add_photo');
    }
    return false;
  }

  function ck(form){}