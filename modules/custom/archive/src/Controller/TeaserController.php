<?php

namespace Drupal\archive\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Cookie;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;

use Drupal\archive\Query\ArchiveQuery;
use Drupal\archive\Data\ArchiveData;
use Drupal\archive\Data\LinkTeaserData;
use Drupal\archive\Data\LinkCount;


/**
 * Class ArchiveController.
 */
class TeaserController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;
  /**
   * Drupal\Core\Entity\Query\QueryFactory definition.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;
  protected $aq;
  protected $archiveData;
  protected $linkList;
  protected $cLangId;

  /**
   * Constructs a new ArchiveController object.
   */
  public function __construct(EntityTypeManager $entity_type_manager, QueryFactory $entity_query) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityQuery = $entity_query;

    $ad = new ArchiveData();
    $this->archiveData = $ad;
    $this->filter_list = $ad->getFilterList();
    $this->type_list = $ad->getTypeList();

    $ltd = new LinkTeaserData();
    $this->linkList = $ltd->getLinkList();

    // Current Language ID
    $this->cLangId = \Drupal::languageManager()->getCurrentLanguage()->getId();
  }


  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity.query')
    );
  }

  /**
   * Products
   */
  public function products() {
    $data = $this->buildPageProducts();
    return $data;
  }

  /**
   * System
   */
  public function systems() {
    $data = $this->buildPageSystems();
    return $data;
  }

  /**
   * Costruisco la pagina Prodotti
   */
  public function buildPageProducts(){
    
    // Link tutti i prodotti
    $content['more']['archive'] = $this->getMoreLink();
    $content['more']['catalab'] = $this->getCatalabLink();

    // Teaser pagine
    if (isset($this->linkList['products'])){
      $list = $this->linkList['products'];

      $TE = $this->entityTypeManager;
      $SN = $TE->getStorage('node');
      $WB = $TE->getViewBuilder('node');
      $nodes = $SN->loadMultiple($list);

      foreach ($list as $key => $nid) {
        $k = $key . '_title';
        $content[$k] = $WB->view($nodes[$nid], 'teaser');
      }
    }

    $keys = [
      'textile', 'tool', 'cart', 'eco',
    ];
    foreach ($keys as $key => $name) {
      if (isset($this->linkList[$name])){
        $list = $this->linkList[$name];
        $content[$name] = $this->createBlockList($list);
      }
    }

    $content['webform'] = $this->addWebform();

    $data = [
      '#theme' => 'archive_products',
      '#content' => $content,
    ];
    return $data;
  }

  /**
   * Costruisco la pagina Sistems
   */
  public function buildPageSystems(){
    
    // Link tutti i prodotti
    $content['more'] = $this->getMoreLink();

    // Teaser pagine
    if (isset($this->linkList['systems'])){
      $list = $this->linkList['systems'];
      $TE = $this->entityTypeManager;
      $SN = $TE->getStorage('node');
      $WB = $TE->getViewBuilder('node');
      $nodes = $SN->loadMultiple($list);

      foreach ($list as $key => $nid) {
        $k = $key . '_title';
        $content[$k] = $WB->view($nodes[$nid], 'teaser');
      }
    }

    $keys = [
      'sweeping', 'washing', 'glasses', 'hospital', 'hotel', 'informatic'
    ];

    foreach ($keys as $key => $name) {
      if (isset($this->linkList[$name])){
        $list = $this->linkList[$name];
        $content[$name] = $this->createBlockList($list);
      }
    }

    $content['webform'] = $this->addWebform();
    $data = [
      '#theme' => 'archive_systems',
      '#content' => $content,
    ];
    return $data;
  }

  private function createBlockList($list){
    $data['#theme'] = 'block_list';

    foreach ($list as $l => $link) {
      
      $href = '#';

      // Archive path
      if (isset($link['query'])){
        $url = Url::fromRoute('archive.archive_controller_archive');
        $lk = new LinkCount($link['query']);
        $url->setRouteParameters($link['query']);
        $href = $url->toString();
      }

      if (isset($link['nid'])){
        $url = Url::fromRoute('entity.node.canonical', ['node' => $link['nid']]);
        $href = $url->toString();
      }

      // Static path
      if (isset($link['path'])){
        $href = $link['path'];

        // Localized path
        if (isset($link['localized'][$this->cLangId])){
          $href = $link['localized'][$this->cLangId];  
        }
      }

      $data['#list'][$l] = [ 
        'href' => $href,
        'title' => $link['name'],
      ];

      if (isset($link['attributes'])){
        $data['#list'][$l]['attributes'] = $link['attributes'];
      }

      if (isset($link['query']) && isset($link['count']) && $link['count']){
        if (is_string($link['count'])){
          $data['#list'][$l]['count'] = $link['count'];
        } else {
          $data['#list'][$l]['count'] = $lk->getCount();      
        }
      }

      if (isset($link['small_circle'])){
        $data['#list'][$l]['green_circle'] = true;
      }
    }
    return $data;
  }

  private function getMoreLink(){
    // Link all'archivio con tutti i prodotti
    $url = Url::fromRoute('archive.archive_controller_archive');
    $url->setOptions([
      'attributes' => [
        'class' => [
          'btn', 'btn-default'
        ],
      ],
      'query' => [
        'reset' => true,
      ],
    ]);

    $link = Link::fromTextAndUrl('Tutti i prodotti', $url)->toRenderable();
    return $link;
  }

  private function getCatalabLink(){
    // Link all'archivio con tutti i prodotti
    $url = Url::fromRoute('catalab.archive');
    $url->setOptions([
      'attributes' => [
        'class' => [
          'a-arrow', 'small'
        ],
      ],
    ]);

    $link = Link::fromTextAndUrl('Catalab', $url)->toRenderable();
    return $link;
  }

  private function addWebform(){
    return [
      '#theme' => 'magic_form',
      '#subject' => [
        '#markup' => 'sui prodotti Falpi',
      ],
      '#webform' => $this->getWebForm(),
      '#id' => 'magic-form-products',
    ];
  }

  private function getWebForm(){
    return [
      '#type' => 'webform',
      '#webform' => 'contact',
      '#default_data' => [
        'info' => 'Pagina riepilogo prodotti',
      ],
    ];
  }


}
