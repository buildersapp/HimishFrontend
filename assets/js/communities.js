/**
 * Leave Community.
*/
$(document).on("click", ".leave-community-fn", function () {
    var id = $(this).data('id');
    showConfirmationModal({
        text: `Do you really want to leave this Community ?`,
        confirmText: "Yes",
        cancelText: "No",
        onConfirm: () => {
            $.ajax({
                url: 'ajax.php?action=leave_community',
                type: 'POST',
                data: { id: id },
                success: function (response) {
                  try {
                    var jsonResponse = JSON.parse(response);
                    if (jsonResponse.success) {
                        location.reload();
                    }else{
                        $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
                    }
                  } catch (e) {
                    console.error("Invalid JSON response:", response);
                  }
                },
                error: function () {
                    console.log("Failed to leave community.");
                }
            });
        },
        onCancel: () => {
            console.log("Cancelled....");
        }
    });
});