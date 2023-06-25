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
class VendorController extends ControllerBase
{

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static();
  }

  /**
   * Registers a new vendor.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function registerVendor(Request $request)
  {
    // Retrieve the necessary request data.
    $data = $request->request->all();

    // Create a new user entity as the vendor.
    $user = User::create();
    $user->enforceIsNew();
    $user->setUsername($data['username']);
    $user->setEmail($data['email']);
    // Set other relevant user fields.
    $user->activate();
    $user->save();

    // Add the vendor role.
    $user->addRole('vendor');
    $user->save();

    // Add any additional vendor-related configuration or setup as needed.

    // Prepare the response.
    $response = [
      'message' => 'Vendor registered successfully.',
      'user_id' => $user->id(),
    ];

    return new JsonResponse($response);
  }



  /**
   * Updates a vendor.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param int $vendor_id
   *   The ID of the vendor.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function updateVendor(Request $request, $vendor_id)
  {
    // Load the vendor entity.
    $vendor = User::load($vendor_id);

    // Check if the vendor exists and is a vendor role.
    if (!$vendor || !$vendor->hasRole('vendor')) {
      return new JsonResponse(['error' => 'Invalid vendor ID.'], 400);
    }

    // Retrieve the necessary request data.
    $data = $request->request->all();

    // Update the vendor entity with the provided data.
    $vendor->set('field_vendor_custom_field', $data['custom_field']);
    // Update other relevant vendor fields.
    $vendor->save();

    // Prepare the response.
    $response = [
      'message' => 'Vendor updated successfully.',
      'user_id' => $vendor->id(),
    ];

    return new JsonResponse($response);
  }


  public function getVendors()
  {
    $query = \Drupal::entityQuery('user')
      ->condition('status', 1)
      ->condition('roles', 'vendor');

    $vendor_ids = $query->execute();
    $vendors = User::loadMultiple($vendor_ids);

    $response = [];

    foreach ($vendors as $vendor) {
      $response[] = [
        'vendor_id' => $vendor->id(),
        'name' => $vendor->getDisplayName(),
        // Include other relevant vendor data in the response.
      ];
    }

    return new JsonResponse($response);
  }


  public function deleteVendor($vendor_id)
  {
    // Load the vendor entity.
    $vendor = User::load($vendor_id);

    // Check if the vendor exists and is a vendor role.
    if (!$vendor || !$vendor->hasRole('vendor')) {
      return new JsonResponse(['error' => 'Invalid vendor ID.'], 400);
    }

    // Delete the vendor entity.
    $vendor->delete();

    // Prepare the response.
    $response = [
      'message' => 'Vendor deleted successfully.',
      'vendor_id' => $vendor_id,
    ];

    return new JsonResponse($response);
  }
}
