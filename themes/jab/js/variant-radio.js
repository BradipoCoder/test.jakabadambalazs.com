/**
 * Falpi Variant Radio
 */

(function ($, Drupal) {
  Drupal.behaviors.variantRadio = {
    attach: function (context, settings) {
      var self = this;
      self.armRadio(context, self);
      self.armThumbs(context, self); 
    },
    thumbs: false, // Esistono le thumbnail nella pagina
    armRadio: function(context, self){
      $('#variant-radio', context).once('variantRadio').each(function(){
        var variant = $(this);
        $('.a-variant-radio', variant).click(function(){
          var clicked = $(this);
          var mcode = clicked.attr('data-mcode');

          // Change radio status
          self.changeRadio(context, self, mcode);

          // Update Thumbs
          if (self.thumbs){
            self.changeImage(context, self, mcode);
          }
        });
      });
    },
    armThumbs: function(context, self){
      $('#thumbs', context).once('thumbsVariant').each(function(){

        var tbs = $('#thumbs');

        if (tbs.lenght !== 0){
          // Salvo la variabile thumbs
          self.thumbs = true;
          // Armo i click
          $('.thumb--item', context).click(function(){
            var code = $(this).attr('data-mcode');
            self.changeImage(context, self, code);
            self.changeRadio(context, self, code);
          });

          $('.thumb--item', tbs).first().addClass('active');
        }
      });
    },
    changeImage: function(context, self, code){
      var wrapper = $('#product-main-image', context);
      var items = $('.cbox-item', wrapper);
      items.addClass('hide');

      var tbs = $('#thumbs');
      var thumbs = $('.thumb--item', tbs).removeClass('active');
      var this_thumb = $('#thumb-item-' + code);
      this_thumb.addClass('active');

      var active = $('#cbox-item-' + code, wrapper);
      active.removeClass('hide');

      //var img = $('#big-img-' + code + ' a', context);
      //var main = $('#product-main-image');
      //if (img.lenght !== 0){
      //  $('a.colorbox', main).remove();
      //  main.append(img.clone());
      //}
    },
    changeRadio: function(context, self, code){
      var radio = $('#variant-radio');
      var item = $('#variant-radio-' + code);
      $('.checked', radio).removeClass('checked');
      $('.active', radio).removeClass('active');
      item.addClass('active');
      $('.f-circle', item).addClass('checked');

      // Update Magic Form
      var code = item.attr('data-code');
      var title = item.attr('data-title');
      var description = code + ' ' + title;
      $('[data-drupal-selector="edit-code"]').attr('value', description);
      $('#subject-code').html(description);
    },
  };
})(jQuery, Drupal);
