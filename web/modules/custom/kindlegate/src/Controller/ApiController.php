<?php

namespace Drupal\kindlegate\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides an API endpoint.
 */
class ApiController extends ControllerBase {

  /**
   * Returns JSON response.
   */
  public function getData() {
    $data = [
      'message' => 'Hello, API!',
    ];
    return new JsonResponse($data);
  }
}
