<?php

/**
 * @file
 * Contains \Drupal\mittelmoda\Slider\Slider.
 */

namespace Drupal\mittelmoda\Slider;

use Drupal\mittelmoda\Query\MittelQuery;

/**
 * Query utili per Mittelmoda
 */
class Slider{
  
  private $entityTypeManager = NULL;

  function __construct(){
    // This is a tricks to use services here
    $this->entityTypeManager = \Drupal::entityTypeManager(); 
  }

  public function getSponsorSlider($queue_name = 'sponsor'){
    $mq = new MittelQuery();
    $nids = $mq->getOrderedSponsors($queue_name);

    if ($nids){
      $et = $this->entityTypeManager;
      $related = $et->getStorage('node')->loadMultiple($nids);
      $build = $et->getViewBuilder('node')->viewMultiple($related, 'teaser');

      $slider = [
        '#theme' => 'lightslider',
        '#content' => $build,
        '#lsid' => 'related',
      ];

      $options = array(
        'item' =>  5,
        'mode' => 'slide',
        //'loop' => true,
        'slideMargin' => 0,
        'galleryMargin' => 0,
        'slideMove' => 4,
        'slideEndAnimation' => false,
        'auto' => false,
        'speed' => 2000,
        'pause' => 10000,
        //'controls' => false,
        'prevHtml' => '<i class="material-icons">arrow_backward</i>',
        'nextHtml' => '<i class="material-icons">arrow_forward</i>',
        'responsive' => array(
          array(
            'breakpoint' => 1192,
            'settings' => array(
              'item' => 4,
              'slideMove' => 4,
            ),
          ),
          array(
            'breakpoint' => 768,
            'settings' => array(
              'item' => 2,
              'slideMove' => 2,
              //'pager' => false,
            ),
          ),
        ),
      );

      // Unfortunatily is not working in preprocess function
      $slider['#attached']['drupalSettings']['lightslider']['related'] = $options;

      return $slider;
    }
  } 
  
}