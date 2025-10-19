$(document).ready(function () {

    // Handle file input change event
    $(document).on("change", ".comunity-image-upload-input", function (event) {
        let input = $(this);
        let index = input.attr("id").replace("imageUploadInput", "");
        let file = this.files[0];

        if (file) {
            if (!pixelarity.open(file, false, function (res, faces) {
                console.log("Faces detected:", faces);

                // Update image preview
                $("#previewImage" + index).attr("src", res).css({
                    height: "120px",
                    objectFit: "contain"
                });

                // Show preview wrapper & hide upload label
                $("#previewWrapper" + index).show();
                $("#imageUploadInput" + index).siblings("label").hide();

                // Save cropped image data in hidden field
                $("#croppedImage" + index).val(res);
            }, "jpg", 0.7, true)) {
                alert("Whoops! That is not an image!");
            }
        }
    });

    // Trigger file input when clicking on preview image
    $(document).on("click", ".preview-img", function () {
        let index = $(this).data("index");
        $("#imageUploadInput" + index).click();
    });

    // Remove image and reset preview
    $(document).on("click", ".remove-img", function () {
        let index = $(this).data("index");

        // Reset preview image
        $("#previewImage" + index).attr("src", "");
        $("#previewWrapper" + index).hide();

        // Show upload label again
        $("#imageUploadInput" + index).siblings("label").show();

        // Clear hidden input value
        $("#croppedImage" + index).val("");
        $("#imageUploadInput" + index).val(""); // Reset file input
    });
});