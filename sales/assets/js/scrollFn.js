// Pagination Variables
let currentPagePosts = 1;
let currentPagePostDropDown = 1;
let currentPageCommission = 1;
let currentPageMT = 1;
let isLoadingPosts = false;
let isLoadingPostsDropDown = false;
let isLoadingCommission = false;
let isLoadingMT = false;
let currentLimit = 20; // Number of posts per request
let scrollToAdFromQuery = false;
const debugMode = false;

// Function to Get Message Templates
function getMessageTemplates({ page = currentPageMT, limit = currentLimit, type = 0, container = "#posts-container", search = '', isMT = 0, filter = '' }) {
    if (isLoadingMT) return; // Prevent the request if already loading
    isLoadingMT = true;

    // Show loading indicator
    $("#loading").show();

    var mtData = {
        page: page,
        limit: limit,
        type: type,
        search: search,
        isMT : isMT,
        filter: filter
    };

    $.ajax({
        url: '../ajax.php?action=get_referral_template_sp&isSalesRep=1',
        type: 'POST',
        data: mtData,
        beforeSend: function () {
            openAjaxLoader(container);
        },
        success: function (response) {
            // Reset loading flag and hide the loading indicator
            isLoadingMT = false;
            $("#loading").hide();


            if(mtData.page === 1){
                $(container).html('');
            }

            if (response.trim() !== '') {
                $(container).append(response); // Append new posts to the container
                currentPagePosts++; // Increment page number for the next request
                mtData.page++;
                enableScrollPagination(mtData,container);
                initTriggers();
                initShowMore();

                setTimeout(scrollToAdFromQuery, 300);
            } else {
                $(window).off("scroll"); // Stop scroll event if no more data
            }
        },
        error: function () {
            console.log("Failed to load templates.");
            isLoadingMT = false; // Reset loading flag in case of error
            $("#loading").hide();
        },
        complete: function () {
            closeAjaxLoader(); // Always stop loader (success or error)
        }
    });
}

// Function to Get Commissions
function getCommissions({ page = currentPageCommission, limit = currentLimit, type = 0, container = "#posts-container" }) {
    if (isLoadingCommission) return; // Prevent the request if already loading
    isLoadingCommission = true;

    // Show loading indicator
    $("#loading").show();

    var commissionData = {
        page: page,
        limit: limit,
        type: type
    };

    $.ajax({
        url: '../ajax.php?action=get_commissions_sales&isSalesRep=1',
        type: 'POST',
        data: commissionData,
        beforeSend: function () {
            openAjaxLoader(container);
        },
        success: function (response) {
            // Reset loading flag and hide the loading indicator
            isLoadingCommission = false;
            $("#loading").hide();


            if(commissionData.page === 1){
                $(container).html('');
            }

            if (response.trim() !== '') {
                $(container).append(response); // Append new posts to the container
                currentPagePosts++; // Increment page number for the next request
                commissionData.page++;
                enableScrollPagination(commissionData,container);

                setTimeout(scrollToAdFromQuery, 300);
            } else {
                $(window).off("scroll"); // Stop scroll event if no more data
            }
        },
        error: function () {
            console.log("Failed to load commissions.");
            isLoadingCommission = false; // Reset loading flag in case of error
            $("#loading").hide();
        },
        complete: function () {
            closeAjaxLoader(); // Always stop loader (success or error)
        }
    });
}

// Function to Get Sale Links
function getSaleLinks({ page = currentPagePosts, limit = currentLimit, search = '', user_id = 0, status = 1, link_id=0, type = 'all', container = "#posts-container", link_type = '' }) {
    if (isLoadingPosts) return; // Prevent the request if already loading
    isLoadingPosts = true;

    // Show loading indicator
    $("#loading").show();

    var postData = {
        link_type: link_type,
        page: page,
        limit: limit,
        search: search,
        user_id: user_id,
        link_id: link_id,
        status: status,
        type: type
    };

    $.ajax({
        url: '../ajax.php?action=get_posts_sales&isSalesRep=1',
        type: 'POST',
        data: postData,
        beforeSend: function () {
            openAjaxLoader(container);
        },
        success: function (response) {
            // Reset loading flag and hide the loading indicator
            isLoadingPosts = false;
            $("#loading").hide();


            if(postData.page === 1){
                $(container).html('');
            }

            if (response.trim() !== '') {
                $(container).append(response); // Append new posts to the container
                currentPagePosts++; // Increment page number for the next request
                postData.page++;
                enableScrollPagination(postData,container);
                initTriggers();

                setTimeout(scrollToAdFromQuery, 300);
            } else {
                $(window).off("scroll"); // Stop scroll event if no more data
            }
        },
        error: function () {
            console.log("Failed to load posts.");
            isLoadingPosts = false; // Reset loading flag in case of error
            $("#loading").hide();
        },
        complete: function () {
            closeAjaxLoader(); // Always stop loader (success or error)
        }
    });
}

// Function to Get user Posts as dropdown
function getUserPostsDropdownList({ page = currentPagePostDropDown, limit = currentLimit, search = '', user_id = 0, container = "#posts-container" }) {
    if (isLoadingPostsDropDown) return; // Prevent the request if already loading
    isLoadingPostsDropDown = true;

    // Show loading indicator
    $("#loading").show();

    var postDataDrp = {
        page: page,
        limit: limit,
        search: search,
        user_id: user_id,
    };

    $.ajax({
        url: '../ajax.php?action=sp_get_user_posts_existing_dropdown&isSalesRep=1',
        type: 'POST',
        data: postDataDrp,
        beforeSend: function () {
            openAjaxLoader(container);
        },
        success: function (response) {
            // Reset loading flag and hide the loading indicator
            isLoadingPostsDropDown = false;
            $("#loading").hide();


            if(postDataDrp.page === 1){
                $(container).html('');
            }

            if (response.trim() !== '') {
                $(container).append(response); // Append new posts to the container
                currentPagePostDropDown++; // Increment page number for the next request
                postDataDrp.page++;
                enableScrollPagination(postDataDrp,container);

                const checkboxes = document.querySelectorAll(".post-selector");
                const hiddenField = document.getElementById("selectedPostId");

                checkboxes.forEach(cb => {
                    cb.addEventListener("change", () => {
                        if (cb.checked) {
                            // uncheck all others
                            checkboxes.forEach(other => {
                                if (other !== cb) other.checked = false;
                            });
                            // save selected ID in hidden field
                            hiddenField.value = cb.value;
                        } else {
                            // clear hidden field if none selected
                            hiddenField.value = "";
                        }
                    });
                });

                setTimeout(scrollToAdFromQuery, 300);
            } else {
                $(window).off("scroll"); // Stop scroll event if no more data
            }
        },
        error: function () {
            console.log("Failed to load user posts drpdwn.");
            isLoadingPostsDropDown = false; // Reset loading flag in case of error
            $("#loading").hide();
        },
        complete: function () {
            closeAjaxLoader(); // Always stop loader (success or error)
        }
    });
}

// Infinite Scroll Event Listener (For Both Window and Specific Divs)
function enableScrollPagination(postsData, containerDiv, isDivScroll = false) {
    let scrollElement = isDivScroll ? $(containerDiv + "-scroll") : $(window);
    scrollElement.on('scroll', function () {
        let container = $(containerDiv);
        let scrollContainer = $(window);
        if(isDivScroll){
            scrollContainer = $(containerDiv + "-scroll");
        }
        let containerHeight = container.height();
        let scrollPosition = isDivScroll
            ? scrollContainer.scrollTop() + scrollContainer.innerHeight()
            : $(window).scrollTop() + $(window).height();
        let containerBottom = container.offset().top + containerHeight;

        // Check if near the bottom (400px before)
        if (scrollPosition >= containerBottom - 400) {
            // If not already loading, make the request
            if (
                (containerDiv === "#posts-container" && !isLoadingPosts) ||
                (containerDiv === "#companies-container" && !isLoadingCompanies) ||
                (containerDiv === "#notifications-container" && !isLoadingNotifications) ||
                (containerDiv === "#pc-comments-container" && !isLoadingPostComments) ||
                (containerDiv === "#ac-comments-container" && !isLoadingAdsComments)
            ) {
                // Disable further scroll events to prevent multiple requests
                scrollElement.off('scroll');

                if (containerDiv === "#companies-container") {
                    getCompanies(postsData);
                } else if (containerDiv === "#notifications-container") {
                    getNotifications(postsData);
                } else if (containerDiv === "#pc-comments-container") {
                    getPostComments(postsData);
                } else if (containerDiv === "#ac-comments-container") {
                    getAdsComments(postsData);
                } else {
                    getPosts(postsData);
                }
            }
        }
    });
}