/**
 * Make all forms autocomplete off.
 */
(function() {
  // Function to disable autocomplete on forms and their inputs
  function disableAutocomplete() {
    document.querySelectorAll("form").forEach(form => {
      form.setAttribute("autocomplete", "off");

      // Also apply to inputs, selects, textareas
      form.querySelectorAll("input, select, textarea").forEach(el => {
        el.setAttribute("autocomplete", "off");

        // Optional: For stricter browser blocking (obfuscate name)
        if (!el.name || el.name === "") {
          el.name = "field_" + Math.random().toString(36).substring(2, 10);
        }
      });
    });
  }

  // Run once on load
  document.addEventListener("DOMContentLoaded", disableAutocomplete);

  // Observe future DOM changes
  const observer = new MutationObserver(disableAutocomplete);
  observer.observe(document.body, { childList: true, subtree: true });
})();

/**
 * Shows a dynamic confirmation modal using Tailwind CSS.
 */
function showConfirmationModal({ text, confirmText, cancelText, onConfirm, onCancel, isHtml = false }) {
  const modal = document.getElementById('confirmationModal');
  const confirmBtn = document.getElementById('confirmButton');
  const cancelBtn = document.getElementById('cancelButton');
  const confirmationText = document.getElementById('confirmationText');

  // Set modal text
  if (isHtml) {
    confirmationText.innerHTML = text || "Are you sure?";
    confirmBtn.innerHTML = confirmText || "Confirm";
    cancelBtn.innerHTML = cancelText || "Cancel";
  } else {
    confirmationText.innerText = text || "Are you sure?";
    confirmBtn.innerText = confirmText || "Confirm";
    cancelBtn.innerText = cancelText || "Cancel";
  }

  // Clone buttons to remove old event listeners
  const newConfirmBtn = confirmBtn.cloneNode(true);
  const newCancelBtn = cancelBtn.cloneNode(true);

  confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
  cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);

  // Add event listeners
  newConfirmBtn.addEventListener("click", () => {
    if (onConfirm) onConfirm();
    modal.classList.add('hidden');
  });

  newCancelBtn.addEventListener("click", () => {
    if (onCancel) onCancel();
    modal.classList.add('hidden');
  });

  // Show the modal
  modal.classList.remove('hidden');

  // Prevent closing on ESC or outside click (optional)
  document.addEventListener('keydown', escHandler);
  modal.addEventListener('click', outsideClickHandler);

  function escHandler(e) {
    if (e.key === 'Escape') {
      e.preventDefault();
    }
  }

  function outsideClickHandler(e) {
    if (e.target === modal) {
      e.stopPropagation(); // prevent closing
    }
  }

  // Clean up on hide
  function cleanup() {
    document.removeEventListener('keydown', escHandler);
    modal.removeEventListener('click', outsideClickHandler);
  }

  newCancelBtn.addEventListener('click', cleanup);
  newConfirmBtn.addEventListener('click', cleanup);
}

/**
 * Programmatically closes the confirmation modal.
 * Also removes any related event listeners.
 */
function closeConfirmationModal() {
  const modal = document.getElementById('confirmationModal');

  if (modal) {
    modal.classList.add('hidden');

    // Optional: remove event listeners added during show
    document.removeEventListener('keydown', escHandler);
    modal.removeEventListener('click', outsideClickHandler);
  }

  // Define handlers here so we can reference and remove them
  function escHandler(e) {
    if (e.key === 'Escape') {
      e.preventDefault();
    }
  }

  function outsideClickHandler(e) {
    if (e.target === modal) {
      e.stopPropagation();
    }
  }
}

/**
 * Google Autocomplete.
 */
function initAutoCompleteGoogle() {
  const addressInputs = document.querySelectorAll(".address-autocomplete");
  addressInputs.forEach((input) => {
    const autocomplete = new google.maps.places.Autocomplete(input, {
        types: [],
    });

    google.maps.event.addListener(autocomplete, "place_changed", function () {
        const place = autocomplete.getPlace();
        if (place.geometry) {
            const address = place.formatted_address;
            const lat = place.geometry.location.lat();
            const lng = place.geometry.location.lng();

            var city = '';
            var zipcode= '';
            var state = '';
            var country = '';
            var fullAddress = place.formatted_address;

            place.address_components.forEach((component) => {
                const types = component.types;

                if (types.includes('locality')) {
                    city = component.long_name;
                } else if (types.includes('sublocality_level_1')) {
                    city = component.long_name;
                } else if (types.includes('sublocality')) {
                    city = component.long_name;
                }

                if (types.includes("administrative_area_level_1")) {
                    state = component.short_name;
                }

                if (types.includes("postal_code")) {
                    zipcode = component.long_name;
                }

                // Get country (country)
                if (types.indexOf('country') !== -1) {
                    country = component.short_name;
                }
            });

            // Remove country name from formatted_address if the country is USA
            if (country.toLowerCase() === 'united states' || country.toLowerCase() === 'usa' || country.toLowerCase() === 'us') {
              fullAddress = fullAddress.substring(0, fullAddress.lastIndexOf(',')).trim();
            }

            // Set city and state in hidden inputs
            input.value = fullAddress;
            document.getElementById('city').value = city;
            document.getElementById('state').value = state;
            document.getElementById('country_code').value = country;
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
            document.getElementById('zipcode').value = zipcode;
        }
    });
  });
}

initAutoCompleteGoogle();

/**
 * Preview Media.
 */
function previewMedia(event, targetPrefix) {
  const fileInput = event.target;
  const file = fileInput.files[0];

  const previewImg = document.getElementById(`${targetPrefix}-img`);
  const previewVideo = document.getElementById(`${targetPrefix}-video`);
  const previewVideoCnt = document.getElementById(`${targetPrefix}-video-container`);
  const textHolder = document.getElementById(`${targetPrefix}-text`);
  const croppedInput = document.getElementById(`${targetPrefix}-cropped`);

  // Reset previews
  previewImg.style.display = 'none';
  previewVideo.style.display = 'none';
  previewVideoCnt.style.display = 'none';
  textHolder.innerHTML = '';

  if (!file) return;

  const reader = new FileReader();

  reader.onload = function (e) {
    const fileType = file.type.split('/')[0];

    if (fileType === 'image') {
      // Crop image using pixelarity
      if (!pixelarity.open(file, false, function (res, faces) {
        previewImg.src = res;
        previewImg.style.display = 'block';
        croppedInput.value = res;
      }, "jpg", 0.7, true)) {
        alert("Whoops! That is not an image!");
      }

    } else if (fileType === 'video') {
      previewVideo.src = e.target.result;
      previewVideo.style.display = 'block';
      previewVideoCnt.style.display = 'flex';
      croppedInput.value = ''; // Clear cropped image if a video is uploaded
    } else {
      alert("Unsupported file type.");
    }
  };

  reader.readAsDataURL(file);
}

/**
 * Function to format Phone.
 */
function formatPhoneNumberUSRC(value) {
    const cleaned = value.replace(/\D/g, "").substring(0, 10); // Only digits, max 10
    const match = cleaned.match(/^(\d{3})(\d{3})(\d{0,4})$/);
    if (match) {
        return `(${match[1]}) ${match[2]}-${match[3]}`;
    }
    return cleaned;
}

/**
 * Copy to Clipboard.
 */
function copyToClipboard(text) {
  if (!navigator.clipboard) {
      // Fallback for older browsers
      const textarea = document.createElement("textarea");
      textarea.value = text;
      textarea.style.position = "fixed";  // Avoid scrolling to bottom
      document.body.appendChild(textarea);
      textarea.focus();
      textarea.select();
      try {
          document.execCommand("copy");
          $.toastr.success('Copied!', {position: 'top-center',time: 2000});
      } catch (err) {
          $.toastr.error('Failed to copy!', {position: 'top-center',time: 2000});
      }
      document.body.removeChild(textarea);
  } else {
      navigator.clipboard.writeText(text).then(function () {
        $.toastr.success('Copied!', {position: 'top-center',time: 2000});
      }, function (err) {
        $.toastr.error('Failed to copy!', {position: 'top-center',time: 2000});
      });
  }
}

/**
 * Upcoming Modal Feature
 */
function upcomingFeatureToggleModal(show) {
  const modal = document.getElementById("upcomingFeatureModal");
  modal.classList.toggle("hidden", !show);
}

const initTriggers = () => {
  const triggers = document.querySelectorAll(".upcomingFeatureTrigger");
  if (!triggers.length) return; // ✅ no error if elements are missing

  const showModal = (e) => {
    e.preventDefault();
    if (typeof upcomingFeatureToggleModal === "function") {
      upcomingFeatureToggleModal(true);
    }
  };

  const interactionEvents = ["click", "focus"];

  triggers.forEach(trigger => {
    interactionEvents.forEach(eventName => {
      trigger.addEventListener(eventName, showModal, { once: true });
    });
  });
};

function initShowMore(containerSelector = ".show-more-container") {
    document.querySelectorAll(containerSelector).forEach(container => {
        const shortText = container.querySelector(".short-text");
        const fullText = container.querySelector(".full-text");
        const toggleBtn = container.querySelector(".toggle-btn");

        if (!toggleBtn) return;

        toggleBtn.addEventListener("click", () => {
            const isHidden = fullText.classList.contains("hidden");
            shortText.classList.toggle("hidden", isHidden);
            fullText.classList.toggle("hidden", !isHidden);
            toggleBtn.textContent = isHidden ? "Show Less" : "Show More";
        });
    });
}

(function () {
  // ✅ If DOM is already loaded, run immediately
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initTriggers);
  } else {
    initTriggers();
  }
})();

/**
 * Share Generate Link Fn.
*/
$(document).on("click", ".share-fn", function () {
    var id = $(this).data("id");
    var type = $(this).data("type");
    var meta = $(this).data("meta");
    var title = $(this).data("title");

    $.ajax({
        url: "../ajax.php?action=get_deep_link_wb&isSalesRep=1",
        type: "POST",
        data: { id: id, type: type, meta: meta },
        beforeSend: function () {
            openScreenLoader('Creating Link! Do not refresh this page...');
        },
        success: function (response) {
            try {
                response = JSON.parse(response);

                var shareLink = response.link;
                var shareText = response.text;

                // Populate input field with the generated link
                $("#linkInput").val(shareLink);

                // Set share URLs for different social media platforms
                $("#shareFacebook").attr("href", `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareLink)}&quote=${encodeURIComponent(shareText)}`);
                $("#sharePinterest").attr("href", `https://pinterest.com/pin/create/button/?url=${encodeURIComponent(shareLink)}&description=${encodeURIComponent(shareText)}`);
                $("#shareReddit").attr("href", `https://www.reddit.com/submit?url=${encodeURIComponent(shareLink)}&title=${encodeURIComponent(shareText)}`);
                $("#shareTwitter").attr("href", `https://twitter.com/intent/tweet?url=${encodeURIComponent(shareLink)}&text=${encodeURIComponent(shareText)}`);
                $("#shareWhatsApp").attr("href", `https://wa.me/?text=${encodeURIComponent(shareText + " \n\n " + shareLink)}`);
                $("#shareLinkedIn").attr("href", `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(shareLink)}&summary=${encodeURIComponent(shareText)}`);
                $("#shareEmail").attr("href", `mailto:?subject=Check out this amazing post!&body=${encodeURIComponent(shareText + " \n\n " + shareLink)}`);

                // Update modal title
                $("#shareName").text(title);

                // Show boost content if applicable
                if (meta === 'boost_post') {
                    $(".boostPost-Cont").removeClass("hidden").show();
                } else {
                    $(".boostPost-Cont").addClass("hidden").hide();
                }

                // Show the Tailwind modal
                document.getElementById('shareModal').classList.remove('hidden');

                // Copy link to clipboard (Share-FN)
                $("#copyButton").click(function () {
                    var copyText = $("#linkInput");
                    console.log(copyText);
                    copyText.select();
                    document.execCommand("copy");
                    $.toastr.success('Link Copied', {position: 'top-center',time: 5000});
                });

            } catch (e) {
                console.error("Error parsing JSON response:", e);
                alert("Something went wrong while creating the share link.");
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", error);
            alert("An error occurred while generating the share link.");
        },
        complete: function () {
            closeScreenLoader();
        }
    });
});

/**
 * Share With Link Fn.
*/
$(document).on("click", ".share-link-fn", function () {
    var shareLink = $(this).data("share-link");
    var shareText = $(this).data("title");
    var type = $(this).data("type");

    // Populate input field with the generated link
    $("#linkInput").val(shareLink);

    // Set share URLs for different social media platforms
    $("#shareFacebook").attr("href", `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareLink)}&quote=${encodeURIComponent(shareText)}`);
    $("#sharePinterest").attr("href", `https://pinterest.com/pin/create/button/?url=${encodeURIComponent(shareLink)}&description=${encodeURIComponent(shareText)}`);
    $("#shareReddit").attr("href", `https://www.reddit.com/submit?url=${encodeURIComponent(shareLink)}&title=${encodeURIComponent(shareText)}`);
    $("#shareTwitter").attr("href", `https://twitter.com/intent/tweet?url=${encodeURIComponent(shareLink)}&text=${encodeURIComponent(shareText)}`);
    $("#shareWhatsApp").attr("href", `https://wa.me/?text=${encodeURIComponent(shareText + " \n\n " + shareLink)}`);
    $("#shareLinkedIn").attr("href", `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(shareLink)}&summary=${encodeURIComponent(shareText)}`);
    $("#shareEmail").attr("href", `mailto:?subject=Check out this amazing post!&body=${encodeURIComponent(shareText + " \n\n " + shareLink)}`);

    // Update modal title
    $("#shareName").text(type);

    // Show the Tailwind modal
    document.getElementById('shareModal').classList.remove('hidden');

    // Copy link to clipboard (Share-FN)
    $("#copyButton").click(function () {
        var copyText = $("#linkInput");
        console.log(copyText);
        copyText.select();
        document.execCommand("copy");
        $.toastr.success('Link Copied', {position: 'top-center',time: 5000});
    });
});

function closeShareModal() {
  document.getElementById("shareModal").classList.add("hidden");
}

/**
 * Regenerate Referral Link Fn.
*/
$(document).on("click", ".regenerate-link-fn", function () {
    var id = $(this).data("link-id");

    $.ajax({
        url: "../ajax.php?action=sr_regenerate_link&isSalesRep=1",
        type: "POST",
        data: { id: id },
        beforeSend: function () {
            openScreenLoader('Regenerating Link! Do not refresh this page...');
        },
        success: function (response) {
            try {
               var jsonResponse = JSON.parse(response);
                if (jsonResponse.success) {
                  $.toastr.success(jsonResponse.message, {position: 'top-center',time: 2000});
                    setTimeout(function(){
                        location.reload();
                    },1500);
                }else{
                  $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
                }
            } catch (e) {
                console.error("Error parsing JSON response:", e);
                alert("Something went wrong while creating the referral link.");
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", error);
            alert("An error occurred while re-generating referral link.");
        },
        complete: function () {
            closeScreenLoader();
        }
    });
});

/**
 * Delete Template.
*/
$(document).on("click", ".delete-template-fn", function () {
    var id = $(this).data('id');
    var type = $(this).data('type');
    showConfirmationModal({
        text: `Do you really want to delete this Template ?`,
        confirmText: "Yes",
        cancelText: "No",
        onConfirm: () => {
            $.ajax({
                url: '../ajax.php?action=delete_template_sp&isSalesRep=1',
                type: 'POST',
                data: { id: id },
                success: function (response) {
                  try {
                    var jsonResponse = JSON.parse(response);
                    if (jsonResponse.success) {
                        $.toastr.success(jsonResponse.message, {position: 'top-center',time: 5000});
                        setTimeout(function(){
                          window.location.href = 'message-templates.php?type='+type;
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