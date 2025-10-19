/*******
 * 
 * **************************************************************************
 * **************************************************************************
 * **************************************************************************
 * **************************************************************************
 * 
 * 
 *******/


$(document).ready(function () {

    // loginUser
    $('#loginUser').on('submit', function (e) {
        e.preventDefault(); // Prevent the default form submission

        var email = $('#email').val();
        var password = $('#password').val();
        var timezone = $('#timezone').val();

        var data = {
            email: email,
            password: password,
            timezone: timezone,
            device_token: 'web',
            device_type: 3
        };

        $.ajax({
            url: 'ajax.php?action=loginUser',
            type: 'POST',
            data: data,
            beforeSend: function () {
                $('#loginButton').prop('disabled', true).text('Logging in...');
                localStorage.setItem('gu_change_location',0);
                openScreenLoader('Logging in. Do not refresh this page...');
            },
            success: function (response) {
                handleResponse(response, 'home.php');
            },
            error: function (xhr, status, error) {
                console.error("Error: " + error);
                closeScreenLoader();
            },
            complete: function () {
                $('#loginButton').prop('disabled', false).text('Login');
                closeScreenLoader();
            }
        });
    });

    // emailVerification
    $('#emailVerification').on('submit', function (e) {
        e.preventDefault(); // Prevent the default form submission

        // Validate the form with Parsley
        if ($(this).parsley().isValid()) {
            var name = $('#name').val();
            var email = $('#email').val();
            var password = $('#password').val();
            var timezone = $('#timezone').val();
            var referral_code = $('#referral_code').val();

            var data = {
                name: name,
                email: email,
                password: password,
                timezone: timezone,
                referral_code: referral_code,
                device_token: 'web',
                device_type: 3
            };

            localStorage.setItem('registerUser', JSON.stringify(data));

            $.ajax({
                url: 'ajax.php?action=emailVerification',
                type: 'POST',
                data: data,
                beforeSend: function () {
                    $('#registerButton').prop('disabled', true).text('Sending verification code...');
                    openScreenLoader('Sending Verification Code . Do not refresh this page...');
                },
                success: function (response) {
                    var RespJson = JSON.parse(response);
                    // // Process response after OTP is sent
                    localStorage.setItem('otpCode',RespJson.body.otp);
                    handleResponse(response, 'email-verification.php?em=' + btoa(email));
                },
                error: function (xhr, status, error) {
                    console.error("Error: " + error);
                    closeScreenLoader();
                },
                complete: function () {
                    $('#registerButton').prop('disabled', false).text('Loading...');
                    closeScreenLoader();
                }
            });
        } else {
            console.log('Form is invalid');
        }
    });

    // registerFinal
    $('#registerFinal').on('submit', function (e) {
        e.preventDefault(); // Prevent the default form submission
        var backendOTP = localStorage.getItem('otpCode');
        // Get all OTP input fields
        var otpInputs = document.querySelectorAll('input[name="otp[]"]');

        // Collect the values from the OTP fields
        var userOTP = Array.from(otpInputs).map(input => input.value).join('');
        // Validate the form with Parsley
        if (backendOTP == userOTP) {

            // Get user data from localStorage
            var userData = JSON.parse(localStorage.getItem('registerUser'));  // Get the user data from localStorage

            // Prepare data to send in the AJAX request
            var data = {
                name: userData.name,
                email: userData.email,
                password: userData.password,
                timezone: userData.timezone || 'America/New_York',
                referral_code: userData.referral_code,
                device_token: userData.device_token,
                device_type: userData.device_type,
            };

            $.ajax({
                url: 'ajax.php?action=registerUser',
                type: 'POST',
                data: data,
                beforeSend: function () {
                    $('#registerFinalBtn').prop('disabled', true).text('Saving...');
                    localStorage.setItem('gu_change_location',0);
                    openScreenLoader('Registering . Do not refresh this page...');
                },
                success: function (response) {
                    handleResponse(response, 'home.php');
                },
                error: function (xhr, status, error) {
                    console.error("Error: " + error);
                    closeScreenLoader();
                },
                complete: function () {
                    $('#registerFinalBtn').prop('disabled', false).text('Continue...');
                    closeScreenLoader();
                }
            });
        } else {
            $.toastr.error('Invalid OTP', {position: 'top-center',time: 5000});
        }
    });

    // logoutUser
    $('#logoutUser').on('click', function (e) {
        e.preventDefault(); // Prevent the default form submission
        showConfirmationModal({
            text: `Do you want to logout ?`,
            confirmText: "Yes",
            cancelText: "No",
            onConfirm: () => {
                $.ajax({
                    url: 'ajax.php?action=logoutUser',
                    type: 'PUT',
                    data: {},
                    beforeSend: function () {
                        openScreenLoader('Logging out. Do not refresh this page...');
                    },
                    success: function (response) {
                        localStorage.clear();
                        handleResponse(response, 'index.php');
                    },
                    error: function (xhr, status, error) {
                        console.error("Error: " + error);
                        closeScreenLoader();
                    },
                    complete: function () {
                        closeScreenLoader();
                    }
                });
            },
            onCancel: () => {
                console.log("Cancelled....");
            }
        });
    });

    // deleteAccountFn
    $('#deleteAccountFn').on('click', function (e) {
        e.preventDefault(); // Prevent the default form submission
        showConfirmationModal({
            text: `Are you sure you want to delete your account ?`,
            confirmText: "Yes",
            cancelText: "No",
            onConfirm: () => {
                $.ajax({
                    url: 'ajax.php?action=deleteAccount',
                    type: 'DELETE',
                    data: {},
                    beforeSend: function () {
                        openScreenLoader('Deleting account. Do not refresh this page...');
                    },
                    success: function (response) {
                        handleResponse(response, 'index.php');
                    },
                    error: function (xhr, status, error) {
                        console.error("Error: " + error);
                        closeScreenLoader();
                    },
                    complete: function () {
                        closeScreenLoader();
                    }
                });
            },
            onCancel: () => {
                console.log("Cancelled....");
            }
        });
    });

    // not associated with company , create ad
    $("#notAssociatedCompany").on('click', function (e) {
        showConfirmationModal({
            text: `Not Associated with a Company ?`,
            confirmText: "Connect to Existing Company",
            cancelText: "Create New Company",
            onConfirm: () => {
                window.location.href = 'create-ad.php';
            },
            onCancel: () => {
                window.location.href = 'create-company.php?redirect_path=ads';
            }
        });
    });
});

/*******
 * 
 * **************************************************************************
 * **************************************************************************
 * **************************************************************************
 * **************************************************************************
 * 
 * 
 *******/


// handleResponse
function handleResponse(response, redirectPath = '') {
    response = JSON.parse(response);
    
    // Check if response indicates success or failure
    if (response.success === 1) {
        
        // display toast
        $.toastr.success(response.message, {position: 'top-center',time: 5000});
        
        if(redirectPath){
            setTimeout(function() {
                window.location.href = redirectPath;
            }, 1000);
        }
    } else {
        // display toast
        $.toastr.error(response.message, {position: 'top-center',time: 5000});
    }
}

// openScreenLoader
function openScreenLoader (msg) {
    HoldOn.open({
        theme:"sk-bounce",
        message: msg,
    });
}

function validateFormWithHoldOn(formId, message, includeAdditionalValidation = false) {
    const form = document.getElementById(formId);

    const locationsValue = document.getElementById("selectedLocationsInput")?.value.trim();
    const communitiesValue = document.getElementById("selectedCommunitiesInput")?.value.trim();

    // Run Parsley validation
    if ($(form).parsley().validate()) {
        // Additional custom validation only if includeAdditionalValidation is true
        if (includeAdditionalValidation) {
            // Check if locations are selected
            if (!locationsValue) {
                alert("Please select at least one location.");
                HoldOn.close();
                return false;
            }

            // Check if communities are selected
            if (!communitiesValue) {
                alert("Please select at least one community.");
                HoldOn.close();
                return false;
            }
        }

        // Open the screen loader message
        openScreenLoader(message);
        return true;
    } else {
        HoldOn.close();
        return false;
    }
}

// Function to show the Bootstrap 5 spinner loader
function openAjaxLoader(container) {
    if ($('#screenLoader').length === 0) {
        $(container).append(`
            <div id="screenLoader" class="loader-overlay text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
    }
}

// Guest login modal
function guestLoginModal(){
    showConfirmationModal({
      text: `You are in Guest Mode. Please login to get full access of the application`,
      confirmText: "Login",
      cancelText: "Cancel",
      onConfirm: () => {
        window.location.href = 'login.php';
      },
      onCancel: () => {
        console.log('Cancelled')
      }
    });
}

function closeAjaxLoader() {
    $('#screenLoader').remove();
}

// openScreenLoader
function openScreenLoadeScan (msg) {
    HoldOn.open({
        theme:"sk-bounce",
        message: `<div style="text-align: center;">
                    <p>${msg}</p>
                    <button onclick="stopScan()" 
                        style="padding: 8px 12px; background: red; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        Stop Scan
                    </button>
                  </div>`

    });
}

function stopScan() {
    // Close the loader
    HoldOn.close();

    // Store stop status in localStorage
    localStorage.setItem("scanStatus", 1);

    console.log("Scan stopped and status updated in localStorage.");
}

// closeScreenLoader
function closeScreenLoader (msg) {
    HoldOn.close();
}

// auto complete
function initAutocomplete() {
    var input = document.getElementById('address');

    // var options = {
    //     types: ["(regions)"], // Limits results to cities and states
    // };

    var options = {
        types: [], // You can change types based on your requirements, e.g., ['geocode'] for more general searches
        //componentRestrictions: { country: 'us' }, // Optional: restrict to a specific country
    };

    // Create the Autocomplete object
    var autocomplete = new google.maps.places.Autocomplete(input, options);

    // Modify the autocomplete UI to remove "USA" from suggestions
    google.maps.event.addListener(autocomplete, 'place_changed', function () {
        var place = autocomplete.getPlace();

        if (place.geometry) {
            var lat = place.geometry.location.lat();
            var lng = place.geometry.location.lng();

            // Set the latitude and longitude in the hidden inputs
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;

            // Now extract city and state from the address_components
            var city = '';
            var state = '';
            var country = '';
            var fullAddress = place.formatted_address;

            for (var i = 0; i < place.address_components.length; i++) {
                var component = place.address_components[i];
                var types = component.types;

                // Get city (prefer locality, fallback to sublocality_level_1 or sublocality)
                if (types.includes('locality')) {
                    city = component.long_name;
                } else if (types.includes('sublocality_level_1')) {
                    city = component.long_name;
                } else if (types.includes('sublocality')) {
                    city = component.long_name;
                }

                // Get state (administrative_area_level_1)
                if (types.indexOf('administrative_area_level_1') !== -1) {
                    state = component.short_name;
                }

                // Get country (country)
                if (types.indexOf('country') !== -1) {
                    country = component.short_name;
                }
            }
            
            // Set city and state in hidden inputs
            document.getElementById('city').value = city;
            document.getElementById('state').value = state;
            document.getElementById('country_code').value = country;

            // Remove country name from formatted_address if the country is USA
            if (country.toLowerCase() === 'united states' || country.toLowerCase() === 'usa' || country.toLowerCase() === 'us') {
                fullAddress = fullAddress.substring(0, fullAddress.lastIndexOf(',')).trim();
            }

            input.value = fullAddress;
        }
    });
}

/**
 * Share Connection Fn.
*/
$(document).on("click", ".share-fn", function () {
    // var guestLogin = $("#guestLogin").val();
    // if(guestLogin === '1'){
    //     guestLoginModal();
    //     return false;
    // }
    var id = $(this).data("id");
    var type = $(this).data("type");
    var meta = $(this).data("meta");
    var title = $(this).data("title");

    $.ajax({
        url: "ajax.php?action=get_deep_link_wb",
        type: "POST",
        data: { id: id, type: type, meta: meta },
        beforeSend: function () {
            openScreenLoader('Creating Link! Do not refresh this page...');
        },
        success: function (response) {
            response = JSON.parse(response);

            var shareLink = response.link;
            $("#linkInput").val(shareLink);

            var shareText = response.text;

            // Assign share link to all social media buttons
            $("#shareFacebook").attr("href", `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareLink)}&quote=${encodeURIComponent(shareText)}`);
            $("#sharePinterest").attr("href", `https://pinterest.com/pin/create/button/?url=${encodeURIComponent(shareLink)}&description=${encodeURIComponent(shareText)}`);
            $("#shareReddit").attr("href", `https://www.reddit.com/submit?url=${encodeURIComponent(shareLink)}&title=${encodeURIComponent(shareText)}`);
            $("#shareTwitter").attr("href", `https://twitter.com/intent/tweet?url=${encodeURIComponent(shareLink)}&text=${encodeURIComponent(shareText)}`);
            $("#shareWhatsApp").attr("href", `https://wa.me/?text=${encodeURIComponent(shareText + " \n\n " + shareLink)}`);
            $("#shareLinkedIn").attr("href", `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(shareLink)}&summary=${encodeURIComponent(shareText)}`);
            $("#shareEmail").attr("href", `mailto:?subject=Check out this amazing post!&body=${encodeURIComponent(shareText + " \n\n " + shareLink)}`);

            $("#shareModal").modal("show");
            $("#shareName").text(title);

            if(meta === 'boost_post'){
                $(".boostPost-Cont").show();
            }else{
                $(".boostPost-Cont").hide();
            }
        },
        error: function (xhr, status, error) {
            console.error("Error: " + error);
        },
        complete: function () {
            closeScreenLoader(); // Always stop loader (success or error)
        }
    });
});

// Copy link to clipboard
$("#copyButton").click(function () {
    var copyText = $("#linkInput");
    copyText.select();
    document.execCommand("copy");
    $.toastr.success('Link Copied', {position: 'top-center',time: 5000});
});

// Forgot Password
$('#forgotPassword').on('submit', function (e) {
    e.preventDefault(); // Prevent the default form submission

    // Validate the form with Parsley
    if ($(this).parsley().isValid()) {
        var email = $('#email').val();
        
        var data = {
            email: email,
        };

        $.ajax({
            url: 'ajax.php?action=forgot_password',
            type: 'POST',
            data: data,
            beforeSend: function () {
                $('#forgotButton').prop('disabled', true).text('Sending forgot password link...');
                openScreenLoader('Sending Forgot Password Email . Do not refresh this page...');
            },
            success: function (response) {
                handleResponse(response, 'index.php');
            },
            error: function (xhr, status, error) {
                console.error("Error: " + error);
                closeScreenLoader();
            },
            complete: function () {
                $('#forgotButton').prop('disabled', false).text('Mail Sent');
                closeScreenLoader();
            }
        });
    } else {
        console.log('Form is invalid');
    }
});