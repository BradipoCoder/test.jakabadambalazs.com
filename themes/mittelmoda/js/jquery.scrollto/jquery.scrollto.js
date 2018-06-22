/**
 * Scroll To 
 */

jQuery().ready(function(){
  jQuery('.scroll-to').click(function(e){
    e.preventDefault();
    var anchor = jQuery(this).attr('href');
    
    var navbar = 0;
    if (jQuery('#navbar').hasClass('navbar-fixed-top')){
      navbar = jQuery('#navbar').outerHeight();  
    }

    var toolbar = 0;
    if (jQuery('#toolbar').length !== 0){
      toolbar = jQuery('#toolbar').outerHeight();  
    }
    
    if (jQuery(anchor).length == 1 ){
      jQuery('html, body').stop().animate({
        scrollTop: jQuery(anchor).offset().top - navbar - toolbar + 1
      }, 1000, 'easeOutQuad', function(){
        //alla fine dell'animazione
      });
    }
  });
});

function scrollTo(selector){
  var navbar = 0;
  if (jQuery('#navbar').hasClass('navbar-fixed-top')){
    navbar = jQuery('#navbar').outerHeight();  
  }

  var toolbar = 0;
  if (jQuery('#toolbar').length !== 0){
    toolbar = jQuery('#toolbar').outerHeight();  
  }

  if (jQuery(selector).length == 1 ){
    jQuery('html, body').stop().animate({
      scrollTop: jQuery(selector).offset().top - navbar - toolbar + 1
    }, 1000, 'easeOutQuad', function(){
      //alla fine dell'animazione
    });
  }
}