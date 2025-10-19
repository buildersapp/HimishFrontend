<?php
include_once('utils/helpers.php');
$err = 0;
// Breadcrumbs
$breadcrumb = [
    ['name' => 'Ads Link', 'url' => ''],
];

$response = sendCurlRequest(BASE_URL.'/admin-dashboard', 'GET', []);
$decodedResponse = json_decode($response, true);
$final = $decodedResponse['body'];

$responseSettings = sendCurlRequest(BASE_URL.'/get-setting', 'GET', []);
$decodedResponseSettings = json_decode($responseSettings, true);
$generalSettings = $decodedResponseSettings['body'];


if (isset($_POST['adsInvitation'])) {
    $errors = [];

    // Validate Share Option
    if (empty($_POST['share_option'])) {
        $errors[] = "Please select a sharing method.";
    }

    // Validate Emails (if sharing via email)
    $uniqueEmails = [];
    if ($_POST['share_option'] === 'email') {
        if (empty($_POST['emails']) || !is_array($_POST['emails'])) {
            $errors[] = "At least one email is required.";
        } else {
            $cleanedEmails = array_unique(array_map('trim', $_POST['emails'])); // Remove duplicates & trim spaces

            foreach ($cleanedEmails as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Invalid email format: $email";
                } else {
                    $uniqueEmails[] = $email; // Store valid emails
                }
            }
        }
    }

    // Validate Coupon
    $couponType = $_POST['coupon_type'] ?? null;
    $couponValue = $_POST['coupon_value'] ?? 0;
    if ($_POST['include_coupon'] === 'yes') {
        $percentageLimit = $generalSettings['sales_max_percentage_discount'];
        $fixedLimit = $generalSettings['sales_max_fixed_discount'];

        if ($couponType === 'percentage' && ($couponValue <= 0 || $couponValue > $percentageLimit)) {
            $errors[] = "Percentage discount must be between 1 and $percentageLimit%.";
        }

        if ($couponType === 'fixed' && ($couponValue <= 0 || $couponValue > $fixedLimit)) {
            $errors[] = "Fixed discount must be between 1 and $fixedLimit.";
        }
    }

    // Generate Deep Link
    if (empty($errors)) {
        $customNumber = generatePosId();
        $deepLinkData = [
            'custom_number' => (string)$customNumber,
            'request_type'  => (string)786,
            'user_id'       => (string)$_SESSION['hm_auth_data']['id'],
            'custom_string' => (string)'',
            'share_option'  => $_POST['share_option'],
            'emails'        => implode(',', $uniqueEmails), // Convert emails to CSV string
            'include_coupon'=> $_POST['include_coupon'],
            'coupon_type'   => $couponType,
            'coupon_value'  => $couponValue
        ];

        $shareURL = createBranchShortUrl($deepLinkData);

        // Prepare API Payload
        $postData = [
            'user_id'       => $_SESSION['hm_auth_data']['id'],
            'share_option'  => $_POST['share_option'],
            'emails'        => implode(',', $uniqueEmails),
            'include_coupon'=> $_POST['include_coupon'],
            'coupon_type'   => $couponType,
            'coupon_value'  => $couponValue,
            'custom_number' => $customNumber,
            'deep_link'     => $shareURL // Attach deep link
        ];

        // Send data via API or save in DB
        $response = sendCurlRequest(BASE_URL.'/create-ads-invitation', 'POST', $postData, [], true);
        $decodedResponse = json_decode($response, true);

        //dump($decodedResponse);

        if ($decodedResponse['success']) {
            setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
            echo "<script>window.location.href = 'sp-dashboard.php'</script>";
        } else {
            $errors[] = "API error: " . $decodedResponse['message'];
        }
    }
}

$title = "Ads Link";
include('pages/salesPerson/dashboard.html');