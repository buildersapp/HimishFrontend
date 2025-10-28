document.addEventListener("DOMContentLoaded", function () {
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
  });
});

// Selecting necessary elements
const fileInput = document.getElementById('file-upload');
const photoPreview = document.querySelector('.uploaded-photo-preview');
const noPhotoText = document.querySelector('.uploaded-nophoto-text');
const deleteIcon = document.querySelector('.delete-photo-icon');

if(fileInput){
  fileInput.addEventListener('change', function () {
    const file = this.files[0];

    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        // Set uploaded image as background
        photoPreview.style.backgroundImage = `url(${e.target.result})`;
        photoPreview.style.backgroundSize = 'cover';
        photoPreview.style.backgroundPosition = 'center';
      };
      reader.readAsDataURL(file);

      noPhotoText.style.display = 'none';
      deleteIcon.style.background = '#fff';
    }
  });
}

if(deleteIcon){
  deleteIcon.addEventListener('click', function () {
    photoPreview.style.backgroundImage = 'none';

    noPhotoText.style.display = 'block';
    deleteIcon.style.background = 'transparent';

    fileInput.value = '';
  });
}

// Self-contained component
(function () {
  const container = document.querySelector(".stb-container");
  const body = document.body;

  // Adjust body padding
  function adjustPadding() {
    if (container && window.getComputedStyle(container).display !== "none") {
      body.style.paddingBottom = `${container.offsetHeight}px`;
    } else {
      body.style.paddingBottom = "0";
    }
  }

  // Offcanvas handling
  const offcanvas = document.getElementById("stbOffcanvas");
  if(offcanvas){
    const bsOffcanvas = new bootstrap.Offcanvas(offcanvas, {
      backdrop: true,
      scroll: false,
    });

  // Toggle offcanvas on button click
  document
    .querySelectorAll('[data-bs-target="#stbOffcanvas"]')
    .forEach((btn) => {
      btn.addEventListener("click", () => {
        if (offcanvas.classList.contains("show")) {
          bsOffcanvas.hide();
        } else {
          bsOffcanvas.show();
        }
      });
    });

    // Handle resize
    let resizeTimer;
    window.addEventListener("resize", () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
        adjustPadding();
        if (window.innerWidth >= 768) bsOffcanvas.hide();
      }, 100);
    });
  }

  adjustPadding();
})();
// mobile input

// get query param value
function getQueryParam(name) {
  const urlParams = new URLSearchParams(window.location.search);
  return urlParams.get(name);
}

// update url params
function updateURLParams(newParams) {
  let url = new URL(window.location.href);
  let params = new URLSearchParams(url.search);

  // Update or add new parameters
  Object.keys(newParams).forEach(key => {
      params.set(key, newParams[key]);
  });

  // Construct the new URL and navigate
  window.location.href = url.pathname + '?' + params.toString();
}

// Update URL params, optionally using a provided URL
function updateURLParamsWithHref(newParams, href = window.location.href) {
  let url = new URL(href);
  let params = new URLSearchParams(url.search);

  // Update or add only non-null/undefined parameters
  Object.keys(newParams).forEach(key => {
    const value = newParams[key];
    if (value !== undefined && value !== null) {
      params.set(key, value);
    }
  });

  // Construct the new URL
  const newUrl = url.origin + url.pathname + '?' + params.toString();

  // If using current location, navigate
  if (href === window.location.href) {
    window.location.href = newUrl;
  }

  return newUrl; // Return the updated URL either way
}

// Dynamic Confirmation Modal
function showConfirmationModal({ text, confirmText, cancelText, onConfirm, onCancel, isHtml = false }) {
  // Set the modal text
  document.getElementById("confirmationText").innerText = text || "Are you sure?";

  // Set button text
  if(!isHtml){
    document.getElementById("confirmButton").innerText = confirmText || "Confirm";
    document.getElementById("cancelButton").innerText = cancelText || "Cancel";
  }else{
    document.getElementById("confirmButton").innerHTML = confirmText || "Confirm";
    document.getElementById("cancelButton").innerHTML = cancelText || "Cancel";
  }

  // Remove previous event listeners
  const confirmButton = document.getElementById("confirmButton");
  const cancelButton = document.getElementById("cancelButton");

  confirmButton.replaceWith(confirmButton.cloneNode(true)); 
  cancelButton.replaceWith(cancelButton.cloneNode(true));

  const newConfirmButton = document.getElementById("confirmButton");
  const newCancelButton = document.getElementById("cancelButton");

  // Attach new event listeners
  newConfirmButton.addEventListener("click", () => {
      if (onConfirm) onConfirm();
      let modal = bootstrap.Modal.getInstance(document.getElementById('confirmationModal'));
      modal.hide();
  });

  newCancelButton.addEventListener("click", () => {
      if (onCancel) onCancel();
  });

  // Show the modal
  let modal = new bootstrap.Modal(document.getElementById('confirmationModal'), {
      backdrop: 'static', // Prevent closing on outside click
      keyboard: false // Prevent closing on ESC key
  });
  modal.show();
}

// Function to format phone number
function formatPhoneNumberUSRC(value) {
    const cleaned = value.replace(/\D/g, "").substring(0, 10); // Only digits, max 10
    const match = cleaned.match(/^(\d{3})(\d{3})(\d{0,4})$/);
    if (match) {
        return `(${match[1]}) ${match[2]}-${match[3]}`;
    }
    return cleaned;
}

function formatPhoneNumberCleanUS(input) {
    const cleaned = input.replace(/\D/g, '').substring(0, 10); // Keep only digits, max 10
    if (cleaned.length === 10) {
        return '+1' + cleaned;
    }
    return input; // fallback if not 10 digits
}

function removeIdAndReload() {
  const url = new URL(window.location.href);
  url.searchParams.delete('id');
  url.searchParams.delete('dt');
  url.searchParams.delete('ad_id');
  window.location.href = url.toString(); // Reload without 'id'
}


/*********
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 */

/**
 * Like Post.
*/
$(document).on("click", ".like-post-fn", function () {
  var guestLogin = $("#guestLogin").val();
  if(guestLogin === '1'){
      guestLoginModal();
      return false;
  }
  var button = $(this);
  var post_id = button.data("post-id");
  var isLiked = button.data("liked") == 1; // Convert to boolean
  var newType = isLiked ? 0 : 1; // Toggle like state
  var likeCountElement = button.find(".like-count");
  var currentLikes = parseInt(likeCountElement.text(), 10) || 0;

  $.ajax({
      url: 'ajax.php?action=like_post',
      type: 'POST',
      data: { post_id: post_id, type: newType },
      success: function (response) {
          try {
            var jsonResponse = JSON.parse(response);
            if (jsonResponse.success) {
              button.data("liked", newType);
              button.find("img").attr("src", "assets/img/" + (newType ? "lovefill.png" : "love.png"));
              if (newType) {
                likeCountElement.text(currentLikes + 1); // Increase count
              } else {
                likeCountElement.text(Math.max(0, currentLikes - 1)); // Decrease but not below 0
              }
            }else{
              $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
            }
          } catch (e) {
              console.error("Invalid JSON response:", response);
          }
      },
      error: function () {
          console.log("Failed to like post.");
      }
  });
});

/**
 * Fav Post.
*/
$(document).on("click", ".fav-post-fn", function () {
  var guestLogin = $("#guestLogin").val();
  if(guestLogin === '1'){
      guestLoginModal();
      return false;
  }
  var button = $(this);
  var post_id = button.data("post-id");
  var post_type = button.data("post-type");
  var isFavorited = button.data("favorited") == 1; // Convert to boolean
  var newType = isFavorited ? 0 : 1; // Toggle favorite state

  $.ajax({
      url: 'ajax.php?action=fav_post',
      type: 'POST',
      data: { post_id: post_id, type: newType, post_type: post_type },
      success: function (response) {
          try {
              var jsonResponse = JSON.parse(response);
              if (jsonResponse.success) {
                button.data("favorited", newType);
                button.find("img").attr("src", "assets/img/" + (newType ? "bookmarkfill.png" : "bookmarkBlack.png"));
              }else{
                $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
              }
          } catch (e) {
              console.error("Invalid JSON response:", response);
          }
      },
      error: function () {
          console.log("Failed to favorite post.");
      }
  });
});

/**
 * Fav Listing.
*/
$(document).on("click", ".fav-listing-fn", function () {
  var guestLogin = $("#guestLogin").val();
  if(guestLogin === '1'){
      guestLoginModal();
      return false;
  }
  var button = $(this);
  var post_id = button.data("post-id");
  var listing_id = button.data("listing-id");
  var type = button.data("type");
  var isFavorited = button.data("favorited") == 1; // Convert to boolean
  var newType = isFavorited ? 0 : 1; // Toggle favorite state

  $.ajax({
      url: 'ajax.php?action=fav_listing',
      type: 'POST',
      data: { post_id: post_id, api_type: newType, listing_id: listing_id, type: type },
      success: function (response) {
          try {
            var jsonResponse = JSON.parse(response);
            if (jsonResponse.success) {
              button.data("favorited", newType);
              button.find("img").attr("src", "assets/img/" + (newType ? "bookmarkfill.png" : "bookmarkBlack.png"));
            }else{
              $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
            }
          } catch (e) {
            console.error("Invalid JSON response:", response);
          }
      },
      error: function () {
          console.log("Failed to favorite post.");
      }
  });
});

/**
 * Show Listing Interest.
*/
$(document).on("click", ".show-listing-interest-fn", function () {
  var guestLogin = $("#guestLogin").val();
  if(guestLogin === '1'){
      guestLoginModal();
      return false;
  }
  var button = $(this);
  var post_id = button.data("post-id");

  // Show loader
  button.prop("disabled", true).html(`<span class="spinner-border spinner-border-sm me-2"></span>Submitting...`);

  $.ajax({
      url: 'ajax.php?action=show_listing_interest',
      type: 'POST',
      data: { post_id: post_id },
      success: function (response) {
        try {
          var jsonResponse = JSON.parse(response);
          if (jsonResponse.success) {
            $.toastr.success(jsonResponse.message, {position: 'top-center',time: 5000});
            setTimeout(function(){
              location.reload();
            },1000);
          }else{
            $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
          }
        } catch (e) {
            console.error("Invalid JSON response:", response);
        }
      },
      error: function () {
          console.log("Failed to show interest listing.");
      }
  });
});

/**
 * Report Post.
*/
$(document).on("click", "#reportPostFn", function () {

  var $btn = $(this); // cache button
  var originalHtml = $btn.html();

  // Show loader and disable button
  $btn.prop("disabled", true).html(`<span class="spinner-border spinner-border-sm me-2"></span>Submitting...`);

  var post_id = $('#report_post_id').val();
  var type = $('#report-type').val();
  var message = $('#report-message').val();
  var phone = $('#report-phone').val() || '';
  var email = $('#report-email').val() || '';
  var request_type = $('#request-type').val() || 0;


  if(type !== "delete_post"){
    var guestLogin = $("#guestLogin").val();
    if(guestLogin === '1'){
      $btn.prop("disabled", false).html(originalHtml);
      guestLoginModal();
      return false;
    }
  }

  $.ajax({
      url: 'ajax.php?action=report_post',
      type: 'POST',
      data: { post_id: post_id, type: type, message: message, phone: phone, email: email, request_type: request_type },
      success: function (response) {
        try {
          var jsonResponse = JSON.parse(response);
          if (jsonResponse.success) {
            // Close Bootstrap 5 Offcanvas
            var offcanvasElement = document.getElementById('reportPostOffcanvas'); // Change to your actual Offcanvas ID
            var offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
            if (offcanvas) {
              offcanvas.hide();
            }
          }else{
            $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
          }
        } catch (e) {
          console.error("Invalid JSON response:", response);
        }

        $btn.prop("disabled", false).html(originalHtml);
      },
      error: function () {
        console.log("Failed to report post.");
        $btn.prop("disabled", false).html(originalHtml);
        $.toastr.error("Failed to report post.", {position: 'top-center',time: 5000});
      }
  });
});

// Submit claim report
$(document).on("click", "#reportClaimPostFn", function () {
    var $btn = $(this);
    var originalHtml = $btn.html();

    // Show loader
    $btn.prop("disabled", true).html(`<span class="spinner-border spinner-border-sm me-2"></span>Submitting...`);

    // Form field values
    var post_id = $('#report_claim_post_id').val();
    var post_itype = $('#report_claim_post_type').val() || 0;
    var message = $('#report-claim-message').val();
    var communication = $('#preferred-communication').val();
    var phone = $('#report-claim-phone').val() || '';
    var email = $('#report-claim-email').val() || '';

    // Email validation function
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Frontend validation
    if (!message || !communication) {
        alert('Please fill all required fields.');
        $btn.prop("disabled", false).html(originalHtml);
        return;
    }

    if (communication === 'email') {
        if (!email) {
            $.toastr.error('Please enter  your email address.', { position: 'top-center', time: 5000 });
            $btn.prop("disabled", false).html(originalHtml);
            return;
        }
        if (!isValidEmail(email)) {
            $.toastr.error('Please enter a valid email address.', { position: 'top-center', time: 5000 });
            $btn.prop("disabled", false).html(originalHtml);
            return;
        }
    }

    if (communication === 'phone' && !phone) {
        $.toastr.error('Please enter your phone number.', { position: 'top-center', time: 5000 });
        $btn.prop("disabled", false).html(originalHtml);
        return;
    }

    // AJAX submit
    $.ajax({
        url: 'ajax.php?action=report_claim_post',
        type: 'POST',
        data: {
            post_id: post_id,
            message: message,
            communication: communication,
            phone: phone,
            sender: formatPhoneNumberCleanUS(phone),
            email: email,
            post_itype: post_itype
        },
        success: function (response) {
            try {
                var jsonResponse = JSON.parse(response);
                if (jsonResponse.success) {
                    $.toastr.success(jsonResponse.message, { position: 'top-center', time: 5000 });
                    setTimeout(function () {
                      var offcanvasElement = document.getElementById('reportClaimPostOffcanvas');
                      var offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
                      if (offcanvas) offcanvas.hide();
                    }, 2000);
                } else {
                    $.toastr.error(jsonResponse.message, { position: 'top-center', time: 5000 });
                }
            } catch (e) {
                console.error("Invalid JSON:", response);
            }
            $btn.prop("disabled", false).html(originalHtml);
        },
        error: function () {
            console.log("AJAX error.");
            $btn.prop("disabled", false).html(originalHtml);
        }
    });
});

/**
 * Claim Post.
*/
$(document).on("click", "#claimPostFn", function () {
  var guestLogin = $("#guestLogin").val();
  if(guestLogin === '1'){
      guestLoginModal();
      return false;
  }
  var type = $('#ad_cc_type').val();
  var post_id = $('#post_id').val();
  var company_id = $('#company_id').val();
  var show_username = $('#show_username').length 
    ? ($('#show_username').prop('checked') ? 1 : 0)
    : 0;
  var email = $('#email').val();

  $.ajax({
      url: 'ajax.php?action=claim_post',
      type: 'POST',
      data: { post_id: post_id, company_id: company_id, show_username: show_username, email: email, type: type },
      beforeSend: function() {
        openScreenLoader('Claiming Company. Do not refresh this page...');
      },
      success: function (response) {
        try {
          var jsonResponse = JSON.parse(response);
          if (jsonResponse.success) {
            if(post_id == 0){
              $.toastr.success(jsonResponse.message, {position: 'top-center',time: 5000});
              setTimeout(function() {
                location.reload();
              }, 2000);
            }else{
              // Close Bootstrap 5 Offcanvas
              var offcanvasElement = document.getElementById('createClaimOffcanvas'); // Change to your actual Offcanvas ID
              var offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
              if (offcanvas) {
                offcanvas.hide();
                $("#claimp"+post_id).text('Request Sent');
              }
            }
          }else{
            $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
          }
        } catch (e) {
          console.error("Invalid JSON response:", response);
        }
      },
      error: function () {
          console.log("Failed to claim post.");
      },
      complete: function(data) {
        // Close loader
        closeScreenLoader();
      }
  });
});

/**
 * Like Ads.
*/
$(document).on("click", ".like-ads-fn", function () {
  var guestLogin = $("#guestLogin").val();
  if(guestLogin === '1'){
      guestLoginModal();
      return false;
  }
  var button = $(this);
  var ads_id = button.data("ads-id");
  var ads_type = button.data("ads-type") || 0;
  var isLiked = button.data("liked") == 1; // Convert to boolean
  var newType = isLiked ? 0 : 1; // Toggle like state
  var likeCountElement = button.find(".like-count");
  var currentLikes = parseInt(likeCountElement.text(), 10) || 0;

  $.ajax({
      url: 'ajax.php?action=like_ads',
      type: 'POST',
      data: { ads_id: ads_id, type: newType, ads_type: ads_type },
      success: function (response) {
          try {
            var jsonResponse = JSON.parse(response);
            if (jsonResponse.success) {
              button.data("liked", newType);
              button.find("img").attr("src", "assets/img/" + (newType ? "lovefill.png" : "love.png"));
              if (newType) {
                likeCountElement.text(currentLikes + 1); // Increase count
              } else {
                likeCountElement.text(Math.max(0, currentLikes - 1)); // Decrease but not below 0
              }
            }else{
              $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
            }
          } catch (e) {
              console.error("Invalid JSON response:", response);
          }
      },
      error: function () {
          console.log("Failed to like ads.");
      }
  });
});

/**
 * Fav Ads.
*/
$(document).on("click", ".fav-ads-fn", function () {
  var guestLogin = $("#guestLogin").val();
  if(guestLogin === '1'){
      guestLoginModal();
      return false;
  }
  var button = $(this);
  var ad_id = button.data("ads-id");
  var isFavorited = button.data("favorited") == 1; // Convert to boolean
  var newType = isFavorited ? 0 : 1; // Toggle favorite state

  $.ajax({
      url: 'ajax.php?action=fav_ads',
      type: 'POST',
      data: { ad_id: ad_id, type: newType },
      success: function (response) {
          try {
              var jsonResponse = JSON.parse(response);
              if (jsonResponse.success) {
                button.data("favorited", newType);
                button.find("img").attr("src", "assets/img/" + (newType ? "bookmarkfill.png" : "bookmarkBlack.png"));
              }else{
                $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
              }
          } catch (e) {
              console.error("Invalid JSON response:", response);
          }
      },
      error: function () {
          console.log("Failed to favorite ads.");
      }
  });
});

/**
 * Global Search
*/
$(document).on("keyup", ".global-search-inp", function () {
  var searchValue = $(this).val();
  var radiusValue = $(this).data('radius');
  var typeValue = $(this).data('type');
  $(".clear-withoutSrc").show();

  if(searchValue === ""){
    $(".clear-withoutSrc").hide();
  }

  if (searchValue.length < 2) {
    typeValue === "qs" ? $(".global-search-dropdown-inp").hide() : $(".global-search-dropdown").hide();
    return;
  }

  $.ajax({
      url: 'ajax.php?action=get_search_suggestions',
      type: 'POST',
      data: { search: searchValue, radius: radiusValue },
      success: function (response) {
          try {
              var jsonResponse = JSON.parse(response);
              var dropdown = typeValue === "qs" ? $(".global-search-dropdown-inp") : $(".global-search-dropdown");
              dropdown.empty();

              jsonResponse.forEach(category => {

                  var categoryTitleText = category.title.replace(
                    new RegExp(searchValue, 'gi'),
                    match => `<strong>${match}</strong>`
                  );
              
                  var categoryTitle = `<div class="global-search-category">${categoryTitleText}</div>`;

                  var suggestionsList = category.suggestions.map(suggestion => {
                    // Highlight search key in title (case insensitive)
                    let title = suggestion.title.replace(
                        new RegExp(searchValue, 'gi'),
                        match => `<strong>${match}</strong>`
                    );
                
                    // Bold the last word of the title
                    const words = title.split(" ");
                    if (words.length > 0) {
                        const lastWord = words.pop();
                        words.push(`<strong>${lastWord}</strong>`);
                        title = words.join(" ");
                    }
                
                    return `
                        <div class="global-search-item" 
                             data-id="${suggestion.id}" 
                             data-ispost="${suggestion.isPost}" 
                             data-category="${category.category}" 
                             data-keyword="${suggestion.keyword}">
                            ${title}
                        </div>`;
                }).join("");                                

                  dropdown.append(categoryTitle + suggestionsList);
              });

              if (jsonResponse.length > 0) {
                  dropdown.show();
              } else {
                  dropdown.append('<div class="global-dropdown-item no-results">No results found</div>');
                  dropdown.show();
              }
          } catch (e) {
              console.error("Invalid JSON response:", response);
          }
      },
      error: function () {
          console.log("Failed to fetch search suggestions.");
      }
  });
});

// Handle click event for search suggestions
$(document).on("click", ".global-search-item", function () {
  var suggestionId = $(this).data("id");
  var isPost = $(this).data("ispost");
  var category = $(this).data("category");
  var keyword = $(this).data("keyword");

  // Base64 encode the ID
  var encodedId = btoa(suggestionId);

  if (isPost === 1) {
      // Open in a new tab
      if (category == 4) {
        window.location.href = `company-details.php?id=${encodedId}`;
      } else if (category == 5) {
        window.location.href = `ad-details.php?id=${encodedId}`;
      } else {
        window.location.href = `post-details.php?id=${encodedId}`;
      }
  } else {
    if(category == 2){
      window.location.href = `home.php?search=${keyword}&type=1`;
    }else if(category == 3){
      window.location.href = `home.php?search=${keyword}&type=2`;
    }else{
      window.location.href = `home.php?search=${keyword}&type=0`;
    }
  }
});

// clear-withoutSrc
$(document).on("click", ".clear-withoutSrc", function () {
  $(".global-search-inp").val('');
  $(".global-search-dropdown").hide();
  $(".global-search-dropdown-inp").hide();
  $(this).hide();
});

// Handle clear search functionality
$(document).on("click", ".clear-global-search", function () {
  const url = new URL(window.location.href);
  url.searchParams.delete('search'); // remove search param if input is empty
  window.location.href = url.toString();
});

// Handle search functionality
// $(document).on("click", ".manual-global-search", function () {
//   const searchValue = $('.global-search').val().trim();
//   const url = new URL(window.location.href);
//   if (searchValue) {
//     url.searchParams.set('search', searchValue); // add real search value
//   } else {
//     url.searchParams.delete('search'); // remove search param if input is empty
//   }
//   window.location.href = url.toString();
// });

$(document).on("click", ".manual-global-search", function () {
  triggerGlobalSearch();
});

$(document).on("keydown", ".global-search-inp", function (e) {
  if (e.key === "Enter" || e.keyCode === 13) {
    triggerGlobalSearch();
  }
});

function triggerGlobalSearch() {
  const searchValue = $('.global-search-inp').val().trim();
  const url = new URL(window.location.href);
  if (searchValue) {
    url.searchParams.set('search', searchValue);
  } else {
    url.searchParams.delete('search');
  }
  window.location.href = url.toString();
}

// get post comments
$(document).on("click", ".post-comments-fn", function () {
  var guestLogin = $("#guestLogin").val();
  if(guestLogin === '1'){
      guestLoginModal();
      return false;
  }
  var id = $(this).data('id');
  var title = $(this).data('title');
  $("#pc_post_id").val(id);
  $("#previewContComm").html($('#lcp-'+atob(id)).html());
  getPostComments({ page: 1, post_id : id, title: title });
});

// get ads comments
$(document).on("click", ".ads-comments-fn", function () {
  var guestLogin = $("#guestLogin").val();
  if(guestLogin === '1'){
      guestLoginModal();
      return false;
  }
  var id = $(this).data('id');
  var ads_type = $(this).data('ads-type') || 0;
  var title = $(this).data('title');
  $("#ac_post_id").val(id);
  $("#ac_post_type").val(ads_type);
  $("#previewContCommAds").html($('#lcp-'+atob(id)).html());
  getAdsComments({ page: 1, post_id : id, ads_type: ads_type, title: title });
});

/**
 * Post Comment
*/
$("#postCommentForm").submit(function (e) {
  e.preventDefault(); // Prevent default form submission
  
  let commentText = $("#commentInput").val().trim();
  let post_id = $("#pc_post_id").val().trim();
  if (commentText === "") {
      alert("Please enter a comment.");
      return;
  }

  $.ajax({
      url: 'ajax.php?action=post_comment',
      type: "POST",
      data: { comment: commentText, post_id: post_id },
      success: function (response) {
        $("#commentInput").val('');
        try {
          var jsonResponse = JSON.parse(response);
          if (jsonResponse.success) {
            getPostComments({ page: 1, post_id : post_id });
          }else{
            $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
          }
        } catch (e) {
          console.error("Invalid JSON response:", response);
        }
      },
      error: function () {
        console.log("Error submitting comment.");
      }
  });
});

/**
 * Ads Comment
*/
$("#adsCommentForm").submit(function (e) {
  e.preventDefault(); // Prevent default form submission
  
  let commentText = $("#commentInputAds").val().trim();
  let post_id = $("#ac_post_id").val().trim();
  let post_type = $("#ac_post_type").val().trim();
  if (commentText === "") {
    alert("Please enter a comment.");
    return;
  }

  $.ajax({
      url: 'ajax.php?action=add_ads_comment',
      type: "POST",
      data: { comment: commentText, post_id: post_id, post_type: post_type },
      success: function (response) {
        $("#commentInputAds").val('');
        try {
          var jsonResponse = JSON.parse(response);
          if (jsonResponse.success) {
            getAdsComments({ page: 1, post_id : post_id, ads_type: post_type });
          }else{
            $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
          }
        } catch (e) {
          console.error("Invalid JSON response:", response);
        }
      },
      error: function () {
        console.log("Error submitting comment.");
      }
  });
});

/**
 * Delete Post Comment.
*/
$(document).on("click", ".delete-pc-fn", function () {
  var button = $(this);
  var comment_id = button.data("id");
  $.ajax({
      url: 'ajax.php?action=delete_post_comment',
      type: 'POST',
      data: { comment_id: comment_id },
      success: function (response) {
          try {
              var jsonResponse = JSON.parse(response);
              if (jsonResponse.success) {
                $.toastr.success(jsonResponse.message, {position: 'top-center',time: 5000});
                $('#parntPc'+comment_id).remove();
              }else{
                $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
              }
          } catch (e) {
              console.error("Invalid JSON response:", response);
          }
      },
      error: function () {
          console.log("Failed to delete post comment.");
      }
  });
});

/**
 * Delete Ad Comment.
*/
$(document).on("click", ".delete-ac-fn", function () {
  var button = $(this);
  var comment_id = button.data("id");
  $.ajax({
      url: 'ajax.php?action=delete_ad_comment',
      type: 'POST',
      data: { comment_id: comment_id },
      success: function (response) {
          try {
              var jsonResponse = JSON.parse(response);
              if (jsonResponse.success) {
                $.toastr.success(jsonResponse.message, {position: 'top-center',time: 5000});
                $('#parntAc'+comment_id).remove();
              }else{
                $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
              }
          } catch (e) {
              console.error("Invalid JSON response:", response);
          }
      },
      error: function () {
          console.log("Failed to delete ad comment.");
      }
  });
});

/**
 * Delete Post.
*/
$(document).on("click", ".delete-pst-fn", function () {
  var button = $(this);
  var post_id = button.data("id");
  showConfirmationModal({
    text: "Do you want to delete this post ?",
    confirmText: "Yes",
    cancelText: "Cancel",
    onConfirm: () => {
      $.ajax({
        url: 'ajax.php?action=delete_post',
        type: 'POST',
        data: { post_id: post_id },
        success: function (response) {
            try {
                var jsonResponse = JSON.parse(response);
                if (jsonResponse.success) {
                  $.toastr.success(jsonResponse.message, {position: 'top-center',time: 5000});
                  location.reload();
                }else{
                  $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
                }
            } catch (e) {
                console.error("Invalid JSON response:", response);
            }
        },
        error: function () {
            console.log("Failed to delete post.");
        }
      });
    },
    onCancel: () => {
      console.log("Cancelled.");
    }
  });
});

/**
 * Recommend Company.
*/
$(document).on("click", ".company_recommend_fn", function () {

  var guestLogin = $("#guestLogin").val();
  if(guestLogin === '1'){
      guestLoginModal();
      return false;
  }
  var button = $(this);
  var company_id = button.attr("data-company-id");
  var post_container = button.attr("data-post-container");
  $.ajax({
      url: 'ajax.php?action=recommend_company',
      type: 'POST',
      data: { company_id: company_id },
      success: function (response) {
          try {
              var jsonResponse = JSON.parse(response);
              if (jsonResponse.success) {
                $.toastr.success(jsonResponse.message, {position: 'top-center',time: 5000});
                button.attr('disabled', true);
                button.css('opacity', '0.5');
                button.css('background', 'grey');
                if (post_container) {
                  const $container = $('#' + post_container);
                  const $recommendButton = $container.find('.action-rcmd-container');
                  const $imgElem = $recommendButton.find('img');
                  const $countElem = $recommendButton.find('.action-rcmd-count');
                  if ($recommendButton.length && $imgElem.length && $countElem.length) {
                    $imgElem.attr('src', 'assets/img/like-fill.png');

                    let currentCount = parseInt($countElem.text().replace(/\D/g, '')) || 0;
                    $countElem.text(currentCount + 1);
                  } else {
                    console.warn("One or more required elements are missing.");
                  }
                }

                getCompanyRecommends({company_id: company_id});
              }else{
                $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
              }
          } catch (e) {
              console.error("Invalid JSON response:", response);
          }
      },
      error: function () {
          console.log("Failed to delete post comment.");
      }
  });
});

// Close dropdown on clicking outside
$(document).on("click", function (event) {
  if (!$(event.target).closest(".search").length) {
      $(".global-search-dropdown").hide();
      $(".global-search-dropdown-inp").hide();
  }
});

/**
 * Send Company Follow Connection Request.
*/
$(document).on("click", ".follow-company-fn", function () {

  var guestLogin = $("#guestLogin").val();
  if(guestLogin === '1'){
      guestLoginModal();
      return false;
  }

  var company_id = $(this).data('company-id');
  var type = $(this).data('type');
  var text = 'Are you sure you want to follow this company?';
  if(type == 0){
      text = 'Are you sure you want to cancel your connection request?';
  }
  showConfirmationModal({
    text: text,
    confirmText: "Yes",
    cancelText: "No",
    onConfirm: () => {
      $.ajax({
        url: 'ajax.php?action=company_follow',
        type: 'POST',
        data: { company_id: company_id, type: type },
        success: function (response) {
          try {
            var jsonResponse = JSON.parse(response);
            if (jsonResponse.success) {
              if(type == 0){
                $("#cf"+company_id).attr('data-type',1);
                $("#cf"+company_id).text('Connect');
              }else{
                $("#cf"+company_id).attr('data-type',0);
                $("#cf"+company_id).text('Connection Request Sent');
                  setTimeout(function() {
                  location.reload();
                }, 500);
              }
            }else{
              $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
            }
          } catch (e) {
            console.error("Invalid JSON response:", response);
          }
        },
        error: function () {
            console.log("Failed to accept-reject association req.");
        }
      });
    },
    onCancel: () => {
        console.log("Cancelled....");
    }
  });
});

/**
 * Accept / Reject Connection Request.
*/
$(document).on("click", ".ar-cnt-fn", function () {
  var request_id = $(this).data('request-id');
  var type = $(this).data('type');
  var text = 'Are you sure you want to accept this request?';
  if(type == 2){
      text = 'Are you sure you want to reject this request?';
  }
  showConfirmationModal({
    text: text,
    confirmText: "Yes",
    cancelText: "No",
    onConfirm: () => {
      $.ajax({
        url: 'ajax.php?action=accept_reject_connection_request',
        type: 'POST',
        data: { request_id: request_id, type: type },
        success: function (response) {
          try {
            var jsonResponse = JSON.parse(response);
            if (jsonResponse.success) {
              $.toastr.success(jsonResponse.message, {position: 'top-center',time: 5000});
              location.reload();
            }else{
              $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
            }
          } catch (e) {
            console.error("Invalid JSON response:", response);
          }
        },
        error: function () {
            console.log("Failed to accept-reject association req.");
        }
      });
    },
    onCancel: () => {
        console.log("Cancelled....");
    }
  });
});

/**
 * Accept / Reject Connection Request.
*/
$(document).on("click", ".ar-pst-fn", function () {
  var request_id = $(this).data('request-id');
  var type = $(this).data('type');
  var text = 'Are you sure you want to accept this request?';
  if(type == 2){
      text = 'Are you sure you want to reject this request?';
  }
  showConfirmationModal({
    text: text,
    confirmText: "Yes",
    cancelText: "No",
    onConfirm: () => {
      $.ajax({
        url: 'ajax.php?action=accept_reject_post_request',
        type: 'POST',
        data: { request_id: request_id, type: type },
        success: function (response) {
          try {
            var jsonResponse = JSON.parse(response);
            if (jsonResponse.success) {
              $.toastr.success(jsonResponse.message, {position: 'top-center',time: 5000});
              location.reload();
            }else{
              $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
            }
          } catch (e) {
            console.error("Invalid JSON response:", response);
          }
        },
        error: function () {
            console.log("Failed to accept-reject post req.");
        }
      });
    },
    onCancel: () => {
        console.log("Cancelled....");
    }
  });
});

/**
 * Create Deal Modal
*/
$(document).on("click", ".createDealModalFn", function () {

  var guestLogin = $("#guestLogin").val();
  if(guestLogin === '1'){
      guestLoginModal();
      return false;
  }

  showConfirmationModal({
      text: "What you want to post ?",
      confirmText: "Deal (With Buy Link)",
      cancelText: "Deal Share",
      onConfirm: () => {
        window.location.href = 'create-deal.php';
      },
      onCancel: () => {
        window.location.href = 'create-deal-share.php';
      }
  });
});

/**
 * Boost Post Modal
*/
$(document).on("click", ".boostPostModalFn", function () {

  var guestLogin = $("#guestLogin").val();
  if(guestLogin === '1'){
    guestLoginModal();
    return false;
  }

  var post_id = $(this).data("post-id");
  var boost_expire = parseInt($(this).data("boost-expire")) || 0;

  var confirmTextBtn = `<b>Boost Post</b> <br><small class="txt-grey-small">Boosted posts will be visible on top for the duration of boost (*charges apply)</small>`;
  var confirmAction = 'boost-post.php?post_id=' + post_id;
  if (boost_expire > 0) {
    const expireDate = new Date(boost_expire * 1000);

    // Format date as DD-MM-YYYY
    const day = String(expireDate.getDate()).padStart(2, '0');
    const month = String(expireDate.getMonth() + 1).padStart(2, '0');
    const year = expireDate.getFullYear();
    const formattedDate = `${month}/${day}/${year}`;

    confirmTextBtn = `<b>Already Boosted</b> <br><small class="txt-grey-small">This post is already boosted.<br>Boost expires on <b>${formattedDate}</b></small>`;
    
    confirmAction = '#'; // No action
  }

  showConfirmationModal({
      text: "Boost Type",
      confirmText: confirmTextBtn,
      cancelText: `<b>Convert to AD</b> <br><small class="txt-grey-small">Convert post to full featured AD. Ads are bold and appear frequently to attract users (*charges apply)</small>`,
      onConfirm: () => {
        window.location.href = confirmAction;
      },
      onCancel: () => {
        window.location.href = 'create-ad.php?post_id='+ post_id;
      },
      isHtml: true
  });
});

/**
 * Fav Company.
*/
$(document).on("click", ".fav-company-fn", function () {

  var guestLogin = $("#guestLogin").val();
  if(guestLogin === '1'){
      guestLoginModal();
      return false;
  }

  var button = $(this);
  var company_id = button.data("company-id");
  var company_type = button.data("company-type");
  var isFavorited = button.data("favorited") == 1; // Convert to boolean
  var newType = isFavorited ? 0 : 1; // Toggle favorite state

  $.ajax({
      url: 'ajax.php?action=fav_company',
      type: 'POST',
      data: { company_id: company_id, type: newType, company_type: company_type },
      success: function (response) {
          try {
              var jsonResponse = JSON.parse(response);
              if (jsonResponse.success) {
                button.data("favorited", newType);
                button.find("img").attr("src", "assets/img/" + (newType ? "bookmarkfill.png" : "bookmarkBlack.png"));
              }else{
                $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
              }
          } catch (e) {
              console.error("Invalid JSON response:", response);
          }
      },
      error: function () {
          console.log("Failed to favorite post.");
      }
  });
});

/**
 * Accept / Reject Association Request.
*/
$(document).on("click", ".acceptRejectAssociationReq", function () {
  var company_id = $(this).data('company-id');
  var id = $(this).data('id');
  var status = $(this).data('status');
  var text = 'Are you sure you want to accept this request?';
  if(status == 2){
      text = 'Are you sure you want to reject this request?';
  }
  showConfirmationModal({
      text: text,
      confirmText: "Yes",
      cancelText: "No",
      onConfirm: () => {
          $.ajax({
              url: 'ajax.php?action=accept_reject_association_request',
              type: 'POST',
              data: { id: id, status: status },
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
                  console.log("Failed to accept-reject association req.");
              }
          });
      },
      onCancel: () => {
          console.log("Cancelled....");
      }
  });
});

/**
 * Accept / Reject Community Request.
*/
$(document).on("click", ".acceptRejectCommunityReq", function () {
  var community_id = $(this).data('community-id');
  var id = $(this).data('id');
  var status = $(this).data('status');
  var text = 'Are you sure you want to accept this request?';
  if(status == 2){
      text = 'Are you sure you want to reject this request?';
  }
  showConfirmationModal({
      text: text,
      confirmText: "Yes",
      cancelText: "No",
      onConfirm: () => {
          $.ajax({
              url: 'ajax.php?action=accept_reject_community_request',
              type: 'POST',
              data: { id: id, status: status },
              success: function (response) {
                try {
                  var jsonResponse = JSON.parse(response);
                  if (jsonResponse.success) {
                    updateURLParamsWithHref({ id: btoa(community_id), active: 2 },'community-details.php');
                  }else{
                      $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
                  }
                } catch (e) {
                  console.error("Invalid JSON response:", response);
                }
              },
              error: function () {
                  console.log("Failed to accept-reject association req.");
              }
          });
      },
      onCancel: () => {
          console.log("Cancelled....");
      }
  });
});

document.addEventListener('DOMContentLoaded', function () {
  const websiteInputs = document.querySelectorAll('.auto-prepend-https');

  websiteInputs.forEach(function (input) {

    // Clean up any duplicated https://
    function normalizeHttps(val) {
      // Remove all existing "http://" or "https://" occurrences
      val = val.replace(/^https?:\/\//gi, '');
      // Always prepend exactly one "https://"
      return 'https://' + val;
    }

    // Auto prepend or clean up on focus
    input.addEventListener('focus', function (event) {
      input.value = normalizeHttps(input.value.trim());
      event.preventDefault();
      event.stopPropagation();
    });

    // Clean pasted content to ensure single https://
    input.addEventListener('paste', function (event) {
      event.preventDefault();
      const pastedText = (event.clipboardData || window.clipboardData).getData('text');
      input.value = normalizeHttps(pastedText.trim());
    });

    // Clean up manually typed "https://" duplication in real time
    input.addEventListener('input', function () {
      // Only fix if user tries to type another https://
      if ((input.value.match(/https?:\/\//gi) || []).length > 1) {
        input.value = normalizeHttps(input.value);
      }
    });
  });
});

var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
tooltipTriggerList.map(function(tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
});