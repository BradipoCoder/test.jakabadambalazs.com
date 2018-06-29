<?php
/**
 * Created by Adam Jakab.
 * Date: 28/06/18
 * Time: 9.01
 */

namespace Drupal\training_calendar\Oauth2;

use Drupal\simple_oauth\Repositories\UserRepository;
use Symfony\Component\HttpFoundation\RequestStack;

use Drupal\simple_oauth\Plugin\Oauth2GrantManager;
use Drupal\simple_oauth\Plugin\Oauth2GrantManagerInterface;

use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Psr\Http\Message\ResponseInterface;

use \League\OAuth2\Server\Grant\PasswordGrant;
use \Drupal\simple_oauth\Repositories\RefreshTokenRepository;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\TempStore\PrivateTempStore;

/**
 * Class TokenManager
 *
 * @package Drupal\training_calendar\Oauth2
 */
class TokenManager {
  /** @var RequestStack */
  protected $requestStack;

  /** @var  Oauth2GrantManagerInterface */
  protected $grantManager;

  /** @var UserRepository */
  protected $userRepository;

  /** @var RefreshTokenRepository */
  protected $refreshTokenRepository;

  /** @var ConfigFactoryInterface */
  protected $configFactory;

  /** @var PrivateTempStoreFactory */
  protected $tempStoreFactory;

  /**
   * TokenManager constructor.
   *
   * @param RequestStack $requestStack
   * @param Oauth2GrantManager $grantManager
   * @param UserRepository $userRepository
   * @param RefreshTokenRepository $refreshTokenRepository
   * @param ConfigFactoryInterface $configFactory
   * @param PrivateTempStoreFactory $tempStoreFactory
   */
  public function __construct(RequestStack $requestStack,
                              Oauth2GrantManager $grantManager,
                              UserRepository $userRepository,
                              RefreshTokenRepository $refreshTokenRepository,
                              ConfigFactoryInterface $configFactory,
                              PrivateTempStoreFactory $tempStoreFactory) {
    $this->requestStack = $requestStack;
    $this->grantManager = $grantManager;
    $this->userRepository = $userRepository;
    $this->refreshTokenRepository = $refreshTokenRepository;
    $this->configFactory = $configFactory;
    $this->tempStoreFactory = $tempStoreFactory;
  }


  /**
   * Intercept credentials, obtain tokens through auth2 authentication and
   * register them in session
   *
   * @todo: use DrupalAccountGrant [create it] instead of PasswordGrant!
   *
   * @see http://oauth2.thephpleague.com/authorization-server/resource-owner-password-credentials-grant/
   *
   * @throws \Exception
   */
  public function authenticate() {
    //Oauth2 App defined in /admin/config/services/consumer
    $client_id = "6578f259-aca8-4a41-87fd-4992753f574c";
    $client_secret = "TrainingCalendarApp2018";

    //Get expiration times from simple oauth
    $simple_oauth_settings = $this->configFactory->get('simple_oauth.settings');
    $accessTokenExpiration = new \DateInterval(sprintf('PT%dS', $simple_oauth_settings->get('access_token_expiration')));
    $refreshTokenExpiration = new \DateInterval(sprintf('PT%dS', $simple_oauth_settings->get('refresh_token_expiration')));

    /** @var  Request $request */
    $request = $this->requestStack->getCurrentRequest();
    $username = $request->get('name');
    $password = $request->get('pass');
    $grant_type = $request->get('grant_type', 'password');
    $scope = $request->get('scope', "");

    if (!($username && $password)) {
      throw new \Exception("Username or password is missing from request");
    }

    $authServer = $this->grantManager->getAuthorizationServer($grant_type);


    // Password Grant
    $grant = new PasswordGrant($this->userRepository, $this->refreshTokenRepository);
    $grant->setRefreshTokenTTL($refreshTokenExpiration);

    //Enable Grant on Auth Server
    $authServer->enableGrantType($grant, $accessTokenExpiration);

    //ServerRequest compatible with authServer
    $serverRequest = ServerRequest::fromGlobals();
    $serverRequest = $serverRequest->withParsedBody(
      [
        "grant_type" => $grant_type,
        "client_id" => $client_id,
        "client_secret" => $client_secret,
        "scope" => $scope,
        "username" => $username,
        "password" => $password
      ]
    );


    $authServerResponse = $authServer->respondToAccessTokenRequest($serverRequest, new GuzzleResponse());
    $authServerResponse->getBody()->rewind();
    $tokenData = $authServerResponse->getBody()->getContents();
    $authServerResponse->getBody()->close();


    if ($tokenData) {
      $tokenData = json_decode($tokenData);

      /** @var PrivateTempStore $privateSessionStorage */
      $privateSessionStorage = $this->tempStoreFactory->get("training_calendar");
      $privateSessionStorage->set("oauth_token_data", $tokenData);
    }

    /*
    dpm(
      [
        "grant_type" => $grant_type,
        "intercepted_credentials" => [$username, $password],
        "TOKENDATA" => $tokenData
      ]
    );
    */
  }
}