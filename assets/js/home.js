// Function to get user's location using geolocation
function getUserLocation(updateHd = false) {
    const isManual = localStorage.getItem('locationManuallySet') === '1';
    const locationElem = document.querySelector(".nav-location span");
    if (isManual) {
        //console.log('Manual location set by user. Skipping auto-location update.');
        return;
    }

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                let latitude = position.coords.latitude;
                let longitude = position.coords.longitude;

                // Save current coordinates
                localStorage.setItem('currentLatitude', latitude);
                localStorage.setItem('currentLongitude', longitude);

                // AJAX request to get API key
                $.ajax({
                    url: 'ajax.php?action=gt_gcd_ky', // Endpoint with action parameter
                    type: "GET",
                    success: function (response) {
                        try {
                            var jsonResponse = JSON.parse(response);
                            if (jsonResponse.success) {
                                const apiKey = jsonResponse.apiKey; // Assuming response has apiKey field
                                const geocodeUrl = `https://maps.googleapis.com/maps/api/geocode/json?latlng=${latitude},${longitude}&key=${apiKey}`;

                                fetch(geocodeUrl)
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.status === "OK" && data.results.length > 0) {
                                            let addressComponents = data.results[0].address_components;
                                            let address = data.results[0].formatted_address;

                                            let city = "";
                                            let state = "";
                                            let country = "";
                                            let country_code = "";

                                            for (let component of addressComponents) {
                                                if (component.types.includes("administrative_area_level_1")) state = component.short_name;
                                                if (component.types.includes("country")) {
                                                    country = component.long_name;
                                                    country_code = component.short_name;
                                                }
                                                if (!city && component.types.includes("locality")) city = component.long_name;
                                            }

                                            // Fallback for city
                                            if (!city) {
                                                for (let component of addressComponents) {
                                                    if (component.types.includes("sublocality_level_1") || component.types.includes("sublocality")) {
                                                        city = component.long_name;
                                                        break;
                                                    }
                                                }
                                            }

                                            const savedCity = localStorage.getItem('userCity');
                                            const savedState = localStorage.getItem('userState');
                                            const newCityState = `${city}, ${state}`;
                                            const savedCityState = `${savedCity}, ${savedState}`;

                                            // Compare and update only if location has changed
                                            if (newCityState !== savedCityState) {
                                                updateUserLocationInDB({
                                                    latitude,
                                                    longitude,
                                                    city,
                                                    state,
                                                    address,
                                                    country_code
                                                }, newCityState);
                                            }

                                            // Optionally update header
                                            if (updateHd) {
                                                if (locationElem) {
                                                    locationElem.innerText = newCityState;
                                                }
                                            }

                                            // Store current detected location
                                            localStorage.setItem('currentCity', city);
                                            localStorage.setItem('currentState', state);
                                            localStorage.setItem('currentAddress', address);
                                            localStorage.setItem('currentCountry', country);
                                            localStorage.setItem('currentCountryCode', country_code);
                                        } else {
                                            console.log(data);
                                            console.error("Google Maps API error:", data.status);
                                            locationElem.innerText = 'Change Location';
                                        }
                                    })
                                    .catch(error => {
                                        locationElem.innerText = 'Change Location';
                                        console.error("Error fetching address from Google Maps:", error);
                                    });
                            } else {
                                $.toastr.error(jsonResponse.message, { position: 'top-center', time: 5000 });
                                locationElem.innerText = 'Change Location';
                            }
                        } catch (e) {
                            console.error("Invalid JSON response:", response);
                            locationElem.innerText = 'Change Location';
                        }
                    },
                    error: function () {
                        console.error("Error fetching API key.");
                        locationElem.innerText = 'Change Location';
                    }
                });
            },
            function (error) {
                console.error("Error getting location:", error);
                console.log('Please enable your location to view personalized feeds and nearby content.');
                locationElem.innerText = 'Change Location';
            }
        );
    } else {
        alert('Geolocation is not supported by this browser.');
    }
}

function updateUserLocationInDB(locationData, newCityState) {
    var guestLogin = $("#guestLogin").val();
    if(guestLogin !== '1') {
        $.ajax({
            url: 'ajax.php?action=updateUserProfile', // 🔁 Your endpoint to handle location update
            method: 'POST',
            data: {
                latitude: locationData.latitude,
                longitude: locationData.longitude,
                city: locationData.city,
                state: locationData.state,
                location: locationData.address,
                country_code: locationData.country_code,
                country: locationData.country_code
            },
            success: function(response) {
                try {
                    const res = JSON.parse(response); // in case response is JSON string
                    if (res.success) {
                        // ✅ Show popup only after DB update
                        showLocationChangePopup(newCityState);

                        // Update saved user location in localStorage
                        localStorage.setItem('userLatitude', locationData.latitude);
                        localStorage.setItem('userLongitude', locationData.longitude);
                        localStorage.setItem('userCity', locationData.city);
                        localStorage.setItem('userState', locationData.state);
                        localStorage.setItem('userAddress', locationData.address);
                        localStorage.setItem('userCountryCode', locationData.country_code);
                    } else {
                        console.warn("Update failed:", res.message || "No success flag.");
                    }
                } catch (err) {
                    console.error("Invalid JSON from server:", response);
                }
            },
            error: function() {
                console.error('AJAX error while updating user location.');
            }
        });
    }
}

function showLocationChangePopup(newLocation) {
    const popup = document.createElement("div");
    popup.className = "location-popup";
    popup.innerHTML = `
        <div class="popup-content">
            <p>Your location has changed to <strong>${newLocation}</strong>.</p>
            <button onclick="this.parentElement.parentElement.remove()">OK</button>
        </div>
    `;
    document.body.appendChild(popup);
}

// Tab click event to reset posts and load specific content
$(document).on('click', '#home-posts-tab', function () {
    window.location.href = window.location.pathname + '?type=0';
});

$(document).on('click', '#home-listing-tab', function () {
    window.location.href = window.location.pathname + '?type=1';
});

$(document).on('click', '#home-deals-tab', function () {
    window.location.href = window.location.pathname + '?type=2';
});

$(document).on('click', '#home-onsale-tab', function () {
    window.location.href = window.location.pathname + '?type=4';
});

$(document).on('click', '#home-onSale-tab', function () {
    window.location.href = window.location.pathname + '?type=3';
});

document.querySelectorAll(".deal-sort-menu li").forEach(item => {
    item.addEventListener("click", function() {
        let sortValue = this.getAttribute("data-sort");
        let url = new URL(window.location.href);
        url.searchParams.set("sort", sortValue);
        window.location.href = url.toString();
    });
});

const sortSelect = document.querySelector(".set-select-sort");

if (sortSelect) {
    sortSelect.addEventListener("change", function() {
        const sortValue = this.value; // Get selected value
        const url = new URL(window.location.href);
        
        if (sortValue) {
            url.searchParams.set("sort", sortValue); // Set sort parameter
        } else {
            url.searchParams.delete("sort"); // Remove sort parameter if no value
        }

        window.location.href = url.toString(); // Redirect
    });
}

let scrollTimeout;

// function isElementInViewport(el) {
//   const rect = el.getBoundingClientRect();
//   return (
//     rect.top < (window.innerHeight || document.documentElement.clientHeight) &&
//     rect.bottom > 0
//   );
// }

function isElementInViewport(el, offset = 100) {
  const rect = el.getBoundingClientRect();
  const elemCenter = rect.top + rect.height / 2;
  const windowHeight = (window.innerHeight || document.documentElement.clientHeight);

  return elemCenter >= offset && elemCenter <= windowHeight;
}

function checkVisibleCardsAndSendAjax() {
  // Combine all cards into a single list in DOM order
  const selector = '.vpc-post-card, .vpc-listing-card, .vpc-deal-card, .vpc-ad-card';
  const $cards = $(selector);

  $cards.each(function () {
    const $el = $(this);

    if (isElementInViewport(this)) {
      const iView = $el.data('i-view');

      // Print class and data
      //console.log("Visible Element Class:", this.className);
      //console.log("Visible Element Data:", this.dataset);

      if (!iView) {
        const isAd = $el.hasClass('vpc-ad-card');
        const idKey = isAd ? 'ad-id' : 'post-id';
        const ajaxUrl = isAd ? 'ajax.php?action=ad_view_count_fnc' : 'ajax.php?action=view_post_eye_count';
        const countClass = isAd ? '.ads-spotted-count' : '.post-spotted-count';
        const dataKey = isAd ? 'ad_id' : 'post_id';
        const idValue = $el.data(idKey);

        if (!idValue) {
          console.warn("Missing ID for element:", $el);
          return false; // Still stop loop, but skip this one
        }

        $.ajax({
          url: ajaxUrl,
          method: 'POST',
          data: { [dataKey]: idValue, type: 0 },
          success: function (response) {
            $el.data('i-view', true); // Mark as viewed

            const $countElem = $el.find(countClass);
            if ($countElem.length) {
              let currentCount = parseInt($countElem.text().replace(/\D/g, '')) || 0;
              $countElem.text(currentCount + 1);
            } else {
              console.warn("Count element not found inside:", $el);
            }
          },
          error: function () {
            console.error('AJAX error for', isAd ? 'ad' : 'post', 'ID:', idValue);
          }
        });
      }

      return false; // Stop after first visible card
    }
  });
}

$(window).on('scroll resize', function () {
  clearTimeout(scrollTimeout);
  const guestLogin = $("#guestLogin").val();
  if (guestLogin !== '1') {
    scrollTimeout = setTimeout(checkVisibleCardsAndSendAjax, 5000);
  }
});

$(document).ready(function () {
  const guestLogin = $("#guestLogin").val();
  if (guestLogin !== '1') {
    // Check immediately on load
    setTimeout(checkVisibleCardsAndSendAjax, 1000);
  }
});


document.getElementById("preferred-communication").addEventListener("change", function () {
    const selected = this.value;

    // Hide both fields first
    document.querySelector(".phone-field").style.display = "none";
    document.querySelector(".email-field").style.display = "none";

    // Remove required from both
    document.getElementById("report-claim-phone").removeAttribute("required");
    document.getElementById("report-email").removeAttribute("required");

    // Show and require the selected field
    if (selected === "email") {
        document.querySelector(".email-field").style.display = "block";
        document.getElementById("report-email").setAttribute("required", "required");
    } else if (selected === "phone") {
        document.querySelector(".phone-field").style.display = "block";
        document.getElementById("report-claim-phone").setAttribute("required", "required");
    }
});

// Listing Category
document.querySelectorAll('.category-link').forEach(link => {
    link.addEventListener('click', function (e) {
        e.preventDefault();
        const category = this.dataset.category;
        const type = 1;

        const url = new URL(window.location.href);
        url.searchParams.set('type', type);
        url.searchParams.set('listing_cat', category);
        window.location.href = url.toString(); // redirect with both params
    });
});