<?php
include_once('utils/helpers.php');

// Breadcrumbs
$breadcrumb = [
    ['name' => 'General Settings', 'url' => ''],
    ['name' => 'General', 'url' => 'general-settings.php']
];

$apiData=[];
$response = sendCurlRequest(BASE_URL.'/get-setting', 'GET', $apiData);
$decodedResponse = json_decode($response, true);
$final = $decodedResponse['body'];

if(isset($_POST['updateSettings'])){
    $contact_us_mail    =   cleanInputs($_POST['contact_us_mail']);
    $smtp_email         =   cleanInputs($_POST['smtp_email']);
    $smtp_password      =   cleanInputs($_POST['smtp_password']);
    $referal_credit     =   cleanInputs($_POST['referal_credit']);
    $marketing_url      =   cleanInputs($_POST['marketing_url']);
    $python_api      =   cleanInputs($_POST['python_api']);
    $python_api_2      =   cleanInputs($_POST['python_api_2']);
    $test_prompt        =   cleanInputs($_POST['test_prompt']);
    $live_prompt        =   cleanInputs($_POST['live_prompt']);
    $nfc_card_price     =   cleanInputs($_POST['nfc_card_price']);
    $nfc_card_tax       =   cleanInputs($_POST['nfc_card_tax']);
    $post_expire       =   cleanInputs($_POST['post_expire']);
    $ad_expire       =   cleanInputs($_POST['ad_expire']);
    $deals_expire       =   cleanInputs($_POST['deals_expire']);
    $deal_share_expire       =   cleanInputs($_POST['deal_share_expire']);
    $sales_commision       =   cleanInputs($_POST['sales_commision']);
    $ww_community_price       =   cleanInputs($_POST['ww_community_price']);
    $default_community_price       =   cleanInputs($_POST['default_community_price']);
    $all_community_selection_price       =   cleanInputs($_POST['all_community_selection_price']);
    $terms       =   ($_POST['terms']);
    $privacy       =   ($_POST['privacy']);
    $post_image_match_percentage       =   cleanInputs($_POST['post_image_match_percentage']);
    
    // $deal_share_expire       =   cleanInputs($_POST['deal_share_expire']);
    $id                 =   cleanInputs($_POST['id']);
    $invite_to_company_recommend       =   cleanInputs($_POST['invite_to_company_recommend']);
    $invite_to_community       =   cleanInputs($_POST['invite_to_community']);
    $invite_to_app       =   cleanInputs($_POST['invite_to_app']);
    $fd_cost       =   cleanInputs($_POST['fd_cost']);
    $fd_shown_after_x_feeds       =   cleanInputs($_POST['fd_shown_after_x_feeds']);
    $fd_live_for_days       =   cleanInputs($_POST['fd_live_for_days']);


    $post_owner_email_trigger_mode = isset($_POST['post_owner_email_trigger_mode']) ? 1 : 0;

    $post_owner_trigger_test_emails = cleanInputs($_POST['post_owner_trigger_test_emails']);
    $post_owner_trigger_test_phones = cleanInputs($_POST['post_owner_trigger_test_phones']);
    $is_stripe_live = isset($_POST['is_stripe_live']) ? 1 : 0;
    $show_listing_images = isset($_POST['show_listing_images']) ? 1 : 0;
    $show_post_in_search = isset($_POST['show_post_in_search']) ? 1 : 0;
    $free_community = cleanInputs($_POST['free_community']);
    $discount_community = cleanInputs($_POST['discount_community']);

    $apiData = [
        'tag_id'                       => $id,
        'contact_us_mail'              => $contact_us_mail,
        'smtp_email'                   => $smtp_email,
        'smtp_password'                => $smtp_password,
        'referal_credit'               => $referal_credit,
        'nfc_card_tax'                  => $nfc_card_tax,
        'nfc_card_price'               => $nfc_card_price,
        'marketing_url'                => $marketing_url,
        'test_prompt'                  => $test_prompt,
        'python_api'                  => $python_api,
        'python_api_2'                  => $python_api_2,
        'live_prompt'                  => $live_prompt,
        'post_expire'                  => $post_expire,
        'ad_expire'                  => $ad_expire,
        'deals_expire'                  => $deals_expire,
        'sales_commision'                  => $sales_commision,
        'deal_share_expire' => $deal_share_expire,
        'post_image_match_percentage' => $post_image_match_percentage,
        'invite_to_company_recommend' => $invite_to_company_recommend,
         'invite_to_community' => $invite_to_community,
         'invite_to_app' => $invite_to_app,
         'fd_cost' => $fd_cost,
         'fd_shown_after_x_feeds' => $fd_shown_after_x_feeds,
         'fd_live_for_days' => $fd_live_for_days,
         'post_owner_email_trigger_mode' => $post_owner_email_trigger_mode,
        'post_owner_trigger_test_emails' => $post_owner_trigger_test_emails,
        'post_owner_trigger_test_phones' => $post_owner_trigger_test_phones,
        'is_stripe_live' => $is_stripe_live,
        'show_listing_images' => $show_listing_images,
        'privacy' => $privacy,
        'terms' => $terms,
        'default_community_price' => $default_community_price,
        'all_community_selection_price' => $all_community_selection_price,
        'ww_community_price' => $ww_community_price,
        'show_post_in_search' => $show_post_in_search,
        'discount_community' => $discount_community,
        'free_community' => $free_community
        // 'deal_share_expire'                  => $deal_share_expire
    ];

    $response = sendCurlRequest(BASE_URL.'/update-setting', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'general-settings.php?'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'general-settings.php'</script>";
    }
}

$title = "General Settings";
if (!hasPermission($title, 'view')) {
    include('pages/no-permission.html');
    exit;
}
include('pages/generalSettings/list.html');