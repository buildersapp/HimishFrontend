<?php
include_once('includes/check-session.php');

$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$defaultStatus = 1;
if($type === "expired"){
    $defaultStatus = 2;
}else if($type === "active"){
    $defaultStatus = 1;
}else if($type === "active"){
    $defaultStatus = 1;
}

// Title and page rendering (not changed)
$title = "Posts | Sales Representative";

include('pages/posts/index.html');
?>
