<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js?v=<?= time() ?>"></script>
<script src="admin/assets/plugins/holdon/js/HoldOn.js?v=<?= time() ?>"></script>
<script type="application/javascript" src="assets/js/apiScripts.js?v=<?= time() ?>"></script>
<script src="admin/assets/plugins/parsley/parsley.js?v=<?= time() ?>"></script>
<script src="admin/assets/plugins/toaster/js/toastr.min.js?v1.0"></script>
<script src="admin/assets/plugins/confirmation-popup/jquery.confirm.js?v=<?= time() ?>"></script>
<script type="application/javascript" src="admin/assets/js/select2.js?v=<?= time() ?>"></script>
<script src="assets/js/scrollPost.js?v=<?= time() ?>"></script>
<script type="application/javascript" src="assets/js/custom.js?v=<?= time() ?>"></script>
<script src="admin/assets/plugins/rateit/rateit.js?v=<?= time() ?>"></script>
<script type="text/javascript" src="admin/assets/plugins/cropper/pixelarity-face.js?v=<?= time() ?>"></script>
<script src="assets/js/slick.min.js?v=<?= time() ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js?v=<?= time() ?>"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js?v=<?= time() ?>"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js?v=<?= time() ?>"></script>
<script type="application/javascript" src="assets/js/web-feeds-slider.js?v=<?= time() ?>"></script>

<!-- Confirmation Modal -->
<div class="modal fade web-two-modal" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header position-relative">
                <button class="shareModal-close position-absolute top-0 end-0 m-2 text-muted" data-bs-dismiss="modal" aria-label="Close" style="z-index: 100300;">
                    <img src="assets/img/closeblack.png" alt="">
                </button>
            </div>

            <div class="modal-body">
                <div class="post-log-wraper">
                    <p id="confirmationText">Are you sure?</p>
                    <div class="d-flex justify-content-center align-items-center">
                        <button data-bs-dismiss="modal" aria-label="Close" class="modal-button close-button" id="cancelButton">
                            Cancel
                        </button>
                        <button class="modal-button logout-button" id="confirmButton">
                            Confirm
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
if (isset($_COOKIE['wb_errorMsg'])) {
    echo "<script>
        $(document).ready(function() {
            $.toastr.error('" . addslashes($_COOKIE['wb_errorMsg']) . "', {
                position: 'top-center',
                time: 5000
            });
        });
    </script>";
}

if (isset($_COOKIE['wb_successMsg'])) {
    echo "<script>
        $(document).ready(function() {
            $.toastr.success('" . addslashes($_COOKIE['wb_successMsg']) . "', {
                position: 'top-center',
                time: 5000
            });
        });
    </script>";
}
?>
<script>
    let notiData = {};
    // Initial Fetch on Page Load
    $(document).ready(function () {
        <?php if (isset($_SESSION['hm_wb_logged_in']) && $_SESSION['hm_wb_logged_in'] === true) { ?>
        getNotifications(notiData); // Load initial posts with the default data
        enableScrollPagination(notiData,"#notifications-container", true); // Enable infinite scroll

        // All Ads

        // Tab click event to reset posts and load specific content
        $(document).on('click', '#ad-running-pills-posts-tabk, #openAdsCanvas', function () {
            getAds({
                type: '4',
                user_id: <?= $userDetails['id'] ?>,
                container: '#ad-running-pills-postsk',
            });
        });

        $(document).on('click', '#ad-past-pills-listing-tabk', function () {
            getAds({
                type: '2',
                user_id: <?= $userDetails['id'] ?>,
                container: '#ad-running-pills-postsk',
            });
        });

        $(document).on('click', '#ad-others-pills-deals-tabk', function () {
            getAds({
                type: '0',
                user_id: <?= $userDetails['id'] ?>,
                container: '#ad-running-pills-postsk',
            });
        });

        // All Connections

        // Tab click event to reset posts and load specific content
        $(document).on('click', '#all-connections-pills-tabk, #allConnectionsCanvas', function () {
            getConnections({
                type: '2',
                user_id: <?= $userDetails['id'] ?>,
                container: '#my-connections-postsk',
            });
        });

        $(document).on('click', '#in-connections-pills-tabk, #inConnectionsCanvas', function () {
            getConnections({
                type: '1',
                user_id: <?= $userDetails['id'] ?>,
                container: '#my-connections-postsk',
            });
        });

        $(document).on('click', '#out-connections-pills-tabk, #outConnectionsCanvas', function () {
            getConnections({
                type: '0',
                user_id: <?= $userDetails['id'] ?>,
                container: '#my-connections-postsk',
            });
        });

        <?php } ?>

        Fancybox.bind('[data-fancybox^="gallery-listing-"]', {
            compact: false,
            contentClick: "iterateZoom",
            Images: {
                Panzoom: {
                    maxScale: 2,
                },
            },
            Toolbar: {
                display: {
                    left: [
                    // "infobar",
                    ],
                    middle : [],
                    right: [
                    "iterateZoom",
                    "close",
                    ],
                }
            },
            Carousel: {
                Navigation: false, // Removes the left/right arrows
            },
            Thumbs:false 
        });
        
        Fancybox.bind('[data-fancybox^="gallery-company-"]', {
            compact: false,
            contentClick: "iterateZoom",
            Images: {
                Panzoom: {
                    maxScale: 2,
                },
            },
            Toolbar: {
                display: {
                    left: [
                    // "infobar",
                    ],
                    middle : [],
                    right: [
                    "iterateZoom",
                    "close",
                    ],
                }
            },
            Carousel: {
                Navigation: false, // Removes the left/right arrows
            },
            Thumbs:false 
        });

        Fancybox.bind('[data-fancybox="gallery"]', {
            compact: false,
            contentClick: "iterateZoom",
            Images: {
                Panzoom: {
                    maxScale: 2,
                },
            },
            Toolbar: {
                display: {
                    left: [
                    // "infobar",
                    ],
                    middle : [],
                    right: [
                    "iterateZoom",
                    "close",
                    ],
                }
            },
            Carousel: {
                Navigation: false, // Removes the left/right arrows
            },
            Thumbs:false 
        });
    });

    function initFeaturedSlider() {
        if ($('#set-feature-ads').length && !$('#set-feature-ads').hasClass('slick-initialized')) {
            $('#set-feature-ads').slick({
                infinite: true,
                speed: 300,
                slidesToShow: 4,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 5000,
                adaptiveHeight: true,
                responsive: [
                    { breakpoint: 1024, settings: { slidesToShow: 4, slidesToScroll: 3 } },
                    { breakpoint: 600, settings: { slidesToShow: 3, slidesToScroll: 2 } },
                    { breakpoint: 480, settings: { slidesToShow: 3, slidesToScroll: 1 } }
                ]
            });
        }
    }

    document.getElementById("report-type").addEventListener("change", function () {
        const selectedValue = this.value;
        const extraFields = document.querySelectorAll(".delete-post-extra");

        if (selectedValue === "delete_post") {
            extraFields.forEach(field => {
                field.style.display = "block";
                field.querySelector("input").setAttribute("required", "required");
            });
        } else {
            extraFields.forEach(field => {
                field.style.display = "none";
                field.querySelector("input").removeAttribute("required");
            });
        }
    });

</script>
<script type="module">
  import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js";
  import { getMessaging, onMessage } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging.js";

  const firebaseConfig = {
    apiKey: "AIzaSyBANwr3eUpU_tdaSXxIkv052raJefEioUg",
    authDomain: "himish-9d505.firebaseapp.com",
    projectId: "himish-9d505",
    storageBucket: "himish-9d505.appspot.com",
    messagingSenderId: "709596707451",
    appId: "1:709596707451:web:1dabc003797d3123c080de"
  };

    // Init
    const app = initializeApp(firebaseConfig);
    const messaging = getMessaging(app);

    // Function to show toast dynamically
    function showPushToast(title, body, button1 = "", button2 = "") {
        const container = document.getElementById("toastContainer");

        // Create wrapper div
        const toastDiv = document.createElement("div");
        toastDiv.className = "toast align-items-center text-bg-primary border-0 mb-2";
        toastDiv.setAttribute("role", "alert");
        toastDiv.setAttribute("aria-live", "assertive");
        toastDiv.setAttribute("aria-atomic", "true");

        // Toast inner HTML
        toastDiv.innerHTML = `
            <div class="set-content-toast">
                <div class="toast-body">
                    <div class="set-img-round-toast">
                        <img src="assets/images/fav-icon.png" alt="img">
                    </div>
                    <div class="">
                        <strong class="set-f-14-title text-black">${title}</strong><br>
                        <p class="set-f-14-para text-black">${body}</p>
                        <div>
                            ${button1 || ""} ${button2 || ""}
                        </div>
                    </div>
                    <div class="set-flex-shrink-default">
                        <button type="button" class="btn-close set-btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        <small>just now</small>
                    </div>
                </div>   
            </div>
        `;

        // Append to container
        container.appendChild(toastDiv);

        // Show toast
        const toast = new bootstrap.Toast(toastDiv, { delay: 8000 });
        toast.show();

        // Remove from DOM after hidden
        toastDiv.addEventListener("hidden.bs.toast", () => {
            toastDiv.remove();
        });
    }

    // Foreground listener
    onMessage(messaging, (payload) => {
        const title = payload.notification?.title || "Notification";
        let body = payload.notification?.body || "";
        const code = Number(payload.data?.code) || 0;
        const request_id = payload.data?.request_id || 0;
        const metadata = payload.data?.metadata ? JSON.parse(payload.data.metadata) : null;

        let button1 = "";
        let button2 = "";
        let headerMessage = body;
        let link;

        if (code === 61 || code === 63) { // Ads Expire
            const adName = metadata?.name || "";
            body = adName;
            button2 = `<a href="create-ad.php?ad_id=${btoa(request_id)}"><button class="set-btn-reply-sm">Extend Ad Time</button></a>`;
        } 
        else if ([1, 16, 18, 8999, 666].includes(code)) { // LikePost
            button2 = `<a href="post-details.php?id=${btoa(request_id)}"><button class="set-btn-reply-sm">View</button></a>`;
        } 
        else if (code === 10) { // LikeAd
            button2 = `<a href="ad-details.php?id=${btoa(request_id)}"><button class="set-btn-reply-sm">View</button></a>`;
        } 
        else if (code === 600 && metadata?.company) { // AssociationRequest
            button2 = `<a href="company-details.php?id=${btoa(metadata.company.id)}&active=2&activeReq=1"><button class="set-btn-reply-sm">View</button></a>`;
        } 
        else if (code === 601 && metadata?.company) { // AssociationRequestAccepted
            button2 = `<a href="company-details.php?id=${btoa(metadata.company.id)}&active=2"><button class="set-btn-reply-sm">View</button></a>`;
        } 
        else if (code === 14 && metadata?.id) { // Community Request
            button1 = `<button type="button" class="set-btn-reply-sm acceptRejectCommunityReq" data-id="${request_id}" data-community-id="${metadata.id}" data-status="2">Reject</button>`;
            button2 = `<button type="button" class="set-btn-reply-sm acceptRejectCommunityReq" data-id="${request_id}" data-community-id="${metadata.id}" data-status="1">Accept</button>`;
        } 
        else if (code === 603) { // ConnectionRequest
            button1 = `<button type="button" class="set-btn-reply-sm ar-cnt-fn" data-request-id="${request_id}" data-type="2">Reject</button>`;
            button2 = `<button type="button" class="set-btn-reply-sm ar-cnt-fn" data-request-id="${request_id}" data-type="1">Accept</button>`;
        } 
        else if (code === 11) { // RecommendCompany
            button2 = `<a href="company-details.php?id=${btoa(request_id)}"><button class="set-btn-reply-sm">View</button></a>`;
        } 
        else if (code === 55) { // Posted for company
            button2 = `<a href="post-details.php?id=${btoa(request_id)}"><button class="set-btn-reply-sm">View</button></a>`;
            const match = body.match(/(https?:\/\/[^\s]+)/);
            if (match) {
                link = match[0];
                body = `<a class="text-primary" href="${link}">${link}</a>`;
                headerMessage = headerMessage.replace(link, '');
            }
        } 
        else if (code === 66 || code === 6) { // Owner of company
            if (code === 6) {
                button2 = `<a href="company-details.php?id=${btoa(request_id)}"><button class="set-btn-reply-sm">View</button></a>`;
            } else {
                button2 = `<a href="create-ad.php?id=${btoa(request_id)}"><button class="set-btn-reply-sm">Continue To Create Ad</button></a>`;
            }
        } 
        else if (code === 54) { // Post available in feed
            button2 = `<a href="post-details.php?id=${btoa(request_id)}"><button class="set-btn-reply-sm">View</button></a>`;
        } 
        else if (code === 1190) { // Recommend appreciated
            button2 = `<a href="company-details.php?id=${btoa(request_id)}"><button class="set-btn-reply-sm">View</button></a>`;
        } 
        else if (code === 3 || code === 5) { // PostComment, listingComment
            button2 = `<a href="post-details.php?id=${btoa(request_id)}&trigger=comments"><button class="set-btn-reply-sm">View</button></a>`;
        } 
        else if (code === 99) { // AdComment
            button2 = `<a href="ad-details.php?id=${btoa(request_id)}&trigger=comments"><button class="set-btn-reply-sm">View</button></a>`;
        } 
        else if (code === 64 || code === 65) { // AdLive
            button2 = `<a href="ad-details.php?id=${btoa(request_id)}"><button class="set-btn-reply-sm">View</button></a>`;
            const match = body.match(/(https?:\/\/[^\s]+)/);
            if (match) {
                link = match[0];
                body = `<a class="text-primary" href="${link}">${link}</a>`;
                headerMessage = headerMessage.replace(link, '');
            }
        }

        // Show toast
        showPushToast(title, body, button1, button2);
    });

</script>
