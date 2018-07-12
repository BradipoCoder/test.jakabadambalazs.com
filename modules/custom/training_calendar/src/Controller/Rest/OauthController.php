<?php
/**
 * Created by Adam Jakab.
 * Date: 12/07/18
 * Time: 15.54
 */

namespace Drupal\training_calendar\Controller\Rest;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\training_calendar\Oauth2\TokenManager;

/**
 * Class OauthController
 *
 * @package Drupal\training_calendar\Controller\Rest
 */
class OauthController extends ControllerBase
{

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