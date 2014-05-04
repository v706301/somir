var Page = {
  initialize: function() {
    new iMask({
        onFocus: function(obj) {
            obj.setStyles({"background-color":"#ff8", border:"1px solid #880"});
        },

        onBlur: function(obj) {
            obj.setStyles({"background-color":"#fff", border:"1px solid #ccc"});
        },

        onValid: function(event, obj) {
            obj.setStyles({"background-color":"#8f8", border:"1px solid #080"});
        },

        onInvalid: function(event, obj) {
            if(!event.shift) {
                obj.setStyles({"background-color":"#f88", border:"1px solid #800"});
            }
        }
    });
  }
};
window.onDomReady(Page.initialize);