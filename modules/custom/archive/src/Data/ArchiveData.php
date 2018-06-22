<?php
/**
 * @file
 * Contains \Drupal\archive\Data\ArchiveData.
 */
 
namespace Drupal\archive\Data;
 
use Drupal\Core\Url;

/**
 * Controller routines for page example routes.
 */
class ArchiveData{
 
  protected $filter_list = [
    'type' => [
      'name' => 'type',
      'entity' => 'taxonomy_term',
      'bundle' => 'tech', // Riferimento alla scheda tecnica
      'title' => 'Product',
      'field' => 'field_ref_type',
      'type' => 'checkboxes',
    ],
    'cat' => [
      'name' => 'cat',
      'entity' => 'taxonomy_term',
      'title' => 'Type',
      'field' => 'field_ref_cat',
      'type' => 'checkboxes',
      'visible_if' => 'type',
    ],
    'family' => [
      'name' => 'family',
      'entity' => 'node',
      'title' => 'Product family',
      'field' => 'field_ref_family',
      'type' => 'checkboxes',
      'visible_if' => 'type',
    ],
    'cert' => [
      'name' => 'cert',
      'entity' => 'taxonomy_term',
      'bundle' => 'tech', // Riferimento alla scheda tecnica
      'title' => 'Certification',
      'field' => 'field_ref_cert',
      'type' => 'checkboxes',
      'exclude' => [5, 6, 8],
    ],
    'system' => [
      'name' => 'system',
      'entity' => 'taxonomy_term',
      'title' => 'Technique',
      'field' => 'field_ref_system',
      'type' => 'checkboxes',
    ],
    'solution' => [
      'name' => 'solution',
      'entity' => 'taxonomy_term',
      'title' => 'System',
      'field' => 'field_ref_solution',
      'type' => 'checkboxes',
    ],
    'material' => [
      'name' => 'material',
      'entity' => 'taxonomy_term',
      'title' => 'Material',
      'field' => 'field_ref_material',
      'type' => 'checkboxes',
      'visible_if' => 'type',
    ],
    //'where' => [
    //  'name' => 'where',
    //  'entity' => 'taxonomy_term',
    //  'title' => 'Ambito',
    //  'field' => 'field_ref_where',
    //  'type' => 'checkboxes',
    //  'visible_if' => 'type',
    //],
  ];

  /**
   * Match tra tipologia di prodotto e relativo termine
   * @var [type]
   */
  protected $type_list = [
    'all' => 'all',
    'carts' => 94,
    //'textiles-equipments' => [95, 96],
    'textiles' => 95,
    'equipments' => 96,
    'accessories' => 97,
  ];

  protected $custom_header = [
    '1387' => [ // Sweeping
      'type' => 'all',
      'filters' => array(
        'system' => 165,
      ),
    ],
    '1388' => [ // Washing
      'type' => 'all',
      'filters' => array(
        'system' => 160,
      ),
    ],
    '1389' => [ // Pulizia vetri
      'type' => 'all',
      'filters' => array(
        'system' => 163,
      ),
    ],
    '1391' => [ // Sistemi Hospital
      'type' => 'all',
      'filters' => array(
        'solution' => 155,
      ),  
    ],
    '1392' => [ // Carrelli hotel solight
      'type' => 'carts',
      'filters' => array(
        'solution' => 195,
      ),  
    ],
    '1394' => [ // Scopatura a frange
      'type' => 'all',
      'filters' => array(
        'system' => 171,
      ),  
    ],
    '1395' => [ // Scopatura a umido
      'type' => 'all',
      'filters' => array(
        'system' => 167,
      ),  
    ],
    '1396' => [ // Spolvero delle superfici
      'type' => 'all',
      'filters' => array(
        'system' => 173,
      ),  
    ],
    '1397' => [ // Lavaggio Mop ad acqua
      'type' => 'all',
      'filters' => array(
        'system' => 164,
      ),  
    ],
    '1398' => [ // Lavaggio a piatto tradizionale
      'type' => 'all',
      'filters' => array(
        'system' => 172,
      ),  
    ],
    '1399' => [ // Lavaggio a piatto rapid
      'type' => 'all',
      'filters' => array(
        'system' => 161,
      ),  
    ],
    '1400' => [ // Lavaggio Microrapid a frange preimpregnate
      'type' => 'all',
      'filters' => array(
        'system' => 162,
      ),  
    ],
    '1401' => [ // Linea light
      'type' => 'all',
      'filters' => array(
        'solution' => 184,
      ),  
    ],
  ];

  protected $entityTypeManager;
 
  public function __construct() {
    $this->entityTypeManager = \Drupal::entityTypeManager();
  }

  public function getTermFromTypeId($type_id){
    $type = array_search($type_id, $this->type_list);
    return $type;
  }

  public function createArchiveUrl($type_id = false){
    $url = false;
    if ($type_id && $this->getTermFromTypeId($type_id)){
      $type = $this->getTermFromTypeId($type_id);
      $url = Url::fromRoute('archive.archive_controller_archive');
      $url->setRouteParameters(['type' => $type_id]);
    } else {
      $url = Url::fromRoute('archive.archive_controller_archive');  
    }
    
    return $url;
  }

  public function getFilterList(){
    return $this->filter_list;
  }

  public function getTypeList(){
    return $this->type_list;
  }

  public function getCustomHeader(){
    return $this->custom_header;
  }

  /**
   * Recupera i nid delle schede tecniche collegate ai nodi prodotto
   * Utile per i filtri che richiedono dati delle schede tecniche
   * Se non trova schede tecniche, ritorna i nid originali
   */
  public function getTechNids($nids){
    $storage = $this->entityTypeManager->getStorage('node');
    $nodes = $storage->loadMultiple($nids);
    $new = [];
    foreach ($nodes as $key => $node) {
      if (!$node->get('field_ref_tech')->isEmpty()){
        $values = $node->get('field_ref_tech')->getValue();
        $new[] = $values[0]['target_id'];
      }
    }
    if (!empty($new)){
      return $new;
    }
    return $nids;
  }

  /**
   * Partendo dai nids del prodotto
   * ritorna un array con il match con le schede tecniche
   */
  public function getProductsTechNidsMatch($nids){
    $storage = $this->entityTypeManager->getStorage('node');
    $nodes = $storage->loadMultiple($nids);
    $data = [];
    foreach ($nodes as $nid => $node) {
      if (!$node->get('field_ref_tech')->isEmpty()){
        $values = $node->get('field_ref_tech')->getValue();
        $data[$nid]['tech_nid'] = $values[0]['target_id'];
      } else {
        $data[$nid]['tech_nid'] = false; 
      }
      $data[$nid]['product_nid'] = $nid;
    }
    return $data;
  }
}
