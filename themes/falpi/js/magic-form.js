/**
 * Magic Form
 */

(function ($, Drupal) {
  Drupal.behaviors.magicForm = {
    attach: function (context, settings) {
      var self = this;

      $('.magic-form').once('magicForm').each(function(){
        var mf = $(this);

        var toggle = $('.magic-form__hidden', mf);
        toggle.hide().removeClass('magic-form__hidden');

        var textarea = $('.form-textarea', mf);

        mf.click(function(){
          if (!mf.hasClass('open')){
            mf.addClass('open');
            textarea.attr('rows', 5);
            toggle.slideDown();
            scrollTo('.magic-form__head');
          }
        });

        $('.scroll-to-magic-form').click(function(){
          if (!mf.hasClass('open')){
            mf.addClass('open');
            textarea.attr('rows', 5);
            toggle.slideDown();
          }
        });

      });
    },
  };
})(jQuery, Drupal);
