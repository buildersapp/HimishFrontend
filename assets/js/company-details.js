
document.addEventListener("DOMContentLoaded", function () {
    var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
document.addEventListener("DOMContentLoaded", function () {
    const searchButton = document.getElementById("mobileSearchButton");
    const searchBox = document.getElementById("mobileNavSearchBox");

    searchButton.addEventListener("click", function () {
    if (
        searchBox.style.display === "none" ||
        searchBox.style.display === ""
    ) {
        searchBox.style.display = "block";
    } else {
        searchBox.style.display = "none";
    }
    });
});
document.addEventListener("DOMContentLoaded", function () {
    const tabButtons = document.querySelectorAll(".tab-com-button");
    const firstCom = document.querySelector(".first-com");
    const secondCom = document.querySelector(".second-com");

    const activeReq = getQueryParam('activeReq');

    // Utility to activate tab by index
    function activateTab(index) {
        tabButtons.forEach((btn, i) => {
            if (i === index) {
                btn.classList.add("post-card-litss-buttons-active");
                btn.classList.remove("post-card-litss-buttons");
            } else {
                btn.classList.remove("post-card-litss-buttons-active");
                btn.classList.add("post-card-litss-buttons");
            }
        });

        // Show/hide content
        firstCom.style.display = index === 0 ? "block" : "none";
        secondCom.style.display = index === 1 ? "block" : "none";
    }

    // Set click listeners for tab buttons
    tabButtons.forEach((tabButton, index) => {
        tabButton.addEventListener("click", function () {
            activateTab(index);
        });
    });

    // Trigger tab based on query param
    if (activeReq === '1') {
        activateTab(1);
    } else {
        activateTab(0);
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const boostButtons = document.querySelectorAll(".boosttype-card-item");
    const confirmButtonDiv = document.querySelector(
    ".choose-ads-type-bottom-button-off"
    );
    const confirmButton = confirmButtonDiv.querySelector("button");
    let selectedUrl = ""; // Store selected button URL

    boostButtons.forEach((button) => {
    button.addEventListener("click", function () {
        // Remove active class from all buttons
        boostButtons.forEach((btn) =>
            btn.classList.remove("boosttype-card-item-active")
            );

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

document.addEventListener("DOMContentLoaded", function () {
    // Select all buttons
    const navButtons = document.querySelectorAll(".nav-linkx-com");

    // Array of selectors for each tab panel
    const tabSelectors = [
    ".nav-linkx-com-tab-one",
    ".nav-linkx-com-tab-two",
    ".nav-linkx-com-tab-three",
    ".nav-linkx-com-tab-four"
    ];

    // Get tab panel elements based on the selectors
    const navTabs = tabSelectors.map(selector => document.querySelector(selector));

    // Function to switch tabs based on clicked button index.
    function switchNavTab(clickedIndex) {
        // Calculate pair index:
        // If a desktop button is clicked (indexes 0-3), pairIndex equals clickedIndex.
        // If a mobile button is clicked (indexes 4-7), pairIndex equals clickedIndex - 4.
        const pairIndex = clickedIndex < 4 ? clickedIndex : clickedIndex - 4;

        // Remove the active class from all buttons
        navButtons.forEach(btn => btn.classList.remove("nav-linkx-com-active"));

        // Add active class to both buttons in the pair:
        if (navButtons[pairIndex]) {
            navButtons[pairIndex].classList.add("nav-linkx-com-active");
        }
        if (navButtons[pairIndex + 4]) {
            navButtons[pairIndex + 4].classList.add("nav-linkx-com-active");
        }

        // Hide all tab panels
        navTabs.forEach(tab => {
            if (tab) {
            tab.style.display = "none";
            }
        });

        // Show the corresponding tab panel based on the pair index
        if (navTabs[pairIndex]) {
            navTabs[pairIndex].style.display = "block";
        }
    }

    // Attach click event listeners to all buttons
    navButtons.forEach((button, index) => {
        button.addEventListener("click", () => switchNavTab(index));
    });

    // Set default active state for the first pair (buttons 0 and 4) and show the first tab panel.
    const active = getQueryParam("active"); 
    switchNavTab(active ? parseInt(active) : 0);
});

// Select elements
const buttons = document.querySelectorAll(".connect-switch-button");
const [companyTab, contactTab] = document.querySelectorAll(
    ".connect-switch-tab-wrapper > div"
);

// Function to handle tab switching
// const switchTab = (index) => {
//     buttons.forEach((btn) =>
//     btn.classList.remove("connect-switch-button-active")
//     );
//     buttons[index].classList.add("connect-switch-button-active");

//     companyTab.style.display = index === 0 ? "block" : "none";
//     contactTab.style.display = index === 1 ? "block" : "none";
// };

// Add event listeners using forEach
// buttons.forEach((button, index) =>
//     button.addEventListener("click", () => switchTab(index))
// );

// Set initial active state
//switchTab(2);

// Rate it & Slick Slider
$(document).ready(function () {
    $(".rateit").rateit();

    $('.dashboard-cover-slider').slick({
        autoplay: true,
        autoplaySpeed: 3000,
        dots: false,
        arrows: true,
        infinite: true,
        speed: 500,
        fade: true,
        cssEase: 'linear'
    });
});

/**
 * Remove Company Member.
*/
$(document).on("click", "#removeCompanyMemberFn", function () {
    var id = $(this).data('id');
    showConfirmationModal({
        text: `Do you really want to remove this Member ?`,
        confirmText: "Remove",
        cancelText: "No",
        onConfirm: () => {
            $.ajax({
                url: 'ajax.php?action=remove_company_member',
                type: 'POST',
                data: { id: id },
                success: function (response) {
                  try {
                    var jsonResponse = JSON.parse(response);
                    if (jsonResponse.success) {
                        updateURLParams({ id: btoa(company_id), active: 2 });
                    }else{
                        $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
                    }
                  } catch (e) {
                    console.error("Invalid JSON response:", response);
                  }
                },
                error: function () {
                    console.log("Failed to remove company member.");
                }
            });
        },
        onCancel: () => {
            console.log("Cancelled....");
        }
    });
});

/**
 * Make Company Admin.
*/
$(document).on("click", "#makeCompanyAdminFn", function () {
    var company_id = $(this).data('company-id');
    var user_id = $(this).data('user-id');
    var is_admin = $(this).data('is-admin');
    var text = 'Do you really want to make this member as Admin ?';
    if(is_admin){
        text = 'Do you really want to remove this member as Admin ?';
    }
    showConfirmationModal({
        text: text,
        confirmText: "Yes",
        cancelText: "No",
        onConfirm: () => {
            $.ajax({
                url: 'ajax.php?action=make_company_admin',
                type: 'POST',
                data: { company_id: company_id, user_id: user_id },
                success: function (response) {
                  try {
                    var jsonResponse = JSON.parse(response);
                    if (jsonResponse.success) {
                        updateURLParams({ id: btoa(company_id), active: 2 });
                    }else{
                        $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
                    }
                  } catch (e) {
                    console.error("Invalid JSON response:", response);
                  }
                },
                error: function () {
                    console.log("Failed to make company admin.");
                }
            });
        },
        onCancel: () => {
            console.log("Cancelled....");
        }
    });
});

/**
 * Recommend Company Thanks.
*/
$(document).on("click", "#recommendCompanyThanks", function () {
    var id = $(this).data('id');
    var company_id = $(this).data('company-id');
    showConfirmationModal({
        text: `Do you really want to thank this Member ?`,
        confirmText: "Yes",
        cancelText: "No",
        onConfirm: () => {
            $.ajax({
                url: 'ajax.php?action=recommend_company_thanks',
                type: 'POST',
                data: { id: id },
                success: function (response) {
                  try {
                    var jsonResponse = JSON.parse(response);
                    if (jsonResponse.success) {
                        updateURLParams({ id: btoa(company_id), active: 3 });
                    }else{
                        $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
                    }
                  } catch (e) {
                    console.error("Invalid JSON response:", response);
                  }
                },
                error: function () {
                    console.log("Failed to remove company member.");
                }
            });
        },
        onCancel: () => {
            console.log("Cancelled....");
        }
    });
});

/**
 * Remove Showcase.
*/
$(document).on("click", ".del-showcase", function () {
    var id = $(this).data('id');
    var company_id = $(this).data('company-id');
    showConfirmationModal({
        text: `Do you really want to remove this showcase ?`,
        confirmText: "Remove",
        cancelText: "No",
        onConfirm: () => {
            window.location.href = 'delete-showcase.php?id=' + btoa(id) + '&company_id=' + btoa(company_id);
        },
        onCancel: () => {
            console.log("Cancelled....");
        }
    });
});