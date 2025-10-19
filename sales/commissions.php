<?php
include_once('includes/check-session.php');

$type = isset($_GET['type']) ? $_GET['type'] : 0;

// Title and page rendering (not changed)
$title = "Commissions | Sales Representative";

include('pages/commissions/index.html');
?>