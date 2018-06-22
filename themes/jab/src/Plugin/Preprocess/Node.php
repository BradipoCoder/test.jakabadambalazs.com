<?php
/**
 * @file
 * Contains \Drupal\jab\Plugin\Preprocess\Node
 */

namespace Drupal\jab\Plugin\Preprocess;

use Drupal\bootstrap\Plugin\Preprocess\PreprocessBase;
use Drupal\bootstrap\Plugin\Preprocess\PreprocessInterface;
use Drupal\jab\Plugin\Preprocess\NodeType\Page;

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

    if ($vars['node']->getType() == 'page') {
      $node = new Page($vars, $hook, $info);
      $node->preprocess();
    }
  }
}
