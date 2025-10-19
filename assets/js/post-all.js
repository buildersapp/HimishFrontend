function previewImage(event, imgId, textId) {
    var img = event.target.files[0];
    if (
        !pixelarity.open(
            img,
            false,
            function (res, faces) {
                //console.log("Faces detected:", faces);

                // Remove upload text
                //document.getElementById(textId).innerHTML = "";
                //document.getElementById("croppedImage").value = res;

                // Remove previous face rectangles
                document.querySelectorAll(".face").forEach((el) => el.remove());

                scanImage(res);

                // Draw face detection boxes (if any)
                faces.forEach((face) => {
                    let faceBox = document.createElement("div");
                    faceBox.className = "face";
                    faceBox.style.position = "absolute";
                    //faceBox.style.border = '2px solid red';
                    faceBox.style.height = face.height + "px";
                    faceBox.style.width = face.width + "px";
                    faceBox.style.top = previewImg.getBoundingClientRect().top + face.y + "px";
                    faceBox.style.left = previewImg.getBoundingClientRect().left + face.x + "px";

                    document.body.appendChild(faceBox);
                });
            },
            "jpg",
            0.7,
            true
        )
    ) {
        alert("Whoops! That is not an image!");
    }
}

function base64ToFile(base64, filename) {
    const arr = base64.split(',');
    const mime = arr[0].match(/:(.*?);/)[1];
    const bstr = atob(arr[1]);
    let n = bstr.length;
    const u8arr = new Uint8Array(n);
    
    while (n--) {
        u8arr[n] = bstr.charCodeAt(n);
    }

    return new File([u8arr], filename, { type: mime });
}

function scanImage(file) {
    let formData = new FormData();
    const fileA = base64ToFile(file, "uploaded-image.png");
    formData.append("image", fileA);
    let index = 0;
    $.ajax({
        url: localStorage.getItem("scanPostAllUrl"),
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        beforeSend: function () {
            window.ajaxScanCompleted = false;
            index = 0;

            const messages = [
                "Reading Company info",
                "Getting Company Details",
                "We got it..",
                "Finalizing..."
            ];
            
            // Open loader once
            openScreenLoaderScanCP(messages[index], true);

            // Update message only, no blinking
            window.scanTextInterval = setInterval(function () {
                if (!window.ajaxScanCompleted) {
                    index = (index + 1) % messages.length;
                    openScreenLoaderScanCP(messages[index]); // Just update message
                }
            }, 3000);
        },
        success: function (response) {
            // response =JSON.parse(response);
            console.log(response,"==========response")
            if (response) {
                if(response.listing){
                    $("#listing_data").val(JSON.stringify(response.listing));
                }

                if(response.post){
                    $("#post_data").val(JSON.stringify(response.post));
                }

                $("#formLoader").fadeIn(200);

                $(".postDetailsBody")[0].submit();
            }
        },
        complete: function (data) {
            window.ajaxScanCompleted = true;
            closeScreenLoader();
        },
        error: function (error) {
            closeScreenLoader();
            showConfirmationModal({
                text: "Our intake server is currently overloaded. Please try again later.",
                confirmText: "Report Issue",
                cancelText: "Go to Home",
                onConfirm: () => {
                    console.log("Reported!!");
                    $.ajax({
                        url: 'ajax.php?action=report_server_crash',
                        type: 'POST',
                        data: {},
                        success: function (response) {
                            try {
                                var jsonResponse = JSON.parse(response);
                                if (jsonResponse.success) {
                                    $.toastr.success(jsonResponse.message, {position: 'top-center',time: 5000});
                                    window.location.href = 'home.php';
                                }else{
                                    $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
                                }
                            } catch (e) {
                                console.error("Invalid JSON response:", response);
                            }
                        },
                        error: function () {
                            console.log("Failed to report.");
                        }
                    });
                },
                onCancel: () => {
                    window.location.href = 'home.php';
                }
            });
        },
    });
}

// openScreenLoaderScanCP
function openScreenLoaderScanCP(msg, isFirst = false) {
    if (isFirst) {
        HoldOn.open({
            theme: "sk-bounce",
            message: `<div id="scanLoaderContent" style="text-align: center;">
                        <img src="assets/img/load_image_loader.gif" width="80" />
                        <p id="scanLoaderMsg">${msg}</p>
                      </div>`
        });
    } else {
        const msgEl = document.getElementById("scanLoaderMsg");
        if (msgEl) msgEl.textContent = msg;
    }
}