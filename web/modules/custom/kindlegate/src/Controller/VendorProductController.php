<?php

namespace Drupal\kindlegate\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an API endpoint to register a vendor.
 */
class VendorProductController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static();
  }

  /**
 * Adds a product for a vendor.
 *
 * @param \Symfony\Component\HttpFoundation\Request $request
 *   The request object.
 * @param int $vendor_id
 *   The ID of the vendor.
 *
 * @return \Symfony\Component\HttpFoundation\JsonResponse
 *   The JSON response.
 */
public function addVendorProduct(Request $request, $vendor_id) {
    // Load the vendor entity.
    $vendor = User::load($vendor_id);
    
    // Check if the vendor exists and is a vendor role.
    if (!$vendor || !$vendor->hasRole('vendor')) {
      return new JsonResponse(['error' => 'Invalid vendor ID.'], 400);
    }
    
    // Retrieve the necessary request data.
    $data = $request->request->all();
    
    // Create a new product entity.
    $product = \Drupal::entityTypeManager()
      ->getStorage('commerce_product')
      ->create([
        'type' => 'product',
        'title' => $data['title'],
        // Set other relevant product fields.
        'stores' => [$vendor_id],
      ]);
    $product->save();
    
    // Prepare the response.
    $response = [
      'message' => 'Product created successfully.',
      'product_id' => $product->id(),
    ];
    
    return new JsonResponse($response);
  }
  

  public function updateVendorProduct(Request $request, $vendor_id, $product_id) {
    // Load the vendor entity.
    $vendor = User::load($vendor_id);
    
    // Check if the vendor exists and is a vendor role.
    if (!$vendor || !$vendor->hasRole('vendor')) {
      return new JsonResponse(['error' => 'Invalid vendor ID.'], 400);
    }
    
    // Load the product entity.
    $product = \Drupal::entityTypeManager()
      ->getStorage('commerce_product')
      ->load($product_id);
    
    // Check if the product exists and is associated with the vendor.
    if (!$product || !$product->hasStore($vendor_id)) {
      return new JsonResponse(['error' => 'Invalid product ID or not associated with the vendor.'], 400);
    }
    
    // Retrieve the necessary request data.
    $data = $request->request->all();
    
    // Update the product entity with the provided data.
    $product->setTitle($data['title']);
    // Update other relevant product fields.
    $product->save();
    
    // Prepare the response.
    $response = [
      'message' => 'Product updated successfully.',
      'product_id' => $product->id(),
    ];
    
    return new JsonResponse($response);
  }
  
  
  public function deleteVendorProduct($vendor_id, $product_id) {
    // Load the vendor entity.
    $vendor = User::load($vendor_id);
    
    // Check if the vendor exists and is a vendor role.
    if (!$vendor || !$vendor->hasRole('vendor')) {
      return new JsonResponse(['error' => 'Invalid vendor ID.'], 400);
    }
    
    // Load the product entity.
    $product = \Drupal::entityTypeManager()
      ->getStorage('commerce_product')
      ->load($product_id);
    
    // Check if the product exists and is associated with the vendor.
    if (!$product || !$product->hasStore($vendor_id)) {
      return new JsonResponse(['error' => 'Invalid product ID or not associated with the vendor.'], 400);
    }
    
    // Delete the product entity.
    $product->delete();
    
    // Prepare the response.
    $response = [
      'message' => 'Product deleted successfully.',
      'product_id' => $product_id,
    ];
    
    return new JsonResponse($response);
  }
  


}
