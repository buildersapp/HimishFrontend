function selectLocType(type) {
    let locType = $('#loc_type');
    let addressInput = $('#address');
    let currentType = parseInt(locType.val());

    // If same button clicked again, deselect it
    if (currentType === type) {
        locType.val(0);
        addressInput.prop('disabled', false);
        $('#nationBtn').removeClass('btn-active-hover-cd');
        $('#worldBtn').removeClass('btn-active-hover-cd');
        return;
    }

    // Set new loc_type
    locType.val(type);
    addressInput.prop('disabled', true).val(''); // Disable and clear address

    // Toggle button classes
    if (type === 2) {
        $('#nationBtn').addClass('btn-active-hover-cd');
        $('#worldBtn').removeClass('btn-active-hover-cd');
    } else if (type === 1) {
        $('#worldBtn').addClass('btn-active-hover-cd');
        $('#nationBtn').removeClass('btn-active-hover-cd');
    }
}

function validateForm() {
    // Open the loader
    HoldOn.open({
        theme: "sk-bounce",
        message: "Your deal is being created..."
    });

    let address = document.getElementById("address").value;
    let latitude = document.getElementById("latitude").value;
    let longitude = document.getElementById("longitude").value;
    let anywhereChecked = document.getElementById("loc_type").value;

    // If "Anywhere" is not checked, ensure address, latitude, and longitude are provided
    if (anywhereChecked === '0' && (!address || !latitude || !longitude)) {
        // Show an error message with Toastr
        $.toastr.error('Please enter a location or select "Anywhere".', {position: 'top-center', time: 5000});

        // Close the loader if validation fails
        HoldOn.close();

        // Prevent form submission
        return false;
    }

    // Allow form submission
    return true;
}

function previewMedia(event) {
    const fileInput = event.target;
    const file = fileInput.files[0];

    if (file) {
        const previewImg = document.getElementById('previewImg');
        const previewVideo = document.getElementById('previewVideo');

        const reader = new FileReader();

        reader.onload = function (e) {
            const fileType = file.type.split('/')[0]; // Get file type (image/video)

            if (fileType === 'image') {

            if (!pixelarity.open(file, false, function (res, faces) {
                //console.log("Faces detected:", faces);

                // Set cropped image preview
                var previewImg = document.getElementById('previewImg');
                previewImg.src = res;
                previewImg.style.height = "200px";
                previewImg.style.width = "200px";
                previewImg.style.objectFit = "cover";
                previewImg.classList.remove('img-fluid');

                // Hide video preview
                previewVideo.style.display = 'none';

                // Remove upload text
                document.getElementById('textHd').innerHTML = '';
                document.getElementById('croppedImage').value = res;

                // Remove previous face rectangles
                document.querySelectorAll('.face').forEach(el => el.remove());

                // Draw face detection boxes (if any)
                faces.forEach(face => {
                    let faceBox = document.createElement('div');
                    faceBox.className = 'face';
                    faceBox.style.position = 'absolute';
                    //faceBox.style.border = '2px solid red';
                    faceBox.style.height = face.height + "px";
                    faceBox.style.width = face.width + "px";
                    faceBox.style.top = (previewImg.getBoundingClientRect().top + face.y) + "px";
                    faceBox.style.left = (previewImg.getBoundingClientRect().left + face.x) + "px";

                    document.body.appendChild(faceBox);
                });
            }, "jpg", 0.7, true)) {
                alert("Whoops! That is not an image!");
            }
            } else if (fileType === 'video') {
                // Show video preview (first frame thumbnail)
                previewVideo.src = e.target.result;
                previewVideo.style.display = 'block';
                previewVideo.style.height = '200px';
                previewVideo.style.width = '350px';
                previewVideo.controls = true; // Add controls for video playback

                // Hide image preview
                previewImg.style.display = 'none';
            }

            $('#textHd').html('');
            $(".uploadIconComunity").css("width", "auto");
        };

        reader.readAsDataURL(file);
    }
}