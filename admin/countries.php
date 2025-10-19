<?php
include_once('utils/helpers.php');
$dataUp = [];
$err = 0;
$apiDataU = [];
// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Countries', 'url' => 'countries.php']
];

if (isset($_GET['id'])) {
    $countryCode = base64_decode($_GET['id']);
    $responseUpd = sendCurlRequest(BASE_URL.'/countries/template?country_code='.$countryCode, 'GET', [], [], true);
    $decodedResponseUpd = json_decode($responseUpd, true);
    if ($decodedResponseUpd['success']) {
        $apiDataU = $decodedResponseUpd['body'];
        $apiDataU['timezone'] = json_decode($apiDataU['timezone'],true);
    }
}

// If form submitted (Add or Update)
if (isset($_POST['addCountry']) || isset($_POST['updateCountry'])) {
    $country_id      = cleanInputs($_POST['country_id'] ?? null);
    $country_code    = cleanInputs($_POST['country_code']);
    $name            = cleanInputs($_POST['name']);
    $currency_code   = cleanInputs($_POST['currency_code']);
    $currency_symbol = cleanInputs($_POST['currency_symbol']);
    $date_format     = cleanInputs($_POST['date_format']);
    $timezone        = cleanInputs($_POST['timezone']);

    $apiDataU = [
        'country_code'    => $country_code,
        'name'            => $name,
        'currency_code'   => $currency_code,
        'currency_symbol' => $currency_symbol,
        'date_format'     => $date_format,
        'timezone'        => $timezone
    ];

    // Only include multiplier for update
    if (isset($_POST['updateCountry'])) {
        $currency_multiplier     = cleanInputs($_POST['currency_multiplier']);
        $apiDataU['currency_multiplier'] = $currency_multiplier;
    }

    if ($country_id) {
        $apiDataU['id'] = $country_id;
    }

    // Decide if Add (POST) or Update (PUT)
    //dump($apiDataU);
    if (isset($_POST['addCountry'])) {
        $response = sendCurlRequest(BASE_URL.'/countries', 'POST', $apiDataU, [], true);
    } else {
        $response = sendCurlRequest(BASE_URL.'/countries/'.$country_code, 'PUT', $apiDataU, [], true);
    }

    $decodedResponse = json_decode($response, true);

    if ($decodedResponse['success']) {
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'countries.php'</script>";
        exit;
    } else {
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'countries.php?err=1'</script>";
        exit;
    }
}

$title = "Countries";
if (!hasPermission($title, 'view')) {
    include('pages/no-permission.html');
    exit;
}
include('pages/countries/list.html');