<?php
// Include Stripe PHP SDK
require_once 'C:\\xampp\\htdocs\\website\\stripe-php\\init.php';

// Set your Stripe API keys
\Stripe\Stripe::setApiKey('sk_test_51OlXcWSCx3NzEDIk0yAGHfuGiGe6R4FT7iA1iflXuNi8BpWLnFvccInSolXRhgTCnEFzjDKKwi1tba5dcFlftU8x00XrWsd0Me');

// Include database connection
include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
    // Fetch user profile data
    $fetch_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
    $fetch_profile->execute([$user_id]);
    $profile_data = $fetch_profile->fetch(PDO::FETCH_ASSOC);
    $name = $profile_data['name'];
    $number = $profile_data['number'];
    $email = $profile_data['email'];
} else {
    $user_id = '';
    header('location:home.php');
    exit; // Ensure to exit after redirection
}

if(isset($_POST['submit'])){
    // Fetch cart items
    $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
    $check_cart->execute([$user_id]);

    if($check_cart->rowCount() > 0){
        // Fetch cart items from the database
        $cart_items = [];
        while($fetch_cart = $check_cart->fetch(PDO::FETCH_ASSOC)){
            $cart_items[] = $fetch_cart;
        }

        // Create a new Stripe Checkout Session
        try {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => array_map(function($item) {
                    return [
                        'price_data' => [
                            'currency' => 'usd',
                            'unit_amount' => $item['price'] * 100, // Stripe requires amount in cents
                            'product_data' => [
                                'name' => $item['name'],
                            ],
                        ],
                        'quantity' => $item['quantity'],
                    ];
                }, $cart_items),
                'mode' => 'payment',
                'success_url' => 'http://localhost/website/cart.php?payment=success', // URL to redirect after successful payment
                'cancel_url' => 'http://localhost/website/cart.php?payment=cancel', // URL to redirect after payment is canceled
            ]);

            // Redirect user to Stripe Checkout
            header("Location: " . $session->url);
            exit;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Handle Stripe API errors
            echo 'Error: ' . $e->getError()->message;
        }
    } else {
        // Cart is empty, redirect to cart page
        header('Location: cart.php');
        exit;
    }
}
?>
