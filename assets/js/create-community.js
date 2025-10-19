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