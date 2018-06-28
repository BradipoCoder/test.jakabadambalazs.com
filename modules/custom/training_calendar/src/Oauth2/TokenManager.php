<?php
/**
 * Created by Adam Jakab.
 * Date: 28/06/18
 * Time: 9.01
 */

namespace Drupal\training_calendar\Oauth2;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TokenManager
 *
 * @package Drupal\training_calendar\Oauth2
 */
class TokenManager {
  /** @var RequestStack */
  protected $requestStack;

  /**
   * TokenManager constructor.
   *
   * @param RequestStack $requestStack
   */
  public function __construct(RequestStack $requestStack) {
    $this->requestStack = $requestStack;
  }


  /**
   * Intercept credentials, obtain tokens through auth2 authentication and
   * register them in session
   *
   * @throws \Exception
   */
  public function authenticate() {
    $username = $this->requestStack->getCurrentRequest()->request->get('name');
    $password = $this->requestStack->getCurrentRequest()->request->get('pass');

    if (!($username && $password)) {
      throw new \Exception("Username or password is missing from request");
    }

    dpm(
      [
        "intercepted_credentials" => [$username, $password]
      ]
    );
  }
}