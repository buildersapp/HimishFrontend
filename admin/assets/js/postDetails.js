$(document).ready(function () {
    $('#community').select2({placeholder: "Select community", // Placeholder text
    allowClear: true,
    search:true
});

function base64Encode(input) {
    return btoa(input);
}

// Attach event listener for clicks on selected <li> items
$(document).on('click', '.select2-selection__choice', function (e) {
    // Get the database ID from data-select2-id
    const title = $(this).attr('title');

    const dataSelect2Id = $('#community option[value="' + title + '"]').data('id');

    if (dataSelect2Id) {
        // Encode the selected ID to Base64
        const encodedId = base64Encode(dataSelect2Id);
        
        // Construct the URL with the encoded ID
        const url = `community-details.php?id=${encodedId}`;
        
        // Open the URL in a new tab
        window.open(url, '_blank');
    }
});
    const uploadBtn = $('#uploadBtn');
    const imgUpload = $('#imgUpload');
    var id = imgUpload.attr('rel');
    // Trigger file input when the button is clicked
    uploadBtn.on('click', function () {
        imgUpload.click();
    });

    // AJAX upload on file selection
    imgUpload.on('change', function () {
            const file = this.files[0];
            if (!file) return; // Exit if no file selected
            console.log(file)
            const formData = new FormData();
            formData.append('media', file);
    
            // Perform AJAX upload
            $.ajax({
            url: "ajax.php?action=add_post_status&id="+id,
            type: 'POST',
            data:formData,
            contentType: false,
            processData: false,
            beforeSend: function() {
                openScreenLoader('Uploading Media. Do not refresh this page...');
            },
            success: function (response) {
                location.reload();
            },
            error: function(jqXHR) {
                try {
                    console.log('inner')
                    // Attempt to parse the response
                    var response = JSON.parse(jqXHR.responseText);
            
                    // Redirect if 401 Unauthorized
                    if (response.redirect) {
                        window.location.href = response.url;
                    } else if (response.message) {
                        // Show error message if available
                        alert("Error: " + response.message);
                    }
                } catch (e) {
                    // Handle cases where the response is not JSON
                    console.error("Unexpected server response:", jqXHR.responseText);
                    alert("An unexpected error occurred. Please try again later.");
                }
            },
            complete: function(data) {
                // Close loader
                closeScreenLoader();
            }
            });
        });

    // $('#category').on('change', function () {
    //     // Get the selected option
    //     const selectedOption = $(this).find(':selected'); // Get the currently selected option
    
    //     // Get the data-tab_label_option_posts attribute
    //     const tabLabelOptionPosts = selectedOption.data('tab_label_option_posts'); // Access the data-* attribute
    
    //     // Set the service field value
    //     //$('#service').val(tabLabelOptionPosts || ''); // Update the input field or set it to an empty string if null
    // });

    $('.btnSave').on('click', function () {
       // Create FormData object
        const formData = new FormData($('#myPostForm')[0]);

        // Append file if selected
        const file = $('#imageShow')[0].files[0];
        //console.log(formData,"======fiels")

        if (file) {
            console.log("File exists, appending to formData.");
            formData.append('image', file);
        }
    
        // Send the AJAX request
        $.ajax({
            url: "ajax.php?action=update_post",// The form's action attribute
            method: 'POST', // The form's method attribute
            data: formData, // Serialized form data
            processData: false,      // Prevent jQuery from serializing the FormData
            contentType: false, 
            beforeSend: function() {
                openScreenLoader('Updating process. Do not refresh this page...');
            },
            success: function (response) {
                if(formData.get('type') === '0'){
                    window.location.href = 'posts.php';
                }else if(formData.get('type') === '1'){
                    window.location.href = 'listings.php';
                }else if(formData.get('type') === '2' && formData.get('info') !==  null){
                    //console.log(formData.get('info'),formData.get('type'));
                    window.location.href = 'deals.php';
                }else {
                    window.location.href = 'deal-share.php';
                }
            },
            error: function(jqXHR) {
                try {
                    console.log('inner')
                    // Attempt to parse the response
                    var response = JSON.parse(jqXHR.responseText);
            
                    // Redirect if 401 Unauthorized
                    if (response.redirect) {
                        window.location.href = response.url;
                    } else if (response.message) {
                        // Show error message if available
                        alert("Error: " + response.message);
                    }
                } catch (e) {
                    // Handle cases where the response is not JSON
                    console.error("Unexpected server response:", e);
                    alert("An unexpected error occurred. Please try again later.");
                }
            },
            complete: function(data) {
                // Close loader
                closeScreenLoader();
            }
        });
    });

   
    
        
})