// Pagination Variables
let currentPagePosts = 1;
let currentPageCompanies = 1;
let currentPageNotifications = 1;
let currentPagePostComments = 1;
let currentPageAdsComments = 1;
let isLoadingPosts = false;
let isLoadingNotifications = false;
let isLoadingCompanies = false;
let isLoadingPostComments = false;
let isLoadingAdsComments = false;
let currentLimit = 20; // Number of posts per request
let hasScrolledToPost = false;
const debugMode = false;

// Function to Get Posts
function getPosts({type = 0, page = currentPagePosts, limit = currentLimit, radius, search = '', user_id = 0, post_id = 0, sort = '', status = 1, container = "#posts-container", currentListingCategory = '' , latitude = '', longitude = '', country_code = '', ads_id = 0}) {
    if (isLoadingPosts) return; // Prevent the request if already loading
    isLoadingPosts = true;

    // Show loading indicator
    $("#loading").show();

    var postData = {
        type: type,
        page: page,
        limit: limit,
        radius: radius,
        search: search,
        user_id: user_id,
        post_id: post_id,
        ads_id: ads_id,
        sort: sort,
        status: status,
        currentListingCategory: currentListingCategory,
        latitude: latitude,
        longitude: longitude,
        country_code: country_code
    };

    $.ajax({
        url: 'ajax.php?action=get_posts',
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
                initFeaturedSlider();

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

// Function to Get Fav Posts
function getFavPosts({type = 0, user_id = 0}) {

    var guestLogin = $("#guestLogin").val();
    if(guestLogin === '1'){
        guestLoginModal();
        return false;
    }

    var postData = {
        type: type,
        user_id: user_id,
    };
    $.ajax({
        url: 'ajax.php?action=get_fav_posts',
        type: 'POST',
        data: postData,
        success: function (response) {
            if (response.trim() !== '') {
                $('#posts-saved-container').append(response);
            }
        },
        error: function () {
            console.log("Failed to load fav posts.");
        }
    });
}

// Function to Get Fav Listings
function getFavListings({type = 0, user_id = 0}) {

    var guestLogin = $("#guestLogin").val();
    if(guestLogin === '1'){
        guestLoginModal();
        return false;
    }

    var postData = {
        type: type,
        user_id: user_id,
    };
    $.ajax({
        url: 'ajax.php?action=get_fav_listings',
        type: 'POST',
        data: postData,
        success: function (response) {
            if (response.trim() !== '') {
                $('#posts-saved-container').append(response);
            }
        },
        error: function () {
            console.log("Failed to load fav posts.");
        }
    });
}

// Function to Get Company Posts
function getCompanyPosts({company_id = 0}) {

    var guestLogin = $("#guestLogin").val();
    if(guestLogin === '1'){
        guestLoginModal();
        return false;
    }

    var postData = {
        company_id: company_id,
    };
    $.ajax({
        url: 'ajax.php?action=get_company_posts',
        type: 'POST',
        data: postData,
        success: function (response) {
            if (response.trim() !== '') {
                $('#company-post-container').append(response);
            }
        },
        error: function () {
            console.log("Failed to load company posts.");
        }
    });
}

// Function to Get Ads
function getAds({ads_id = 0, user_id = 0, radius = 0, type = 1, container }) {

    var guestLogin = $("#guestLogin").val();
    if(guestLogin === '1'){
        guestLoginModal();
        return false;
    }

    $(container).html('');
    var postData = {
        ads_id: ads_id,
        user_id: user_id,
        radius: radius,
        type: type,
    };
    $.ajax({
        url: 'ajax.php?action=get_ads',
        type: 'POST',
        data: postData,
        beforeSend: function () {
            openAjaxLoader(container);
        },
        success: function (response) {
            if (response.trim() !== '') {
                $(container).append(response);
            }
        },
        error: function () {
            console.log("Failed to load ads.");
        },
        complete: function () {
            closeAjaxLoader(); // Always stop loader (success or error)
        }
    });
}

// Function to Get Connections
function getConnections({user_id = 0, type = 2, container }) {

    var guestLogin = $("#guestLogin").val();
    if(guestLogin === '1'){
        guestLoginModal();
        return false;
    }

    $(container).html('');

    $(".connectxn").removeClass("active");

    if (type === '0') {
        $("#out-connections-pills-tabk").addClass("active");
    } else if (type === '1') {
        $("#in-connections-pills-tabk").addClass("active");
    } else {
        $("#all-connections-pills-tabk").addClass("active");
    }

    var postData = {
        user_id: user_id,
        type: type,
    };
    $.ajax({
        url: 'ajax.php?action=get_connections',
        type: 'POST',
        data: postData,
        beforeSend: function () {
            openAjaxLoader(container);
        },
        success: function (response) {

            if (response.trim() !== '') {
                $(container).append(response);
            }
        },
        error: function () {
            console.log("Failed to load connections.");
        },
        complete: function () {
            closeAjaxLoader(); // Always stop loader (success or error)
        }
    });
}

// Function to Get Companies
function getCompanies({ page = currentPageCompanies, limit = currentLimit, radius, search = '', user_id = 0, company_id = 0, category_id = 0, product_service_id = 0, container = "#companies-container"}) {
    if (isLoadingCompanies) return; // Prevent the request if already loading
    isLoadingCompanies = true;

    // Show loading indicator
    $("#loading").show();
    $(container).fadeTo(200, 0.3);

    var companyData = {
        category_id: category_id,
        page: page,
        limit: limit,
        radius: radius,
        search: search,
        user_id: user_id,
        company_id: company_id,
        product_service_id: product_service_id,
    };

    $.ajax({
        url: 'ajax.php?action=get_companies',
        type: 'POST',
        data: companyData,
        success: function (response) {
            // Reset loading flag and hide the loading indicator
            isLoadingCompanies = false;
            $("#loading").hide();
            $(container).fadeTo(200, 1);

            if(companyData.page === 1){
                $(container).html('');
            }

            if (response.trim() !== '') {
                $(container).append(response); // Append new posts to the container
                currentPageCompanies++; // Increment page number for the next request
                companyData.page++;
                enableScrollPagination(companyData,container);
            } else {
                $(window).off("scroll"); // Stop scroll event if no more data
            }

            $(".rateit").rateit();
        },
        error: function () {
            console.log("Failed to load companies.");
            isLoadingCompanies = false; // Reset loading flag in case of error
            $("#loading").hide();
        }
    });
}

// Function to Get Notifications
function getNotifications({ page = currentPageNotifications, limit = currentLimit, container = "#notifications-container"}) {

    if (isLoadingNotifications) return; // Prevent the request if already loading
    isLoadingNotifications = true;

    // Show loading indicator
    $("#loading").show();

    var notiData = {
        page: page,
        limit: limit,
    };

    $.ajax({
        url: 'ajax.php?action=get_notifications',
        type: 'POST',
        data: notiData,
        beforeSend: function () {
            openAjaxLoader(container);
        },
        success: function (response) {
            // Reset loading flag and hide the loading indicator
            isLoadingNotifications = false;

            if (response.trim() !== '') {
                $(container).append(response); // Append new posts to the container
                currentPageNotifications++; // Increment page number for the next request
                notiData.page++;
                enableScrollPagination(notiData,container, true);

                const badgeContainer = document.getElementById('noti-badge-container');
                badgeContainer.innerHTML = '';

            } else {
                $(window).off("scroll"); // Stop scroll event if no more data
            }
        },
        error: function () {
            console.log("Failed to load notifications.");
            isLoadingNotifications = false; // Reset loading flag in case of error
            $("#loading").hide();
        },
        complete: function () {
            closeAjaxLoader(); // Always stop loader (success or error)
        }
    });
}

// Function to Get Post Comments
function getPostComments({ page = currentPagePostComments, limit = currentLimit, post_id = 0, container = "#pc-comments-container", title =  'Post'}) {

    var guestLogin = $("#guestLogin").val();
    if(guestLogin === '1'){
        guestLoginModal();
        return false;
    }

    if (isLoadingPostComments) return; // Prevent the request if already loading
    isLoadingPostComments = true;

    $("#pc_desc").text(title);

    // Show loading indicator
    $("#loading").show();

    var pcData = {
        page: page,
        limit: limit,
        post_id: post_id
    };

    $.ajax({
        url: 'ajax.php?action=get_post_comments',
        type: 'POST',
        data: pcData,
        beforeSend: function () {
            openAjaxLoader(container);
        },
        success: function (response) {
            // Reset loading flag and hide the loading indicator
            isLoadingPostComments = false;

            if(page === 1){

                $(container).html('');

                var offcanvasElement = document.getElementById('postCommentsOffcanvas');
                
                // Check if the offcanvas instance exists
                var offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
                
                if (!offcanvas) {
                    // If not initialized, create a new instance
                    offcanvas = new bootstrap.Offcanvas(offcanvasElement);
                }
                
                offcanvas.show();
            }

            if (response.trim() !== '') {             
                $(container).append(response); // Append new posts to the container
                currentPagePostComments++; // Increment page number for the next request
                pcData.page++;
                enableScrollPagination(pcData,container, true);
            } else {
                $(window).off("scroll"); // Stop scroll event if no more data
            }
        },
        error: function () {
            console.log("Failed to load comments.");
            isLoadingPostComments = false; // Reset loading flag in case of error
            $("#loading").hide();
        },
        complete: function () {
            closeAjaxLoader(); // Always stop loader (success or error)
        }
    });
}

// Function to Get Ads Comments
function getAdsComments({ page = currentPageAdsComments, limit = currentLimit, post_id = 0, container = "#ac-comments-container", title =  'Ads', ads_type =  0}) {

    var guestLogin = $("#guestLogin").val();
    if(guestLogin === '1'){
        guestLoginModal();
        return false;
    }

    if (isLoadingAdsComments) return; // Prevent the request if already loading
    isLoadingAdsComments = true;

    $("#ac_desc").text(title);

    // Show loading indicator
    $("#loading").show();

    var pcData = {
        page: page,
        limit: limit,
        post_id: post_id,
        ads_type: ads_type
    };

    $.ajax({
        url: 'ajax.php?action=get_ads_comments',
        type: 'POST',
        data: pcData,
        beforeSend: function () {
            openAjaxLoader(container);
        },
        success: function (response) {
            // Reset loading flag and hide the loading indicator
            isLoadingAdsComments = false;

            if(page === 1){

                $(container).html('');

                var offcanvasElement = document.getElementById('adsCommentsOffcanvas');
                
                // Check if the offcanvas instance exists
                var offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
                
                if (!offcanvas) {
                    // If not initialized, create a new instance
                    offcanvas = new bootstrap.Offcanvas(offcanvasElement);
                }
                
                offcanvas.show();
            }

            if (response.trim() !== '') {             
                $(container).append(response); // Append new posts to the container
                currentPageAdsComments++; // Increment page number for the next request
                pcData.page++;
                enableScrollPagination(pcData,container, true);
            } else {
                $(window).off("scroll"); // Stop scroll event if no more data
            }
        },
        error: function () {
            console.log("Failed to load ads scomments.");
            isLoadingAdsComments = false; // Reset loading flag in case of error
            $("#loading").hide();
        },
        complete: function () {
            closeAjaxLoader(); // Always stop loader (success or error)
        }
    });
}

// Function to Get Company Recommends
function getCompanyRecommends({ company_id = 0, company_name, total_recommends = 0, container = "#pc-recommends-container", i_recommend = 0, post_container = '' }) {

    var guestLogin = $("#guestLogin").val();
    if(guestLogin === '1'){
        guestLoginModal();
        return false;
    }

    $("#rm_company_txt").text(company_name);
    $("#rm_company_txt_n").text(company_name);
    $("#rm_company_count").text(total_recommends);
    $("#rm_company_btn_name").text(i_recommend !== '0' ? 'Recommended' : 'Recommend');
    if(i_recommend !== '0'){
        $("#recomdCmpy").attr('disabled', true);
        $("#recomdCmpy").css('opacity', '0.5');
        $("#recomdCmpy").css('background', 'grey');
    }else{
        $("#recomdCmpy").attr('data-company-id', company_id);
        $("#recomdCmpy").attr('data-post-container', post_container);
        $("#recomdCmpy").addClass('company_recommend_fn');
        $("#recomdCmpy").attr('disabled', false);
        $("#recomdCmpy").css('opacity', '1');
        $("#recomdCmpy").css('background', 'linear-gradient(90deg, rgba(70, 20, 202, 1) 0%, rgba(149, 77, 225, 1) 100%)');
    }

    var prData = {
        company_id: company_id,
        company_name: company_name
    };

    $.ajax({
        url: 'ajax.php?action=company_recommends',
        type: 'POST',
        data: prData,
        beforeSend: function () {
            openAjaxLoader(container);
        },
        success: function (response) {
            // Reset loading flag and hide the loading indicator
            isLoadingPostComments = false;

            $(container).html('');

            var offcanvasElement = document.getElementById('recommendationsOffcanvas');
            
            // Check if the offcanvas instance exists
            var offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
            
            if (!offcanvas) {
                // If not initialized, create a new instance
                offcanvas = new bootstrap.Offcanvas(offcanvasElement);
            }

            if (response.trim() !== '') {             
                $(container).append(response);
            }
                
            offcanvas.show();
        },
        error: function () {
            console.log("Failed to load company recommends.");
        },
        complete: function () {
            closeAjaxLoader(); // Always stop loader (success or error)
        }
    });
}

// Function to Get Fav companies
function getFavCompanies({type = 0, user_id = 0}) {
    // var postData = {
    //     type: type,
    //     user_id: user_id,
    // };

    var guestLogin = $("#guestLogin").val();
    if(guestLogin === '1'){
        guestLoginModal();
        return false;
    }

    $.ajax({
        url: 'ajax.php?action=get-fav-companies',
        type: 'GET',
        // data: postData,
        success: function (response) {
            console.log(response, 'reponseeeeeeeeeeeeeeeeeeeeeeeeee');
            if (response.trim() !== '') {
                $('#posts-saved-container').append(response);
            }
        },
        error: function () {
            console.log("Failed to load fav posts.");
        }
    });
}

// Function to Delete companies
function deleteCompanies(company_id) {

    var guestLogin = $("#guestLogin").val();
    if(guestLogin === '1'){
        guestLoginModal();
        return false;
    }

    var postData = {
        id: company_id,
    };

    showConfirmationModal({
        text: `Do you want to delete this company ?`,
        confirmText: "Yes",
        cancelText: "No",
        onConfirm: () => {
            $.ajax({
                url: 'ajax.php?action=delete-company',
                type: 'POST',
                data: postData,
                beforeSend: function () {
                    openScreenLoader('Deleting company. Do not refresh this page...');
                },
                success: function (response) {
                    location.reload();
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
}

// Function to Delete Ad
function deleteAds(ads_id) {

    var guestLogin = $("#guestLogin").val();
    if(guestLogin === '1'){
        guestLoginModal();
        return false;
    }

    var postData = {
        ads_id: ads_id,
    };

    showConfirmationModal({
        text: `Do you want to delete this ad ?`,
        confirmText: "Yes",
        cancelText: "No",
        onConfirm: () => {
            $.ajax({
                url: 'ajax.php?action=deleteSingleAds',
                type: 'POST',
                data: postData,
                beforeSend: function () {
                    openScreenLoader('Deleting ad. Do not refresh this page...');
                },
                success: function (response) {
                    //location.reload();
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
}

function scrollToAdFromQuery(retries = 5) {
  const urlParams = new URLSearchParams(window.location.search);
  const scrollTo = urlParams.get('scrollTo');

  if (debugMode) {
    console.log("[ScrollToPost] scrollTo param:", scrollTo);
    console.log("[ScrollToPost] hasScrolledToPost:", hasScrolledToPost);
  }

  if (scrollTo && !hasScrolledToPost) {
    const el = document.querySelector(`[data-scroll-ads="${scrollTo}"]`);

    if (el) {
      if (debugMode) {
        //console.log("[ScrollToPost] Element found:", el);
        console.log("[ScrollToPost] Scrolling to element...");
      }

      el.scrollIntoView({ behavior: "smooth", block: "start" });
      hasScrolledToPost = true;

      // ✅ Remove scrollTo param from URL
      const url = new URL(window.location);
      url.searchParams.delete("scrollTo");
      window.history.replaceState({}, document.title, url.pathname + url.search);
    } else if (retries > 0) {
      if (debugMode) {
        console.warn(`[ScrollToPost] Element not found. Retrying in 300ms... (${retries} retries left)`);
      }
      setTimeout(() => scrollToPostFromQuery(retries - 1), 300);
    } else {
      if (debugMode) {
        console.error("[ScrollToPost] Element not found after maximum retries.");
      }
    }
  }
}