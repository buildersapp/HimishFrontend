$(document).ready(function () {
    let selectedCommunities = [];
    let locationsArray = [];
    window.allCommunityLocations = window.allCommunityLocations || [];
    window.myCommunityLocations = window.myCommunityLocations || [];

    // Handle community selection
    $(".community-radio-ch").change(function () {
        let parentBox = $(this).closest(".single-com-box");
        let communityId = $(this).val();
        
        // Extract data from HTML attributes
        let communityData = {
            id: communityId,
            address: $(this).data("address"),
            latitude: $(this).data("latitude"),
            longitude: $(this).data("longitude"),
            name: $(this).data("name"),
            country_code: $(this).data("country-code"),
            description: $(this).data("description"),
            state: $(this).data("state"),
            city: $(this).data("city")
        };

        if ($(this).is(":checked")) {
            selectedCommunities.push(communityData);
            parentBox.css("background", "linear-gradient(90deg, rgba(70, 20, 202, 1) 0%, rgba(149, 77, 225, 1) 100%)");
            parentBox.find(".com-title, .com-text").css("color", "#fff");

            // Add to locations if it has valid coordinates
            if (communityData.latitude && communityData.longitude) {
                locationsArray.push({
                    address: communityData.address,
                    latitude: communityData.latitude,
                    longitude: communityData.longitude,
                    state: communityData.state,
                    country_code: communityData.country_code,
                    city: communityData.city,
                    community_id : communityData.id
                });
                renderLocationCheckboxes();
            }
        } else {
            // Remove community from selected list
            selectedCommunities = selectedCommunities.filter(comm => comm.id !== communityId);
            parentBox.css("background", "#f1f5f9");
            parentBox.find(".com-title").css("color", "#222");
            parentBox.find(".com-text").css("color", "#484848");

            // Remove related location
            locationsArray = locationsArray.filter(loc => loc.latitude !== communityData.latitude || loc.longitude !== communityData.longitude);
        }

        updateSelectionCount();
    });

    // Select All / Unselect All functionality
    $(".btn-unselect-all").click(function () {
        let allCheckboxes = $(".community-radio-ch");

        if (selectedCommunities.length === allCheckboxes.length) {
            selectedCommunities = [];
            locationsArray = [];
            allCheckboxes.prop("checked", false).each(function () {
                let parentBox = $(this).closest(".single-com-box");
                parentBox.css("background", "#f1f5f9");
                parentBox.find(".com-title").css("color", "#222");
                parentBox.find(".com-text").css("color", "#484848");
            });
            $(this).html('Select All');
        } else {
            selectedCommunities = [];
            locationsArray = [];
            allCheckboxes.prop("checked", true).each(function () {
                let communityId = $(this).val();
                let communityData = {
                    id: communityId,
                    address: $(this).data("address"),
                    latitude: $(this).data("latitude"),
                    longitude: $(this).data("longitude"),
                    name: $(this).data("name"),
                    description: $(this).data("description"),
                    state: $(this).data("state"),
                    city: $(this).data("city")
                };

                selectedCommunities.push(communityData);
                if (communityData.latitude && communityData.longitude) {
                    locationsArray.push({
                        address: communityData.address,
                        latitude: communityData.latitude,
                        longitude: communityData.longitude,
                        country_code: communityData.country_code,
                        state: communityData.state,
                        city: communityData.city,
                        community_id : communityData.id
                    });
                    renderLocationCheckboxes();
                }

                let parentBox = $(this).closest(".single-com-box");
                parentBox.css("background", "linear-gradient(90deg, rgba(70, 20, 202, 1) 0%, rgba(149, 77, 225, 1) 100%)");
                parentBox.find(".com-title, .com-text").css("color", "#fff");
            });
            $(this).html('Unselect All');
        }

        updateSelectionCount();
    });

    // Search functionality
    $(".comunity-search-input input").on("input", function () {
        let searchValue = $(this).val().toLowerCase();
        $(".single-com-box").each(function () {
            let title = $(this).find(".com-title").text().toLowerCase();
            $(this).toggle(title.includes(searchValue));
        });
    });

    // Save location from input field
    $("#address").on("blur", function () {
        setTimeout(function () {
            let userEnteredAddress = $("#address").val() || 0;
            let latitude = $("#latitude").val() || 0;
            let longitude = $("#longitude").val() || 0;
            let city = $("#city").val() || "";
            let state = $("#state").val() || "";
            let country_code = $("#country_code").val() || "US";
            if (userEnteredAddress && city && state) {
                let newLocation = {
                    address: userEnteredAddress,
                    latitude: latitude, // Manually enter or fetch lat/lng from API
                    longitude: longitude,
                    country_code: country_code,
                    state: state, // Manually enter or fetch from API
                    city: city,
                    community_id : 0
                };

                locationsArray = locationsArray.filter(loc => !(loc.community_id === 0 ));
                
                // Check if the city and state combination is already in the locationsArray
                if (!locationsArray.some(loc => loc.city === newLocation.city && loc.state === newLocation.state)) {
                    // Only push the new location if the city and state are not already in the array
                    locationsArray.push(newLocation);
                    //renderLocationCheckboxes();
                }
            }
        }, 1000);
    });

    // Function to render checkboxes for locations
    function renderLocationCheckboxes() {
        $('#listingLoc').show();
        let container = $("#locationCheckboxes");
        container.empty(); // Clear previous checkboxes

        locationsArray.forEach((location, index) => {
            let checkboxHtml = `
                <div class="form-check">
                    <input class="form-check-input location-checkbox" type="checkbox" value="${index}" id="loc-${index}">
                    <label class="form-check-label" for="loc-${index}">
                        ${location.city} , ${location.state}
                    </label>
                </div>
            `;
            container.append(checkboxHtml);
        });
    }

    // Function to update selected locations in hidden input
    $(document).on("change", ".location-checkbox", function () {
        let selectedLocations = [];

        $(".location-checkbox:checked").each(function () {
            let index = $(this).val();
            selectedLocations.push(locationsArray[index]);
        });

        $("#selectedLocationsInput").val(JSON.stringify(selectedLocations));
        //console.log("Updated Selected Locations:", selectedLocations);
    });

    // Update selected count and log data
    function updateSelectionCount() {
        $(".stat-item-2 h4").text(selectedCommunities.length);
        $('#totalSelectedComm').text(selectedCommunities.length);
        // Save JSON data in hidden fields
        $('#selectedCommunitiesInput').val(JSON.stringify(selectedCommunities));
        $('#selectedLocationsInput').val(JSON.stringify(locationsArray));
    }

    $(".community-radio").on("click", function () {
        let selectedValue = $("input[name='community_type']:checked").val();
        if (selectedValue === "2") {
            $("#selecComunityDetailsModal").modal('show');
        }
    });

    $(".community-radio").on("change", function () {
        let selectedValue = $(this).val().trim(); // Get selected radio value

        // Clear only locations with community_id > 0
        locationsArray = locationsArray.filter(loc => loc.community_id <= 0);

        if (selectedValue.toLowerCase() === "2") {
            text = 'Choose';
            $("#selecComunityDetailsModal").modal('show');
        }else if (selectedValue.toLowerCase() === "1") {
            text = 'My';
            locationsArray = locationsArray.concat(window.myCommunityLocations);
        }else{
            text = 'All';
            locationsArray = locationsArray;
        }

        $('#totalSelectedComm').text(text);

        // Remove selected class from all items
        $(".communites-contentsx-item").removeClass("selected");
        
        // Add selected class to the clicked item
        $(this).parent().addClass("selected");

        renderLocationCheckboxes();
    });

    // Handle file input change event
    $(document).on("change", ".comunity-image-upload-input", function (event) {
        let input = $(this);
        let index = input.attr("id").replace("imageUploadInput", "");
        let file = this.files[0];

        if (file) {
            if (!pixelarity.open(file, false, function (res, faces) {
                console.log("Faces detected:", faces);

                // Update image preview
                $("#previewImage" + index).attr("src", res).css({
                    height: "120px",
                    objectFit: "contain"
                });

                // Show preview wrapper & hide upload label
                $("#previewWrapper" + index).show();
                $("#imageUploadInput" + index).siblings("label").hide();

                // Save cropped image data in hidden field
                $("#croppedImage" + index).val(res);
            }, "jpg", 0.7, true)) {
                alert("Whoops! That is not an image!");
            }
        }
    });

    // Trigger file input when clicking on preview image
    $(document).on("click", ".preview-img", function () {
        let index = $(this).data("index");
        $("#imageUploadInput" + index).click();
    });

    // Remove image and reset preview
    $(document).on("click", ".remove-img", function () {
        let index = $(this).data("index");

        // Reset preview image
        $("#previewImage" + index).attr("src", "");
        $("#previewWrapper" + index).hide();

        // Show upload label again
        $("#imageUploadInput" + index).siblings("label").show();

        // Clear hidden input value
        $("#croppedImage" + index).val("");
        $("#imageUploadInput" + index).val(""); // Reset file input
    });
});