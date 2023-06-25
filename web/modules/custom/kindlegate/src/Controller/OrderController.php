<?php

namespace Drupal\kindlegate\Controller;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for handling CRUD operations on orders.
 */
class OrderController extends ControllerBase {

  /**
   * Retrieves an order by ID.
   *
   * @param int $order_id
   *   The ID of the order.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response containing the order data.
   */
  public function getOrder($order_id) {
    // Load the order entity.
    $order = \Drupal::entityTypeManager()->getStorage('commerce_order')->load($order_id);

    // Check if the order exists.
    if ($order instanceof OrderInterface) {
      // Build the response data.
      $data = [
        'id' => $order->id(),
        'order_number' => $order->getOrderNumber(),
        // Include any additional order properties you want to expose.
      ];

      // Return the JSON response.
      return new JsonResponse($data);
    }

    // Return a 404 response if the order is not found.
    return new JsonResponse(['error' => 'Order not found.'], 404);
  }

  /**
   * Creates a new order.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response containing the created order data.
   */
  public function createOrder(Request $request) {
    // Retrieve the request data.
    $data = json_decode($request->getContent(), TRUE);

    // Perform validation and data processing as needed.

    // Create the order entity.
    $order = \Drupal::entityTypeManager()->getStorage('commerce_order')->create([
      // Set the order properties based on the received data.
      // Adjust the code based on your specific requirements.
    ]);

    // Save the order entity.
    $order->save();

    // Return the JSON response with the created order data.
    return new JsonResponse(['message' => 'Order created.', 'order_id' => $order->id()]);
  }

  /**
   * Updates an existing order.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param int $order_id
   *   The ID of the order.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response indicating the success or failure of the update operation.
   */
  public function updateOrder(Request $request, $order_id) {
    // Load the order entity.
    $order = \Drupal::entityTypeManager()->getStorage('commerce_order')->load($order_id);

    // Check if the order exists.
    if ($order instanceof OrderInterface) {
      // Retrieve the request data.
      $data = json_decode($request->getContent(), TRUE);

      // Perform validation and data processing as needed.

      // Update the order properties based on the received data.
      // Adjust the code based on your specific requirements.

      // Save the order entity.
      $order->save();

      // Return a success message.
      return new JsonResponse(['message' => 'Order updated.']);
    }

    // Return a 404 response if the order is not found.
    return new JsonResponse(['error' => 'Order not found.'], 404);
  }

  /**
   * Deletes an existing order.
   *
   * @param int $order_id
   *   The ID of the order.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response indicating the success or failure of the delete operation.
   */
  public function deleteOrder($order_id) {
    // Load the order entity.
    $order = \Drupal::entityTypeManager()->getStorage('commerce_order')->load($order_id);

    // Check if the order exists.
    if ($order instanceof OrderInterface) {
      // Delete the order.
      $order->delete();

      // Return a success message.
      return new JsonResponse(['message' => 'Order deleted.']);
    }

    // Return a 404 response if the order is not found.
    return new JsonResponse(['error' => 'Order not found.'], 404);
  }

}
