/**
 * Light Slider
 */

(function ($, Drupal) {
  Drupal.behaviors.lightslider = {
    attach: function (context, settings) {
      // Using once() to apply the myCustomBehaviour effect when you want to do just run one function.
      // $('body', context).once('myCustomBehavior').addClass('well');
      var self = this;



      $('.lightslider', context).once('lightslider').each(function(){
        // Nascondo temporaneamente lo slider per evitare di vedere le immagini giganti
        jQuery('.wrapper-lightslider').css('height', 0).css('overflow', 'hidden');
        jQuery('.wrapper-lightslider').removeClass('hide');

        var id = $(this).attr('data-lsid');
        self.armSlider(id, context, settings, self);
      });
    },
    armSlider: function (id, context, settings, self){
      
      var selector = '.lightslider-' + id;
      var options = settings.lightslider[id];

      var wrapper = $('.wrapper-lightslider-' + id);
      wrapper.css('height', 0).css('overflow', 'hidden');

      // Quando tutte le immagini sono caricate
      $(selector).imagesLoaded(function(){
        options.onSliderLoad = function(el){
          wrapper.attr('style','').hide().slideDown(1000, function(){
            wrapper.attr('style','');  
          });
        }
        $(selector).lightSlider(options);
      });
    }
  };
})(jQuery, Drupal);
