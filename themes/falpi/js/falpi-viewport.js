/**
 * Falpi Viewport Arm
 */

(function ($, Drupal) {
  Drupal.behaviors.viewport = {
    attach: function (context, settings) {
      var self = this;
      $('body', context).once('wieport').each(function(){
        self.build(self);
      });

      // Possible options
      // classToAdd: 'visible', // Class to add to the elements when they are visible,
      // classToAddForFullView: 'full-visible', // Class to add when an item is completely visible in the viewport
      // classToRemove: 'invisible', // Class to remove before adding 'classToAdd' to the elements
      // removeClassAfterAnimation: false, // Remove added classes after animation has finished
      // offset: [100 OR 10%], // The offset of the elements (let them appear earlier or later). This can also be percentage based by adding a '%' at the end
      // invertBottomOffset: true, // Add the offset as a negative number to the element's bottom
      // repeat: false, // Add the possibility to remove the class if the elements are not visible
      // callbackFunction: function(elem, action){}, // Callback to do after a class was added to an element. Action will return "add" or "remove", depending if the class was added or removed
      // scrollHorizontal: false // Set to true if your website scrolls horizontal instead of vertical.
    },
    build: function(self){
      // Per gli schermi più piccoli l'animazione parte subito
      var ww = $( document ).width();
      var wait = false;
      if (ww > 768){
        wait = true;
      }

      if (wait){
        $('.vc').once('viewport').viewportChecker({
          classToAdd: 'vc-visible',
          classToAddForFullView: 'vc-full-visible',
          //repeat: true,
          callbackFunction: self.viewportAnimate,
          offset: '40%',
        });
      } else {
        self.viewportAnimate(false, false);
      }

      $('.animated-circle').click(function(e){
        var href = $(this).attr('data-href');
        console.debug('click');
        if (href !== undefined){
          window.location.href = href;
        }
      })
    },
    viewportAnimate: function (elem, action){
      // Animate number
      $('.animated-number').each(function(){
        var number = $(this).attr('data-number');
        if (number){
          $(this).once('viewport').animateNumber({
              number: number,
              easing: 'easeOutQuad',
            },
            2000
          );
        }
      });

      $('.animated-circle').once('viewport').circleProgress({
        value: 1,
        size: 190,
        reverse: true,
        fill: {
          gradient: ['#96C33F', '#EDEDED'],
        },
        lineCap: 'round',
        thickness: '2',
        animation: {
          duration: 2000,
          easing: "circleProgressEasing"
        },
        startAngle: -45,
      });
    }
  };
})(jQuery, Drupal);
