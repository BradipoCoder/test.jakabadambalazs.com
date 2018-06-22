<?php

namespace Drupal\jab\Plugin\Preprocess\NodeType;

/**
 * Class NodeType
 *
 * @package Drupal\jab\Plugin\Preprocess\NodeType
 */
class NodeType
{
  protected $vars;
  protected $hook;
  protected $info;

  /**
   * NodeType constructor.
   *
   * @param array $vars
   * @param $hook
   * @param array $info
   */
  function __construct(array &$vars, $hook, array $info)
  {
    $this->vars = &$vars;
    $this->hook = $hook;
    $this->info = $info;
  }
}