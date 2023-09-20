<?php
namespace Drupal\kindlegate\Controller;

use Drupal\commerce_store\Entity\StoreInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

class StoreController extends ControllerBase {
  protected $entityTypeManager;
  protected $currentUser;

  public function __construct(EntityTypeManagerInterface $entityTypeManager, AccountInterface $currentUser) {
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  public function createStore(Request $request) {
    // Check if the user has the necessary permission to create a store.
    // if (!$this->currentUser->hasPermission('create commerce store')) {
    //   return new JsonResponse(['error' => 'Access denied.'], 403);
    // }

    $data = $request->request->all();

    // Create the store entity.
    $store = $this->entityTypeManager->getStorage('commerce_store')->create([
      'type' => 'store', // The store type machine name.
      'name' => $data['username'],
      'mail' => $data['email'],
      // Set other store field values as needed.
    ]);

    // Save the store.
    $store->save();

    // Return a success response.
    return new JsonResponse(['message' => 'Store created successfully.']);
  }

  public function deleteStore($store_id = null) {
    // Check if the user has the necessary permission to delete a store.
    // if (!$this->currentUser->hasPermission('delete commerce store')) {
    //   return new JsonResponse(['error' => 'Access denied.'], 403);
    // }

    // Load the store entity.
    $store = $this->entityTypeManager->getStorage('commerce_store')->load($store_id);

    // Check if the store exists.
    if (!$store instanceof StoreInterface) {
      return new JsonResponse(['error' => 'Store not found.'], 404);
    }

    // Delete the store.
    $store->delete();

    // Return a success response.
    return new JsonResponse(['message' => 'Store deleted successfully.']);
  }

  public function updateStore(Request $request, $store_id = null) {
    // Check if the user has the necessary permission to update a store.
    // if (!$this->currentUser->hasPermission('edit commerce store')) {
    //   return new JsonResponse(['error' => 'Access denied.'], 403);
    // }

    // Load the store entity.
    $store = $this->entityTypeManager->getStorage('commerce_store')->load($store_id);

    // Check if the store exists.
    if (!$store instanceof StoreInterface) {
      return new JsonResponse(['error' => 'Store not found.'], 404);
    }

    $data = $request->request->all();

    // Update the store field values.
    $store->setName($data['name']);
    $store->setEmail($data['email']);
    // Set other store field values as needed.

    // Save the store.
    $store->save();

    // Return a success response.
    return new JsonResponse(['message' => 'Store updated successfully.']);
  }

  public function viewAllStores() {
    // Check if the user has the necessary permission to view all stores.
    // if (!$this->currentUser->hasPermission('view all commerce stores')) {
    //   return new JsonResponse(['error' => 'Access denied.'], 403);
    // }

    // Load all store entities.
    $stores = $this->entityTypeManager->getStorage('commerce_store')->loadMultiple();

    // Prepare the response data.
    $data = [];
    foreach ($stores as $store) {
      $data[] = [
        'id' => $store->id(),
        'name' => $store->getName(),
        'email' => $store->getEmail(),
        // Add other relevant store fields as needed.
      ];
    }

    // Return the store entities as a JSON response.
    return new JsonResponse($data);
  }

  public function viewStore($storeId) {
    // Check if the user has the necessary permission to view a store.
    if (!$this->currentUser->hasPermission('view commerce store')) {
      return new JsonResponse(['error' => 'Access denied.'], 403);
    }

    // Load the store entity.
    $store = $this->entityTypeManager->getStorage('commerce_store')->load($storeId);

    // Check if the store exists.
    if (!$store instanceof StoreInterface) {
      return new JsonResponse(['error' => 'Store not found.'], 404);
    }

    // Prepare the response data.
    $data = [
      'id' => $store->id(),
      'name' => $store->getName(),
      'email' => $store->getEmail(),
      // Add other relevant store fields as needed.
    ];

    // Return the store entity as a JSON response.
    return new JsonResponse($data);
  }
}
