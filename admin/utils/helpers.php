<?php

$current_page = basename($_SERVER['PHP_SELF']);
ob_start();
session_start();
// Include the constants configuration file
include('constants.php');

/**
 * Function to print data
 *
 */
function dump($data) {
    echo '<pre>';
    print_r($data);
    exit;
}

/**
 * Function to return time ago
 *
 */

 function time_ago($timestamp) {
    // Convert the ISO 8601 format timestamp to a Unix timestamp
    $timestamp = strtotime($timestamp);

    // Get the difference in seconds between now and the provided timestamp
    $time_ago = time() - $timestamp;
    
    // Calculate the time differences
    $seconds = $time_ago;
    $minutes      = round($time_ago / 60);           // value 60 is seconds
    $hours        = round($time_ago / 3600);         // value 3600 is 60 minutes * 60 sec
    $days         = round($time_ago / 86400);        // value 86400 is 24 hours * 60 minutes * 60 sec
    $weeks        = round($time_ago / 604800);       // value 604800 is 7 days * 24 hours * 60 minutes * 60 sec
    $months       = round($time_ago / 2629440);      // value 2629440 is ((365+365+365+365+365)/5/12) ~ average month in seconds
    $years        = round($time_ago / 31553280);     // value 31553280 is 365.25 days * 24 hours * 60 minutes * 60 sec
    
    // Return a human-readable time difference
    if ($seconds <= 60) {
        return "Just Now";
    } else if ($minutes <= 60) {
        if ($minutes == 1) {
            return "1m";
        } else {
            return "$minutes" . "m";
        }
    } else if ($hours <= 24) {
        if ($hours == 1) {
            return "1h";
        } else {
            return "$hours" . "h";
        }
    } else if ($days <= 7) {
        if ($days == 1) {
            return "1d";
        } else {
            return "$days" . "d";
        }
    } else if ($weeks <= 4.3) { // 4.3 == 30/7
        if ($weeks == 1) {
            return "1w";
        } else {
            return "$weeks" . "w";
        }
    } else if ($months <= 12) {
        if ($months == 1) {
            return "1m";
        } else {
            return "$months" . "m";
        }
    } else {
        if ($years == 1) {
            return "1y";
        } else {
            return "$years" . "y";
        }
    }
}

/**
 * Function to return emails from JSON
 *
 */
function extractEmailsFromJson($jsonString) {
    $emails = [];

    // Decode JSON into PHP array
    $data = json_decode($jsonString, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Invalid JSON'];
    }

    // Recursive function to traverse all values
    $extract = function ($item) use (&$extract, &$emails) {
        if (is_array($item)) {
            foreach ($item as $value) {
                $extract($value);
            }
        } elseif (is_string($item)) {
            // Match email pattern
            if (filter_var($item, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $item;
            }
        }
    };

    $extract($data);

    // Remove duplicates and return
    return array_unique($emails);
}

/**
 * Function to create placeholder image
 *
 */
function generateBase64Image($name, $imageUrl = null) {
    // If image URL is provided, return it directly
    if (!empty($imageUrl)) {
        return $imageUrl;
    }

    // Extract initials from the user's name (first and last letter)
    $nameParts = explode(' ', trim($name));
    $initials = strtoupper($nameParts[0][0] . (isset($nameParts[1]) ? $nameParts[1][0] : ''));

    // Image dimensions
    $width = 200;
    $height = 200;

    // Create an image
    $image = imagecreatetruecolor($width, $height);

    // Google-style vibrant background colors
    // $colors = [
    //     [66, 133, 244],  // Blue
    //     [219, 68, 55],   // Red
    //     [244, 180, 0],   // Orange
    //     [15, 157, 88],   // Green
    //     [171, 71, 188],  // Purple
    //     [176, 187, 204], // Soft Gray-Blue
    //     [226, 235, 244], // Light Blue-Gray (#e2ebf4)
    // ];
    $colors = [
        [176, 187, 204], // New Color (Soft Gray-Blue)
    ];
    $randomColor = $colors[array_rand($colors)];
    $bgColor = imagecolorallocate($image, $randomColor[0], $randomColor[1], $randomColor[2]);

    // Fill background
    imagefill($image, 0, 0, $bgColor);

    // Text color (White)
    $textColor = imagecolorallocate($image, 255, 255, 255);

    // Use a TTF font (Make sure the font file exists on your server)
    $fontPath = __DIR__ . '/Roboto/Roboto-VariableFont_wdth,wght.ttf'; // Adjust path to a real font file
    $fontSize = 40; // Large font size for better readability

    // Calculate text box dimensions
    $textBox = imagettfbbox($fontSize, 0, $fontPath, $initials);
    $textWidth = $textBox[2] - $textBox[0];
    $textHeight = $textBox[1] - $textBox[7];

    // Calculate the position of the text to be centered
    $x = ($width - $textWidth) / 2;
    $y = ($height + $textHeight) / 2 - 10; // Slightly adjust to center properly

    // Add text to the image
    imagettftext($image, $fontSize, 0, $x, $y, $textColor, $fontPath, $initials);

    // Start output buffering to capture image data
    ob_start();
    imagepng($image);
    $imageData = ob_get_contents();
    ob_end_clean();

    // Destroy the image resource to free up memory
    imagedestroy($image);

    // Return the base64-encoded image as a data URL
    return 'data:image/png;base64,' . base64_encode($imageData);
}

/**
 * Function to mask email
 *
 */
function maskEmail($email) {
    // Split the email into username and domain
    list($username, $domain) = explode('@', $email);
    
    // Mask the middle part of the username with asterisks
    $maskedUsername = substr($username, 0, 4) . '***';
    
    // Combine the masked username and domain
    return $maskedUsername . '@' . $domain;
}

/**
 * Function to mask link
 *
 */
function maskLink($url, $visibleChars = 20) {
    // If the URL is shorter than the visible limit, return as is
    if (strlen($url) <= $visibleChars) {
        return $url;
    }

    // Show beginning and end, mask the middle part
    $start = substr($url, 0, floor($visibleChars / 2));
    $end = substr($url, -floor($visibleChars / 2));

    return $start . '...' . $end;
}


/**
 * Function to print US date
 *
 */
function formatToUSDate($isoDate, $time = 0) {
    try {
        // Check if timezone is set in session
        $timezone = isset($_SESSION['hm_timezone']) ? $_SESSION['hm_timezone'] : 'America/New_York';

        // Create a DateTime object with the provided ISO date and default timezone
        $date = new DateTime($isoDate, new DateTimeZone('UTC'));

        // Set the timezone to the one from the session
        $date->setTimezone(new DateTimeZone($timezone));

        // Format the date based on the $time parameter
        if ($time) {
            return $date->format('m/d/y h:i A'); 
        } else {
            return $date->format('m/d/Y'); 
        }
    } catch (Exception $e) {
        // Handle invalid date strings
        return "Invalid date format.";
    }
}


/**
 * Function for Action Buttons
 * viewLink == modal // showing modal
 */
function renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true, $hasShare = false, $shareType = '', $editPopUp = false) {
    // Start the button HTML
    $buttons = '';

    // View button
    if ($hasView) {
        $buttons .= '
            <button class="btn btn-crud" data-action="view" data-table="' . $table . '" data-view-link="' . $viewLink . '" data-id="' . $id . '" data-bs-toggle="tooltip" data-bs-placement="top" title="view" onclick="handleActionButtons(this)">
                <img src="assets/img/list/view.svg" alt="view" />
            </button>';
    }

    // Edit button
    if ($hasEdit) {
        $buttons .= '
            <button class="btn btn-crud" data-action="edit" data-table="' . $table . '" data-edit-link="' . $editLink . '" data-id="' . $id . '" data-pop-up="' . $editPopUp . '" data-bs-toggle="tooltip" data-bs-placement="top" title="edit" onclick="handleActionButtons(this)">
                <img src="assets/img/list/edit.svg" alt="edit" />
            </button>';
    }

    // Delete button
    if ($hasDelete) {
        $buttons .= '
            <button class="btn btn-crud" data-action="delete" data-table="' . $table . '" data-id="' . $id . '" data-bs-toggle="tooltip" data-bs-placement="top" title="delete" onclick="handleActionButtons(this)">
                <img src="assets/img/list/delete.svg" alt="delete" />
            </button>';
    }

    // Share button
    if ($hasShare && !empty($shareType)) {
        $buttons .= '
            <button class="btn btn-crud" data-action="share" data-id="' . $id . '" data-type="'. $shareType .'" data-bs-toggle="tooltip" data-bs-placement="top" title="share" onclick="createBranchIOLink(this)">
                <i class="fa fa-share"></i>
            </button>';
    }

    return $buttons;
}


/**
 * Function to clean input
 *
 */
function cleanInputs($data) {
    $clean_input = Array();
    if (is_array($data)) {
        foreach ($data as $k => $v) {
            $clean_input[$k] = cleanInputs($v);
        }
    } else {
		$clean_input = trim($data);
        $clean_input = trim(strip_tags($clean_input));
		$clean_input = addslashes($clean_input); 
		
    }
    return $clean_input;
}

/**
 * Function to send a cURL request
 *
 */
function sendCurlRequest($url, $method = 'GET', $data = [], $headers = [], $isMultipart = false, $adminAuth = '')
{
    $isSalesRep = 0;
    // Initialize cURL session
    $ch = curl_init();

    // Set URL
    curl_setopt($ch, CURLOPT_URL, $url);

    // Set headers
    $defaultHeaders = [
        'Content-Type: application/json', // Default content type
        'security_key: ' . SECURITY_KEY,
    ];

    if ((isset($_GET['isSalesRep']) && $_GET['isSalesRep'] == 1) || (!empty($data['isSalesRep']) && $data['isSalesRep'] == 1)) {
        $isSalesRep = 1;
    }

    if (strpos($_SERVER['REQUEST_URI'], '/admin') !== false) {
        $authorizationKey = isset($_SESSION['hm_auth_data']) ? $_SESSION['hm_auth_data']['authorization'] : '';
    } else if ($isSalesRep || strpos($_SERVER['REQUEST_URI'], '/sales') !== false) {
        $authorizationKey = isset($_SESSION['hm_sr_auth_data']) ? $_SESSION['hm_sr_auth_data']['authorization'] : '';
    } else {
        $authorizationKey = isset($_SESSION['hm_wb_auth_data']) ? @$_SESSION['hm_wb_auth_data']['authorization'] : $adminAuth;
    }

    if (!empty($authorizationKey)) {
        $defaultHeaders[] = 'authorization: ' . $authorizationKey;
    }

    // If the request is multipart, remove the JSON content-type
    if ($isMultipart) {
        $defaultHeaders = array_diff($defaultHeaders, ['Content-Type: application/json']);
    }

    // Merge default headers with user-provided headers
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($defaultHeaders, $headers));

    // Return response instead of outputting
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2); // Force TLS 1.2

    // Disable SSL verification (for testing only, do not use in production)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    // Handle different HTTP request methods
    switch (strtoupper($method)) {
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);

            if ($isMultipart) {
                // If it's a multipart request, use the CURLFile object for the file
                $postFields = [];
                foreach ($data as $key => $value) {
                    if ($value instanceof CURLFile) {
                        $postFields[$key] = $value; // Attach file
                    } else {
                        $postFields[$key] = $value; // Attach other fields
                    }
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // JSON data
            }
            break;

        case 'PUT':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $isMultipart ? $data : json_encode($data));
            break;

        case 'PATCH':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $isMultipart ? $data : json_encode($data));
            break;

        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $isMultipart ? $data : json_encode($data));
            break;

        case 'GET':
        default:
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            // For GET request, append the data as query string
            if (!empty($data)) {
                $queryString = http_build_query($data);
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $queryString);
            }
            break;
    }

    // Execute the cURL request
    $response = curl_exec($ch);

    // Get HTTP status code from the response
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Check for cURL errors
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        show500ErrorPage($error.'--'.$httpCode);
    }

    // Close the cURL session
    curl_close($ch);

    // Decode response (if it's JSON) or return raw response
    //$decodedResponse = json_decode($response, true);

    return $response;
}

/**
 * Function to show 500 page
 *
 */
function show500ErrorPage($err)
{
    require '500.php';
    exit();
}

/**
 * Function to format number
 *
 */
function formatNumberUS($number) {
    if ($number >= 1000000000) {
        // Convert to billions
        return number_format($number / 1000000000, 1) . 'B';
    } elseif ($number >= 1000000) {
        // Convert to millions
        return number_format($number / 1000000, 1) . 'M';
    } elseif ($number >= 1000) {
        // Convert to thousands
        return number_format($number / 1000, 1) . 'K';
    } else {
        // Return the original number with US-style formatting (commas for thousands)
        return number_format($number);
    }
}

/**
 * Function to create a short URL using Branch.io
 *
 * @param string $longUrl The long URL to shorten
 * @param array $params Additional data to include in the Branch.io link
 * @return string|null The short URL or null on failure
 */
function createBranchShortUrl($params = []) {
    // API endpoint
    $url = 'https://api2.branch.io/v1/url';

    // Prepare payload
    $payload = [
        'branch_key' => BRANCH_KEY,
        'campaign' => 'example_campaign',
        'feature' => 'short_link',
        'channel' => 'web',
        'data' => array_merge([
            '$host' => BRANCH_HOST,
            'is_test_mode' => BRANCH_TEST_MODE,
        ], $params)
    ];

    if(isset($params['alias'])) {
        $payload['alias'] = $params['alias']; // Default alias if not provided
    }

    //dump($payload);

    // Initialize cURL
    $curl = curl_init();

    // Set cURL options
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
        ],
    ]);

    // Execute the request
    $response = curl_exec($curl);

    // Handle errors
    if (curl_errno($curl)) {
        echo 'cURL Error: ' . curl_error($curl);
        curl_close($curl);
        return null;
    }

    curl_close($curl);

    // Decode the response
    $responseBody = json_decode($response, true);

    // Return the short URL or handle errors
    if (isset($responseBody['url'])) {
        return $responseBody['url'];
    } else {
        echo "Error: " . ($responseBody['error']['message'] ?? 'Unknown error occurred');
        return null;
    }
}

function generatePosId() {
    return 'POS' . time() . substr(uniqid(), -4) . mt_rand(100, 999);
}

function convertToUSDateFormat($date) {
    // Check if the input is a valid dd/mm/yyyy date
    $parts = explode('/', $date);
    if (count($parts) === 3) {
        // Rearrange to mm/dd/yyyy
        return $parts[1] . '/' . $parts[0] . '/' . $parts[2];
    } else {
        // Return original if format is not as expected
        return $date;
    }
}

function addUniqueLocations($locationsArray, &$uniqueKeys, &$locations, $defaultCommunityPrice = 0) {
    foreach ($locationsArray as $communityData) {
        if (
            !empty($communityData['city']) &&
            !empty($communityData['state']) &&
            !empty($communityData['address']) &&
            !empty($communityData['latitude']) &&
            !empty($communityData['longitude']) &&
            !empty($communityData['id'])
        ) {
            $city = strtolower(trim(preg_replace('/\s+/', ' ', $communityData['city'])));
            $state = strtolower(trim(preg_replace('/\s+/', ' ', $communityData['state'])));
            $uniqueKey = $city . '_' . $state;

            if (!in_array($uniqueKey, $uniqueKeys)) {
                $uniqueKeys[] = $uniqueKey;

                $locations[] = [
                    'address'       => $communityData['address'],
                    'latitude'      => $communityData['latitude'],
                    'longitude'     => $communityData['longitude'],
                    'country_code'  => $communityData['country_code'],
                    'price'         => $communityData['price'] > 0 ? $communityData['price'] : $defaultCommunityPrice,
                    'state'         => $communityData['state'],
                    'city'          => $communityData['city'],
                    'community_id'  => $communityData['id'],
                    'name'          => $communityData['name'],
                    'unique_id'     => $uniqueKey
                ];
            }
        }
    }
}

function getAddressFromLatLng($latitude, $longitude) {
    $apiKey = 'AIzaSyA8-xD4gQvyPqth_tvkgSuKwf7-p0cmSvc'; // Replace with your real API key
    $geocodeUrl = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$latitude,$longitude&key=$apiKey";

    $response = file_get_contents($geocodeUrl);
    if ($response === FALSE) {
        return ['error' => 'Failed to connect to Google Maps API'];
    }

    $data = json_decode($response, true);
    if ($data['status'] !== 'OK' || empty($data['results'])) {
        return ['error' => 'No address found for these coordinates'];
    }

    $result = $data['results'][0];
    $addressComponents = $result['address_components'];
    $formattedAddress = $result['formatted_address'];

    $city = '';
    $state = '';
    $country = '';
    $country_code = '';

    foreach ($addressComponents as $component) {
        if (in_array('administrative_area_level_1', $component['types'])) {
            $state = $component['short_name'];
        }
        if (in_array('country', $component['types'])) {
            $country = $component['long_name'];
            $country_code = $component['short_name'];
        }
        if (in_array('locality', $component['types']) && !$city) {
            $city = $component['long_name'];
        }
    }

    // Fallback if city is still empty
    if (empty($city)) {
        foreach ($addressComponents as $component) {
            if (in_array('sublocality_level_1', $component['types']) || in_array('sublocality', $component['types'])) {
                $city = $component['long_name'];
                break;
            }
        }
    }

    return [
        'address' => $formattedAddress,
        'city' => $city,
        'state' => $state,
        'country' => $country,
        'country_code' => $country_code,
        'latitude' => $latitude,
        'longitude' => $longitude,
    ];
}

// Helper: Get address from lat/lng if city/state missing
function resolveAddressIfMissing($location) {
    if (empty($location['city']) && empty($location['state']) && !empty($location['latitude']) && !empty($location['longitude'])) {
        $resolved = getAddressFromLatLng($location['latitude'], $location['longitude']);
        $location['city'] = $resolved['city'] ?? '';
        $location['state'] = $resolved['state'] ?? '';
        $location['address'] = $resolved['address'] ?? '';
    }
    return $location;
}

// is removte image valid
function isRemoteImageValid($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($status == 200);
}

function getBrowserLocale(string $default = 'en_US'): string
{
    if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        // Example: "de-DE,de;q=0.9,en;q=0.8"
        $langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        if (!empty($langs[0])) {
            $primary = explode(';', $langs[0])[0]; // take first lang before ";q="
            return str_replace('-', '_', trim($primary)); // "de-DE" → "de_DE"
        }
    }
    return $default;
}

function formatPriceIntl($price): string
{

    //return $price;

    // 1. Sanitize input
    if (is_string($price)) {
        $price = preg_replace('/[^\d.\-]/u', '', $price);
    }

    if ($price === null || $price === '' || !is_numeric($price)) {
        return '-';
    }

    // 2. Get session settings
    $currency   = $_SESSION['currency']['code']   ?? 'USD';
    $symbol     = $_SESSION['currency']['symbol'] ?? '';
    $multiplier = (float)($_SESSION['currency']['multiplier'] ?? 1.0);
    $locale     = getBrowserLocale('en_US');

    // 3. Apply multiplier
    $converted = (float)$price * $multiplier;

    // 4. Format with intl if available
    if (class_exists('NumberFormatter')) {
        $fmt = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        $fmt->setAttribute(NumberFormatter::FRACTION_DIGITS, 2);

        $out = $fmt->formatCurrency($converted, $currency);
        if ($out !== false) {
            return $out;
        }
    }

    // 5. Fallback: manual formatting with symbol from session
    return $symbol . number_format($converted, 2, '.', ',');
}

function getClientIP(): string {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP']; // Cloudflare
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    }
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

function getCountryCodeFromIP(): string {
    $ip = getClientIP();

    // local IPs → force US
    if ($ip === '127.0.0.1' || $ip === '::1') {
        return 'US';
    }

    $ch = curl_init("https://ipinfo.io/{$ip}/country");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $country = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || empty($country)) {
        return 'US'; // fallback
    }

    return strtoupper(trim($country));
}

function displayWithFallback($value, $min = 1, $max = 50) {
    //return ($value == 0) ? 10 : $value;
    return $value;
}

function getUserIp() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // For proxies
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

function generateUUIDv4() {
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40); // version 4
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80); // variant
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function getOrCreateGuestToken() {
    if (isset($_COOKIE['sessionToken'])) {
        return $_COOKIE['sessionToken']; // already exists
    } else {
        // Generate UUID v4
        $token = generateUUIDv4();

        // Store in cookie (7 days, HTTP only)
        setcookie(
            "sessionToken",
            $token,
            time() + (7 * 24 * 60 * 60), // 7 days
            "/",                        // path
            "",                         // domain (current domain)
            isset($_SERVER['HTTPS']),   // secure flag
            true                        // httponly
        );

        return $token;
    }
}

function secureUrlEncode($data) {
    // Accept array or scalar
    if (is_array($data)) {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    $cipher = 'AES-256-CBC';
    $ivLength = openssl_cipher_iv_length($cipher);
    $iv = random_bytes($ivLength);

    $encrypted = openssl_encrypt($data, $cipher, MY_URL_SECRET_KEY, OPENSSL_RAW_DATA, $iv);
    if ($encrypted === false) {
        return false;
    }

    // Concatenate IV + ciphertext
    $final = $iv . $encrypted;

    // URL-safe Base64 (no urlencode/urldecode needed)
    return rtrim(strtr(base64_encode($final), '+/', '-_'), '=');
}

function secureUrlDecode($encoded) {
    $cipher = 'AES-256-CBC';
    $ivLength = openssl_cipher_iv_length($cipher);

    // Convert back to standard Base64
    $encoded = strtr($encoded, '-_', '+/');
    $decoded = base64_decode($encoded, true);

    if ($decoded === false || strlen($decoded) <= $ivLength) {
        return false;
    }

    $iv = substr($decoded, 0, $ivLength);
    $ciphertext = substr($decoded, $ivLength);

    $decrypted = openssl_decrypt($ciphertext, $cipher, MY_URL_SECRET_KEY, OPENSSL_RAW_DATA, $iv);
    if ($decrypted === false) {
        return false;
    }

    // If it was a JSON string, return array
    $json = json_decode($decrypted, true);
    return (json_last_error() === JSON_ERROR_NONE) ? $json : $decrypted;
}

/**
 * Format a date based on user locale or IP
 *
 * @param string $date Input date (any format accepted by DateTime)
 * @param string|null $locale Optional: force a specific locale like 'en_US'
 * @return string Formatted date, or '-' if invalid
 */
function formatDateIntl($date): string
{

    if (empty($date)) {
        return '';
    }

    // 1. Parse the input date safely
    try {
        // Accept any valid date format
        $dt = new DateTime($date);
    } catch (Exception $e) {
        return '-'; // invalid date
    }

    // 2. Detect locale if not provided
    $locale = detectUserLocale() ?? 'en_US';

    // 3. Handle timezone correctly
    $timezone = $dt->getTimezone();
    $tzName = $timezone ? $timezone->getName() : date_default_timezone_get();

    // Fix 'Z' to 'UTC' (for ISO 8601 UTC dates)
    if ($tzName === 'Z') {
        $tzName = 'UTC';
        $dt->setTimezone(new DateTimeZone('UTC'));
    }

    // 4. Use IntlDateFormatter if available with custom pattern for leading zeros
    if (class_exists('IntlDateFormatter')) {
        // Custom pattern ensures leading zeros for day/month
        $pattern = 'dd/MM/yy'; // Default pattern, we will adjust per locale

        // Adjust pattern for US vs others
        if (stripos($locale, 'US') !== false) {
            $pattern = 'MM/dd/yy';
        }

        $formatter = new IntlDateFormatter(
            $locale,
            IntlDateFormatter::NONE,
            IntlDateFormatter::NONE,
            $tzName,
            null,
            $pattern
        );

        $formatted = $formatter->format($dt->getTimestamp());
        if ($formatted !== false) {
            return $formatted;
        }
    }

    // 5. Fallback: manual formatting with leading zeros
    if (stripos($locale, 'IN') !== false) {
        return $dt->format('d/m/Y'); // India
    } elseif (stripos($locale, 'US') !== false) {
        return $dt->format('m/d/Y'); // USA
    }

    // Default ISO fallback
    return $dt->format('Y-m-d');
}

/**
 * Detect the user's locale globally
 *
 * Priority:
 * 1. IP-based country detection
 * 2. Browser Accept-Language
 * 3. Default to en_US
 *
 * @return string Locale in format like 'en_US', 'en_IN', etc.
 */
function detectUserLocale(): string
{
    // 1. Try IP-based detection
    $country = getCountryCodeFromIP();
    if ($country) {
        $country = strtoupper(trim($country));
        $countryMap = [
            'IN' => 'en_IN',
            'US' => 'en_US',
            'GB' => 'en_GB',
            'FR' => 'fr_FR',
            'DE' => 'de_DE',
            'ES' => 'es_ES',
            'IT' => 'it_IT',
            'JP' => 'ja_JP',
            'CN' => 'zh_CN',
            // Add more as needed
        ];

        if (isset($countryMap[$country])) {
            return $countryMap[$country];
        }
    }

    // 2. Try browser Accept-Language header
    if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        if (!empty($langs[0])) {
            // Convert language code to locale
            $lang = substr($langs[0], 0, 2); // e.g., 'en'
            switch ($lang) {
                case 'en':
                    return 'en_US';
                case 'fr':
                    return 'fr_FR';
                case 'de':
                    return 'de_DE';
                case 'es':
                    return 'es_ES';
                case 'it':
                    return 'it_IT';
                case 'ja':
                    return 'ja_JP';
                case 'zh':
                    return 'zh_CN';
                default:
                    return 'en_US';
            }
        }
    }

    // 3. Default fallback
    return 'en_US';
}

function hasPermission($module, $action) {
    // Superadmin bypass
    if (isset($_SESSION['hm_auth_data']['account_type']) && $_SESSION['hm_auth_data']['account_type'] == 1) {
        return true;
    }

    // Regular permissions check
    if (!isset($_SESSION['permissions'][$module])) {
        return false;
    }

    return !empty($_SESSION['permissions'][$module][$action]) 
           && $_SESSION['permissions'][$module][$action] == 1;
}

function checkMaintenance() {
    if (MAINTENANCE_MODE_HM === true) {
        header("Location: early-access.php");
        exit();
    }
}

?>
