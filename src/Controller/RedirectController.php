<?php

namespace Drupal\legacy_redirect\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class RedirectController.
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
    return $instance;
  }

  /**
   * @return void
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function legacyRedirect() {
    $config = $this->config(static::SETTINGS);
    $pid_field = $config->get('pid_reference');
    $destination = $config->get('not_found') ? $config->get('not_found') : '/';
    $uri = $this->requestStack->getMasterRequest()->getRequestUri();
    $message = $this->t("The page you are looking for - $uri - does not exist on this site");
    $status = 404;

    if (strpos($uri, "islandora/object/") !== false) {
      $parts = \explode("islandora/object/", $uri);
      if (count($parts) > 0) {
        $pid = $parts[1];
        $nodes = \Drupal::entityTypeManager()
          ->getStorage('node')
          ->loadByProperties([$pid_field => $pid]);
        if ($nodes) {
          $node = \reset($nodes);
          $destination = "/node/{$node->id()}";
          $message = $config->get('redirect_message');
          $status = 302;
        }
      }
    }
    $this->messenger->addMessage($message);
    $response = new RedirectResponse($destination, $status);
    \Drupal::service('http_middleware.legacy_redirect_redirect')->setRedirectResponse($response);
    return;
  }
}
