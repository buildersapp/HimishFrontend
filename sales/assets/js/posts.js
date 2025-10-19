document.querySelectorAll("#post-links-filters .filter-btn").forEach(btn => {
    btn.addEventListener("click", function() {
        const type = this.getAttribute("data-type");

        // Get current URL
        const url = new URL(window.location.href);

        // Update the type parameter
        url.searchParams.set("type", type);

        // Redirect with new param
        window.location.href = url.toString();
    });
});

// Highlight active filter on page load
(function() {
    const urlParams = new URLSearchParams(window.location.search);
    let currentType = urlParams.get("type") || "all"; // default "all"

    document.querySelectorAll("#post-links-filters .filter-btn").forEach(btn => {
        if (btn.getAttribute("data-type") === currentType) {
            btn.classList.add("bg-primary", "text-white");
            btn.classList.remove("bg-gray-100", "text-gray-700");
        } else {
            btn.classList.add("bg-gray-100", "text-gray-700");
            btn.classList.remove("bg-primary", "text-white");
        }
    });
})();

/**
 * Delete Post Referral.
*/
$(document).on("click", ".delete-post-referral-fn", function () {
    var id = $(this).data('id');
    showConfirmationModal({
        text: `Do you really want to delete this link ?`,
        confirmText: "Yes",
        cancelText: "No",
        onConfirm: () => {
            $.ajax({
                url: '../ajax.php?action=delete_post_referral_sales&isSalesRep=1',
                type: 'POST',
                data: { link_id: id },
                success: function (response) {
                  try {
                    var jsonResponse = JSON.parse(response);
                    if (jsonResponse.success) {
                        $.toastr.success(jsonResponse.message, {position: 'top-center',time: 5000});
                        setTimeout(function(){
                          window.location.href = 'posts.php';
                        },2000);
                    }else{
                        $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
                    }
                  } catch (e) {
                    console.error("Invalid JSON response:", response);
                  }
                },
                error: function () {
                    console.log("Failed to delete post referral.");
                }
            });
        },
        onCancel: () => {
            console.log("Cancelled....");
        }
    });
});