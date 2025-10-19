$(document).ready(function () {
    let selectedCommunities = [];
    let locationsArray = [];

    // Handle community selection
    $(".community-radio-ch").change(function () {
        let parentBox = $(this).closest(".single-com-box");
        let communityId = $(this).val();
        
        let communityData = {
            id: communityId,
        };

        if ($(this).is(":checked")) {
            selectedCommunities.push(communityData);
            parentBox.css("background", "linear-gradient(90deg, rgba(70, 20, 202, 1) 0%, rgba(149, 77, 225, 1) 100%)");
            parentBox.find(".com-title, .com-text").css("color", "#fff");
        } else {
            selectedCommunities = selectedCommunities.filter(comm => comm.id !== communityId);
            parentBox.css("background", "#f1f5f9");
            parentBox.find(".com-title").css("color", "#222");
            parentBox.find(".com-text").css("color", "#484848");
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
                };

                selectedCommunities.push(communityData);

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

    // Update selected count and log data
    function updateSelectionCount() {
        $(".stat-item-2 h4").text(selectedCommunities.length);
        $('#totalSelectedComm').text(selectedCommunities.length);
        // Save JSON data in hidden fields
        $('#selectedCommunitiesInput').val(JSON.stringify(selectedCommunities));
    }
});

/**
 * join community.
*/
$(document).on("click", ".join-comm-fn", function () {
    var communities = $('#selectedCommunitiesInput').val();
    if(communities){
        var parsedCommunities = JSON.parse(communities);
        if(parsedCommunities.length > 0){
            var communityIds = parsedCommunities.map(comm => comm.id).join(',');
            $.ajax({
                url: 'ajax.php?action=join_community',
                type: 'POST',
                data: { community_id: communityIds },
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
                    console.log("Failed to join communities.");
                }
            });
        }else{
            alert("Please select at least one community to join.");
        }
    }else{
        alert("Please select at least one community to join.");
    }
});

/**
 * Create Community.
*/
// $(document).on("click", "#createCommunity", function () {
$("#createCommunity").submit(function(e){
    e.preventDefault();
    var private_checkbox = $("#private-checkbox").val();
    var communityName = $(".communityName").val();
    var communityDescription = $(".communityDescription").val();
    var address = $("#address").val();
    var latitude = $("#latitude").val();
    var longitude = $("#longitude").val();
    var city = $("#city").val();
    var state = $("#state").val();
    var postData = { 
      name: communityName,
      description: communityDescription,
      is_private: private_checkbox,
      address: address,
      city: city,
      state: state,
      latitude: latitude,
      longitude: longitude
    }
    console.log(postData, 'postDataaaaaaaaaaaaaaaaaaa');
    $.ajax({
        url: 'ajax.php?action=add-community',
        type: 'POST',
        data: postData,
        success: function (response) {
            try {
                var jsonResponse = JSON.parse(response);
                if (jsonResponse.success) {
                  // button.data("favorited", newType);
                  // button.find("img").attr("src", "assets/img/" + (newType ? "bookmarkfill.png" : "bookmarkBlack.png"));
                  $.toastr.success(jsonResponse.message, {position: 'top-center',time: 5000});
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