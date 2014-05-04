var Slide_Show = function(slideShowName){
  this.infoSpeed = 10;
  this.imgSpeed = 10;
  this.slideExpositionTime = 5;
  this.infoOpacity = 50;
  this.thumbOpacity = 70;
  this.navHover = 70;
  this.navOpacity = 25;
  this.scrollSpeed = 5;
  this.emptySpaceColor = '#000000';
  this.selectedThumbBorderColor = '#ffffff';
  this.thumbSpacing = 2;
  this.slideShowName = slideShowName;
  this.styleLink = '';
  this.slideIndex = 0;
  this.slides = [];
};

Slide_Show.prototype = {
  init:function(
      slides,
      divImageId,
      divImgPrevId,
      divImgNextId,
      divInfoId,
      divThumbsFrameId,
      divThumbsId,
      divThumbsLeftScroll,
      divThumbsRightScroll
      ){
    this.slides = slides;
    this.divImage = e$(divImageId);
    this.divThumbs = e$(divThumbsId);
    this.divInfo = e$(divInfoId);
    this.divInfo.style.opacity = this.infoOpacity/100; 
    this.divInfo.style.filter = 'alpha(opacity='+this.infoOpacity+')';
    this.imageWidth = parseInt(s$(this.divImage,'width'));
    this.imageHeight = parseInt(s$(this.divImage,'height'));
    var w = 0;
    for(var i = 0; i < this.slides.length; i++){
      var slide = this.slides[i];
      var thumb = new Image();
      thumb.src = slide.thumbURL;
      thumb.setAttribute('class','image');
      thumb.style.width = slide.thumbWidth+'px';
      thumb.style.height = slide.thumbHeight+'px';
      thumb.style.cursor = 'pointer';
      thumb.style.border = '1px solid #aaaaaa';
      thumb.style.padding = '2px';

      w += parseInt(slide.thumbWidth) + 6;
      if(i != this.slides.length-1){
        thumb.style.marginRight = this.thumbSpacing+'px';
        w += this.thumbSpacing;
      }
      thumb.style.opacity = this.thumbOpacity/100;
      thumb.style.filter = 'alpha(opacity='+this.thumbOpacity+')';
      thumb.onmouseover = new Function('startTransparencyAnimation(this,100,5)');
      thumb.onmouseout = new Function('startTransparencyAnimation(this,'+this.thumbOpacity+',5)');
      thumb.onclick = new Function(this.slideShowName+'.prepareImageAt('+i+',1)')
      this.divThumbs.appendChild(thumb);
    }
    this.divThumbs.style.width = w + 'px';
    this.divThumbs.style.verticalAlign = 'middle';
    var divThumbsFrame = e$(divThumbsFrameId);
    var left = e$(divThumbsLeftScroll);
    var right = e$(divThumbsRightScroll);
    if(divThumbsFrame.offsetWidth >= w){
      left.style.display = right.style.display = 'none';
    }
    else{
      left.onmouseover = new Function('thumbsDiv_StartAnimation("'+this.divThumbs.id+'",-1,'+this.scrollSpeed+')');
      left.onmouseout = right.onmouseout = new Function('thumbsDiv_StopAnimation("'+this.divThumbs.id+'")');
      right.onmouseover = new Function('thumbsDiv_StartAnimation("'+this.divThumbs.id+'",1,'+this.scrollSpeed+')');
    }
    
    if(divImgPrevId && divImgNextId){
      var divImgPrev = e$(divImgPrevId);
      var divImgNext = e$(divImgNextId);
      if(this.slides.length == 1){
        divImgPrev.style.display = divImgNext.style.display = 'none';
      }
      else{
        divImgPrev.style.opacity = divImgNext.style.opacity = this.navOpacity/100;
        divImgPrev.style.filter = divImgNext.style.filter = 'alpha(opacity='+this.navOpacity+')';
        divImgPrev.onmouseover = divImgNext.onmouseover = new Function('startTransparencyAnimation(this,'+this.navHover+',5)');
        divImgPrev.onmouseout = divImgNext.onmouseout = new Function('startTransparencyAnimation(this,'+this.navOpacity+',5)');
        divImgPrev.onclick = new Function(this.slideShowName+'.showNextImage(-1,1)');
        divImgNext.onclick = new Function(this.slideShowName+'.showNextImage(1,1)')
      }
    }
  },
  
  start:function(){
    this.prepareImageAt(this.slideIndex,0);
  },
  stop:function(){
    this.prepareImageAt(this.slideIndex,1);
  },
  
  showNextImage:function(direction,manualMode){
    var nextSlide = this.slideIndex + direction;
    this.slideIndex = nextSlide = (nextSlide < 0) ? this.slides.length - 1 : (nextSlide > this.slides.length - 1) ? 0 : nextSlide;
    this.prepareImageAt(nextSlide,manualMode);
  },
  
  prepareImageAt:function(index,manualMode){
    clearTimeout(this.infoDivAnimationTimer);
    if(manualMode)
      clearTimeout(this.imageDivAnimationTimer);
    this.slideIndex = index;
    if(this.divInfo)
      infoDiv_StartAnimation(this.divInfo,1,this.infoSpeed/2);
    this.currentImage = new Image();
    this.currentImage.style.cursor = 'pointer';
    this.currentImage.style.opacity = 0;
    this.currentImage.style.filter = 'alpha(opacity=0)';
    this.currentImage.onload = new Function(this.slideShowName+'.showImageAt('+index+','+manualMode+')');
    this.currentImage.onclick = new Function(this.slideShowName+'.showNextImage('+manualMode+','+(manualMode == 0 ? 1 : 0)+')');
    this.currentImage.style.width = this.slides[index].photoWidth + 'px';
    this.currentImage.style.height = this.slides[index].photoHeight + 'px';
    this.currentImage.src = this.slides[index].photoURL;
    if(this.divThumbs){
      var thumbsArray = p$('img',this.divThumbs);
      for(var i = 0; i < thumbsArray.length; i++)
        thumbsArray[i].style.borderColor = (i != index) ? '' : this.selectedThumbBorderColor;
    }
  },

  showImageAt:function(index,manualMode){
    this.divImage.appendChild(this.currentImage);
    var emptyWidth = this.imageWidth - parseInt(this.currentImage.offsetWidth);
    var emptyHeight = this.imageHeight - parseInt(this.currentImage.offsetHeight);
    if(emptyWidth > 0){
      var x = Math.floor(emptyWidth/2);
      this.currentImage.style.borderLeft = x+'px solid '+this.emptySpaceColor;
      this.currentImage.style.borderRight = (emptyWidth-x)+'px solid '+this.emptySpaceColor;
    }
    if(emptyHeight > 0){
      var x = Math.floor(emptyHeight/2);
      this.currentImage.style.borderTop = x+'px solid '+this.emptySpaceColor;
      this.currentImage.style.borderBottom = (emptyHeight-x)+'px solid '+this.emptySpaceColor;
    }
    startTransparencyAnimation(this.currentImage,100,this.imgSpeed);
    this.infoDivAnimationTimer = setTimeout(new Function(this.slideShowName+'.prepareInfoDiv('+index+')'),this.infoSpeed*100);
    if(!manualMode)
      this.imageDivAnimationTimer = setTimeout(new Function(this.slideShowName+'.showNextImage(1,0)'),this.slideExpositionTime*1000);
    var imageList = p$('img',this.divImage);
    if(imageList.length > 2)
      this.divImage.removeChild(imageList[0]);
  },
  
  prepareInfoDiv:function(index){
    if(this.divInfo){
      var slide = this.slides[index];
      p$('h3',this.divInfo)[0].innerHTML = slide.name == null ? '' : slide.name;
      p$('p',this.divInfo)[0].innerHTML = slide.desc == null ? '' : slide.desc;
      this.divInfo.style.height = 'auto';
      var maxHeight = parseInt(this.divInfo.offsetHeight);
      this.divInfo.style.height = '0px';
      infoDiv_StartAnimation(this.divInfo,maxHeight,this.infoSpeed);
    }
  }
};

function thumbsDiv_StartAnimation(element,direction,speed){
  element = (typeof element == 'object') ? element : e$(element); 
  var x = element.style.left || s$(element,'left');
  element.style.left = x;
  var maxScrollPosition = (direction == 1) ? parseInt(element.offsetWidth) - parseInt(element.parentNode.offsetWidth) : 0;
  element.si = setInterval(function(){thumbsDiv_Animate(element,maxScrollPosition,direction,speed);},20);
}

function thumbsDiv_Animate(element,maxScrollPosition,direction,speed){
  var currentLeftPosition = parseInt(element.style.left); 
  if(currentLeftPosition == maxScrollPosition)
    thumbsDiv_StopAnimation(element);
  else{
    var pixelsToScroll = Math.abs(maxScrollPosition+currentLeftPosition);
    pixelsToScroll = pixelsToScroll < speed ? pixelsToScroll : speed;
    var newLeftPosition = currentLeftPosition - pixelsToScroll * direction;
    element.style.left = newLeftPosition+'px';
  }
}

function thumbsDiv_StopAnimation(element){
  element = (typeof element == 'object') ? element : e$(element); 
  clearInterval(element.si);  
}

function infoDiv_StartAnimation(element,maxHeight,animationFrameCount){
  element = (typeof element == 'object') ? element : e$(element); 
  var offsetHeight = element.offsetHeight;
  var styleHeight = element.style.height || s$(element,'height');
  var heightDelta = 0;offsetHeight - parseInt(styleHeight); 
  var direction = offsetHeight - heightDelta > maxHeight ? -1 : 1;
  clearInterval(element.si);
  element.si = setInterval(function(){infoDiv_Animate(element,maxHeight,heightDelta,direction,animationFrameCount)},20);
}

//var sd = false;

function infoDiv_Animate(element,maxHeight,heightDelta,direction,animationFrameCount){
  var actualHeight = element.offsetHeight - heightDelta;
  if(actualHeight == maxHeight)
    clearInterval(element.si);
  else{
    var h = actualHeight + (Math.ceil(Math.abs(maxHeight - actualHeight)/animationFrameCount) * direction);
//    if(!sd)
//      sd = e$('slideshow-debug');
//    sd.innerHTML += "<br />" + "#" + 
//    "aH="+actualHeight+"; mH="+maxHeight+"; hD="+heightDelta+"; dir="+direction+"; afc="+animationFrameCount+
//    "; h="+h;
//    if(h < 0)
//      h = 0;
    element.style.height = h + 'px';
  }
}

function startTransparencyAnimation(element,maxOpacity,animationFrameCount){
  element = (typeof element == 'object') ? element : e$(element); 
  var opacity = element.style.opacity || s$(element,'opacity');
  var direction = maxOpacity > opacity * 100 ? 1 : -1;
  element.style.opacity = opacity;
  element.style.filter='alpha(opacity='+opacity*100+')';
  clearInterval(element.ai); 
  element.ai=setInterval(function(){transparencyAnimation(element,maxOpacity,direction,animationFrameCount)},20);
}

function transparencyAnimation(element,maxOpacity,direction,animationFrameCount){
  var opacity = Math.round(element.style.opacity * 100);
  if(opacity == maxOpacity)
    clearInterval(element.ai);
  else{
    opacity += Math.ceil(Math.abs(maxOpacity-opacity)/animationFrameCount) * direction;
    element.style.opacity = opacity/100;
    element.style.filter='alpha(opacity='+opacity+')';
  }
}
