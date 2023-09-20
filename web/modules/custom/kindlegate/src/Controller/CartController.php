<?php

namespace Drupal\custom_cart_api\Controller;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for handling CRUD operations on the cart.
 */
class CartController extends ControllerBase {

  /**
   * Adds a product to the cart.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response indicating the success or failure of adding the product to the cart.
   */
  public function addToCart(Request $request) {
    // Retrieve the request data.
    $data = json_decode($request->getContent(), TRUE);

    // Validate the request data (e.g., product ID, quantity, etc.).

    // Load the current cart or create a new one.
    $cart = \Drupal\commerce_cart\CartProvider::getCart();

    // Add the product to the cart.
    $product_id = $data['product_id'];
    $quantity = $data['quantity'];
    $cart->addProduct($product_id, $quantity);

    // Save the cart.
    $cart->save();

    // Return a success message.
    return new JsonResponse(['message' => 'Product added to the cart successfully.']);
  }

  /**
   * Updates the quantity of a cart item.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param string $cart_item_id
   *   The ID of the cart item to update.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response indicating the success or failure of updating the cart item.
   */
  public function updateCartItem(Request $request, $cart_item_id) {
    // Load the current cart or create a new one.
    $cart = \Drupal\commerce_cart\CartProvider::getCart();

    // Find the cart item to update.
    $cart_item = $cart->get($cart_item_id);

    // Check if the cart item exists.
    if ($cart_item) {
      // Retrieve the request data.
      $data = json_decode($request->getContent(), TRUE);

      // Validate the request data (e.g., quantity, etc.).

      // Update the cart item's quantity.
      $quantity = $data['quantity'];
      $cart_item->setQuantity($quantity);

      // Save the cart.
      $cart->save();

      // Return a success message.
      return new JsonResponse(['message' => 'Cart item updated successfully.']);
    }

    // Return a 404 response if the cart item is not found.
    return new JsonResponse(['error' => 'Cart item not found.'], 404);
  }

  /**
   * Retrieves the cart.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response containing the cart data.
   */
  public function viewCart() {
    // Load the current cart or create a new one.
    $cart = \Drupal\commerce_cart\CartProvider::getCart();

    // Build the response data.
    $cart_data = [];
    foreach ($cart->getItems() as $cart_item) {
      $cart_data[] = [
        'product_id' => $cart_item->getPurchasedEntity()->id(),
        'quantity' => $cart_item->getQuantity(),
      ];
    }

    // Return the JSON response.
    return new JsonResponse($cart_data);
  }

}
