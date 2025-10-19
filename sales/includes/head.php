<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> | Himish</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="Himish is a platform where users can post feeds, view nearby businesses, claim their business listings, and connect with other companies and users. Users can also post buy/sell/share listings and discover nearby deals and products.">
    <meta name="keywords" content="Himish, business connections, local businesses, buy and sell, nearby deals, claim business, product discovery, local feed, community connections">
    <meta name="author" content="Himish">
    <meta name="robots" content="index, follow">
    <meta name="language" content="en">
    
    <!-- Open Graph Meta Tags (For Social Media Sharing) -->
    <meta property="og:title" content="<?= $title ?>" />
    <meta property="og:description" content="Himish is a platform where users can post feeds, claim businesses, create connections with companies and other users, and post buy/sell/share listings to find nearby deals." />
    <meta property="og:image" content="assets/img/logo.png" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:image:type" content="image/jpeg" />
    <meta property="og:url" content="<?= htmlspecialchars($fullUrl) ?>" />
    <meta property="og:type" content="website" />

    <!-- Additional Open Graph Meta Tags for Facebook -->
    <meta property="og:image" content="https://himish.com/assets/img/logo.png" />
    <meta property="og:image:secure_url" content="https://himish.com/assets/img/logo.png" />
    <meta property="og:image:alt" content="Himish" /> <!-- Alt text for the image -->

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $title ?>">
    <meta name="twitter:description" content="Himish is a platform where users can post feeds, claim businesses, create connections with companies and other users, and post buy/sell/share listings to find nearby deals.">
    <meta name="twitter:image" content="https://himish.com/assets/img/logo.png">

    <!-- Favicon -->
    <link rel="icon" href="../assets/img/favicon.ico" type="image/x-icon" />
    <link rel="icon" href="../assets/img/favicon.ico" type="image/png" />


    <link rel="stylesheet" href="assets/css/custom.css?v=<?= time() ?>" />
    <link rel="stylesheet" type="text/css" href="../admin/assets/plugins/cropper/pixelarity.css">
    <link rel="stylesheet" href="../admin/assets/plugins/toaster/css/toastr.min.css?v1.0" />
    <link href="../admin/assets/plugins/holdon/css/HoldOn.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
    <link href="../admin/assets/css/select2.css" rel="stylesheet" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;500;600;700;800;900&amp;display=swap" />


    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="assets/js/tailwind.config.js?v=<?= time() ?>"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDOJlqpBimJCGqQPmAty3XzmxjO4Gu_QGQ&libraries=geometry,places"></script>
    <script type="application/javascript" src="../assets/js/jquery.js?v=<?= time() ?>"></script>
    
</head>