<?php

namespace Drupal\falpi\Plugin\Preprocess\NodeType;

/**
  * 
  */
class NodeType
{
  protected $vars;
  protected $hook;
  protected $info;

  function __construct(array &$vars, $hook, array $info)
  {
    $this->vars = &$vars;
    $this->hook = $hook;
    $this->info = $info;
  }
}