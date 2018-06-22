/**
 * Magic Form
 */

(function ($, Drupal) {
  Drupal.behaviors.falpiHome = {
    attach: function (context, settings) {
      var self = this;

      $('#home-play', context).once('falpiHome').each(function(){
        self.build(self);
      });
    },
    build: function(self){
      $('#home-play').colorbox({
        href: 'https://player.vimeo.com/video/267806497',
        width: '90%',
        height: '90%',
        iframe: true
      });
    },
  };
})(jQuery, Drupal);
