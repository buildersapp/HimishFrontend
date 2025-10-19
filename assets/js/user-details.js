// Post Tabs

$(document).on('click', '#ud-posts-1, #ud-myposts-tab', function () {
    updateURLParams({ type: 0, tp: 'posts' });
});

$(document).on('click', '#ud-posts-looking-for', function () {
    updateURLParams({ type: 1, tp: 'posts' });
});

$(document).on('click', '#ud-posts-deals', function () {
    updateURLParams({ type: 2, tp: 'posts' });
});

$(document).on('click', '#ud-posts-ads', function () {
    updateURLParams({ type: 'ads', tp: 'posts' });
});

// Saved Tabs

$(document).on('click', '#ud-saved-posts, #ud-saved-tab', function () {
    updateURLParams({ type: 0, tp: 'saved' });
});

$(document).on('click', '#ud-saved-looking-for', function () {
    updateURLParams({ type: 1, tp: 'saved' });
});

$(document).on('click', '#ud-saved-deals', function () {
    updateURLParams({ type: 2, tp: 'saved' });
});

$(document).on('click', '#ud-saved-ads', function () {
    updateURLParams({ type: 'ads', tp: 'saved' });
});

$(document).on('click', '#ud-saved-companies', function () {
    updateURLParams({ type: 'companies', tp: 'saved' });
});

// Drafts Tabs

$(document).on('click', '#ud-draft-posts, #ud-draft-tab', function () {
    updateURLParams({ type: 0, tp: 'draft' });
});

$(document).on('click', '#ud-draft-looking-for', function () {
    updateURLParams({ type: 1, tp: 'draft' });
});

$(document).on('click', '#ud-draft-deals', function () {
    updateURLParams({ type: 2, tp: 'draft' });
});

$(document).on('click', '#ud-draft-ads', function () {
    updateURLParams({ type: 'ads', tp: 'draft' });
});

// Recommends Tabs

$(document).on('click', '#ud-recommends-tab', function () {
    updateURLParams();
});