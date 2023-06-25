<?php

namespace Drupal\kindlegate\Controller;

use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Controller for managing inventory of products.
 */
class InventoryController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructs an InventoryController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, AccountInterface $currentUser, LoggerChannelFactoryInterface $loggerFactory) {
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
    $this->loggerFactory = $loggerFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('logger.factory')
    );
  }

  /**
   * Updates the stock quantity of a product.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param int $product_id
   *   The ID of the product.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response indicating the success or failure of the stock update.
   */
  public function updateStock(Request $request, $product_id) {
    // Load the product entity.
    $product = $this->entityTypeManager->getStorage('commerce_product')->load($product_id);

    // Check if the product exists.
    if ($product instanceof ProductInterface) {
      // Retrieve the request data.
      $data = json_decode($request->getContent(), TRUE);

      // Validate the request data.

      // Update the stock quantity.
      $stock_quantity = $data['stock_quantity'];
      $product->set('stock_quantity', $stock_quantity);
      $product->save();

      // Log the stock update.
      $this->loggerFactory->get('kindlegate')->info('Stock quantity updated for product: @product', [
        '@product' => $product->label(),
        'user' => $this->currentUser->getAccountName(),
      ]);

      // Return a success message.
      return new JsonResponse(['message' => 'Stock quantity updated.']);
    }

    // Return a 404 response if the product is not found.
    return new JsonResponse(['error' => 'Product not found.'], 404);
  }

  /**
   * Validates the stock availability of a product.
   *
   * @param int $product_id
   *   The ID of the product.
   * @param int $quantity
   *   The requested quantity.
   *
   * @return bool
   *   TRUE if the requested quantity is available in stock, FALSE otherwise.
   */
  public function validateStockAvailability($product_id, $quantity) {
    // Load the product entity.
    $product = $this->entityTypeManager->getStorage('commerce_product')->load($product_id);

    // Check if the product exists and if it has sufficient stock.
    if ($product instanceof ProductInterface && $product->get('stock_quantity')->value >= $quantity) {
      return TRUE;
    }

    return FALSE;
  }

}
