<?php
/**
 * Created by Adam Jakab.
 * Date: 22/06/18
 * Time: 14.25
 */

namespace Drupal\training_calendar\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Url;
use Drupal\Core\Link;


class TrainingCalendarController extends ControllerBase
{
  /** @var EntityTypeManager */
  protected $etm;

  /**
   * TrainingCalendarController constructor.
   *
   * @param EntityTypeManager $entity_type_manager
   */
  public function __construct(EntityTypeManager $entity_type_manager)
  {
    $this->etm = $entity_type_manager;

    // Current Language ID
    //$this->cLangId = \Drupal::languageManager()->getCurrentLanguage()->getId();
  }

  /**
   * Method for creating parameters for constructor injection
   *
   * @param ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container)
  {
    /** @var EntityTypeManager $etm */
    $etm = $container->get('entity_type.manager');

    return new static($etm);
  }

  /**
   * @return array|RedirectResponse
   */
  public function calendar()
  {
    if (!\Drupal::currentUser()->hasPermission('tc_access'))
    {
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

  /**
   * @return JsonResponse
   */
  public function ping()
  {
    if (!\Drupal::currentUser()->hasPermission('tc_access'))
    {
      return $this->getUnauthorizedJsonResponse();
    }

    $currentUser = \Drupal::currentUser();
    $now = new \DateTime('now', new \DateTimeZone($currentUser->getTimeZone()));

    $data = [
      "account_info" => [
        "account_name" => $currentUser->getAccountName(),
        "display_name" => $currentUser->getDisplayName(),
        "account_id" => $currentUser->getAccount()->id(),
      ],
      "system_info" => [
        "server_time" => $now
      ],
    ];

    return new JsonResponse($data);
  }

  /**
   * @return JsonResponse
   */
  protected function getUnauthorizedJsonResponse()
  {
    return new JsonResponse(
      [
        "message"=>"unauthorized"
      ]
      , 403
      , [

      ]
    );
  }
}