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
use Drupal\Core\Menu\MenuTreeParameters;

use Drupal\archive\Data\ArchiveData;
use Drupal\catalab\Data\Interpreter;


/**
 * Preprocess Node Page
 */
class Page extends NodeType{

  //protected $entityTypeManager;
  //protected $dateFormatter;
  
  // Current Language Id
  private $cLangId = false;

  // Pagination object
  private $pagination = false;

  protected $entityTypeManager;
  protected $menuLinkManager;

  function __construct(array &$vars, $hook, array $info) {
    parent::__construct($vars, $hook, $info);

    // This is a tricks to use services here
    $this->entityTypeManager = \Drupal::entityTypeManager();
    // Try to use also \Drupal::service('id')
    $this->dateFormatter = \Drupal::service('date.formatter');
    $this->menuLinkManager = \Drupal::service('plugin.manager.menu.link');

    // Current Language ID
    $this->cLangId = \Drupal::languageManager()->getCurrentLanguage()->getId();
  }

  public function preprocess(){
    $node = $this->vars['node'];

    if ($this->vars['view_mode'] == 'full'){
      $this->preprocessFull();
    }
    if ($this->vars['view_mode'] == 'teaser'){
      $this->preprocessTeaser();
    }
  }

  private function preprocessFull(){
    $node = $this->vars['node'];

    $this->addTabMenu();
    $this->checkTitleOver();

    $this->vars['content']['webform'] = $this->addWebform();

    if ($node->id() == '1886'){
      $this->preprocessFullCatalag(); 
    }
  }

  private function preprocessFullCatalag(){
    unset($this->vars['content']['webform']);

    $this->vars['content']['top_webform'] = [
      '#prefix' => '<div class="form-in-card">',
      '#suffix' => '</div>',
      '#type' => 'webform',
      '#webform' => 'catalog',
      '#weight' => 10,
      //'#default_data' => [
      //  'info' => 'Pagina ' . $node->getTitle(),
      //],
    ];
  }

  private function preprocessTeaser(){
    $node = $this->vars['node'];

    if (!$node->get('field_file_ico')->isEmpty()){

      global $base_path;
      $path = $base_path . drupal_get_path('theme', 'falpi') . '/img/ico/';

      $ico = $node->get('field_file_ico')->getValue();
      $file_name = $ico[0]['value'];
      $this->vars['content']['ico'] = [
        '#prefix' => '<div class="page-teaser-icon">',
        '#suffix' => '</div>',
        '#markup' => '<img src="' . $path . $file_name . '" class="img-responsive"/>',
        '#weight' => -8,
      ];
    }
      
  }

  private function addTabMenu(){
    $node = $this->vars['node'];

    $tabbed = false;
    if (!$node->get('field_tabbed')->isEmpty()){
      $values = $node->get('field_tabbed')->getValue();
      if ($values[0]['value']){
        $tabbed = true;
      }
    }

    if (!$tabbed){
      return;
    }

    $etm = $this->entityTypeManager;
    $ns = $etm->getStorage('node');

    $nid = \Drupal::routeMatch()->getRawParameter('node');
    if ($nid) {
      $mlm = $this->menuLinkManager;
      $menuLinks = $mlm->loadLinksByRoute('entity.node.canonical', array('node' => $nid));

      foreach ($menuLinks as $key => $menuLink) {
        if ($menuLink->getParent() !== ''){
          $pid = $menuLink->getParent();
          $thisId = $key;
        }
      }
      // Esiste un genitore (visualizzo le tab)
      if (isset($pid)){
        $childIds = $mlm->getChildIds($pid);

        foreach ($childIds as $key => $id) {
          $mLink = $mlm->createInstance($id);
          $parent = $mLink->getParent();
          $url = $mLink->getUrlObject();

          // Controllo se il nodo può essere inserito in tab
          $in_tab = false;

          if ($parent == $pid){
            if ($mLink->getRouteName() == 'entity.node.canonical'){
              $parameters = $url->getRouteParameters();
              if (isset($parameters['node'])){
                $nid = $parameters['node'];
                $t_node = $ns->load($nid);
                if (!$t_node->get('field_tabbed')->isEmpty()){
                  $values = $t_node->get('field_tabbed')->getValue();
                  if ($values[0]['value']){
                    $in_tab = true; 
                  }
                }
              }
            }
          }

          if ($in_tab){
            if ($id == $thisId){
              $url->setOptions([
                'attributes' => [
                  'class' => 'active',
                ],
              ]);  
            }

            $link = new Link($mLink->getTitle(), $url);

            $links[$id] = [
              '#prefix' => '<div class="menu-tab__item">',
              '#suffix' => '</div>',
              'link' => $link->toRenderable(),
            ];  
          }
        }

        if (!empty($links)){
          $count = count($links);
          $this->vars['content']['tabs'] = [
            '#prefix' => '<div class="menu-tab menu-tab__' . $count .'">',
            '#suffix' => '</div>',
            'data' => $links,
          ];  
        }
      }
    }  
  }

  private function checkTitleOver(){
    $node = $this->vars['node'];

    //kint($node);
    $title = 'Missing?';
    if (!$node->get('title')->isEmpty()){
      $values = $node->get('title')->getValue();
      if (isset($values[0]['value'])){
        $title = $values[0]['value'];
      }
    }
    // Il title field_title_over sovrascrive il titolo normale
    if (!$node->get('field_title_over')->isEmpty()){
      $values = $node->get('field_title_over')->getValue();
      if (isset($values[0]['value'])){
        $title = $values[0]['value'];
      }
    }

    $this->vars['content']['title'] = array(
      '#prefix' => '<div class="title-over title-over-page"><h1>',
      '#suffix' => '</h1></div>',
      '#markup' => $title,
    );
  }

  private function addWebform(){
    $webform = [
      '#theme' => 'magic_form',
      '#subject' => [
        '#markup' => 'Scrivici a info@falpi.com o chiamaci al +39 015 738 77 77',
      ],
      '#webform' => $this->getWebForm(),
      '#id' => 'magic-form-product',
    ];
    return $webform;
  }

  private function getWebForm(){
    $node = $this->vars['node'];
    
    return [
      '#type' => 'webform',
      '#webform' => 'contact',
      '#default_data' => [
        'info' => 'Pagina ' . $node->getTitle(),
      ],
    ];
  }
}