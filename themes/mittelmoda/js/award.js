/**
 * Award Countdown
 */

(function ($, Drupal) {
  Drupal.behaviors.award = {
    attach: function (context, settings) {
      // Using once() to apply the myCustomBehaviour effect when you want to do just run one function.
      // $('body', context).once('myCustomBehavior').addClass('well');
      var self = this;

      $('.mittelmoda-counter', context).once('award-countdown').each(function(){
        self.createCountDown();
        self.showCountDown();
      });
    },
    createCountDown: function(){
      var cts = $('.mittelmoda-counter');

      cts.each(function(){
        var countdown = $(this);
        
        var date = countdown.attr('data-date');

        var counter = $('.counter', countdown);

        counter.countdown(date, function(event) {
          var $this = $(this).html(
            event.strftime(
              ''
              + '<span class="chunk"><span class="number">%D</span><span class="points">:</span><span class="cd-label">days</span></span>'
              + '<span class="chunk"><span class="number">%H</span><span class="points">:</span><span class="cd-label">hours</span></span>'
              + '<span class="chunk"><span class="number">%M</span><span class="points">:</span><span class="cd-label">minutes</span></span>'
              + '<span class="chunk"><span class="number">%S</span><span class="cd-label">seconds</span></span>'));
        });
      });
    },
    showCountDown: function(){
      var countdown = $('.mittelmoda-counter');  
      countdown.hide().removeClass('hide');

      if (countdown.hasClass('mittelmoda-counter-default')){
        countdown.slideDown(1000, "easeInOutQuad", function(){});  
      } else {
        countdown.show();
      }
      
    }
  };
})(jQuery, Drupal);