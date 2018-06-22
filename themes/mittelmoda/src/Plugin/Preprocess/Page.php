<?php

/**
 * @file
 * Contains \Drupal\mittelmoda\Plugin\Preprocess\Page.
 */

namespace Drupal\mittelmoda\Plugin\Preprocess;

use Drupal\bootstrap\Annotation\BootstrapPreprocess;
use Drupal\bootstrap\Utility\Element;
use Drupal\bootstrap\Utility\Variables;


use Drupal\mittelmoda\Award\AwardData;
use Drupal\mittelmoda\Query\MittelQuery;
use Drupal\mittelmoda\Slider\Slider;


/**
 * Pre-processes variables for the "page" theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("page")
 */
class Page extends \Drupal\bootstrap\Plugin\Preprocess\Page {

  private $entityTypeManager = NULL;
  

  function __construct(){
    // This is a tricks to use services here
    $this->entityTypeManager = \Drupal::entityTypeManager(); 
  }

  /**
   * {@inheritdoc}
   */
  public function preprocess(array &$vars, $hook, array $info) {

    $this->addLogo($vars);

    if ($vars['is_front']){
      $this->preprocessHome($vars);
      unset($vars['page']['content']['mittelmoda_content']);
    }

    $this->addCover($vars);

    $this->addInfoForm($vars);

    // If you are extending and overriding a preprocess method from the base
    // theme, it is imperative that you also call the parent (base theme) method
    // at some point in the process, typically after you have finished with your
    // preprocessing.
    parent::preprocess($vars, $hook, $info);
  }

  private function addLogo(&$vars){
    global $base_url;
    $src = $base_url . '/' . drupal_get_path('theme', 'mittelmoda') . '/img/logo-mittelmoda.png';

    $logo = [
      '#markup' => '<a href="/" title="Homepage"><img src="' . $src . '" class="header-logo img-responsive"/></a>',
    ];

    $vars['page']['logo'] = $logo;
  }

  private function addCover(&$vars){

    if (!isset($vars['node'])){
      return;
    }

    $route_name = \Drupal::routeMatch()->getRouteName();

    if ($route_name !== 'entity.node.canonical'){
      return;
    }

    $node = $vars['node'];

    $display = $this->entityTypeManager->getStorage('entity_view_display')->load('node.award.default');
    $vb = $this->entityTypeManager->getViewBuilder('node');

    $image = false;
    if ($node->getType() == 'award' || $node->getType() == 'page'){
      if (!$node->get('field_image')->isEmpty()){    
        $image = $vb->viewField($node->field_image, $display->getComponent('field_image'));
      }
    }

    if ($image){
      $vars['page']['cover'] = [
        '#prefix' => '<div class="row row-cover">',
        '#suffix' => '</div>',
      ];

      $vars['page']['cover']['image'] = $image;

      if ($node->getType() == 'award'){
        $aw = new AwardData($node);
        $dates = $aw->getDates();
        $next = false;
        if ($dates){
          foreach ($dates as $key => $date) {
            if ($date['status'] == 'coming'){
              $next = $key;
              break;
            }
          } 
        }
        
        if ($next){
          $field_name = 'field_date_' . $next;
          $date = $vb->viewField($node->$field_name, $display->getComponent($field_name));
          
          $content['details'] = $date;
          $content['date']['#markup'] = date('Y/m/d H:i:s', $dates[$next]['timestamp']);
          $counter = [
            '#theme' => 'mittelmoda_counter',
            '#content' => $content,
          ];

          $vars['page']['cover']['counter'] = [
            '#prefix' => '<div class="wrapper-container-counter">',
            '#suffix' => '</div>',
            'counter' => $counter,
          ];
        }  
      }
      

    }
  }

  private function preprocessHome(&$vars){
    
    // Load home page settings
    $ns = $this->entityTypeManager->getStorage('node');
    $vb = $this->entityTypeManager->getViewBuilder('node');
    $hp_settings = $ns->load(2);

    // Renderizzo la cover in modalità teaser
    if (!$hp_settings->get('field_ref_cover')->isEmpty()){
      $values = $hp_settings->get('field_ref_cover')->getValue();
      $c_nid = $values[0]['target_id'];
      $cover = $ns->load($c_nid);
      $content['node'] = $vb->view($cover, 'teaser');
      
      // Field display
      $display = $this->entityTypeManager->getStorage('entity_view_display')->load('node.cover.teaser');
      $content['image'] = $vb->viewField($cover->field_image, $display->getComponent('field_image'));
    }

    // Load award teaser
    $award = $ns->load(3);
    $content['award'] = $vb->view($award, 'teaser');

    $slider = new Slider();
    $content['sponsor'] = $slider->getSponsorSlider('sponsor');

    $vars['page']['front'] = [
      '#theme' => 'mittelmoda_front',
      '#content' => $content,
    ];

    $vars['container'] = 'container-fluid';
  }

  private function addInfoForm(&$vars){

    //kint($vars);

    $form = false;
    if ($vars['is_front']){
      $form = true;
    }

    if (isset($vars['node'])){
      $node = $vars['node'];
      if ($node->getType() == 'award'){
        $form = true;
      }

      if ($node->getType() == 'page'){
        $form = true;
      }
    }

    if ($form){
      $vars['page']['webform'] = [
        '#theme' => 'magic_form',
        '#webform' => $this->getWebForm(),
        '#id' => 'magic-form-page',
      ];  
    }
  }

  private function getWebForm(){
    return [
      '#type' => 'webform',
      '#webform' => 'info',
      '#default_data' => [
        //'info' => 'Scheda prodotto: ' . $node->getTitle(),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function preprocessVariables(Variables $variables) {
    // This method is almost identical to the one above, but it introduces the
    // Variables utility class in the base theme. This class has a plethora of
    // helpful methods to quickly modify common tasks when you're in a
    // preprocess function. It also acts like the normal $variables array when
    // you need it to in instances of accessing nested content or in loop
    // structures like foreach.
    //$value = isset($variables['element']['child']['#value']) ? $variables['element']['child']['//#value'] : FALSE;
    //if (_some_module_condition($value)) {
    //  $variables->addClass(['my-theme-class', 'another-theme-class'])->removeClass('page');
    //}
    parent::preprocessVariables($variables);
  }

  /**
   * {@inheritdoc}
   */
  protected function preprocessElement(Element $element, Variables $variables) {
    // This method is only ever invoked if either $variables['element'] or
    // $variables['elements'] exists. These keys are usually only found in forms
    // or render arrays when there is a #type being used. This introduces the
    // Element utility class in the base theme. It too has a bucket-load of
    // features, specific to the unique characteristics of render arrays with
    // their "properties" (keys starting with #). This will quickly allow you to
    // access some of the nested element data and reduce the overhead required
    // for commonly used logic.
    // $value = $element->child->getProperty('value', FALSE);
    // if (_some_module_condition($value)) {
    //   $variables->addClass(['my-theme-class', 'another-theme-class'])->removeClass('page');
    // }
    parent::preprocessElement($element, $variables);
  }

}