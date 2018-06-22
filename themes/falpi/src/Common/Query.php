<?php

namespace Drupal\falpi\Common;

/**
 * Query
 */
class Query{

  private $entityQuery;
  private $entityTypeManager;

  private $nids = false;
  private $options;

  function __construct($options = []){
    $this->entityQuery = \Drupal::service('entity.query');
    $this->entityTypeManager = \Drupal::service('entity_type.manager');
    $this->options = $options;

    $this->queryNodes();
  }

  private function queryNodes(){
    $opt = $this->options;

    $query = $this->entityQuery
      ->get('node')
      ->condition('status', 1)
      ->accessCheck(FALSE)
      ->sort('title' , 'ASC');

    if (isset($opt['bundle'])){
      $query->condition('type', $opt['bundle']);
    }

    if (isset($opt['family_nid'])){
      $query->condition('field_ref_family', $opt['family_nid'], '=');
    }

    if (isset($opt['tech_type'])){
      if (is_array($opt['tech_type'])){
        $query->condition('field_ref_tech.entity.field_ref_type', $opt['tech_type'], 'IN');    
      } else {
        $query->condition('field_ref_tech.entity.field_ref_type', $opt['tech_type'], '=');    
      }
      
    }

    $this->nids = array_values($query->execute());
  }

  public function getNids(){
    return $this->nids;
  }
}