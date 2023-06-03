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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->requestStack = $container->get('request_stack');
    $instance->messenger = $container->get('messenger');
    return $instance;
  }

  /**
   * Redirects calls from old site.
   */
  public function reklRedirect() {
    $uri = $this->requestStack->getMasterRequest()->getRequestUri();
    $path_parts = \explode('/', $uri);
    $destination = '/';
    $message = $this->t("The page you are looking for - $uri - does not exist on this site");

    if (substr($path_parts[1], 0, 4) == 'rekl') {
      $rekl = \str_replace('_', ':', $path_parts[1]);
      $destination = "/solr-search/content?search_api_fulltext=$rekl&sort_by=field_edtf_date_created&sort_order=ASC&items_per_page=10";
      $message = $this->t("You have arrived here using a URL from our old site. \nWe hope this will help you find what you are looking for.");
    }
    $this->messenger->addMessage($message);
    $response = new RedirectResponse($destination, 302);
    \Drupal::service('http_middleware.legacy_redirect_redirect')->setRedirectResponse($response);
    return;
  }

  /**
   * Redirects calls from old site.
   */
  public function legacyRedirect() {
    $uri = $this->requestStack->getMasterRequest()->getRequestUri();
    $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties(['field_hash' => 'YOUR_HASH']);
    $path_parts = \explode('/', $uri);
    $destination = '/';
    $message = $this->t("The page you are looking for - $uri - does not exist on this site");

    if (substr($path_parts[1], 0, 4) == 'rekl') {
      $rekl = \str_replace('_', ':', $path_parts[1]);
      $destination = "/solr-search/content?search_api_fulltext=$rekl&sort_by=field_edtf_date_created&sort_order=ASC&items_per_page=10";
      $message = $this->t("You have arrived here using a URL from our old site. \nWe hope this will help you find what you are looking for.");
    }
    $this->messenger->addMessage($message);
    $response = new RedirectResponse($destination, 302);
    \Drupal::service('http_middleware.legacy_redirect_redirect')->setRedirectResponse($response);

    return;
  }
}
