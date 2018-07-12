<?php
/**
 * Created by Adam Jakab.
 * Date: 22/06/18
 * Time: 14.25
 */

namespace Drupal\training_calendar\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class TrainingCalendarController
 *
 * @package Drupal\training_calendar\Controller
 */
class TrainingCalendarController extends ControllerBase
{
  /**
   *  The main Training Calendar Page
   *
   * @return array|RedirectResponse
   */
  public function calendar() {
    if (!\Drupal::currentUser()->hasPermission('tc_access')) {
      return $this->redirect('user.page');
    }

    $data = [
      '#theme' => 'training_calendar_calendar',
      '#content' => [
        'component_title' => [
          '#markup' => t('Calendar'),
        ]
      ],
    ];

    return $data;
  }
}
