document.querySelectorAll('#community-type-options input[type=radio]').forEach(radio => {
    radio.addEventListener('change', () => {
        // Hide all dots
        document.querySelectorAll('#community-type-options .dot').forEach(dot => 
          dot.classList.add('hidden')
        );

        // Show selected dot
        radio.closest('label').querySelector('.dot').classList.remove('hidden');

        // Update hidden field (0 = public, 1 = private)
        document.getElementById('is_private').value = radio.value === 'private' ? 1 : 0;
    });
});

/**
 * Delete Community.
*/
$(document).on("click", ".delete-community-fn", function () {
    var id = $(this).data('id');
    showConfirmationModal({
        text: `Do you really want to delete this Community ?`,
        confirmText: "Yes",
        cancelText: "No",
        onConfirm: () => {
            $.ajax({
                url: '../ajax.php?action=delete_community&isSalesRep=1',
                type: 'POST',
                data: { id: id },
                success: function (response) {
                  try {
                    var jsonResponse = JSON.parse(response);
                    if (jsonResponse.success) {
                        $.toastr.success(jsonResponse.message, {position: 'top-center',time: 5000});
                        setTimeout(function(){
                          window.location.href = 'communities.php';
                        },2000);
                    }else{
                        $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
                    }
                  } catch (e) {
                    console.error("Invalid JSON response:", response);
                  }
                },
                error: function () {
                    console.log("Failed to delete community.");
                }
            });
        },
        onCancel: () => {
            console.log("Cancelled....");
        }
    });
});

/******************************************* */

function validateCreateCommunity(form) {
    let errors = [];

    // ✅ 1. First run Parsley validation
    if (!$(form).parsley().validate()) {
        return false; // stop if Parsley fails
    }

    // ✅ 2. Custom validation
    const community_name = document.getElementById("community_name").value.trim();
    if (!community_name) {
        errors.push("Please enter community name.");
    }

    if (errors.length > 0) {
        errors.forEach(msg => {
            $.toastr.error(msg, {position: 'top-center',time: 3000});
        });
        return false; // prevent submit
    }

    // ✅ 3. If everything is valid, then show HoldOn
    HoldOn.open({
        theme: "sk-bounce",
        message: "Hold on, we're submitting your request...",
    });

    return true; // allow submit
}