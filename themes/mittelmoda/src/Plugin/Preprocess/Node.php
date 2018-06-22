<?php
/**
 * @file
 * Contains \Drupal\mittelmoda\Plugin\Preprocess\Node
 */

namespace Drupal\mittelmoda\Plugin\Preprocess;

use Drupal\bootstrap\Plugin\Preprocess\PreprocessBase;
use Drupal\bootstrap\Plugin\Preprocess\PreprocessInterface;
use Drupal\Core\Render\Element;

// use Drupal\bootstrap\Annotation\BootstrapPreprocess;
// use Drupal\bootstrap\Utility\Variables;

use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\views\Views;
use Drupal\mittelmoda\Award\AwardData;
use Drupal\mittelmoda\Slider\Slider;

/**
 * Pre-processes variables for the "node" theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("node")
 */
class Node extends PreprocessBase implements PreprocessInterface {

  private $entityTypeManager;
  private $entityQuery;

  public function __construct(){
    $this->entityTypeManager = \Drupal::service('entity_type.manager');
    $this->entityQuery = \Drupal::service('entity.query');
  }

  /**
   * {@inheritdoc}
   */
  public function preprocess(array &$vars, $hook, array $info) {
    $vars['content']['title']['#printed'] = true;

    $node = $vars['node'];


    switch ($node->getType()) {
      case 'award':
        $this->preprocessAward($vars);
        break;
      
      case 'cover':
        $this->preprocessCover($vars);
        break;

      case 'webform':
        $this->preprocessWebform($vars);
        break;

      case 'designer':
        $this->preprocessDesigner($vars);
        break;

      case 'finalists':
        $this->preprocessFinalists($vars);
        break;

      case 'page':
        $this->preprocessNodePage($vars);
          break;

      default:
        # code...
        break;
    }
  }

  private function preprocessAward(&$vars){
    $node = $vars['node'];

    // Show counter and dates
    $ad = new AwardData($node);
    $dates = $ad->getDates();
    
    $display = $this->entityTypeManager->getStorage('entity_view_display')->load('node.award.default');
    $vb = $this->entityTypeManager->getViewBuilder('node');

    $first_coming = true;
    if ($dates){
      foreach ($dates as $key => $date) {
        // Field name
        $field_name = 'field_date_' . $key;
        $field = $vb->viewField($node->$field_name, $display->getComponent($field_name));

        if ($date['status'] == 'coming' && $first_coming){
          $first_coming = false;
          $next = $key;
          $cont['details'] = $field;
          $cont['date']['#markup'] = date('Y/m/d H:i:s', $date['timestamp']);
          $counter = [
            '#theme' => 'mittelmoda_counter',
            '#content' => $cont,
            '#class' => 'teaser',
          ];

          $box[$key]['counter'] = [
            '#prefix' => '<div class="wrapper-container-teaser-counter">',
            '#suffix' => '</div>',
            'counter' => $counter,
          ];
        } else {
          $box[$key] = [
            '#prefix' => '<div class="wrapper-container-teaser-static">',
            '#suffix' => '</div>',
            'field' => $field,
          ];

          if (!isset($next)){
            $box[$key]['#prefix'] = '<div class="wrapper-container-teaser-static passed">';
          }

        }
      }

      $vars['content']['box'] = $box;
    }

    // Form | Visibile solo nel primo step
    $vars['show_form'] = false;
    if ($next == 1){
      $form = $this->entityTypeManager->getStorage('node')->load(5);
      if ($form){
        $title = $form->getTitle();
        $vars['content']['form_title']['#markup'] = $title;
        $vars['show_form'] = true;
      }
    }

    // Solo per utenti amministratori
    //$user = \Drupal::currentUser();
    //$vars['show_new'] = false;
    //if ($user->id() == 1){
    //  //drupal_set_message('L\'anteprima della news in questa pagina è visibile solo agli amministratori');
    //  $vars['show_news'] = true;
    //}

    if ($vars['view_mode'] == 'teaser'){
      $this->preprocessAwardTeaser($vars);  
    }

    if ($vars['view_mode'] == 'full'){
      $this->preprocessAwardFull($vars);
    }
  }

  private function preprocessAwardTeaser(&$vars){
    $node = $vars['node'];

    // More link
    $url = $node->toUrl();
    $url->setOptions([
      'attributes' => [
        'class' => [
          'btn', 'btn-ghost', 'btn-with-arrow', 'btn-cutted'
        ],
      ],
    ]);
    $link = Link::fromTextAndUrl('Read more', $url)->toRenderable();
    $vars['content']['more'] = $link;
  }

  private function preprocessAwardFull(&$vars){
    $slider = new Slider();
    $vars['content']['sponsor'] = $slider->getSponsorSlider('sponsor_main');
  }

  private function preprocessCover(&$vars){
    $node = $vars['node'];
    if ($vars['view_mode'] == 'teaser'){
      if (!$node->get('field_link')->isEmpty()){
        $link = $node->get('field_link')->getValue();
        if (isset($link[0]['uri'])){
          $uri = $link[0]['uri'];
          $url = Url::fromUri($uri);
          $string = $url->toString();
          $vars['content']['url']['#markup'] = $string;
        }
      }
    }
  }

  private function preprocessWebform(&$vars){
    if ($vars['view_mode'] == 'full'){
      $slider = new Slider();
      $vars['content']['sponsor'] = $slider->getSponsorSlider('sponsor_main'); 
    }
  }

  private function preprocessDesigner(&$vars){
    if ($vars['view_mode'] == 'full'){
      $this->preprocessDesignerFull($vars);  
    }  
  }

  private function preprocessDesignerFull(&$vars){
    $node = $vars['node'];

    // Check if is in a node finalists
    $list = $this->getFinalistsReference($node);
    if ($list){
      $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($list);
      $n = 0;
      foreach ($nodes as $key => $page) {
        $link = $page->toLink();
        $vars['content']['finalists'][$key]['link'] = $link->toRenderable();
        if ($n !== 0){
          $vars['content']['finalists'][$key]['link']['#prefix'] = ' – ';  
        }
        $n++;
      }
      $vars['content']['finalists']['#prefix'] = '/ ';
    }

    $this->addLastEditionTeaser($vars);
  }

  private function preprocessFinalists(&$vars){
    $node = $vars['node'];
    if ($vars['view_mode'] == 'teaser'){

      // Recupero gli ultimi 4 finalisti
      $children = Element::children($vars['content']['field_ref_finalists'], true);
      $n = 1;
      foreach ($children as $key) {
        if ($n > 3){
          unset($vars['content']['field_ref_finalists'][$n]);
        }
        $n++;
      }
    }
  }

  private function preprocessNodePage(&$vars){
    $node = $vars['node'];

    if ($node->id() == 40){
      $this->preprocessNodePageMittelmoda($vars);
    }

    if ($node->id() == 41){
      $this->preprocessNodePageNews($vars);
    }
  }

  private function preprocessNodePageMittelmoda(&$vars){
    if ($vars['view_mode'] == 'full'){
      $slider = new Slider();
      $vars['content']['sponsor'] = $slider->getSponsorSlider('sponsor');  
    }
  }

  private function preprocessNodePageNews(&$vars){
    if ($vars['view_mode'] == 'full'){
      $view = Views::getView('news');
      if (is_object($view)) {
        $vars['content']['view'] = $view->buildRenderable('default');
      }
    }
  }

  private function getFinalistsReference($node){
    $nid = $node->id();

    $query = $this->entityQuery
      ->get('node')
      ->condition('status', 1)
      ->condition('type', 'finalists')
      ->condition('field_ref_finalists', $nid)
      ->sort('title','ASC');

    $nids = $query->execute();
    return array_values($nids);
  }

  private function addLastEditionTeaser(&$vars){
    $last_edition_nid = 29;
    $node = $this->entityTypeManager->getStorage('node')->load($last_edition_nid);
    if ($node){
      $build = $this->entityTypeManager->getViewBuilder('node')->view($node, 'teaser');
      $vars['content']['bottom_finalist'] = $build;
      //kint($vars['content']);
    }
  }
}
