<?php
include('admin/utils/helpers.php');
include('includes/custom-functions.php');

if (isset($_GET['_branch_referrer'])) {
    $branch_referrer = $_GET['_branch_referrer'];

    // Convert URL-safe Base64 to standard Base64
    $base64_string = strtr($branch_referrer, '-_', '+/');

    // Fix padding if necessary
    $missing_padding = strlen($base64_string) % 4;
    if ($missing_padding) {
        $base64_string .= str_repeat('=', 4 - $missing_padding);
    }

    $decoded_referrer = base64_decode($base64_string);
    if ($decoded_referrer) {
        $uncompressed_data = @gzdecode($decoded_referrer);
        if ($uncompressed_data) {
            $branchUrl = htmlspecialchars($uncompressed_data);
            $result = getBranchLinkData($branchUrl);
            if(isset($result['data'])){
                $dataRes = $result['data'];
                $custom_number = $dataRes['custom_number'];
                $custom_string = $dataRes['custom_string'];
                $user_id = $dataRes['user_id'];
                $request_type = $dataRes['request_type'];
                $meta = $dataRes['meta'] ?? '';

                if($request_type == 0){ // posts
                    echo "<script>window.location.href = 'home.php?type=0&id=".base64_encode($custom_number)."'</script>";
                } else if($request_type == 1){ // ads
                    echo "<script>window.location.href = 'home.php?type=0&ad_id=".base64_encode($custom_number)."'</script>";
                } else if($request_type == 2){ // looking for
                    echo "<script>window.location.href = 'home.php?type=1&id=".base64_encode($custom_number)."'</script>";
                } else if($request_type == 3){ // deal share
                    echo "<script>window.location.href = 'home.php?type=2&id=".base64_encode($custom_number)."&dt=ds'</script>";
                } else if($request_type == 4){ // deals
                    echo "<script>window.location.href = 'home.php?type=2&id=".base64_encode($custom_number)."'</script>";
                } else if($request_type == 5){ // companies
                    echo "<script>window.location.href = 'companies.php?id=".base64_encode($custom_number)."'</script>";
                } else if($request_type == 6){ // on sale
                    echo "<script>window.location.href = 'home.php?type=4&id=".base64_encode($custom_number)."'</script>";
                } else if($request_type == 7){ // sales portal link data

                    $apiData = ['link_id' => $meta];
                    $response = sendCurlRequest(BASE_URL . '/get-link-by-linkId', 'GET', $apiData);
                    $linkData = json_decode($response, true);

                    if($linkData['success'] == 1){

                        $apiData = ['link_id' => $meta, 'type' => 0];

                        if (isset($_SESSION['hm_wb_auth_data']['id'])) {
                            // Logged in user
                            $apiData['user_id'] = $_SESSION['hm_wb_auth_data']['id'];
                        } else {
                            // Guest user → use session token
                            $sessionToken = getOrCreateGuestToken(); // function we built earlier
                            $apiData['ip_address'] = $sessionToken;
                        }

                        $responseIC = sendCurlRequest(BASE_URL . '/increase-views-link', 'POST', $apiData);
                        $increaseCountResp = json_decode($responseIC, true);
                        if($increaseCountResp['success'] == 1) {
                            // View count increased successfully
                            $clickedId = cleanInputs($increaseCountResp['body']['__create_click_id']['id'] ?? 0);
                            if($clickedId > 0) {
                                setcookie("last_clicked_id", $clickedId, time() + (7 * 24 * 60 * 60), "/"); // 7 days
                                setcookie("link_id", base64_encode($meta), time() + (7 * 24 * 60 * 60), "/"); // 7 days
                            }
                        }

                        if($linkData['body']['type'] == 0){ // Post
                            setcookie("redirect_url", 'home.php?type=0&id='.base64_encode($linkData['body']['post_id']).'', time() + (1 * 24 * 60 * 60), "/"); // 7 days
                            echo "<script>window.location.href = 'home.php?type=0&id=".base64_encode($linkData['body']['post_id'])."'</script>";
                        }else{
                            setcookie("redirect_url", 'community-detail.php?id='.base64_encode($linkData['body']['community_id']).'', time() + (1 * 24 * 60 * 60), "/"); // 7 days
                            echo "<script>window.location.href = 'community-detail.php?id=".base64_encode($linkData['body']['community_id'])."'</script>";
                        }
                    }else{
                        echo "<script>window.location.href = 'home.php'</script>";
                    }
                } else if($request_type == 8){ // community
                    echo "<script>window.location.href = 'community-detail.php?id=".base64_encode($custom_number)."'</script>";
                }
            }
        }
    }
}