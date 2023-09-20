<?php

namespace Drupal\kindlegatecalculator\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides an API endpoint.
 */
class TaxCalculatorController extends ControllerBase {

  /**
   * Returns JSON response.
   */
  public function getData() {
    $data = [
      'message' => 'Hello, API!',
    ];
    return new JsonResponse($data);
  }

  public function calculate()
  {
    $data = [
      'message' => '',
    ];

    return new JsonResponse($data);
  }
}
