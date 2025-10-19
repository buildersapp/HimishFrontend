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
            device_type: 3,
        };

        $.ajax({
            url: '../ajax.php?action=adminLogin',
            type: 'POST',
            data: data,
            beforeSend: function () {
                $('#loginButton').prop('disabled', true).text('Logging in...');
                localStorage.setItem('gu_change_location',0);
                openScreenLoader('Logging in. Do not refresh this page...');
            },
            success: function (response) {
                var responseJsn = JSON.parse(response);
                if(responseJsn.success === 1){
                    var body = responseJsn.body;
                    if(body.account_type !== 3){
                        $.toastr.error('You are not authorized to access this page', {position: 'top-center',time: 5000});
                        closeScreenLoader();
                    }else{
                        handleResponse(response, 'dashboard.php');
                    }
                }else{
                    handleResponse(response);
                }
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
            var referral_code = '';

            var data = {
                name: name,
                email: email,
                password: password,
                timezone: timezone,
                referral_code: referral_code,
                device_token: 'web',
                device_type: 3,
            };

            localStorage.setItem('registerUser', JSON.stringify(data));

            $.ajax({
                url: '../ajax.php?action=emailVerification',
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
        var userOTP = document.getElementById('otp').value;

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
                account_type: 3
            };

            $.ajax({
                url: '../ajax.php?action=registerUser',
                type: 'POST',
                data: data,
                beforeSend: function () {
                    $('#registerFinalBtn').prop('disabled', true).text('Saving...');
                    localStorage.setItem('gu_change_location',0);
                    openScreenLoader('Registering . Do not refresh this page...');
                },
                success: function (response) {
                    handleResponse(response, 'dashboard.php');
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

    // logoutSalesRep
    $('#logoutSalesRep').on('click', function (e) {
        e.preventDefault(); // Prevent the default form submission
        showConfirmationModal({
            text: `Do you want to logout ?`,
            confirmText: "Yes",
            cancelText: "No",
            onConfirm: () => {
                $.ajax({
                    url: '../ajax.php?action=logoutSalesRep&isSalesRep=1',
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
                url: '../ajax.php?action=forgot_password',
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
        $.toastr.success(response.message + '! Hang on Redirecting..', {position: 'top-center',time: 5000});
        
        if(redirectPath){
            setTimeout(function() {
                window.location.href = redirectPath;
            }, 2000);
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