<?php

namespace Drupal\falpi\Plugin\Preprocess\NodeType;

use Drupal\falpi\Common\Pagination;
use Drupal\falpi\Common\Query;

use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\Element;
use Drupal\file\Entity\File;
use Drupal\views\Views;

use Drupal\archive\Data\ArchiveData;

/**
 * Preprocess Node Product
 */
class Family extends NodeType{

  //protected $entityTypeManager;
  //protected $dateFormatter;
  
  // Current Language Id
  private $cLangId = false;

  // Pagination object
  private $pagination = false;

  protected $entityTypeManager;

  function __construct(array &$vars, $hook, array $info) {
    parent::__construct($vars, $hook, $info);

    // This is a tricks to use services here
    $this->entityTypeManager = \Drupal::entityTypeManager();
    // Try to use also \Drupal::service('id')
    $this->dateFormatter = \Drupal::service('date.formatter');

    // Current Language ID
    $this->cLangId = \Drupal::languageManager()->getCurrentLanguage()->getId();;
  }

  public function preprocess(){
    if ($this->vars['view_mode'] == 'full'){
      $this->preprocessFull();
    }
  }

  private function preprocessFull(){
    $this->addPagination();
    $this->addWebform();

    $this->addWhere();
    $this->addPlaces();

    $this->addProducts();

    $this->vars['details'] = false;
    if (isset($this->vars['content']['where']) || isset($this->vars['content']['places'])){
      $this->vars['details'] = true;
    }
  }

  private function addWhere(){
    $node = $this->vars['node'];

    $et = $this->entityTypeManager;
    $taxonomy = $et->getStorage('taxonomy_term');

    // WHERE
    if (!$node->get('field_ref_where')->isEmpty()){
      $where = $node->get('field_ref_where')->getValue();
      foreach ($where as $key => $value) {
        $tids[$key] = $value['target_id'];
      }
      $terms = $taxonomy->loadMultiple($tids);

      $where = [
        '#prefix' => '<div class="wrapper-family-where"><p class="small">',
        '#suffix' => '</p></div>',
      ];
      foreach ($terms as $key => $term) {
        $names[] = $term->getName();
      }
      $where['#markup'] = implode(' – ', $names);
      $this->vars['content']['where'] = $where;
    }
  }

  private function addPlaces(){
    $node = $this->vars['node'];

    $et = $this->entityTypeManager;
    $taxonomy = $et->getStorage('taxonomy_term');

    // WHERE
    if (!$node->get('field_ref_places')->isEmpty()){
      $places = $node->get('field_ref_places')->getValue();
      foreach ($places as $key => $value) {
        $tids[$key] = $value['target_id'];
      }
      $terms = $taxonomy->loadMultiple($tids);

      $places = [
        '#prefix' => '<div class="wrapper-family-places"><p class="small">',
        '#suffix' => '</p></div>',
      ];
      foreach ($terms as $key => $term) {
        $names[] = $term->getName();
      }
      $places['#markup'] = implode(' – ', $names);
      $this->vars['content']['places'] = $places;
    }
  }

  private function addProducts(){
    $node = $this->vars['node'];

    $et = $this->entityTypeManager;
    $taxonomy = $et->getStorage('taxonomy_term');

    $AD = new ArchiveData();
    $typeList = $AD->getTypeList();

    $options = [
      'bundle' => 'product',
      'family_nid' => $node->ID(),
    ];

    unset($typeList['textiles-equipments']);

    foreach ($typeList as $type => $tid) {
      $options['tech_type'] = $tid;
      $fq = new Query($options);
      $nids = $fq->getNids();

      $term = $taxonomy->load($tid); 

      if ($nids){
        $results[$type]['nids'] = $nids;
        $results[$type]['name'] = $term->getName();
      }
    }

    // Se c'è più di una tipologia di prodotti attivo lo slider
    // altrimenti visualizzo tutti i risuta
    if (isset($results)){
      foreach ($results as $type => $value) {
        $nodes = $et->getStorage('node')->loadMultiple($value['nids']);
        $builds[$type]['nodes'] = $et->getViewBuilder('node')->viewMultiple($nodes, 'teaser'); 
        $builds[$type]['name'] = $results[$type]['name'];
      }

      if (count($results) > 1){
        // Slider
        foreach ($builds as $type => $build) {
          $id = 'family' . $type;
          $slider = [
            '#theme' => 'lightslider',
            '#content' => $build['nodes'],
            '#lsid' => $id,
          ];

          $slider['#attached']['drupalSettings']['lightslider'][$id] = $this->getSliderOptions();
          $data[$type] = [
            '#theme' => 'related_products',
            '#content' => $slider,
            '#title' => [
              '#markup' => $build['name'] . ' della famiglia ' . $node->getTitle(),
            ],
          ];  
        }
      } else {
        
        $keys = array_keys($builds);
        $first = $keys[0];

        // Display all products
        $data = [
          '#theme' => 'related_products',
          '#content' => $builds[$first]['nodes'],
          '#title' => [
            '#markup' => $builds[$first]['name'] . ' della famiglia ' . $node->getTitle(),
          ],
        ];
      }

      $this->vars['content']['products'] = $data;
    }
  }

  private function getSliderOptions(){
    $sliderOptions = array(
      'item' =>  4,
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
      'prevHtml' => '<i class="material-icons">keyboard_arrow_left</i>',
      'nextHtml' => '<i class="material-icons">keyboard_arrow_right</i>',
      'responsive' => array(
        array(
          'breakpoint' => 1192,
          'settings' => array(
            'item' => 3,
            'slideMove' => 3,
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

    return $sliderOptions;
  }

  private function addWebform(){
    $node = $this->vars['node'];

    $description = $node->getTitle();

    $this->vars['content']['webform'] = [
      '#theme' => 'magic_form',
      '#subject' => [
        '#markup' => 'su ' . $description,
      ],
      '#webform' => $this->getWebForm(),
      '#id' => 'magic-form-family',
    ];
  }

  private function getWebForm(){
    $node = $this->vars['node'];

    return [
      '#type' => 'webform',
      '#webform' => 'contact',
      '#default_data' => [
        'info' => 'Scheda prodotto: ' . $node->getTitle(),
      ],
    ];
  }

  private function addPagination(){
    $node = $this->vars['node'];
    $options = [
      'bundle' => 'family',
    ];
    $this->pagination = new Pagination($node->id(), $options);
    $data = $this->pagination->getData();
    if ($data){
      $prev = $data['prev'];
      $next = $data['next'];

      $build = [
        '#theme' => 'pagination_arrow',
        '#prev_id' => $prev['nid'],
        '#prev_url' => $prev['url']->toString(),
        '#prev_title' => $prev['node']->getTitle(),
        '#next_id' => $next['nid'],
        '#next_url' => $next['url']->toString(),
        '#next_title' => $next['node']->getTitle(),
      ];

      $this->vars['content']['pagination'] = $build;
    }
  }
}