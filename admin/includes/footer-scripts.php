<script type="application/javascript" src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js?v=<?= time() ?>"></script>
<script type="application/javascript" src="assets/js/custom.js?v=<?= time() ?>"></script>
<script src="assets/plugins/holdon/js/HoldOn.js?v=<?= time() ?>"></script>
<script type="application/javascript" src="assets/js/apiScripts.js?v=<?= time() ?>"></script>
<script src="assets/plugins/parsley/parsley.js?v=<?= time() ?>"></script>
<script src="assets/plugins/toaster/js/toastr.min.js?v1.0"></script>
<script src="assets/plugins/confirmation-popup/jquery.confirm.js?v=<?= time() ?>"></script>
<script type="application/javascript" src="assets/js/select2.js?v=<?= time() ?>"></script>
<script type="text/javascript" src="assets/plugins/cropper/pixelarity-face.js?v=<?= time() ?>"></script>
<!-- <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js?v=<?= time() ?>"></script> -->

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<?php
if (isset($_COOKIE['errorMsg'])) {
    echo "<script>
        $(document).ready(function() {
            $.toastr.error('" . addslashes($_COOKIE['errorMsg']) . "', {
                position: 'top-center',
                time: 5000
            });
        });
    </script>";
}

if (isset($_COOKIE['successMsg'])) {
    echo "<script>
        $(document).ready(function() {
            $.toastr.success('" . addslashes($_COOKIE['successMsg']) . "', {
                position: 'top-center',
                time: 5000
            });
        });
    </script>";
}


?>
