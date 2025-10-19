// A map to store timers per slider ID
const sliderTimers = {};

// Common handler to track ad view
function handleAdViewTracking($slide) {
    const adId = $slide.find('.set-ad-id').val();
    const iSpotted = $slide.find('.set-i-spotted').val();
    var guestLogin = $("#guestLogin").val();
    if(guestLogin == '1'){
        return false;
    }

    if (adId && iSpotted == 0) {
        $.ajax({
            url: 'ajax.php?action=ad_view_count_fnc',
            method: 'POST',
            data: { ad_id: adId, type: 1 },
            success: function (response) {
                const $iSpottedElem = $slide.find('.set-i-spotted');
                const $totalSpotted = $slide.find('.data-total-spotted');
                const $innerCard = $slide.find('.set-inner-card-story');

                if ($iSpottedElem.length) $iSpottedElem.val(1);
                if ($totalSpotted.length) {
                    $totalSpotted.text(function (i, text) {
                        return parseInt(text || '0') + 1;
                    });
                }
                if ($innerCard.length) $innerCard.addClass('set-blur-img');
            },
            error: function () {
                console.error('--- AJAX error for web ad view count ---');
            }
        });
    }
}

// Function to initialize a slider with tracking
function initSliderWithTracking(sliderSelector) {
    const sliderId = sliderSelector.replace('#', '');

    // $(sliderSelector).on('init', function (event, slick) {
    //     if (sliderTimers[sliderId]) clearTimeout(sliderTimers[sliderId]);

    //     // Always schedule tracking for the first visible slide
    //     const $firstSlide = $(slick.$slides.get(slick.currentSlide));
    //     sliderTimers[sliderId] = setTimeout(() => {
    //         handleAdViewTracking($firstSlide);
    //     }, 10000);
    // }).on('afterChange', function (event, slick, currentSlide) {
    //     if (sliderTimers[sliderId]) clearTimeout(sliderTimers[sliderId]);

    //     const $currentSlide = $(slick.$slides.get(currentSlide));
    //     sliderTimers[sliderId] = setTimeout(() => {
    //         handleAdViewTracking($currentSlide);
    //     }, 10000);
    // });

    $(sliderSelector).slick();
}