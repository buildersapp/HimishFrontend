<script src="../admin/assets/plugins/holdon/js/HoldOn.js?v=<?= time() ?>"></script>
<script src="../admin/assets/plugins/parsley/parsley.js?v=<?= time() ?>"></script>
<script src="../admin/assets/plugins/toaster/js/toastr.min.js?v1.0"></script>
<script src="../admin/assets/plugins/confirmation-popup/jquery.confirm.js?v=<?= time() ?>"></script>
<script type="application/javascript" src="../admin/assets/js/select2.js?v=<?= time() ?>"></script>
<script type="application/javascript" src="assets/js/custom.js?v=<?= time() ?>"></script>
<script type="application/javascript" src="assets/js/scrollFn.js?v=<?= time() ?>"></script>
<script type="application/javascript" src="assets/js/apiScripts.js?v=<?= time() ?>"></script>
<script src="../admin/assets/plugins/rateit/rateit.js?v=<?= time() ?>"></script>
<script type="text/javascript" src="../admin/assets/plugins/cropper/pixelarity-face.js?v=<?= time() ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js?v=<?= time() ?>"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js?v=<?= time() ?>"></script>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
  <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 relative">
    
    <!-- Top Icon -->
    <div class="flex justify-center">
      <div class="w-14 h-14 flex items-center justify-center rounded-full bg-red-100">
        <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M12 9v2m0 4h.01M12 5a7 7 0 100 14 7 7 0 000-14z"/>
        </svg>
      </div>
    </div>

    <!-- Title -->
    <h2 class="text-xl font-semibold text-gray-900 text-center mt-4" id="confirmationText">
      Are you sure?
    </h2>

    <!-- Description (optional) -->
    <p class="text-gray-600 text-center mt-2">
      This action cannot be undone. Please confirm before proceeding.
    </p>

    <!-- Buttons -->
    <div class="flex justify-center gap-4 mt-6">
      <button
        id="cancelButton"
        onclick="closeConfirmationModal()"
        class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition"
      >
        Cancel
      </button>
      <button
        id="confirmButton"
        class="px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium transition"
      >
        Confirm
      </button>
    </div>
  </div>
</div>

<!-- Upcoming Feature Modal -->
<div id="upcomingFeatureModal" class="fixed inset-0 bg-black bg-opacity-40 hidden flex items-center justify-center z-50">
  <!-- Modal Content -->
  <div class="fade-in bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-8 text-center">
    <div class="flex flex-col items-center mb-4">
      <div class="w-14 h-14 rounded-full bg-indigo-100 flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
        </svg>
      </div>
      <h2 class="text-2xl font-bold text-gray-800 mb-2">Feature Coming Soon!</h2>
      <p class="text-sm text-gray-600">
        We’re working hard to bring you powerful tools to help you succeed. Great things take time,
        and your patience means the world to us. Stay tuned — you’ll be notified in your portal as soon as this feature goes live.
      </p>
    </div>
    <button onclick="upcomingFeatureToggleModal(false)"
      class="mt-6 px-6 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-full hover:scale-105 transition-transform duration-200">
      Got it, thanks!
    </button>
  </div>
</div>

<!-- Share Modal -->
<div id="shareModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-50">
  <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 p-6 animate-fadeIn space-y-6">
    
    <!-- Modal Header -->
    <div class="flex items-center justify-between">
      <h5 class="text-xl font-semibold text-gray-900">
        Share this <span id="shareName" class="text-purple-600 font-bold">Community</span>
      </h5>
      <button onclick="closeShareModal()" class="text-gray-500 hover:text-gray-800 transition">
        <img src="../assets/img/closeblack.png" alt="Close" class="h-8 w-12">
      </button>
    </div>

    <!-- Notification Bar -->
    <div class="hidden px-4 py-2 rounded-md bg-green-100 text-green-700 text-sm" id="notificationBar">
      Link copied successfully!
    </div>

    <!-- Share Link -->
    <div class="flex items-center bg-gray-100 border border-gray-300 rounded-lg px-3 py-2">
      <div class="flex items-center space-x-2 flex-grow">
        <img src="../assets/img/linkform.png" alt="Link Icon" class="h-5 w-5">
        <input
          type="text"
          id="linkInput"
          readonly
          value="https://www.posterzs.com/test/postshare"
          class="bg-transparent w-full text-sm text-gray-700 focus:outline-none"
        >
      </div>
      <button id="copyButton" class="ml-3 p-2">
        <img src="../assets/img/copy.png" alt="Copy" class="h-5 w-5">
      </button>
    </div>

    <!-- Share Buttons -->
    <div class="grid grid-cols-3 sm:grid-cols-4 gap-4">
      <a href="#" id="sharePinterest" target="_blank" class="flex flex-col items-center text-sm text-gray-700 hover:text-purple-600">
        <img src="../assets/img/pinterest.png" alt="Pinterest" class="h-8 w-8 object-contain mb-1">
        <span>Pinterest</span>
      </a>
      <a href="#" id="shareFacebook" target="_blank" class="flex flex-col items-center text-sm text-gray-700 hover:text-purple-600">
        <img src="../assets/img/facebook1.png" alt="Facebook" class="h-8 w-8 object-contain mb-1">
        <span>Facebook</span>
      </a>
      <a href="#" id="shareReddit" target="_blank" class="flex flex-col items-center text-sm text-gray-700 hover:text-purple-600">
        <img src="../assets/img/reddit.png" alt="Reddit" class="h-8 w-8 object-contain mb-1">
        <span>Reddit</span>
      </a>
      <a href="#" id="shareTwitter" target="_blank" class="flex flex-col items-center text-sm text-gray-700 hover:text-purple-600">
        <img src="../assets/img/twitter.png" alt="Twitter" class="h-8 w-8 object-contain mb-1">
        <span>Twitter</span>
      </a>
      <a href="#" id="shareWhatsApp" target="_blank" class="flex flex-col items-center text-sm text-gray-700 hover:text-purple-600">
        <img src="../assets/img/whatsapp-fill.png" alt="WhatsApp" class="h-8 w-8 object-contain mb-1">
        <span>WhatsApp</span>
      </a>
      <a href="#" id="shareLinkedIn" target="_blank" class="flex flex-col items-center text-sm text-gray-700 hover:text-purple-600">
        <img src="../assets/img/linkedin.png" alt="LinkedIn" class="h-8 w-8 object-contain mb-1">
        <span>LinkedIn</span>
      </a>
      <a href="#" id="shareEmail" target="_blank" class="flex flex-col items-center text-sm text-gray-700 hover:text-purple-600">
        <img src="../assets/img/mail1.png" alt="Email" class="h-8 w-8 object-contain mb-1">
        <span>Email</span>
      </a>
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