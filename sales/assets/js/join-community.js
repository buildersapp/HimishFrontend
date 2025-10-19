/**
 * join community.
*/
$(document).on("click", ".join-comm-fn", function () {
    var communityIds = $(this).data('community-id');
    $(this).html('Joining Community...');
    $(this).css('opacity','0.5');
    if(communityIds > 0){
        $.ajax({
            url: '../ajax.php?isSalesRep=1&action=join_community',
            type: 'POST', 
            data: { community_id: communityIds },
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
                    console.error("Invalid JSON response:", response);
                }
            },
            error: function () {
                console.log("Failed to join communities.");
            }
        });
    }else{
        alert("Please select community to join.");
    }
});