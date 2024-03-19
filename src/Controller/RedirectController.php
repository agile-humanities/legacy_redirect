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
   * @var \Drupal\legacy_redirect\Middleware\Redirect
   */
  protected $legacyRedirect;

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'legacy_redirect.settings';

  /**
   * Media source service.
   *
   * @var \Drupal\islandora\MediaSource\MediaSourceService
   */
  protected $mediaSourceService;

  /**
   * Islandora Utils.
   *
   * @var \Drupal\islandora\IslandoraUtils
   */
  protected $utils;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->requestStack = $container->get('request_stack');
    $instance->messenger = $container->get('messenger');
    $instance->legacyRedirect = $container->get('http_middleware.legacy_redirect_redirect');
    $instance->mediaSourceService = $container->get('islandora.media_source_service');
    $instance->utils = $container->get('islandora.utils');
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
    $original_file_term = $this->utils->getTermForUri('http://pcdm.org/use#OriginalFile');

    $file_type_map = [
      'document' => 'field_media_document',
      'file' => 'field_media_file',
      'audio' => 'field_media_audio_file',
      'image' => 'field_media_image',
      'video' => 'field_media_video_file',
    ];

    $pid_field = $config->get('pid_reference');
    $destination = $config->get('not_found') ? $config->get('not_found') : '/';
    $uri = $this->requestStack->getMainRequest()->getRequestUri();
    $uri = rtrim($uri, '/');
    $message = $this->t("The page you were looking for: @uri does not exist on this site", ['@uri' => $uri]);

    if (str_contains($uri, "islandora/object/")) {
      $parts = \explode("islandora/object/", $uri);
      if (count($parts) > 0) {
        $target = 'node';
        $components = explode('/', $parts[1]);
        if (count($components) > 1 && $components[1] == 'datastream') {
          $target = 'download';
        }
        $pid = $components[0];
        $nodes = $this->entityTypeManager()
          ->getStorage('node')
          ->loadByProperties([$pid_field => $pid]);
        if ($nodes) {
          $node = \reset($nodes);
          if ($target == 'node') {
            $destination = "/node/{$node->id()}";
            $message = $config->get('redirect_message');
          }
          if ($target == 'download') {
            $media = $this->utils->getMediaWithTerm($node, $original_file_term);
            $bundle = $media->bundle();
            $field = $file_type_map[$bundle];
            $document_field = $media->get($field);
            $document_values = $document_field->getValue();
            if (!empty($document_values)) {
              $fid = $document_values[0]['target_id'];
              $file = $this->entityTypeManager->getStorage('file')->load($fid);
              $destination = $this->utils->getDownloadUrl($file);
            }
          }
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
