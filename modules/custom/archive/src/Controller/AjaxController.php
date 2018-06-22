<?php

namespace Drupal\archive\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryFactory;

use Drupal\archive\Query\ArchiveQuery;
use Drupal\archive\Data\ArchiveData;

/**
 * Class AjaxController.
 */
class AjaxController extends ControllerBase {

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

  protected $filter_list;
  protected $type_list;
  protected $archiveData;

  /**
   * Constructs a new AjaxController object.
   */
  public function __construct(EntityTypeManager $entity_type_manager, QueryFactory $entity_query) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityQuery = $entity_query;

    $ad = new ArchiveData();
    $this->archiveData = $ad;
    $this->filter_list = $ad->getFilterList();
    $this->type_list = $ad->getTypeList();
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
   * Ritorna i nodi renderizzati
   */
  public function getdata(Request $request) {
    $data = $this->createResult($request);
    return $data;
  }

  /**
   * Crea i risultati renderizzati rispetto ai nid richiesti
   */
  private function createResult(Request $request){
    $post = json_decode($request->getContent(), TRUE);
    
    $data['results'] = [
      '#prefix' => '<div id="wrapper-results">',
      '#suffix' => '</div>',  
    ];

    if ($post['nids']){
      $nids = $post['nids'];
      $products = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
      $vb = $this->entityTypeManager->getViewBuilder('node');
      $products = $vb->viewMultiple($products, 'teaser');
      
      $data['results']['nodes'] = $products;
    }

    return $data;
  }

  public function updateFilter(Request $request){
    $post = json_decode($request->getContent(), TRUE);

    $nids = explode(',', $post['nids']);
    $filters = $post['filters'];

    // Recupero la lista di filtri salvata in ArchiveData
    $dataFilters = $this->filter_list;

    // A seconda dei filtri impostati, devo filtrare tutti i nodi
    // Dopo di che devo ritornare i nuovi NID e le eventuali opzioni (attive o disattive)

    $aq = new ArchiveQuery($this->entityQuery);
    $results['nids'] = $aq->getNidSelection($nids, $filters);

    if (!$results['nids']){
      // Se non ci sono risultati, provo a ripetere la query con le parole splittate
      $results['nids'] = $aq->getNidSelection($nids, $filters, true);  
    }

    if ($results['nids']){
      foreach ($filters as $name => $value) {
        $nids = $results['nids'];

        if ($dataFilters[$name]['entity'] == 'taxonomy_term'){
          // Se la tassonomia si riferisce alla scheda tecnica,
          // sostituisco i nid tra cui cercare con quelli delle relative schede tecniche
          if (isset($dataFilters[$name]['bundle']) && $dataFilters[$name]['bundle'] == 'tech'){
            $nids = $this->archiveData->getTechNids($nids);
          }

          $results['filters'][$name] = $aq->getVidOptions($name, $nids);   
        }

        if ($dataFilters[$name]['entity'] == 'node'){
          $results['filters'][$name] = $aq->getRefOptions($name, $nids);   
        }
               
      }  
    }

    return new JsonResponse($results);
  }


}
