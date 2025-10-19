document.addEventListener("DOMContentLoaded", function () {
    const tabButtons = document.querySelectorAll(".tab-com-button");
    const firstCom = document.querySelector(".first-com");
    const secondCom = document.querySelector(".second-com");

    tabButtons.forEach((tabButton, index) => {
        tabButton.addEventListener("click", function () {
            // Remove active class from all buttons & add default class back
            tabButtons.forEach((btn) => {
                btn.classList.remove("post-card-litss-buttons-active");
                btn.classList.add("post-card-litss-buttons"); // Add default class
            });

            // Add active class to the clicked button & remove default class
            this.classList.add("post-card-litss-buttons-active");
            this.classList.remove("post-card-litss-buttons");

            // Toggle divs based on active button
            if (index === 0) {
                firstCom.style.display = "block";
                secondCom.style.display = "none";
            } else if (index === 1) {
                firstCom.style.display = "none";
                secondCom.style.display = "block";
            }
        });
    });

    // Set default state on page load
    tabButtons[0].classList.add("post-card-litss-buttons-active");
    tabButtons[1].classList.add("post-card-litss-buttons");
    firstCom.style.display = "block";
    secondCom.style.display = "none";
});

document.addEventListener("DOMContentLoaded", function () {
    const boostButtons = document.querySelectorAll(".boosttype-card-item");
    const confirmButtonDiv = document.querySelector(".choose-ads-type-bottom-button-off");
    const confirmButton = confirmButtonDiv.querySelector("button");
    let selectedUrl = ""; // Store selected button URL

    boostButtons.forEach(button => {
        button.addEventListener("click", function () {
            // Remove active class from all buttons
            boostButtons.forEach(btn => btn.classList.remove("boosttype-card-item-active"));

            // Add active class to the clicked button
            this.classList.add("boosttype-card-item-active");

            // Show confirm button
            confirmButtonDiv.classList.remove("hidden");

            // Store the selected URL
            selectedUrl = this.getAttribute("data-url");
        });
    });

    // Handle confirm button click
    confirmButton.addEventListener("click", function () {
        if (selectedUrl) {
            window.location.href = selectedUrl; // Redirect to selected URL
        }
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
                url: 'ajax.php?action=delete_community',
                type: 'POST',
                data: { id: id },
                success: function (response) {
                  try {
                    var jsonResponse = JSON.parse(response);
                    if (jsonResponse.success) {
                        window.location.href = 'communities.php';
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