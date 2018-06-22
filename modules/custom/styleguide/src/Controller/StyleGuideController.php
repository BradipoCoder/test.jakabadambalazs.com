<?php

namespace Drupal\styleguide\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class StyleGuideController.
 */
class StyleGuideController extends ControllerBase {

  /**
   * Styliguide.
   *
   * @return string
   *   Return Hello string.
   */
  public function styleguide() {

    $content['title']['#markup'] = 'Qualche vago ione tipo zolfo, bromo, sodio';

    $data = [
      '#theme' => 'styleguide',
      '#content' => $content,
    ];

    return $data;
  }

}
