<?php

namespace Drupal\archive\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Cookie;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Render\Markup;

use Drupal\archive\Query\ArchiveQuery;
use Drupal\archive\Data\ArchiveData;


/**
 * Class ArchiveController.
 */
class ArchiveController extends ControllerBase {

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
  protected $aliasManager;
  protected $aq;
  protected $nids;
  protected $cLangId;
  
  protected $filter_list = [];
  protected $type_list = [];
  protected $custom_header = [];
  
  /**
   * Numero di prodotti per pagina
   * @var integer
   */
  protected $per_page = 18;

  /**
   * Termine che identifica la tipologia di prodotto
   * Può essere un array; può anche essere 'all'
   */
  protected $type = false; // @TODO clean up
  protected $type_machine = false;
  protected $type_url = false;

  protected $archiveData;

  protected $url;

  /**
   * Constructs a new ArchiveController object.
   */
  public function __construct(EntityTypeManager $entity_type_manager, QueryFactory $entity_query, $alias_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityQuery = $entity_query;
    $this->aliasManager = $alias_manager;

    $ad = new ArchiveData();
    $this->archiveData = $ad;
    $this->filter_list = $ad->getFilterList();
    $this->type_list = $ad->getTypeList();
    $this->custom_header = $ad->getCustomHeader();

    // Current Language ID
    $this->cLangId = \Drupal::languageManager()->getCurrentLanguage()->getId();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity.query'),
      $container->get('path.alias_manager')
    );
  }

  /**
   * Archive.
   */
  public function archive() {
    $this->setMachineType();

    $data = $this->buildPage();

    //$user = \Drupal::currentUser();
    //if ($user->id() == 1){
    //  // Debug query
    //  $nids = ['1623', '1671', '1776', '1528'];
    //  $filters = [
    //    'family' => 1407,
    //  ];
    //  $results = $this->aq->getNidSelection($nids, $filters, true);
    //}

    return $data;
  }

  /**
   * Costruisco la pagina e i suoi filtri
   */
  public function buildPage(){
    $products = [
      '#markup' => '<div class="col-md-12"><p>Nessun prodotto pubblicato per questa tipologia</p></div>',
    ];
    $filters = [];

    $this->aq = new ArchiveQuery($this->entityQuery);
    $this->nids = $this->aq->getProducts();

    if ($this->nids){
      $first_page_nids = array_slice($this->nids, 0, $this->per_page);
      $products = $this->entityTypeManager->getStorage('node')->loadMultiple($first_page_nids);
      $vb = $this->entityTypeManager->getViewBuilder('node');
      $products = $vb->viewMultiple($products, 'teaser', $this->cLangId);
      $filters = $this->createArchiveFilters();
    }

    // Arguments data
    $get_data = $_GET;
    if (empty($get_data)){
      $get_data = false;
    } else {
      $get_data = json_encode($get_data);
    }

    // Cookie filtri
    $cookie = false;
    if (isset($_COOKIE['archive-data'])){
      $cookie = true;
    }

    // Non renderizzo i prodotti se:
    // - ci sono argomenti nell'URL
    // - ci sono i filtri salvati nei cookie
    // verranno caricati in maniera asincrona tramite ajax
    if ($get_data || $cookie){
      $products = [];
    }

    // Creo un header differte per alcuni url custom
    $header = $this->createHeader();

    // Get current alias by path
    // $am = $this->aliasManager;
    // $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    // $alias = $am->getAliasByPath('/products/archive');
    // kint($alias);

    $data = [
      '#theme' => 'archive',
      '#content' => [
        'header' => $header,
        'products' => $products,
        'filter' => $filters,
        'per_page' => $this->per_page,
        'get_data' => $get_data,
        //'alias' => $alias,
      ],
      '#cache' => [
        'contexts' => ['url'],
      ],
    ];

    


    return $data;
  }

  private function createArchiveFilters(){
    $fl = $this->filter_list;
    $data = [];

    foreach ($fl as $name => $filter) {
      if ($filter['entity'] == 'taxonomy_term'){
        $nids = $this->nids;

        // Se la tassonomia si riferisce alla scheda tecnica,
        // sostituisco i nid tra cui cercare, con quelli delle relative schede tecniche
        if (isset($filter['bundle']) && $filter['bundle'] == 'tech'){
          $nids = $this->archiveData->getTechNids($nids);
        }
        $tids = $this->aq->getVidOptions($name, $nids);
        if ($tids){
          $storage = $this->entityTypeManager->getStorage('taxonomy_term');
          $terms = $storage->loadMultiple($tids);
          $data[$name] = $this->constructFilter($terms, $filter);
        }
      }

      if ($filter['entity'] == 'node'){
        $ref_nids = $this->aq->getRefOptions($name, $nids);  
        if ($ref_nids){
          $storage = $this->entityTypeManager->getStorage('node');
          $nodes = $storage->loadMultiple($ref_nids);
          $data[$name] = $this->constructFilter($nodes, $filter);
        }
      }
    }

    $nids = implode($this->nids, ',');
    $data['nids'] = [
      '#markup' => '<div id="archive-nids" class="hide" data-value="' . $nids . '"></div>',
    ];
    $data['filterd_nids'] = [
      '#markup' => '<div id="filtered-nids" class="hide" data-value="' . $nids . '"></div>',
    ];

    return $data;
  }

  /**
   * Crea il filtro con le possibili soluzioni
   */
  private function constructFilter($list, $filter){
    $data = [];

    $exclude = [];
    if (isset($filter['exclude'])){
      $exclude = $filter['exclude'];
    }

    $taxonomy = $this->entityTypeManager->getStorage('taxonomy_term');

    $items = [];
    if ($filter['entity'] == 'taxonomy_term'){
      foreach ($list as $tid => $term) {

        // Se esiste la traduzione del termine
        if ($term->getTranslationStatus($this->cLangId)){
          $term = $term->getTranslation($this->cLangId);  
        }

        if (!in_array($tid, $exclude)){
          $parent = $taxonomy->loadParents($tid);
          if ($parent){
            $parent = reset($parent);
            $p_tid = $parent->id();
            $items[$p_tid]['children'][$tid] = [
              'name' => $term->getName(),
              'value' => $term->id(),
              'filter' => $filter['name'],
              'level' => 'level-b',
            ];
          } else {
            $items[$tid]['name'] = $term->getName();
            $items[$tid]['value'] = $term->id();
            $items[$tid]['filter'] = $filter['name'];
            $items[$tid]['level'] = 'level-a'; 
          }
        }
      }

      // Se la lista è composta solo da un termine; e questo termine ha figli;
      // Sostituisco i figli di questo termine alla lista
      if (count($items) == 1){
        $tmp = reset($items);
        if (isset($tmp['children'])){
          $items = $tmp['children'];
        }
      }
    }

    if ($filter['entity'] == 'node'){
      foreach ($list as $nid => $node) {

        // Se esiste la traduzione del nodo
        if ($node->getTranslationStatus($this->cLangId)){
          $node = $node->getTranslation($this->cLangId);  
        }

        $items[$nid]['name'] = $node->getTitle();
        $items[$nid]['value'] = $node->id();
        $items[$nid]['filter'] = $filter['name'];
      }
    }

    if (empty($items)){
      return [];
    }

    // Filter class
    $class = [];
    if (isset($filter['visible_if'])){
      $class[] = 'visible-if';
      $class[] = 'visible-if-' . $filter['visible_if'];
    }

    $theme_name = 'archive_' . $filter['type'];
    $data = [
      '#theme' => $theme_name,
      '#items' => $items,
      '#name' => $filter['name'],
      '#title' => t($filter['title'], [], ['context' => 'page:archive']),
      '#classes' => implode($class, ' '),
    ];
    return $data;
  }

  /**
   * Setta il machine type se esiste il parametro
   * forse non serve più a nulla
   */
  private function setMachineType(){
    $this->type_machine = 'all';
    if (isset($_GET['type'])){
      $this->type_machine = $_GET['type'];
    }  
  }

  /**
   * @TODO: questa funzione non ha più tanto senso..
   * la tipologia è passata come parametro, per cui bisognerebbe nascondere i filtri con JS
   */
  private function limitFilterByType(){
    if ($this->type_machine == 'all'){
      unset($this->filter_list['cat']);
      unset($this->filter_list['material']);
    }
  }

  private function createHeader(){
    $filter = $_GET;

    $header = [
      'subtitle' => [
        '#markup' => t('Use the filters or the search bar to find the product you are looking for'),
      ],
    ];

    $header['class']['#markup'] = 'falpi-header-products';


    $filter = array(); // Disable custom header
    if (!empty($filter)){
      $type = $this->type_url;
      // Per ora escludo quando il type è un array
      if (!is_array($type)){
        $data['type'] = $type;
        $data['filters'] = $filter;

        // Faccio il match di type e filter
        $result = false;
        $list = $this->custom_header;
        foreach ($list as $nid => $item) {
          if ($data == $item){
            $result = $nid;
          }
        }

        if ($result){
          $NS = $this->entityTypeManager->getStorage('node');
          $node = $NS->load($result);

          if ($node){
            $title = $node->getTitle();  

            if (isset($data['filters']['system']) || isset($data['filters']['solution'])){
              $header['class']['#markup'] = 'falpi-header-systems';   
            }

            $header['title']['#markup'] = $title;

            if (!$node->get('field_subtitle')->isEmpty()){
              $values = $node->get('field_subtitle')->getValue();
              if ($values[0]['value'] !== ''){
                $header['subtitle']['#markup'] = $values[0]['value'];
              }
            }

            $url = Url::fromUri('entity:node/' . $result);
            $opt = [
              'attributes' => [
                'class' => [
                  'btn', 'btn-default', 'btn-archive-more'
                ],
              ],
            ];
            $url->setOptions($opt);
            $link = Link::fromTextAndUrl('Approfondisci', $url)->toRenderable();

            $header['more'] = $link;

          }
        }
      }
    }

    return $header;
  }

  private function fixCamCheckbox(){
    $nids = $this->aq->getTechs();
    $em = $this->entityTypeManager;
    $nodes = $em->getStorage('node')->loadMultiple($nids);
    foreach ($nodes as $key => $tech) {
      if (!$tech->get('field_cam')->isEmpty()){
        $values = $tech->get('field_cam')->getValue();
        if ($values[0]['value']){
          $nid = $tech->id();
          $cams[$nid] = 'cam';
          $tech->get('field_ref_cert')->appendItem(['target_id' => 144]);
          //$tech->save();
        }
      }
    }
    kint($cams);
  }

  /**
   * Funzione utilizzata per fare il bulk update della lingua di tutti i prodotti
   * Con questa funzione si settano come 'indifferente'
   * @return [type] [description]
   */
  private function bulkUpdateProductLanguage(){
    $this->aq = new ArchiveQuery($this->entityQuery);
    $all_products = $this->aq->getProducts();
    $products = $this->entityTypeManager->getStorage('node')->loadMultiple($all_products);
    $count = 0;
    foreach ($products as $key => $product) {
      $lang_code = $product->get('langcode')->value;
      if ($lang_code !== 'zxx'){
        $count++;
        $product->set('langcode', 'zxx');
        if ($count < 50){
          //$product->save();
        }
      }
    }
    if ($count){
      kint($count, 'Prodotti modificati');  
    } else {
      kint('Tutti a posto');
    }
    

    // Test su un prodotto
    // $test = $this->entityTypeManager->getStorage('node')->load(1538);
    // $lang_code = $test->get('langcode')->value;
    // if ($lang_code !== 'zxx'){
    //   $test->set('langcode', 'zxx');
    //   $test->save();
    // }
    // kint($test->get('langcode')->value,'update');
  }
}
