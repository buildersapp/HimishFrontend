<?php
include_once('includes/check-session.php');

$type = isset($_GET['type']) ? $_GET['type'] : 'whatsapp';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

// Title and page rendering (not changed)
$title = "Message Templates | Sales Representative";

include('pages/messageTemplates/index.html');
?>