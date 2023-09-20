<?php

namespace Drupal\kindlegatedonation\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

class DonationApiController extends ControllerBase
{

  /**
   * Donation API endpoint.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function donate(Request $request)
  {
    // Extract donation data from the JSON request.
    $data = json_decode($request->getContent(), TRUE);

    if ($data['payment_gateway'] == "paystack") {
      return ($this->confirmPaystack($data['reference'])) ? $this->storeData($data) : "fail";
    }
    return ($this->confirmStripe($data['reference'])) ? $this->storeData($data) : "fail";
  }


  public function confirmStripe($payment_intent_id)
  {

    $stripe_secret_key = 'sk_test_your_secret_key'; // Replace with your Stripe secret API key
    $payment_intent_id = 'pi_xxxxxxxxxxxxx'; // Replace with the actual Payment Intent ID

    // Set your API endpoint URL
    $api_url = "https://api.stripe.com/v1/payment_intents/$payment_intent_id/confirm";

    // Set the request headers
    $headers = array(
      "Authorization: Bearer $stripe_secret_key",
    );

    // Make the API request
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Execute the request
    $response = curl_exec($ch);
    curl_close($ch);

    // Parse and handle the response
    if ($response === false) {
      // Handle cURL error
      echo 'cURL error: ' . curl_error($ch);
    } else {
      $response_data = json_decode($response, true);

      if ($response_data && isset($response_data['status']) && $response_data['status'] === 'succeeded') {
        // Payment confirmed successfully.
        echo 'Payment confirmed successfully.';

        // You can perform any additional actions here, such as updating your database or sending a confirmation email to the customer.
      } else {
        // Payment confirmation failed. Handle this case accordingly.
        echo 'Payment confirmation failed.';
      }
    }
  }

  public function confirmPaystack($reference)
  {

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . $reference,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer SECRET_KEY",
        "Cache-Control: no-cache",
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      echo "cURL Error #:" . $err;
      return false;
    } else {
      return $response;
    }
  }

  public function storeData($data)
  {
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
}
