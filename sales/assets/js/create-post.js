// Discount toggle functionality
document.getElementById("discountToggle").addEventListener("change", function () {
    const discountFields = document.getElementById("discountFields");
    const hasDiscount = document.getElementById("has_discount");

    if (this.checked) {
        discountFields.classList.remove("opacity-50", "pointer-events-none");
        hasDiscount.value = "1";
    } else {
        discountFields.classList.add("opacity-50", "pointer-events-none");
        hasDiscount.value = "0";
    }
});

// Template tabs functionality
const tabButtons = document.querySelectorAll("#template-tabs button");
tabButtons.forEach((button) => {
    button.addEventListener("click", function () {
        tabButtons.forEach((btn) => {
            btn.classList.remove("bg-white", "text-blue-600", "shadow-sm");
            btn.classList.add("text-gray-600", "hover:text-gray-900");
        });
        this.classList.add("bg-white", "text-blue-600", "shadow-sm");
        this.classList.remove("text-gray-600", "hover:text-gray-900");
    });
});

/******************************************* */

// Get today's date
flatpickr("#discount_expiry", {
  dateFormat: "m/d/Y",   // US format MM/DD/YYYY
  minDate: "today"       // disable past dates
});

/******************************************* */

// Message Template

$(document).ready(function() {
    
    // Store CKEditor instances
    const editors = {};

    // Initialize CKEditor for each message textarea
    $('.template-message').each(function() {
        const textarea = this;
        ClassicEditor
        .create(textarea)
        .then(editor => {
            editors[$(textarea).closest('.template-category').data('type')] = editor;

            // Listen for content changes
            editor.model.document.on('change:data', () => {
                updateTemplatesJSON();
            });
        })
        .catch(error => { console.error(error); });
    });

    // Tab switching
    $('#template-tabs button').click(function() {
        let type = $(this).data('type');
        $('.template-category').addClass('hidden');
        $(`.template-category[data-type=${type}]`).removeClass('hidden');

        $('#template-tabs button').removeClass('bg-white text-blue-600 shadow-sm');
        $(this).addClass('bg-white text-blue-600 shadow-sm');
    });

    // Load Previous Template
    $('#loadTemplate').click(function() {
      let activeType = $('.template-category:not(.hidden)').data('type');

      $.ajax({
        url: '../ajax.php?action=get_referral_template_sp&isSalesRep=1',
        type: 'POST',
        data: { type: activeType , isMT: 0 },
        success: function(res) {
            // Parse response safely
            res = res ? JSON.parse(res) : [];

            if (!res || !Array.isArray(res) || res.length === 0) {
                $.toastr.error('No templates found for this category.', {position: 'top-center', time: 2000});
                return;
            }

            // Build template list with Tailwind UI
            let templateList = `
              <div class="max-h-80 overflow-y-auto divide-y divide-gray-200">
            `;

            res.forEach((t, index) => {
                let title = t.title || `Template ${index + 1}`;
                let message = t.message ? t.message.substring(0, 100) + (t.message.length > 100 ? '...' : '') : '';

                templateList += `
                  <div class="flex items-start justify-between p-3 hover:bg-gray-50">
                    <div class="flex-1 pr-3">
                      <div class="font-medium text-gray-900">${title}</div>
                      <div class="text-sm text-gray-600 mt-1">${message}</div>
                    </div>
                    <button 
                      class="use-template bg-blue-600 text-white px-3 py-1 rounded text-sm"
                      data-index="${index}">
                      Use
                    </button>
                  </div>
                `;
            });

            templateList += `</div>`;

            // Centered modal popup
            let popup = $(`
              <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-lg">
                  <div class="p-3 border-b font-semibold text-gray-700 flex justify-between items-center">
                    <span>Select a Template</span>
                    <button class="close-popup text-gray-500 hover:text-gray-700">&times;</button>
                  </div>
                  ${templateList}
                </div>
              </div>
            `);

            $('body').append(popup);

            // Click template "Use" button
            popup.find('.use-template').click(function() {
                let idx = $(this).data('index');
                let template = res[idx];

                if (!template) return;

                const activeCategory = $(`.template-category[data-type=${activeType}]`);

                // Set title
                activeCategory.find('.template-title').val(template.title || '');

                // Set message (CKEditor)
                if (editors[activeType]) {
                    editors[activeType].setData(template.message || '');
                }

                // --- Save template IDs in hidden input ---
                let hiddenInput = activeCategory.find('.selected-template-ids');
                if (!hiddenInput.length) {
                    // if hidden input doesn't exist, create one
                    hiddenInput = $('<input>', {
                        type: 'hidden',
                        class: 'selected-template-ids',
                        name: 'selected_template_ids[]'
                    });
                    activeCategory.append(hiddenInput);
                }

                let existingIds = hiddenInput.val() ? hiddenInput.val().split(',') : [];

                // Avoid duplicate IDs
                if (!existingIds.includes(String(template.id))) {
                    existingIds.push(template.id);
                }

                hiddenInput.val(existingIds.join(','));

                popup.remove();
            });

            // Close popup on background click or close button
            popup.on('click', function(e) {
                if ($(e.target).is(popup) || $(e.target).hasClass('close-popup')) {
                    popup.remove();
                }
            });
        },
        error: function() {
          $.toastr.error('Failed to load templates.', {position: 'top-center', time: 2000});
        }
      });
    });

    // Update JSON hidden field whenever a title/message changes
    function updateTemplatesJSON() {
        let templates = [];
        $('.template-category').each(function() {
            let type = $(this).data('type');
            let title = $(this).find('.template-title').val();
            // Get message from CKEditor instance safely
            let message = '';
            if (editors[type]) {
                message = editors[type].getData(); // CKEditor 5 getData()
            }
            templates.push({ title, type, message });
        });
        $('#templates_json').val(JSON.stringify(templates));
    }

    $('.template-title').on('input', updateTemplatesJSON);
});

/******************************************* */

function validateCreatePost(form) {
    let errors = [];

    // ✅ 1. First run Parsley validation
    if (!$(form).parsley().validate()) {
        return false; // stop if Parsley fails
    }

    HoldOn.open({
      theme: "sk-bounce",
      message: "Hold on, we're submitting your request...",
    });

    // 1. Post ID must be selected
    const postId = document.getElementById("selectedPostId").value;
    if (!postId || postId === "0") {
        errors.push("Please select a post.");
    }

    // 2. If discount enabled, fields required
    const hasDiscount = document.getElementById("has_discount").value;
    if (hasDiscount === "1") {
        const discountAmount = document.querySelector("[name='discount_amount']").value.trim();
        const discountExpiry = document.querySelector("[name='discount_expiry']").value.trim();

        if (!discountAmount) errors.push("Enter a discount amount.");
        if (!discountExpiry) errors.push("Select a discount expiry date.");
    }

    // 3. At least one message template filled
    // let templateValid = false;
    // document.querySelectorAll(".template-category").forEach(section => {
    //     const title = section.querySelector(".template-title").value.trim();
    //     const message = section.querySelector(".template-message").value.trim();
    //     if (title && message) templateValid = true;
    // });
    // if (!templateValid) {
    //     errors.push("Fill at least one message template (title + message).");
    // }

    // Show toastr errors
    if (errors.length > 0) {
        HoldOn.close();
        errors.forEach(msg => {
            $.toastr.error(msg, {position: 'top-center', time: 5000});
        });
        return false; // prevent submit
    }

    return true; // allow submit
}