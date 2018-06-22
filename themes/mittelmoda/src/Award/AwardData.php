<?php

/**
 * @file
 * Contains \Drupal\mittelmoda\Award\AwardData.
 */

namespace Drupal\mittelmoda\Award;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Pre-processes variables for the "page" theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("page")
 */
class AwardData{
  
  private $entityTypeManager = NULL;
  private $dateFormatter = NULL;
  private $dates;
  
  private $node;

  function __construct($node){
    // This is a tricks to use services here
    $this->entityTypeManager = \Drupal::entityTypeManager();
    $this->dateFormatter = \Drupal::service('date.formatter');

    $this->node = $node;
    $this->prepareData();
  }

  public function getDates(){
    return $this->dates;
  }

  private function prepareData(){
    $node = $this->node;

    $dates = false;

    // Now
    $n = new \DateTime();
    $n->setTimezone(new \DateTimeZone('Europe/Rome'));
    $now['timestamp'] = $n->getTimestamp();
    $now['formatted'] = $n->format('Y-m-d\TH:i:s');

    // Field dates
    if (!$node->get('field_date_1')->isEmpty()){
      $values = $node->get('field_date_1')->getValue();
      $dates[1]['field'] = $values[0]['value'];
    }

    if (!$node->get('field_date_2')->isEmpty()){
      $values = $node->get('field_date_2')->getValue();
      $dates[2]['field'] = $values[0]['value'];
    }

    if (!$node->get('field_date_3')->isEmpty()){
      $values = $node->get('field_date_3')->getValue();
      $dates[3]['field'] = $values[0]['value'];
    }

    $df = $this->dateFormatter;

    if ($dates){
      foreach ($dates as $key => $value) {
        $field = $value['field'];

        // Drupal salva i dati in UTC | creo una classe DateTime, passando il corretto UTC;
        // Dopo posso cambiare TimeZone per renderizzare la data
        $d = \DateTime::createFromFormat('Y-m-d\TH:i:s', $field, new \DateTimeZone('UTC'));

        $timestamp = $d->getTimestamp();
        $dates[$key]['timestamp'] = $timestamp;

        $d->setTimezone(new \DateTimeZone('Europe/Rome'));
        $dates[$key]['clean'] = $d->format('Y-m-d H:i:s');

        $dates[$key]['status'] = 'passed';
        if ($timestamp > $now['timestamp']){
          $dates[$key]['status'] = 'coming';
        }
      }  
    }
    
    $this->dates = $dates;
  }
}