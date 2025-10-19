<?php
function requireLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['hm_sr_logged_in']) || $_SESSION['hm_sr_logged_in'] == false) {
        attemptAutoLogin();
    }

    if (!isset($_SESSION['hm_sr_logged_in']) || $_SESSION['hm_sr_logged_in'] == false) {
        header("Location: index.php");
        exit();
    }
}

function optionalLogin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['hm_sr_logged_in']) || $_SESSION['hm_sr_logged_in'] == false) {
        attemptAutoLogin();
    }
}

function redirectIfLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Attempt auto-login if not already logged in
    if (!isset($_SESSION['hm_sr_logged_in']) || $_SESSION['hm_sr_logged_in'] == false) {
        attemptAutoLogin();
    }

    // If login successful (via session or cookie), redirect to dashboard
    if (isset($_SESSION['hm_sr_logged_in']) && $_SESSION['hm_sr_logged_in'] == true) {
        header("Location: dashboard.php");
        exit();
    }
}

function attemptAutoLogin() {
    if (isset($_COOKIE['pz_sr_user_auth'])) {
        $cookieData = json_decode($_COOKIE['pz_sr_user_auth'], true);

        if ($cookieData && isset($cookieData['user_id'], $cookieData['token'])) {
            $userId = (int)$cookieData['user_id'];
            if ($userId > 0) {
                $query_data = '?user_id=' . $userId;
                $response = sendCurlRequest(BASE_URL . '/get-profile' . $query_data, 'GET', []);
                $decodedResponse = json_decode($response, true);

                if (!empty($decodedResponse['body'])) {
                    $_SESSION['sr_timezone'] = $cookieData['timezone'] ?? 'UTC';
                    $_SESSION['hm_sr_auth_data'] = $decodedResponse['body'];
                    $_SESSION['hm_sr_logged_in'] = true;
                }
            }
        }
    }
}
