<?php

namespace Drupal\legacy_redirect\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirects URLS from legacy Islandora sites.
 */
class RedirectController extends ControllerBase {

  /**
   * Drupal\Core\Http\RequestStack definition.
   *
   * @var \Drupal\Core\Http\RequestStack
   */
  protected $requestStack;

  /**
   * Drupal\Core\Messenger\MessengerInterface definition.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Drupal\legacy_redirect\Middleware\Redirect.
   *
   * @var Drupal\legacy_redirect\Middleware\
   */
  protected $legacyRedirect;

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'legacy_redirect.settings';

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->requestStack = $container->get('request_stack');
    $instance->messenger = $container->get('messenger');
    $instance->legacyRedirect = $container->get('http_middleware.legacy_redirect_redirect');
    return $instance;
  }

  /**
   * Filters and parses incoming 404s.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Unused Response object required by parent class.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function legacyRedirect() {
    $config = $this->config(static::SETTINGS);
    $pid_field = $config->get('pid_reference');
    $destination = $config->get('not_found') ? $config->get('not_found') : '/';
    $uri = $this->requestStack->getMainRequest()->getRequestUri();
    $message = $this->t("The page you were looking for: @uri does not exist on this site", ['@uri' => $uri]);

    if (strpos($uri, "islandora/object/") !== FALSE) {
      $parts = \explode("islandora/object/", $uri);
      if (count($parts) > 0) {
        $pid = $parts[1];
        $nodes = $this->entityTypeManager()
          ->getStorage('node')
          ->loadByProperties([$pid_field => $pid]);
        if ($nodes) {
          $node = \reset($nodes);
          $destination = "/node/{$node->id()}";
          $message = $config->get('redirect_message');
        }
      }
    }
    $this->messenger->addMessage($message);
    $response = new RedirectResponse($destination, 301);
    $this->legacyRedirect->setRedirectResponse($response);
    return new Response(
      'Redirect complete',
      Response::HTTP_OK
    );
  }

}
