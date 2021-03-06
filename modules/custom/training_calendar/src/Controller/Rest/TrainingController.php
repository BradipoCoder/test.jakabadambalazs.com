<?php
/**
 * Created by Adam Jakab.
 * Date: 12/07/18
 * Time: 15.52
 */

namespace Drupal\training_calendar\Controller\Rest;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\ParameterBag;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\node\Entity\Node;


/**
 * Class TrainingController
 *
 * @package Drupal\training_calendar\Controller\Rest
 */
class TrainingController extends ControllerBase
{

  const TRAINING_FIELDS = [
    'id',
    'type',
    'title',
    'body',
    'status',
    'field_start_date',
    'field_total_distance',
    'field_activity_type',
    'created',
    'changed',
  ];

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
  public function __construct(EntityTypeManager $entity_type_manager,
                              RequestStack $requestStack) {
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
   * @return JsonResponse
   * @throws InvalidPluginDefinitionException
   * @throws MissingDataException
   */
  public function getTrainings() {
    $answer = [];

    $uid = \Drupal::currentUser()->id();
    $start_date = $this->rs->getCurrentRequest()->query->get("start_date");
    $end_date = $this->rs->getCurrentRequest()->query->get("end_date");
    //$timezone = $this->rs->getCurrentRequest()->query->get("timezone");

    $nodeStorage = $this->etm->getStorage("node");
    $queryInterface = $nodeStorage->getQuery();


    $nids = $queryInterface
      ->condition('type', ['training'])
      ->condition('uid', $uid)
      ->condition('status', 1)
      ->condition('field_start_date', $start_date, '>=')
      ->condition('field_start_date', $end_date, '<')
      ->execute();
    $nodes = $nodeStorage->loadMultiple($nids);

    /** @var Node $node */
    foreach ($nodes as $node) {
      $answer[] = $this->getSimpleObjectFromNode($node, self::TRAINING_FIELDS);
    }

    return new JsonResponse($answer);
  }

  /**
   * @return JsonResponse
   * @throws EntityStorageException
   * @throws MissingDataException
   */
  public function createTraining() {
    if (!\Drupal::currentUser()->hasPermission('tc_access')) {
      return $this->getUnauthorizedJsonResponse();
    }

    $answer = [];

    //Convert patch to post!?
    $post = $this->rs->getCurrentRequest()->request;
    $patchContent = $this->rs->getCurrentRequest()->getContent();
    if ($patchContent) {
      $patchData = json_decode($patchContent, TRUE);
      $post->replace($patchData);
    }
    //-------------------------

    /** @var Node $node */
    $node = Node::create(['type' => 'training']);
    $node = $this->mapPostDataOntoNode($node, $post);
    $node->save();

    $answer[] = $this->getSimpleObjectFromNode($node, self::TRAINING_FIELDS);

    return new JsonResponse($answer);
  }

  /**
   * @param int $id
   *
   * @return JsonResponse
   * @throws EntityStorageException
   * @throws InvalidPluginDefinitionException
   * @throws MissingDataException
   */
  public function storeTraining($id) {
    if (!\Drupal::currentUser()->hasPermission('tc_access')) {
      return $this->getUnauthorizedJsonResponse();
    }

    $nodeStorage = $this->etm->getStorage("node");

    /** @var Node $node */
    $node = $nodeStorage->load($id);

    if (!$node) {
      return new JsonResponse(
        [
          "message" => "Node ${id} not found!"
        ]
        , 404
      );
    }


    if($node->getOwnerId() != \Drupal::currentUser()->id())
    {
      return new JsonResponse(
        [
          "message" => "Node ${id} is not yours!"
        ]
        , 401
      );
    }

    //Convert patch to post!?
    $post = $this->rs->getCurrentRequest()->request;
    $patchContent = $this->rs->getCurrentRequest()->getContent();
    if ($patchContent) {
      $patchData = json_decode($patchContent, TRUE);
      $post->replace($patchData);
    }
    //-------------------------

    $node = $this->mapPostDataOntoNode($node, $post);
    $node->save();

    $answer[] = $this->getSimpleObjectFromNode($node, self::TRAINING_FIELDS);

    return new JsonResponse($answer);
  }

  /**
   * @param $id
   *
   * @return JsonResponse
   * @throws InvalidPluginDefinitionException
   * @throws EntityStorageException
   */
  public function deleteTraining($id)
  {
    if (!\Drupal::currentUser()->hasPermission('tc_access')) {
      return $this->getUnauthorizedJsonResponse();
    }

    $nodeStorage = $this->etm->getStorage("node");

    /** @var Node $node */
    $node = $nodeStorage->load($id);

    if (!$node) {
      return new JsonResponse(
        [
          "message" => "Node ${id} not found!"
        ]
        , 404
      );
    }


    if($node->getOwnerId() != \Drupal::currentUser()->id())
    {
      return new JsonResponse(
        [
          "message" => "Node ${id} is not yours!"
        ]
        , 401
      );
    }

    $node->delete();

    $answer = [];

    return new JsonResponse($answer, 204);
  }

  /**
   * @param Node $node
   * @param ParameterBag $post
   *
   * @return mixed
   */
  protected function mapPostDataOntoNode($node, $post) {
    $excludedFields = ["nid", "tid", "status", "sticky", "created", "changed"];
    $datakeys = $post->keys();
    foreach ($datakeys as $fieldName) {
      if ($node->hasField($fieldName)) {
        switch ($fieldName) {
          case "field_total_distance":
            $value = $post->get($fieldName, 1);
            $node->set($fieldName, $value);
            break;
          case "field_start_date":
            $value = $post->get($fieldName);
            $dt = new \DateTime($value);
            $dtv = $dt->format("Y-m-d\Th:i:s");//DATETIME_DATETIME_STORAGE_FORMAT OR DATETIME_DATE_STORAGE_FORMAT
            $node->set($fieldName, $dtv);
            break;
          default:
            if (!in_array($fieldName, $excludedFields)) {
              $value = $post->get($fieldName);
              $node->set($fieldName, $value);
            }
        }
      }
    }

    return $node;
  }

  /**
   * @param Node $node
   * @param array $fields
   *
   * @return \stdClass
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getSimpleObjectFromNode(Node $node, $fields) {
    $answer = new \stdClass();

    foreach ($fields as $fieldName) {
      $value = NULL;

      if ($node->hasField($fieldName)) {
        $field = $node->get($fieldName);
        switch ($fieldName) {
          case "type":
            $value = $field->first()->getString();
            break;
          case "body":
            if (isset($field->getValue()[0]["value"])) {
              $value = $field->getValue()[0]["value"];
            }
            break;
          default:
            $value = $field->first()->getValue();
            if (isset($value["value"])) {
              $value = $value["value"];
            } else if (isset($value["target_id"])) {
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
