<?php
/**
 * @file
 * Contains \Drupal\archive\Data\LinkCount.
 */
 
namespace Drupal\archive\Data;

use Drupal\archive\Query\ArchiveQuery;

/**
 * Pass
 */
class LinkCount{
  
  private $filters = [];
  private $entityQuery;
  private $entityTypeManager;

  private $results = [];
  private $count = 0;

  /**
   * Constructs a new ArchiveController object.
   */
  public function __construct($filters){
    $this->filters = $filters;

    if (isset($filters['reset'])){
      unset($filters['reset']);
    }

    $this->entityQuery = \Drupal::service('entity.query');
    $this->entityTypeManager = \Drupal::service('entity_type.manager');

    $AQ = new ArchiveQuery($this->entityQuery);
    $nids = $AQ->getProducts();
    $this->results = $AQ->getNidSelection($nids, $filters); 
  }

  public function getCount($perVariant = true){
    // Se nella variabile viene passato il parametro true, calcolo anche le varianti
    if ($perVariant){
      $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($this->results);
      $tot = 0;
      foreach ($nodes as $key => $node) {
        $add = 1;

        $tech = false;
        if (!$node->get('field_ref_tech')->isEmpty()){
          $values = $node->get('field_ref_tech')->getValue();
          $nid = $values[0]['target_id'];

          $et = $this->entityTypeManager;
          $tech = $et->getStorage('node')->load($nid);
        }

        if ($tech){
          if (!$tech->get('field_ref_variant')->isEmpty()){
            $variants = $tech->get('field_ref_variant')->getValue();
            $add = count($variants);
          }  
        }

        $tot += $add;
      }
      return $tot;
    }

    return count($this->results);
  }
}
