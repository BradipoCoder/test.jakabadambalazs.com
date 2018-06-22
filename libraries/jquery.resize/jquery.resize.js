// resize end
(function(jQuery){
    jQuery.resizeend = function(el, options){
        var base = this;
       
        base.jQueryel = jQuery(el);
        base.el = el;
       
        base.jQueryel.data("resizeend", base);
        base.rtime = new Date(1, 1, 2000, 12,00,00);
        base.timeout = false;
        base.delta = 200;
       
        base.init = function(){
            base.options = jQuery.extend({},jQuery.resizeend.defaultOptions, options);
           
            if(base.options.runOnStart) base.options.onDragEnd();
           
            jQuery(base.el).resize(function() {
               
                base.rtime = new Date();
                if (base.timeout === false) {
                    base.timeout = true;
                    setTimeout(base.resizeend, base.delta);
                }
            });
       
        };
        base.resizeend = function() {
            if (new Date() - base.rtime < base.delta) {
                setTimeout(base.resizeend, base.delta);
            } else {
                base.timeout = false;
                base.options.onDragEnd();
            }               
        };
       
        base.init();
    };
   
    jQuery.resizeend.defaultOptions = {
        onDragEnd : function() {},
        runOnStart : false
    };
   
    jQuery.fn.resizeend = function(options){
        return this.each(function(){
            (new jQuery.resizeend(this, options));
        });
    };
})(jQuery);