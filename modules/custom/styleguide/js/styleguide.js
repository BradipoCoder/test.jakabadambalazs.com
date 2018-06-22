
(function ($, Drupal) {
  Drupal.behaviors.styleguide = {
    attach: function (context, settings) {
      var self = this;
      $('body', context).once('lineTest').each(function(){
        
        $('.sg-color').each(function(){
          var color = $(this).css('border-top-color');
          color = self.rgb2hex(color);
          $(this).append('<br/><code>' + color + '</code>');
        });

        $('.sg-headings .sg-content').children().each(function(){
          self.addSize($(this));
        });

        $('.sg-copy .sg-content').children().each(function(){
          self.addSize($(this));
        });

      });
    },
    rgb2hex: function(rgb) {
      if (/^#[0-9A-F]{6}$/i.test(rgb)) return rgb;

      rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
      function hex(x) {
          return ("0" + parseInt(x).toString(16)).slice(-2);
      }
      return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
    },
    addSize: function(element){
      var size = element.css('font-size');
      var orig = element.html();
      element.html(orig + '<code> / ' + size + '</code>');  
    }
  };
})(jQuery, Drupal);