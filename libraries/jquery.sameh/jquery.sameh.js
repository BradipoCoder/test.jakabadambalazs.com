// Options example
// jQuery(wrapper).sameh({
//   imagesLoaded: true,
//   itemsSelector: '.sameh',
//   minWidth: 1200,
//   debug: true,
// });

(function ( $ ) {
  $.fn.sameh = function(options) {
    if (this.length > 0){

      var wrapper = $(this);

      // Opzioni del plugin
      var settings = $.extend({
        // These are the defaults.
        imagesLoaded: false,      // Attendi che le immagini siano caricate per effettuare il ridimensionamento
        itemsSelector: '.sameh',  // Selettore degli elementi da ridimensionare
        minWidth: 768,            // Dimensione minima al di sotto della quale same-h non lavora (@screen-sm-min)
        debug: false,             // Attiva o disattiva la modalità debug
      }, options );

      // Override opzioni da markup
      if (!settings.imagesLoaded){
        if (wrapper.attr('data-images-loaded')){
          settings.imagesLoaded = true;
        }
      }

      if (wrapper.attr('data-items-selector')){
        settings.itemsSelector = wrapper.attr('data-items-selector');
      }

      console.debug(settings.itemsSelector, 'items');

      if (wrapper.attr('data-min-width')){
        settings.minWidth = parseInt(wrapper.attr('data-min-width'));
      }

      if (wrapper.attr('data-debug')){
        settings.debug = true;
      }

      // Elementi da ridimensionare
      var items = wrapper.find(settings.itemsSelector);
      
      // Larghezza della finestra visibile
      // Qui c'è un problema relativo alle media query che non rispecchiano la reale dipensione della finestra
      // Il javascript è corretto..
      var ww = document.window_width;

      // Debug
      if (settings.debug){
        $('#sameh-size').remove();
        wrapper.append('<div id="sameh-size" class="col-xs-12"><code>Window width:' + document.window_width + 'px;</code></div>');  
      }

      // Azzero min height sotto il minWidth
      if (ww < settings.minWidth){
        items.css('min-height', ''); 

        // Debug helper
        if (settings.debug){ items.css('background', 'rgba(255, 152, 0, 0.4)');}
      } else {
        items.css('background', '');
      }

      // Se gli elementi sono più di uno
      if ((items.length > 0) && (ww >= settings.minWidth)){
        
        // Debug
        if (settings.debug){ items.css('background', 'rgba(149, 220, 45, 0.4)');}

        if (settings.imagesLoaded){
          wrapper.imagesLoaded(function(){
            set_min_height(items);     
          });
        } else {
          set_min_height(items);  
        }
      }

      function set_min_height(elements){
        elements.css('min-height', '');

        // Setto le variabili
        var max_h = 0;
        var h = 0;

        elements.each(function(){
          h = $(this).outerHeight(true);
          if (h > max_h){
            max_h = h;
          }
        });
        
        // +1 to fix rounded dimension
        max_h++; 

        // Applico l'altezza
        elements.css('min-height', max_h);
      }

    } else {
      return this;
    }
  };

  $().ready(function(){

    document.window_width = $(document).width();

    $('.wrapper-sameh').each(function(i){
      var cl = 'wrapper-sameh-' + i;
      
      $(this).addClass(cl);
      var selector = '.' + cl;

      // Lancio Sameh
      $(selector).sameh();

      // Ricarico la funzione dopo che il browser è stato ridimensionato
      $(window).resizeend({
        onDragEnd : function() {
          
          var tmpwidth = $(document).width();
          if (document.window_width !== tmpwidth){
            document.window_width = tmpwidth;

            // Lancio Sameh
            $(selector).sameh();  
          }
        },
        runOnStart : false
      });
    });
  });

}( jQuery ));
