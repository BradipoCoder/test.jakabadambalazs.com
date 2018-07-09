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
use \Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Entity\EntityTypeManager;
use \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\training_calendar\Oauth2\TokenManager;
use \Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Drupal\Core\Link;


class TrainingCalendarController extends ControllerBase {
  /** @var EntityTypeManager */
  protected $etm;

  /** @var RequestStack */
  protected $rs;

  /**
   * TrainingCalendarController constructor.
   *
   * @param EntityTypeManager $entity_type_manager
   * @param RequestStack $requestStack
   */
  public function __construct(EntityTypeManager $entity_type_manager, RequestStack $requestStack) {
    $this->etm = $entity_type_manager;
    $this->rs = $requestStack;
  }

  /**
   * Method for creating parameters for constructor injection
   *
   * @param ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    /** @var EntityTypeManager $etm */
    $etm = $container->get('entity_type.manager');

    /** @var RequestStack $rs */
    $rs = $container->get('request_stack');

    return new static($etm, $rs);
  }

  /**
   * path: /training_calendar
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

  /**
   * path: /training_calendar/rest/refresh_tokens
   *
   * @return JsonResponse
   */
  public function refreshTokens() {
    /** @var TokenManager $tokenManager */
    $tokenManager = \Drupal::service("training_calendar.oauth2.token_manager");
    try {
      $data = $tokenManager->getFreshTokens();
      $data->status = 200;
    } catch(\Exception $e) {
      $data = new \stdClass();
      $data->message = $e->getMessage();
      $data->status = 400;
    }

    return new JsonResponse($data, $data->status);
  }

  /**
   * path: training_calendar/rest/trainings
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getTrainings() {
    $answer = [];

    $start_date = $this->rs->getCurrentRequest()->query->get("start_date");
    $end_date = $this->rs->getCurrentRequest()->query->get("end_date");
    //$timezone = $this->rs->getCurrentRequest()->query->get("timezone");

    $nodeStorage = $this->etm->getStorage("node");
    $queryInterface = $nodeStorage->getQuery();

    $nids = $queryInterface
      ->condition('type', ['training'])
      ->condition('field_start_date', $start_date, '>=')
      ->condition('field_start_date', $end_date, '<')
      ->execute();
    $nodes = $nodeStorage->loadMultiple($nids);

    $fields = [
      'id',
      'type',
      'title',
      'status',
      'field_start_date',
      'field_total_distance',
      'field_activity_type',
    ];

    /** @var Node $node */
    foreach ($nodes as $node) {
      $answer[] = $this->getSimpleObjectFromNode($node, $fields);
    }

    return new JsonResponse($answer);
  }

  /**
   * @param \Drupal\node\Entity\Node $node
   * @param array $fields
   *
   * @return \stdClass
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getSimpleObjectFromNode(Node $node, $fields) {
    $answer = new \stdClass();

    foreach ($fields as $fieldName) {
      $value = null;

      if ($node->hasField($fieldName)) {
        $field = $node->get($fieldName);
        switch ($fieldName) {
          case "type":
            $value = $field->first()->getString();
            break;
          default:
            $value = $field->first()->getValue();
            if(isset($value["value"]))
            {
              $value = $value["value"];
            } else if (isset($value["target_id"]))
            {
              $value = $value["target_id"];
            }
            break;
        }
      } else {
        if ($fieldName == "id") {
          $value = $node->id();
        }
      }

      $answer->$fieldName = $value;
    }

    return $answer;
  }


  /**
   * path: /training_calendar/rest/ping
   *
   * @return JsonResponse
   */
  public function ping() {
    if (!\Drupal::currentUser()->hasPermission('tc_access')) {
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
  protected function getUnauthorizedJsonResponse() {
    return new JsonResponse(
      [
        "message" => "unauthorized"
      ]
      , 403
      , [

      ]
    );
  }
}