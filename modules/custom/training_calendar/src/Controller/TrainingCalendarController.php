<?php
/**
 * Created by Adam Jakab.
 * Date: 22/06/18
 * Time: 14.25
 */

namespace Drupal\training_calendar\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Cookie;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;

use Drupal\archive\Query\ArchiveQuery;
use Drupal\archive\Data\ArchiveData;
use Drupal\archive\Data\LinkTeaserData;
use Drupal\archive\Data\LinkCount;

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
   * @return array
   */
  public function calendar() {
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