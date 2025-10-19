

function populateRepDetailsModal(data) {
    // Basic Info
    $('#viewName').text(data.name || '');
    $('#viewId').text(`Sales Representative ID: SR${data.id || ''}`);
    $('#viewEmail').text(data.email || '');
    $('#viewPhone').text(data.phone || '');
    $('#viewCreated').text(`Since: ${formatDate(data.created_at)}`);
    $('#viewImg').attr('src', data.image ? data.media_url+data.image : 'https://storage.googleapis.com/uxpilot-auth.appspot.com/avatars/avatar-2.jpg');
    $('#viewEedit').attr('data-json', JSON.stringify(data));
    if (data.status == 1) {
        $('#viewStatus')
            .removeClass()
            .addClass('inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800')
            .html(`
                <i class="fa-solid fa-circle text-green-400 mr-1" style="font-size: 6px;"></i> Active
                <button 
                    status="0" 
                    user-id="${data.id}" 
                    onclick="changeUserStatus(this)" 
                    data-bs-toggle="tooltip" 
                    data-bs-placement="top" 
                    data-bs-original-title="Click to deactivate" 
                    class="ml-2 text-xs text-gray-500 hover:text-red-600"
                >
                    <i class="fa-solid fa-power-off"></i>
                </button>
            `);
    } else {
        $('#viewStatus')
            .removeClass()
            .addClass('inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800')
            .html(`
                <i class="fa-solid fa-circle text-red-400 mr-1" style="font-size: 6px;"></i> Flagged
                <button 
                    status="1" 
                    user-id="${data.id}" 
                    onclick="changeUserStatus(this)" 
                    data-bs-toggle="tooltip" 
                    data-bs-placement="top" 
                    data-bs-original-title="Click to activate" 
                    class="ml-2 text-xs text-gray-500 hover:text-green-600"
                >
                    <i class="fa-solid fa-power-off"></i>
                </button>
            `);
    }
    

    // Community (comma-separated string to badges)
    const communityHTML = (data.community || '')
        .split(',')
        .filter(c => c.trim() !== '')
        .map(c => `
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                ${c.trim()}
            </span>
        `).join('');
var __button =''
        // var __button ='<button type="button" data-bs-toggle="modal" data-bs-target="#editRepInfoModal" data-json="'+JSON.stringify(data)+'" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 editReps"><i class="fa-solid fa-plus mr-1"></i> Assign </button>';

    $('#viewCommunity').html(communityHTML + __button);
    $('#view-total-ad').text(data.total_converted || 0);
    $('#view-total-referral').text(data.total_click_count || 0);
    $('#view-total-community').text(data.total_community_count || 0);
    $('#view-total-earning').text(data.total_amount || 0);
    $('#view-this-month').text(data.this_month_amount || 0);
    const now = new Date();
    const month = now.toLocaleString('default', { month: 'short' }); // "Dec"
    const year = now.getFullYear(); // 2024

$('#view-this-month-text').text(`${month} ${year}`);
$('#view-year-text').text(`Since Jan ${year}`);


}

// Helpers
function formatDate(dateStr) {
    if (!dateStr) return '';
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateStr).toLocaleDateString(undefined, options);
}

function getImageUrl(imagePath) {
    return imagePath.startsWith('http') ? imagePath : `https://yourcdn.com/${imagePath}`;
}

function showCommunityDetails(data) {
    $('#communityName').text(data.name);
    $('#communityId').text('ID: COM'+data.id);
    $('#ownerName').text(data.username);
    $('#ownerId').text('ID: SR' + data.user_id);
    $('#privacyStatus').html(data.is_private ? '<i class="fa-solid fa-lock mr-1"></i> Private' : '<i class="fa-solid fa-globe mr-1"></i> Public');
    $('#communityStatus').html(data.status ? '<i class="fa-solid fa-circle text-green-400 mr-1" style="font-size: 6px;"></i> Active' : '<i class="fa-solid fa-circle text-red-400 mr-1" style="font-size: 6px;"></i> Inactive');
    $('#memberCount').text(data.total_member + ' members' || 0);
    $('#createdAt').text(new Date(data.created_at).toLocaleDateString());
    $('#lastActivity').text('Just now'); // Placeholder
    $('#lastActivityDate').text(new Date(data.updated_at).toLocaleDateString());
    $('#description').text(data.description || 'No description');
  
    $('#totalPosts').text(data.total_posts || 0);
    $('#totalReferrals').text(data.total_LF || 0);


    
    $('#posts_this_week').text(data.posts_this_week || 0);

    $('#post_growth_percentage').text(
        (parseFloat(data.post_growth_percentage) >= 0 ? '+' : '') + (parseFloat(data.post_growth_percentage) || 0) + '%'
    );

    $('#referrals_this_week').text(data.referrals_this_week || 0);

    $('#referral_growth_percentage').text(
        (parseFloat(data.referral_growth_percentage) >= 0 ? '+' : '') + (parseFloat(data.referral_growth_percentage) || 0) + '%'
    );

    $('#engagement_rate_this_week').text((parseFloat(data.engagement_rate_this_week) || 0) + '%');

    $('#engagement_rate_growth_percentage').text(
        (parseFloat(data.engagement_rate_growth_percentage) >= 0 ? '+' : '') + (parseFloat(data.engagement_rate_growth_percentage) || 0) + '%'
    );


    $('#engagementRate').text('N/A'); // Customize based on your logic
    $('#comImg').attr('src', data.image ? data.media_url+data.image : 'https://storage.googleapis.com/uxpilot-auth.appspot.com/avatars/avatar-2.jpg');

    $('#viewEditCom').attr('data-json', JSON.stringify(data));
  
  }

  function showEditCommunityModal(data) {
    // Set modal title to indicate edit
    $('#communityModalTitle').text('Edit Community');

    // Set hidden ID if needed
    $('input[name="community_id"]').val(data.id); // Add this hidden field in your form if needed

    // Set Name
    $('input[name="name"]').val(data.name);

    // Set Description
    $('textarea[name="description"]').val(data.description);

    // Set Owner (Sales Person)
    $('select[name="user_id"]').val(data.user_id);

    // Set Status
    $('select[name="status"]').val(data.status);

    //  image
    $('.preview-image').attr('src',data.media_url+data.image)

    //  id
    $('#comId').val(data.id)

    // Set Privacy (Radio)
    $('input[name="is_private"][value="' + data.is_private + '"]').prop('checked', true);

}
// view link
function populateReferralModal(row) {
    // Link ID & Share Link
    $('#modal-link-id').text(row.link_id);
    $('#modal-share-link').val(row.share_link);
    $('#modal-copy-btn').attr('data-link',row.share_link) 

    // User Info
    $('#modal-user-name').text(row.user.name);
    $('#modal-user-id').text('ID: REP-' + row.user.id);
    const userImg = row.user.image ? row.media_url + row.user.image : 'assets/img/fav-icon.png';
    $('#modal-user-img').attr('src', userImg);

    // Post Info
    $('#modal-post-title').text(row.post.company);
    $('#modal-post-category').text(row.post.title);
    $('#modal-post-id').text('Post ID: POST-' + row.post.id);
    $('#modal-link-type').text((row.link_type==1) ? 'Post Convert To AD' :'Create a AD');

    const postImg = row.post.image ? row.media_url + row.post.image : 'assets/default-placeholder.png';
    $('#link-post-image').attr('src', postImg);

    // Dates
    const createdAt = new Date(row.created_at);
    const expireAt = new Date(row.expire_time * 1000); // timestamp

    const formatDate = (date) =>
        date.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });

    $('#modal-created-at').text(formatDate(createdAt));
    $('#modal-expiry-at').text(formatDate(expireAt));

    // Status
    if (row.status == 1) {
        $('#modal-status').html(`<i class="fa-solid fa-circle text-green-400 mr-1" style="font-size: 6px;"></i> Active`);
        $('#modal-status').removeClass().addClass("inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800");
    } else {
        $('#modal-status').html(`<i class="fa-solid fa-circle text-red-400 mr-1" style="font-size: 6px;"></i> Flagged`);
        $('#modal-status').removeClass().addClass("inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800");
    }

    

    // Platform
    $('#modal-source-platform').html(`<i class="fa-brands fa-whatsapp text-green-500 mr-1.5"></i> WhatsApp`);

    // Performance (Replace these with actual values if available)
    $('#modal-clicks').text(row.total_click ?? 0);
    $('#modal-conversions').text(row.total_convert ?? 0);
    $('#editButtonView').attr('data-json', JSON.stringify(row));

    // Show the modal
}
function populateReferralEditForm(data) {
    // 1. Set Link ID and URL
    $('#__linkID').val(data.id);
    $('#linkIdDisplay').text(data.link_id);
    $('#referralUrl').val(data.share_link);
    $('#shareUrlEnter').attr('data-link',data.share_link)

    // 2. Set Expiry Date
    const expiryDate = new Date(data.expire_time * 1000).toISOString().split('T')[0];
    $('#expiryDate').val(expiryDate);

    // 3. Set Assigned Sales Rep (assuming dropdown is dynamically built)
    $('#assignedRep').val(data.user_id);

    // 4. Set Status
    if (data.status == 1) {
        $('#statusActive').prop('checked', true);
    } else {
        $('#statusSuspended').prop('checked', true);
    }

    // 5. Set Notes
    $('#notes').val(data.info || '');

    // 6. Set Linked Post Info
    $('#postTitle').text(data.post.category);
    $('#postCategory').text(data.post.title);
    $('#postId').text(`Post ID: POST-${data.post.id}`);
    
    // Optional: Set Post Image if exists
    if (data.post.image) {
        $('#postImage').attr('src', data.media_url + data.post.image).show();
    } else {
        $('#postImage').hide();
    }
}

  
