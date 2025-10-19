document.addEventListener('DOMContentLoaded', () => {
    const tabs = {
        'members': {
            buttonId: 'members-tab',
            contentId: 'members-list-content',
        },
        'ads': {
            buttonId: 'ads-tab',
            contentId: 'ads-content',
        },
        'info': {
            buttonId: 'info-tab',
            contentId: 'info-content',
        },
        'invite': {
            buttonId: 'invite-tab',
            contentId: '' // Add content ID when implemented
        }
    };

    // Get 'tab' param from URL (e.g. ?tab=ads)
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');

    // Default to 'members' if invalid or missing
    const activeTab = tabs[tabParam] ? tabParam : 'members';

    // Function to activate a tab
    function activateTab(tabKey) {
        Object.entries(tabs).forEach(([key, { buttonId, contentId }]) => {
            const btn = document.getElementById(buttonId);
            const content = contentId ? document.getElementById(contentId) : null;

            if (key === tabKey) {
                btn.classList.add('text-primary', 'border-b-2', 'border-primary');
                btn.classList.remove('text-gray-500');
                if (content) content.classList.remove('hidden');
            } else {
                btn.classList.remove('text-primary', 'border-b-2', 'border-primary');
                btn.classList.add('text-gray-500');
                if (content) content.classList.add('hidden');
            }
        });
    }

    // Attach event listeners to all tab buttons
    Object.entries(tabs).forEach(([key, { buttonId }]) => {
        const btn = document.getElementById(buttonId);
        if (!btn) return;
        btn.addEventListener('click', () => {
            activateTab(key);

            // Update URL parameter without reloading
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('tab', key);
            history.replaceState(null, '', newUrl.toString());
        });
    });

    // Activate tab from URL or default
    activateTab(activeTab);
});

// load communityMemberTable
var tableId = "communityMembersDT";
var Id = $('#'+ tableId).attr('data-community-id');
let statusFilter = null;
let ownerFilter = null;
var communityMembersTable = $("#communityMembersDT").DataTable({
    dom: '<"top">rt<"bottom"ip><"clear">',
    pageLength: 500,
    lengthMenu: [ [10, 25, 50, 100, 500], [10, 25, 50, 100, 500] ],
    language: {
        searchPlaceholder: 'Enter your search term...',
        emptyTable: 'No community members found.',
        zeroRecords: 'No matching records found.',
        processing: '<div class="text-center py-6">Loading community members...</div>'
    },
    language: {
        searchPlaceholder: 'Enter your search term...',
        emptyTable: ''
    },
    serverSide: true,
    processing : true,
    ajax:{
        url :"../ajax.php?isSalesRep=1&action=get_community_members&community_id="+Id, // json datasource
        type: "post",  // method  , by default get
        cache:true,
        data: function (d) {
            d.Id = Id;
            d.status = statusFilter;
            d.is_owner = ownerFilter;
        },
        beforeSend: function () {
            $('#'+tableId+' tbody').html(`
                <tr class="loading-row">
                    <td colspan="100%" class="text-center py-4 text-gray-500">Loading Community Members...</td>
                </tr>
            `);
        },
        error: function(jqXHR) {
            try {
                // Attempt to parse the response
                var response = JSON.parse(jqXHR.responseText);
        
                // Redirect if 401 Unauthorized
                if (response.redirect) {
                    window.location.href = response.url;
                } else if (response.message) {
                    // Show error message if available
                    alert("Error: " + response.message);
                }
            } catch (e) {
                // Handle cases where the response is not JSON
                console.error("Unexpected server response:", jqXHR.responseText);
                alert("An unexpected error occurred. Please try again later.");
            }
        },
        complete : function(data){

            // Highlight search results
            if(communityMembersTable.search()){
                var regex = new RegExp('('+communityMembersTable.search()+')', 'gi');
                $('.content').each(function() {
                    var content = $(this);
                    var text = content.text();
                    if (text.match(regex)) {
                        content.parent().addClass('highlight');
                    }
                });
            }

            // close loader
            $('#'+tableId+' .loading-row').remove();
        }
    },
    drawCallback: function () {
        // Add Tailwind classes to pagination buttons
        $('.dataTables_empty').addClass('py-4 px-4 whitespace-nowrap');
        $(".bottom").addClass('flex justify-between mt-3');
        $('#'+tableId+'_paginate').addClass('flex items-center justify-between');
        $('#'+tableId+'_paginate ul.pagination').addClass('flex');
        $('#'+tableId+'_paginate ul.pagination > li').each(function () {
            $(this).addClass('flex px-3 py-1 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-50 mx-1');

            // Active page button
            if ($(this).hasClass('active')) {
                $(this).removeClass('text-gray-600 border-gray-300 hover:bg-gray-50')
                       .addClass('bg-primary text-white');
            }
        });
    },
    createdRow: function (row, data, dataIndex) {
        // Add class to all <td> elements
        $('td', row).addClass('py-4 px-4 whitespace-nowrap');
    }
});

$('#searchCommMembers').on('input', function() {
    var searchTerm = $(this).val();
    communityMembersTable.search(searchTerm).draw();
});

$('#filterComMembers').on('change', function () {
    const value = $(this).val();

    // Reset both filters
    statusFilter = null;
    ownerFilter = null;

    if (value === "ACM") {
        statusFilter = 1;
    } else if (value === "NACM") {
        statusFilter = 0;
    } else if (value === "ADM") {
        ownerFilter = 1;
    } else if (value === "NADM") {
        ownerFilter = 0;
    }

    communityMembersTable.ajax.reload();
});