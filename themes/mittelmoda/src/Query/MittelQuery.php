<?php

/**
 * @file
 * Contains \Drupal\mittelmoda\Award\MittelQuery.
 */

namespace Drupal\mittelmoda\Query;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Query utili per Mittelmoda
 */
class MittelQuery{
  
  private $entityQuery;
  private $entityTypeManager;

  function __construct(){
    // This is a tricks to use services here
    $this->entityQuery = \Drupal::service('entity.query');
    $this->entityTypeManager = \Drupal::service('entity_type.manager');
  }

  /**
   * Recupera tutti i prodotti
   */
  public function getSponsors($tid = false){
    $query = $this->entityQuery
      ->get('node')
      ->condition('type', 'sponsor')
      ->sort('title','ASC');

    if ($tid){
      $query->condition('field_ref_prizes', $tid);  
    }

    $nids = $query->execute();
    return array_values($nids);
  }

  public function getOrderedSponsors($queue_name = 'sponsor'){
    $etm = $this->entityTypeManager;
    $queue = $etm->getStorage('entity_subqueue')->load($queue_name);

    $list = $queue->get('items')->getValue();
    foreach ($list as $key => $item) {
      $nid = $item['target_id'];
      $nids[$nid] = $nid;
    }

    return $nids;
  }
}