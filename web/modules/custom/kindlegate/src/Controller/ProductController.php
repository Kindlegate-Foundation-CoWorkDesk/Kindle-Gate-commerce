<?php

namespace Drupal\kindlegate\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides an API endpoint to retrieve products.
 */
class ProductController extends ControllerBase
{

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a MyApiController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager)
  {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Returns all products.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response containing the products.
   */
  public function getProducts()
  {
    $products = $this->entityTypeManager->getStorage('commerce_product')->loadMultiple();
    $result = [];
    foreach ($products as $product) {
      $productData = $this->getProductData($product);
      $result[] = $productData;
    }

    return new JsonResponse($result);
  }

  /**
   * Retrieves relevant product data.
   *
   * @param \Drupal\commerce_product\Entity\ProductInterface $product
   *   The product entity.
   *
   * @return array
   *   The product data.
   */
  protected function getProductData(ProductInterface $product)
  {
    // Customize the data you want to retrieve for each product.
    $data = [
      'id' => $product->id(),
      'title' => $product->getTitle(),
      'price' => $product->getPrice()->getNumber(),
    ];

    return $data;
  }




  /**
   * Adds a new product.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function addProduct(Request $request)
  {
    // Retrieve the necessary request data.
    $data = $request->request->all();

    // Validate and process the request data.
    // Example code:
    $productData = [
      'type' => 'product', // Adjust the product type as per your setup.
      'title' => $data['title'],
      'price' => $data['price'],
      // Add other necessary product fields.
    ];
    $product = $this->entityTypeManager->getStorage('commerce_product')->create($productData);
    $product->save();

    // Prepare the response.
    $response = [
      'message' => 'Product created successfully.',
      'product_id' => $product->id(),
    ];

    return new JsonResponse($response);
  }



  /**
   * Retrieves products by vendor.
   *
   * @param int $vendor_id
   *   The ID of the vendor.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response containing the products.
   */
  public function getProductsByVendor($vendor_id)
  {
    // Load the vendor entity.
    $vendor = $this->entityTypeManager->getStorage('commerce_store')->load($vendor_id);

    // Check if the vendor exists.
    if (!$vendor || $vendor->bundle() !== 'store') {
      return new JsonResponse(['error' => 'Invalid vendor ID.'], 400);
    }

    // Get the products associated with the vendor.
    $query = $this->entityTypeManager->getStorage('commerce_product')
      ->getQuery()
      ->condition('stores', $vendor_id, '=');
    $product_ids = $query->execute();
    $products = $this->entityTypeManager->getStorage('commerce_product')->loadMultiple($product_ids);

    // Prepare the response.
    $result = [];
    foreach ($products as $product) {
      $productData = $this->getProductData($product);
      $result[] = $productData;
    }

    return new JsonResponse($result);
  }
}
