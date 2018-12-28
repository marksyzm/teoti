/* jQuery plugin textselect
 * version: 0.9
 * tested on jQuery 1.3.2
 * author: Josef Moravec, josef.moravec@gmail.com
 * 
 * usage:
 * $(function() {
 *    $(document).bind('textselect', function(e) {
 *      Do stuff with e.text
 *    });    
 *   });
 *    
 */
(function($) {
$.event.special.textselect = {
  setup: function(data, namespaces) {
    $(this).data("textselected",false);
    $(this).bind('mouseup', $.event.special.textselect.handler);
  },
  teardown: function(data) {
    $(this).unbind('mouseup', $.event.special.textselect.handler);
  },
  handler: function(event) { 
    var text = $.event.special.textselect.getSelectedText().toString(); 
    if(text!='') {
      $(this).data("textselected",true);
      event.type = "textselect";
      event.text = text;
      $.event.handle.apply(this, arguments);
    }
  },
  getSelectedText: function() {
    var text = '';
    if (window.getSelection) {
      text = window.getSelection();
    } else if (document.getSelection) {
      text = document.getSelection();
    } else if (document.selection) {
      text = document.selection.createRange().text;
    }
    return text;
  }
}

$.event.special.textunselect = {
  setup: function(data, namespaces) {
    $(this).data("textselected",false);
    $(this).bind('mouseup', $.event.special.textunselect.handler);
    $(this).bind('keyup', $.event.special.textunselect.handlerKey)
  },
  teardown: function(data) {
    $(this).unbind('mouseup', $.event.special.textunselect.handler);
  },
  handler: function(event) {  
    if($(this).data("textselected")) {
      var text = $.event.special.textselect.getSelectedText().toString();
      if(text=='') {
        $(this).data("textselected",false);
        event.type = "textunselect";
        $.event.handle.apply(this, arguments);
      }
    }
  },
  handlerKey: function(event) {
    if($(this).data("textselected")) {
      var text = $.event.special.textselect.getSelectedText().toString();
      if((event.keyCode = 27) && (text=='')) { 
        $(this).data("textselected",false);
        event.type = "textunselect";
        $.event.handle.apply(this, arguments);
      }
    }
  }
}
})(jQuery);
