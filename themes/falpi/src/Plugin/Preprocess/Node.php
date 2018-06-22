<?php
/**
 * @file
 * Contains \Drupal\falpi\Plugin\Preprocess\Node
 */

namespace Drupal\falpi\Plugin\Preprocess;

use Drupal\bootstrap\Plugin\Preprocess\PreprocessBase;
use Drupal\bootstrap\Plugin\Preprocess\PreprocessInterface;
use Drupal\falpi\Utils\Helper;
use Drupal\falpi\Plugin\Preprocess\NodeType\Tech;
use Drupal\falpi\Plugin\Preprocess\NodeType\Product;
use Drupal\falpi\Plugin\Preprocess\NodeType\Family;
use Drupal\falpi\Plugin\Preprocess\NodeType\Page;

/**
 * Pre-processes variables for the "node" theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("node")
 */
class Node extends PreprocessBase implements PreprocessInterface {

  /**
   * {@inheritdoc}
   */
  public function preprocess(array &$vars, $hook, array $info) {

    unset($vars['content']['title']['#printed']);

    if ($vars['node']->getType() == 'tech'){
      $Te = new Tech($vars, $hook, $info);
      $Te->preprocess();
    }

    if ($vars['node']->getType() == 'product'){
      $Pr = new Product($vars, $hook, $info);
      $Pr->preprocess();
    }

    if ($vars['node']->getType() == 'family'){
      $Fa = new Family($vars, $hook, $info);
      $Fa->preprocess();
    }

    if ($vars['node']->getType() == 'page'){
      $Pa = new Page($vars, $hook, $info);
      $Pa->preprocess();
    }
  }
}
