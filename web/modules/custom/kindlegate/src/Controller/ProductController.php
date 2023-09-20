<?php

namespace Drupal\kindlegate\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Session\AccountProxyInterface;

use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductAttribute;
use Drupal\commerce_product\Entity\ProductAttributeValue;
use Drupal\commerce_store\CurrentStoreInterface;
use Drupal\commerce_store\Entity\StoreInterface;

// use Drupal\kindlegate\Interface\CurrentStoreInterface;

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
  protected $currentUser;
  protected $currentStore;

  

  /**
   * Constructs a MyApiController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                               AccountProxyInterface $currentUser, 
                               CurrentStoreInterface $currentStore
                               )
  {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $currentUser;
    $this->currentStore = $currentStore;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    // return new static(
    //   $container->get('entity_type.manager')
    // );

    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('commerce_store.current_store')
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
      // 'price' => $product->getPrice()->getNumber(),
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
    // $store = $this->currentStore->getStore();
    $store = \Drupal::entityTypeManager()->getStorage('commerce_store')->load($data['store_id']);
    // Check if the store is available.
    if (!$store instanceof StoreInterface) {
      return new JsonResponse(['error' => 'No store set.'], 400);
    }
    // Validate and process the request data.
    $product = Product::create([
      'type' => 'product', // The product type machine name.
      'title' => $data['title'],
      'sku' => $data['sku'],
      'price' => $data['price'],
      'description' => $data['description'],
      // Set other product field values as needed.
    ]);
    // Set the store for the product.
    $product->set('stores', [$store->id()]);
    $product->save();


    // $variation = ProductVariation::create([
    //   'type' => 'default',
    //   'sku' => $data['sku'], // Variation SKU.
    //   'product_id' => $product->id(),
    //   'price' => new Price('9.99', 'USD'),
    //   // Set other variation field values as needed.
    // ]);
    // $variation->save();
  
    // // Optionally, you can set attributes and attribute values for the product.
    // // Here's an example of setting a color attribute and value:
    // $attribute = ProductAttribute::create([
    //   'attribute_id' => 'color',
    //   'product_id' => $product->id(),
    // ]);
    // $attribute->save();
  
    // $attributeValue = ProductAttributeValue::create([
    //   'attribute_id' => 'color',
    //   'product_id' => $product->id(),
    //   'value' => 'Red',
    // ]);
    // $attributeValue->save();
  



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


  public function deleteProduct($product_id = null)
  {
    $product_storage = \Drupal::entityTypeManager()->getStorage('commerce_product');
    $product = $product_storage->load($product_id);
    if ($product) {
      $product->delete();
      return new JsonResponse(['message' => 'Product deleted successfully']);
    }
    else {
      return new JsonResponse(['error' => 'Product not found'], 404);
    }
  }



  public function updateProduct(Request $request, $product_id)
  {
    $product_storage = $this->entityTypeManager->getStorage('commerce_product');
    $product = $product_storage->load($product_id);
    if ($product) {
      // Update the product fields based on the request data
      // $data = json_decode($request->getContent(), TRUE);
      $data = $request->request->all();
      $product->setTitle($data['title']);
      // $product->set('field_price', $data['price']);
      // Update other fields as needed

      // Save the updated product
      $product->save();

      return new JsonResponse(['message' => 'Product updated successfully']);
    }
    else {
      return new JsonResponse(['error' => 'Product not found'], 404);
    }
  }
}
