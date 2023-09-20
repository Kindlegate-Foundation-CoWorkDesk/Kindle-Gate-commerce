<?php

namespace Drupal\kindlegatedonation\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;

class DonationApiController extends ControllerBase {

  /**
   * Donation API endpoint.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function donate(Request $request) {
    // Extract donation data from the JSON request.
    $data = json_decode($request->getContent(), TRUE);
  
    // Create a new donation node.
    $node = Node::create([
      'type' => 'donation', // Use the machine name of your donation content type.
      'title' => 'Donation', // You can set a default title.
      'field_donor_name' => $data['donor_name'], // Map data from the request to fields.
      'field_email' => $data['email'],
      'field_amount' => $data['amount'],
      // Add other fields as needed.
    ]);
  
    // Save the donation node.
    $node->save();
  
    // Example response.
    $response = [
      'status' => 'success',
      'message' => 'Donation processed and recorded successfully.',
    ];
  
    return new JsonResponse($response);
  }
  

  public function confirmStripe()
  {
    
  }

  public function confirmPaystack()
  {
    
  }
}
