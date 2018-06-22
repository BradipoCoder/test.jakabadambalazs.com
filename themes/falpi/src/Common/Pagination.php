<?php

namespace Drupal\falpi\Common;

/**
 * Pagination
 */
class Pagination{

  // Id of the current node
  private $nid;

  private $options;

  private $entityQuery;
  private $entityTypeManager;

  private $nids = false;

  private $prev = false;
  private $next = false;

  function __construct($nid, $options = []){
    $this->nid = $nid;
    $this->options = $options;
    $this->entityQuery = \Drupal::service('entity.query');
    $this->entityTypeManager = \Drupal::service('entity_type.manager');
  }

  public function getData(){

    // Default options
    $default = [
      'type' => 'family',
      'bundle' => 'product',
    ];
    $this->options = array_merge($default, $this->options);

    // Get nids list
    if ($this->options['bundle'] == 'product'){
      if ($this->options['type'] == 'family' && isset($this->options['family_nid'])){
        $this->nids = $this->getNidListByFamily($this->options['family_nid']);
      }
    }

    if ($this->options['bundle'] == 'family'){
      $this->nids = $this->getNidsByBundle($this->options['bundle']); 
    }

    // Solo per liste maggiori di uno
    if ($this->nids && count($this->nids)){
      $this->setPrevAndNext();
    }

    $data = $this->getResult();
    return $data;
  }

  public function getNidList($remove_current = true){
    $list = $this->nids;
    // Rimuovo il nodo visualizzato
    if ($remove_current){
      if (in_array($this->nid, $list)){
        $current_k = array_search($this->nid, $this->nids);
        unset($list[$current_k]);  
      }  
    }
    return $list;  
  }

  private function getNidListByFamily($family_nid){
    $query = $this->entityQuery
      ->get('node')
      ->condition('status', 1)
      ->condition('type', 'product')
      ->sort('title' , 'ASC');

    $query->condition('field_ref_family', $family_nid, '=');

    $nids = $query->execute();
    return array_values($nids);
  }

  private function getNidsByBundle($bundle){
    $query = $this->entityQuery
      ->get('node')
      ->condition('status', 1)
      ->condition('type', $bundle)
      ->sort('title' , 'ASC');

    $nids = $query->execute();
    return array_values($nids);  
  }

  private function setPrevAndNext(){
    if (in_array($this->nid, $this->nids)){
      // Chiave del nodo corrente all'interno della lista di nodi
      $current_k = array_search($this->nid, $this->nids);
      $last_k = count($this->nids) - 1;

      $next_k = $current_k + 1;
      $prev_k = $current_k - 1;

      // Nodo successivo
      if (isset($this->nids[$next_k])){
        $this->next = $this->nids[$next_k];
      } else {
        $this->next = $this->nids[0];
      }

      // Nodo precedente
      if (isset($this->nids[$prev_k])){
        $this->prev = $this->nids[$prev_k];
      } else {
        $this->prev = $this->nids[$last_k];
      }

      return true;
    }
    return false;
  }

  private function getResult(){
    $data = false;

    if ($this->next && $this->prev){
      $nodeStorage = $this->entityTypeManager->getStorage('node');

      $node_prev = $nodeStorage->load($this->prev);
      $node_next = $nodeStorage->load($this->next);

      $data = [
        'prev' => [
          'nid' => $this->prev,
          'node' => $node_prev,
          'url' => $node_prev->toUrl(),
        ],
        'next' => [
          'nid' => $this->next,
          'node' => $node_next,
          'url' => $node_next->toUrl(),
        ]
      ];
    }

    return $data;
  }
}