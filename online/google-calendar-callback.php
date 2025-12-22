<?php
// configuration.php or top of file
$client_id = 'YOUR_CLIENT_ID';
$client_secret = 'YOUR_CLIENT_SECRET';
$redirect_uri = 'http://localhost/your-project/callback.php'; // Must match Google Console exactly

// 1. Check if Google sent back a code
if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // 2. Prepare the POST request to exchange the code for tokens
    $token_url = "https://oauth2.googleapis.com/token";
    $post_fields = [
        'code'          => $code,
        'client_id'     => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri'  => $redirect_uri,
        'grant_type'    => 'authorization_code'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));

    $response = curl_exec($ch);
    $data = json_decode($response, true);
    curl_close($ch);

    // 3. Handle the Result
    if (isset($data['access_token'])) {
        $access_token  = $data['access_token'];
        $refresh_token = $data['refresh_token'] ?? 'Already have one'; // Only sent the first time!

        // Success! You now have the tokens.
        echo "<h1>Success!</h1>";
        echo "<p>Access Token: " . $access_token . "</p>";
        echo "<p>Refresh Token: " . $refresh_token . "</p>";
        
        // NEXT STEP: Save these to your MySQL database for this user/tenant
        // saveTokensToDb($access_token, $refresh_token);
        
    } else {
        echo "Error exchanging code: " . $response;
    }
} else {
    echo "No authorization code found in the URL.";
}