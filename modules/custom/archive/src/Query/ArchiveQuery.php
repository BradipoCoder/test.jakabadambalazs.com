<?php
/**
 * @file
 * Contains \Drupal\archive\Controller\ArchiveQuery.
 */
 
namespace Drupal\archive\Query;
 
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\archive\Data\ArchiveData; 

/**
 * Controller routines for page example routes.
 */
class ArchiveQuery{
 
  protected $entityQuery;
  protected $archiveData;
  protected $entityTypeManager;

  public function __construct(QueryFactory $entityQuery) {
    $this->entityQuery = $entityQuery;
    $this->archiveData = New ArchiveData();
    $this->filterList = $this->archiveData->getFilterList();
    $this->entityTypeManager = \Drupal::entityTypeManager();
  }

  /**
   * Recupera tutti i prodotti
   */
  public function getProducts(){
    $query = $this->entityQuery
      ->get('node')
      ->condition('status', 1)
      ->condition('type', 'product')
      ->sort('title','ASC');

    $nids = $query->execute();
    return array_values($nids);
  }

  /**
   * Recupera tutti i prodotti
   */
  public function getTechs(){
    $query = $this->entityQuery
      ->get('node')
      ->condition('type', 'tech');

    $nids = $query->execute();
    return array_values($nids);
  }

  /**
   * Ritorna i prodotti che hanno una scheda tecnica relazionata del tipo corretto
   */
  public function getProductsByTypeTid($tid){
    $query = $this->entityQuery
      ->get('node')
      ->condition('status', 1)
      ->condition('type', 'product')
      ->accessCheck(FALSE)
      ->sort('title','ASC');

    if (is_array($tid)){
      $query->condition('field_ref_tech.entity.field_ref_type.target_id', $tid, 'IN');  
    } else {
      $query->condition('field_ref_tech.entity.field_ref_type.target_id', $tid, '=');  
    }

    $nids = $query->execute();
    return array_values($nids);
  }

  /**
   * Recupera le opzioni disponibili per un determinato vocabolario
   * In base ai risultati dei prodotti presenti
   * @param  [type] $nids [description]
   * @param  [type] $voc  [description]
   * @return [type]       [description]
   */
  public function getVidOptions($vid, $nids){
    $query = \Drupal::database()->select('taxonomy_index', 'ti');
    $query->fields('ti', ['nid', 'tid']);

    $query->join('taxonomy_term_data', 'ttd', 'ttd.tid = ti.tid');
    $query->join('taxonomy_term_field_data', 'ttfd', 'ttfd.tid = ti.tid');
    $query->addField('ttd', 'vid');
    $query->condition('ttd.vid', $vid);
    $query->condition('ti.nid', $nids, 'IN');

    $query
      ->orderBy('weight', 'ASC')
      ->orderBy('name', 'ASC');

    $result = $query->execute()->fetchAllAssoc('tid');
    
    return array_keys($result);
  }

  /**
   * Recupera le opzioni disponibili per un riferimento ad un nodo (nome)
   * In base ai risultati dei prodotti già presenti
   */
  public function getRefOptions($name, $nids){
    // Ho un elenco di nodi
    // Devo recuperare tutti i nid a cui sono relazionati nel campo field_ref_name
    
    $table = 'node__field_ref_' . $name;
    $query = \Drupal::database()->select($table, 'ref');


    $target_id = 'field_ref_family_target_id';
    $query->fields('ref', ['entity_id', $target_id]);

    $query->join('node_field_data', 'nfd', 'nfd.nid = ref.entity_id');
    $query->addField('nfd', 'title');

    $query->condition('ref.entity_id', $nids, 'IN');

    $query
      ->orderBy('title', 'ASC');
    $result = $query->execute()->fetchAllAssoc($target_id);

    return array_keys($result);
  }

  public function getNidSelection($nids, $filters, $split_word = false){
    $filterList = $this->filterList;

    $query = $this->entityQuery
      ->get('node')
      ->condition('status', 1)
      ->accessCheck(FALSE)
      ->condition('nid', $nids, 'IN');

    // Se la scheda tecnica non ha l'immagine, la escludo
    // Forse in questo modo vengono escluse anche le schede tecniche che non esistono più
    // Controllare se funziona
    $query->exists('field_ref_tech.entity.field_imgs');

    if ($filters){
      foreach ($filters as $name => $value) {
        
        if ($name == 'query'){
          if ($value !== 'false'){
            
            // Separo le parole nella stringa di ricerca
            $exp = explode(' ', $value);

            // Escludo le parole con due lettere
            foreach ($exp as $key => $word) {
              if (strlen($word) <= 2){
                unset($exp[$key]);
              }
            }

            // Se la ricerca deve essere fatta per ogni parola
            // e se c'è più di una parola nella stringa
            if ($split_word && (count($exp) > 1)){
              
              $and = $query->andConditionGroup();

              foreach ($exp as $k) {
                $or = $query->orConditionGroup();
                // Cerco nel titolo
                $or->condition('field_ref_tech.entity.title', $k, 'CONTAINS');
                // Cerco nel sottotitolo
                $or->condition('field_ref_tech.entity.field_subtitle.value', $k, 'CONTAINS');
                // Cerco nel codice prodotto
                // $or->condition('field_ref_tech.entity.field_code.value', $k, 'CONTAINS');
                // Cerco nella descrizione
                $or->condition('field_ref_tech.entity.body.value', $k, 'CONTAINS');

                // Cerco nei nomi delle tassonomie
                // $or->condition('field_ref_tech.entity.field_ref_cert.entity.name', $k, 'CONTAINS');
                // $or->condition('field_ref_system.entity.name', $k, 'CONTAINS');
                // $or->condition('field_ref_solution.entity.name', $k, 'CONTAINS');
                // $or->condition('field_ref_tech.entity.field_ref_type.entity.name', $k, 'CONTAINS');
                // $or->condition('field_ref_cat.entity.name', $k, 'CONTAINS');
                // $or->condition('field_ref_material.entity.name', $k, 'CONTAINS');

                $and->condition($or);
              }
              $query->condition($and);

            } else {

              $or = $query->orConditionGroup();
            
              // Cerco nel titolo
              $or->condition('field_ref_tech.entity.title', $value, 'CONTAINS');

              // Cerco nel codice prodotto
              $or->condition('field_ref_tech.entity.field_code.value', $value, 'CONTAINS');

              // Cerco nel sottotitolo
              $or->condition('field_ref_tech.entity.field_subtitle.value', $value, 'CONTAINS');

              // Cerco nella descrizione
              $or->condition('field_ref_tech.entity.body.value', $value, 'CONTAINS');

              // Cerco nei nomi delle tassonomie
              // $or->condition('field_ref_tech.entity.field_ref_cert.entity.name.value', $value, 'CONTAINS');
              // $or->condition('field_ref_tech.entity.field_ref_type.entity.name.value', $value, 'CONTAINS');
              
              // $or->condition('field_ref_system.entity.name.value', $value, 'CONTAINS');
              // $or->condition('field_ref_solution.entity.name.value', $value, 'CONTAINS');
              // $or->condition('field_ref_cat.entity.name.value', $value, 'CONTAINS');

              $query->condition($or);
            }
          }
        } else {
          if ($value !== 'false'){
            $values = explode(',', $value);
            
            // Tassonomie
            if ($filterList[$name]['entity'] == 'taxonomy_term'){
              // Se il filtro è tra quelli in elenco ed è relativo alle schede tecniche
              if (isset($filterList[$name]['bundle']) && $filterList[$name]['bundle'] == 'tech'){
                $token = 'field_ref_tech.entity.field_ref_' . $name . '.target_id';
              } else{
                $token = 'field_ref_' . $name . '.target_id';
              }

              // Inclusivo, se il nodo ha un valore o l'altro
              // $query->condition($token, $values, 'IN');

              // Esclusivo, se il nodo ha tutti e due i valori
              foreach ($values as $value) {
                $and = $query->andConditionGroup();
                $and->condition($token, $value);
                $query->condition($and);
              }
            }

            // Node reference
            if ($filterList[$name]['entity'] == 'node'){
              $token = 'field_ref_' . $name . '.target_id';
              $and = $query->andConditionGroup();
              $and->condition($token, $value);
              $query->condition($and);
            }
            
          }  
        } 
      }
    }

    $query->sort('title','ASC');
    
    $result =$query->execute();
    $nids = array_values($result);

    // Riordino la query in base all'ordinamento dato da backend
    if (count($nids) > 1){
      $nids = $this->sortByQueue($nids);  
    }

    return $nids;
  }

  /**
   * Query secondaria per ordinare i risultati in base all'ordine stabilito da back end
   */
  protected function sortByQueue($nids){
    // Carico la tabella dei nodi (con solo i nodi che risultano dalla query precedente)
    $query = \Drupal::database()->select('node_field_data', 'nodes');
    $query->addField('nodes', 'nid');
    $query->addField('nodes', 'title');
    $query->condition('nodes.nid', $nids, 'IN');

    // Left join con la tabella dell'ordine dei prodotti
    $query->leftJoin('entity_subqueue__items', 'queue', 'nodes.nid = queue.items_target_id');
    $query->addField('queue', 'delta');
    $query->addField('queue', 'items_target_id');
    
    // Ordino prima con l'ordine della coda
    // Dopo per titolo
    $query
      ->orderBy('delta', 'DESC')
      ->orderBy('title', 'ASC');

    $result = $query->execute()->fetchAllAssoc('nid');
    return array_keys($result);
  }

  /**
   * Query vecchia, ricercava se le parole erano tutte presenti in almeno uno dei campi
   */
  protected function oldQueryInverted(&$query, $exp){
    $title = $query->andConditionGroup();
    $sub = $query->andConditionGroup();
    $code = $query->andConditionGroup();
    $desc = $query->andConditionGroup();

    $cert = $query->andConditionGroup();
    $system = $query->andConditionGroup();
    $solution = $query->andConditionGroup();
    $type = $query->andConditionGroup();
    $material = $query->andConditionGroup();

    // Carrelli + EPD
    foreach ($exp as $k) {
      // Cerco nel titolo
      $title->condition('title', $k, 'CONTAINS');
      // Cerco nel sottotitolo
      $sub->condition('field_ref_tech.entity.field_subtitle.value', $k, 'CONTAINS');
      // Cerco nel codice prodotto
      $code->condition('field_ref_tech.entity.field_code.value', $k, 'CONTAINS');
      // Cerco nella descrizione
      $desc->condition('field_ref_tech.entity.body.value', $k, 'CONTAINS');

      // Cerco nei nomi delle tassonomie
      $cert->condition('field_ref_tech.entity.field_ref_cert.entity.name.value', $k, 'CONTAINS');
      //$system->condition('field_ref_system.entity.name.value', $k, 'CONTAINS');
      //$solution->condition('field_ref_solution.entity.name.value', $k, 'CONTAINS');
      //$type->condition('field_ref_tech.entity.field_ref_type.entity.name.value', $k, 'CONTAINS');
      //$material->condition('field_ref_material.entity.name.value', $k, 'CONTAINS');
    }

    $group = $query->orConditionGroup();
    $group->condition($title);
    $group->condition($code);
    $group->condition($desc);

    $group->condition($cert);
    //$group->condition($system);
    //$group->condition($solution);
    //$group->condition($type);
    //$group->condition($material);

    $query->condition($group);  
  }

  // Da qui in poi ci sono degli esempi

  protected function simpleQuery() {
    $query = $this->entityQuery->get('node')
      ->condition('status', 1);
    $nids = $query->execute();
    return array_values($nids);
  }
 
  public function basicQuery() {
    return [
      '#title' => 'Published Nodes',
      'content' => [
        '#theme' => 'item_list',
        '#items' => $this->simpleQuery()
      ]
    ];
  }
 
  protected function intermediateQuery() {
    $query = $this->entityQuery->get('node')
      ->condition('status', 1)
      ->condition('changed', REQUEST_TIME, '<')
      ->condition('title', 'ipsum lorem', 'CONTAINS')
      ->condition('field_tags.entity.name', 'test');
    $nids = $query->execute();
    return array_values($nids);
  }
 
  public function conditionalQuery() {
    return [
      '#title' => 'Published Nodes Called "ipsum lorem" That Have a Tag "test"',
      'content' => [
        '#theme' => 'item_list',
        '#items' => $this->intermediateQuery()
      ]
    ];
  }
 
  protected function advancedQuery() {
    $query = $this->entityQuery->get('node')
      ->condition('status', 1)
      ->condition('changed', REQUEST_TIME, '<');
    $group = $query->orConditionGroup()
      ->condition('title', 'ipsum lorem', 'CONTAINS')
      ->condition('field_tags.entity.name', 'test');
    $nids = $query->condition($group)->execute();
    return array_values($nids);
  }
 
  public function conditionalGroupQuery() {
    return [
      '#title' => 'Published Nodes That Are Called "ipsum lorem" Or Have a Tag "test"',
      'content' => [
        '#theme' => 'item_list',
        '#items' => $this->advancedQuery()
      ]
    ];
  }
 
}