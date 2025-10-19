/******************************************* */
// Message Template
const allowedPlaceholders = ["[POST_TITLE]", "[INVITE_LINK]"];

$(document).ready(function() {
    // Store CKEditor instances
    const editors = {};

    // Initialize CKEditor for each message textarea
    $('.template-message').each(function() {
        const textarea = this;
        ClassicEditor
        .create(textarea)
        .then(editor => {
            // Store instance by type (email, sms, etc.)
            editors[$(textarea).closest('.template-eds').data('type')] = editor;
        })
        .catch(error => { console.error(error); });
    });

    // Load Previous Template
    $('.loadTemplate').click(function() {
        let activeType = $(this).data('type');

        $.ajax({
            url: '../ajax.php?action=get_referral_template_sp&isSalesRep=1',
            type: 'POST',
            data: { type: activeType, isMT: 0 },
            success: function(res) {
                res = res ? JSON.parse(res) : [];

                if (!res || !Array.isArray(res) || res.length === 0) {
                    $.toastr.error('No templates found for '+activeType+' category.', {position: 'top-center', time: 2000});
                    return;
                }

                let templateList = `<div class="max-h-80 overflow-y-auto divide-y divide-gray-200">`;
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

                popup.find('.use-template').click(function() {
                    let idx = $(this).data('index');
                    let template = res[idx];
                    if (!template) return;

                    // Set message into CKEditor
                    if (editors[activeType]) {
                        editors[activeType].setData(template.message || '');
                    }

                    popup.remove();
                });

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

    // EMAIL FORM SUBMISSION
    $("#emailInviteForm").on("submit", function(e) {
        e.preventDefault();

        if (!validateEmailInvite(this, editors)) {
            return false;
        }

        let form = $(this);
        let formData = form.serializeArray(); // instead of serialize()

        const replacements = {
            "[POST_TITLE]": $("#postTitle").val() || "",
            "[INVITE_LINK]": $("#inviteLink").val() || ""
        };

        // Assuming editorInstance is your CKEditor instance
        let rawMessage = editors['email'] ? editors['email'].getData().trim() : form.find("textarea[name='message']").val().trim();

        // Replace placeholders dynamically
        let finalMessage = rawMessage;
        for (let placeholder in replacements) {
            finalMessage = finalMessage.replaceAll(placeholder, replacements[placeholder]);
        }

        // Push into formData (or send via AJAX)
        formData.push({
            name: "messageFn", // or whatever key you want
            value: finalMessage
        });

        // Convert to query string if you need
        formData = $.param(formData);

        HoldOn.open({ theme: "sk-bounce", message: "Submitting..." });

        $.ajax({
            url: "../ajax.php?action=sendEmailInvite&isSalesRep=1",
            type: "POST",
            data: formData,
            success: function(response) {
                HoldOn.close();

                try {
                    let res = JSON.parse(response);
                    if (res.success) {
                        $.toastr.success(res.message, { position: 'top-center', time: 3000 });
                        setTimeout(() => { location.reload(); }, 1500);
                    } else {
                        console.log(res);
                        $.toastr.error(res.message, { position: 'top-center', time: 3000 });
                    }
                } catch (err) {
                    $.toastr.error("Unexpected response, please try again.");
                }
            },
            error: function(xhr) {
                HoldOn.close();
                $.toastr.error("Server error: " + xhr.statusText);
            }
        });
    });

    // SMS FORM SUBMISSION
    $("#smsInviteForm").on("submit", function(e) {
        e.preventDefault();

        if (!validateSMSInvite(this, editors)) {
            return false;
        }

        let form = $(this);
        let formData = form.serializeArray(); // instead of serialize()

        const replacements = {
            "[POST_TITLE]": $("#postTitle").val() || "",
            "[INVITE_LINK]": $("#inviteLink").val() || ""
        };

        // Assuming editorInstance is your CKEditor instance
        let rawMessage = editors['sms'] ? editors['sms'].getData().trim() : form.find("textarea[name='message']").val().trim();

        // Replace placeholders dynamically
        let finalMessage = rawMessage;
        for (let placeholder in replacements) {
            finalMessage = finalMessage.replaceAll(placeholder, replacements[placeholder]);
        }

        // Generate plain text (replace <br> with \n and strip other HTML)
        let plainMessage = htmlToSms(finalMessage);       // strip all remaining HTML tags

        // Push both into formData
        formData.push(
            { name: "messageFn", value: finalMessage },   // original (HTML)
            { name: "plainMessage", value: plainMessage } // SMS safe plain text
        );

        // Convert to query string if you need
        formData = $.param(formData);

        HoldOn.open({ theme: "sk-bounce", message: "Submitting..." });

        $.ajax({
            url: "../ajax.php?action=sendPhoneInvite&isSalesRep=1",
            type: "POST",
            data: formData,
            success: function(response) {
                HoldOn.close();

                try {
                    let res = JSON.parse(response);
                    if (res.success) {
                        $.toastr.success(res.message, { position: 'top-center', time: 3000 });
                        setTimeout(() => { location.reload(); }, 1500);
                    } else {
                        $.toastr.error(res.message, { position: 'top-center', time: 3000 });
                    }
                } catch (err) {
                    $.toastr.error("Unexpected response, please try again.");
                }
            },
            error: function(xhr) {
                HoldOn.close();
                $.toastr.error("Server error: " + xhr.statusText);
            }
        });
    });

    // WHATSAPP FORM SUBMISSION
    $("#whatsAppInviteForm").on("submit", function(e) {
        e.preventDefault();

        if (!validateWhatsappInvite(this, editors)) {
            return false;
        }

        let form = $(this);
        let formData = form.serializeArray(); // instead of serialize()

        let phoneField = form.find("input[name='whtsph']");
        if (phoneField.length) {
            let formattedPhone = formatPhoneNumberCleanUS(phoneField.val().trim());
            // Replace in formData
            formData = formData.map(field => {
                if (field.name === "whtsph") {
                    return { name: field.name, value: formattedPhone };
                }
                return field;
            });
        }

        const replacements = {
            "[POST_TITLE]": $("#postTitle").val() || "",
            "[INVITE_LINK]": $("#inviteLink").val() || ""
        };

        // Assuming editorInstance is your CKEditor instance
        let rawMessage = editors['whatsapp'] ? editors['whatsapp'].getData().trim() : form.find("textarea[name='message']").val().trim();

        // Replace placeholders dynamically
        let finalMessage = rawMessage;
        for (let placeholder in replacements) {
            finalMessage = finalMessage.replaceAll(placeholder, replacements[placeholder]);
        }

        // Generate plain text (replace <br> with \n and strip other HTML)
        let plainMessage = htmlToSms(finalMessage);       // strip all remaining HTML tags

        // Push both into formData
        formData.push(
            { name: "messageFn", value: finalMessage },   // original (HTML)
            { name: "plainMessage", value: plainMessage } // SMS safe plain text
        );

        // Convert to query string if you need
        formData = $.param(formData);

        HoldOn.open({ theme: "sk-bounce", message: "Submitting..." });

        $.ajax({
            url: "../ajax.php?action=sendWhatsAppInvite&isSalesRep=1",
            type: "POST",
            data: formData,
            success: function(response) {
                HoldOn.close();
                let res = JSON.parse(response);
                if (res.success) {
                    if (res.whatsappUrl) {
                        openWhatsApp(res.whatsappUrl);
                    }
                }
            },
            error: function(xhr) {
                HoldOn.close();
                $.toastr.error("Server error: " + xhr.statusText);
            }
        });
    });

    $(".copy-mt").on("click", function () {
        // Find parent container with data-type (e.g. copy_share, email, sms, etc.)
        let dataType = $(this).data("type"); 
        
        copyMessageTemplate(dataType, editors);
    });

    $(document).on("click", ".insert-placeholder", function() {
        const placeholder = $(this).data("placeholder");
        const emailType = $(this).data("type");

        if (editors[emailType]) {
            // CKEditor 5 way
            const editor = editors[emailType];
            editor.model.change(writer => {
                editor.model.insertContent(writer.createText(placeholder));
            });
        } else {
            // fallback for plain textarea
            let textarea = $("textarea[name='message']");
            let cursorPos = textarea.prop("selectionStart");
            let text = textarea.val();
            let newText = text.substring(0, cursorPos) + placeholder + text.substring(cursorPos);
            textarea.val(newText);
        }
    });
});

/******************************************* */
// EMAIL INVITE Template

// ✅ Validation Function (using CKEditor value)
function validateEmailInvite(form, editors) {
    let errors = [];

    if (!$(form).parsley().validate()) {
        return false;
    }

    const emailType = form.querySelector("input[name='email_type']")?.value || "email";

    let message = '';
    if (editors[emailType]) {
        message = editors[emailType].getData().trim();
    } else {
        // fallback if textarea only
        message = form.querySelector("textarea[name='message']")?.value.trim() || "";
    }

    const emails = Array.from(form.querySelectorAll("input[name='emails[]']"))
        .map(e => e.value.trim().toLowerCase())
        .filter(Boolean);

    if (emails.length === 0) errors.push("Please enter at least one email address.");

    // ✅ Duplicate check
    const duplicates = emails.filter((email, i) => emails.indexOf(email) !== i);
    if (duplicates.length > 0) {
        errors.push(`Duplicate email(s) found: ${[...new Set(duplicates)].join(", ")}`);
    }

    // ✅ Format validation
    emails.forEach(email => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            errors.push(`Invalid email address: ${email}`);
        }
    });

    if (!message) errors.push("Please enter a message.");

    // ✅ Placeholder validation
    const matches = message.match(/\[[A-Z_]+\]/g) || [];
    matches.forEach(ph => {
        if (!allowedPlaceholders.includes(ph)) {
            errors.push(`Invalid placeholder found: ${ph}. Allowed: ${allowedPlaceholders.join(", ")}`);
        }
    });

    if (errors.length > 0) {
        errors.forEach(msg => {
            $.toastr.error(msg, {position: 'top-center', timeOut: 3000});
        });
        return false;
    }

    return true;
}

// Add/remove email fields
function addEmailField() {
    const container = document.getElementById("email-container");
    const row = document.createElement("div");
    row.className = "flex items-center mb-2 email-row";

    const input = document.createElement("input");
    input.type = "email";
    input.name = "emails[]";
    input.autocomplete = "off";
    input.placeholder = "Enter email address";
    input.required = true;
    input.className = "flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500";

    const btn = document.createElement("button");
    btn.type = "button";
    btn.innerHTML = "x";
    btn.className = "ml-2 text-red-500 hover:text-red-700 font-bold text-lg";
    btn.onclick = function () { row.remove(); };

    row.appendChild(input);
    row.appendChild(btn);
    container.appendChild(row);
}

/******************************************* */
// PHONE INVITE Template

function addPhoneField() {
    const container = document.getElementById("phone-container");

    const row = document.createElement("div");
    row.className = "flex items-center mb-2 phone-row";

    const input = document.createElement("input");
    input.type = "tel";
    input.autocomplete = "off";
    input.name = "phones[]";
    input.placeholder = "(555) 123-4567";
    input.required = true;
    input.className = "flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500";

    // ✅ Add the oninput handler
    input.oninput = function () {
        this.value = formatPhoneNumberUSRC(this.value);
    };

    const btn = document.createElement("button");
    btn.type = "button";
    btn.innerHTML = "x";
    btn.className = "ml-2 text-red-500 hover:text-red-700 font-bold text-lg";
    btn.onclick = function () {
        row.remove();
    };

    row.appendChild(input);
    row.appendChild(btn);
    container.appendChild(row);
}

// validation SMS Invite (using CKEditor value)
function validateSMSInvite(form, editors) {
    let errors = [];

    if (!$(form).parsley().validate()) {
        return false;
    }

    // ✅ Get selected sms_type
    const smsType = form.querySelector("select[name='sms_type']")?.value || "sms";

    // ✅ Dynamically get CKEditor instance
    let message = '';
    if (editors[smsType]) {
        message = editors[smsType].getData().trim();
    }

    // ✅ Get phone numbers
    const phones = Array.from(form.querySelectorAll("input[name='phones[]']"))
        .map(p => p.value.trim())
        .filter(Boolean);

    if (phones.length === 0) errors.push("Please enter at least one phone number.");

    // ✅ Regex for 10-digit or valid US formats
    const phoneRegex = /^(?:\d{10}|(?:\+1\s?)?(?:\(\d{3}\)|\d{3})[-.\s]?\d{3}[-.\s]?\d{4})$/;

    // ✅ Validate each phone
    let invalidPhones = phones.filter(phone => !phoneRegex.test(phone));
    if (invalidPhones.length > 0) {
        errors.push(`Invalid phone(s): ${invalidPhones.join(", ")}`);
    }

    // ✅ Duplicate check
    const duplicates = phones.filter((p, i) => phones.indexOf(p) !== i);
    if (duplicates.length > 0) {
        errors.push(`Duplicate phone(s) found: ${[...new Set(duplicates)].join(", ")}`);
    }

    if (!message) errors.push("Please enter a message.");

    if (errors.length > 0) {
        errors.forEach(msg => {
            $.toastr.error(msg, { position: 'top-center', time: 3000 });
        });
        return false;
    }

    return true;
}

/******************************************* */

// validation SMS Invite (using CKEditor value)
function validateWhatsappInvite(form, editors) {
    let errors = [];

    if (!$(form).parsley().validate()) {
        return false;
    }

    // ✅ Get selected email_type
    const smsType = form.querySelector("select[name='whatsapp']")?.value || "whatsapp";

    // ✅ Get ONLY the first phone number field
    const phoneInput = form.querySelector("input[name='whtsph']");
    const phone = phoneInput ? phoneInput.value.trim() : "";

    if (!phone) {
        errors.push("Please enter a phone number.");
    } else {
        // 🔹 Regex for 10-digit OR US formats
        const phoneRegex = /^(?:\d{10}|(?:\+1\s?)?(?:\(\d{3}\)|\d{3})[-.\s]?\d{3}[-.\s]?\d{4})$/;

        if (!phoneRegex.test(phone)) {
            errors.push("Please enter a valid 10-digit or US formatted phone number.");
        }
    }

    // ✅ Dynamically get CKEditor instance
    let message = '';
    if (editors[smsType]) {
        message = editors[smsType].getData().trim();
    }

    if (!message) errors.push("Please enter a message.");

    if (errors.length > 0) {
        errors.forEach(msg => {
            $.toastr.error(msg, {position: 'top-center',time: 3000});
        });
        return false;
    }

    return true;
}

// Function to format phone number
function formatPhoneNumberUSRC(value) {
    const cleaned = value.replace(/\D/g, "").substring(0, 10); // Only digits, max 10
    const match = cleaned.match(/^(\d{3})(\d{3})(\d{0,4})$/);
    if (match) {
        return `(${match[1]}) ${match[2]}-${match[3]}`;
    }
    return cleaned;
}

// Convert HTML to SMS-safe plain text
function htmlToSms(html) {
  return html
    .replace(/<br\s*\/?>/gi, "\n")    // <br> → new line
    .replace(/<\/p>/gi, "\n")         // </p> → new line
    .replace(/<[^>]+>/g, "")          // remove all other HTML tags
    .replace(/&nbsp;/gi, " ")         // replace non-breaking space
    .replace(/&amp;/gi, "&")          // replace &
    .replace(/&lt;/gi, "<")           // replace <
    .replace(/&gt;/gi, ">")           // replace >
    .replace(/\n\s*\n\s*\n+/g, "\n\n") // collapse too many new lines
    .trim();                          // remove leading/trailing spaces
}

// Function to copy message template with formatting (WhatsApp/Telegram compatible)
function copyMessageTemplate(editorId, editors) {
    let editorData = editors[editorId].getData(); // CKEditor HTML

    // Convert HTML → WhatsApp/Markdown style
    let formattedText = convertHtmlToWhatsApp(editorData);

    // Replace placeholders with hidden field values
    const replacements = {
        "[POST_TITLE]": $("#postTitle").val() || "",
        "[INVITE_LINK]": $("#inviteLink").val() || ""
    };

    // Replace placeholders conditionally
    formattedText = formattedText.replace(/\[(POST_TITLE|INVITE_LINK)\]/g, (match) => {
        switch(match) {
            case "[POST_TITLE]":
                return $("#postTitle").val();
            case "[INVITE_LINK]":
                return $("#inviteLink").val();
            default:
                return "";
        }
    });

    if (navigator.clipboard && window.ClipboardItem) {
        const textInput = new Blob([formattedText], { type: "text/plain" });
        const data = new ClipboardItem({ "text/plain": textInput });

        navigator.clipboard.write([data]).then(() => {
            $.toastr.success("Message copied!", { position: 'top-center', time: 3000 });
        }).catch(err => {
            console.error("Clipboard API failed, fallback:", err);
            fallbackCopy(formattedText);
        });
    } else {
        fallbackCopy(formattedText);
    }
}

// Convert HTML to WhatsApp-compatible formatting
function convertHtmlToWhatsApp(html) {
    let $temp = $("<div>").html(html);

    // Replace bold <b> or <strong> with *bold*
    $temp.find("b, strong").each(function() {
        $(this).replaceWith("*" + $(this).text() + "*");
    });

    // Replace italic <i> or <em> with _italic_
    $temp.find("i, em").each(function() {
        $(this).replaceWith("_" + $(this).text() + "_");
    });

    // Replace underline <u> with ~underline~
    $temp.find("u").each(function() {
        $(this).replaceWith("~" + $(this).text() + "~");
    });

    // Replace lists
    $temp.find("ul li").each(function() {
        $(this).replaceWith("• " + $(this).text() + "\n");
    });
    $temp.find("ol li").each(function(i) {
        $(this).replaceWith((i + 1) + ". " + $(this).text() + "\n");
    });

    // Replace <br> and <p> with new lines
    $temp.find("br").replaceWith("\n");
    $temp.find("p").each(function() {
        $(this).replaceWith($(this).text() + "\n\n");
    });

    // Get final text
    return $temp.text().trim();
}

// Fallback for older browsers
function fallbackCopy(text) {
    const textarea = document.createElement("textarea");
    textarea.value = text;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand("copy");
    document.body.removeChild(textarea);
    $.toastr.success("Message copied!", { position: 'top-center', time: 3000 });
}

// Format phone number to +1XXXXXXXXXX
function formatPhoneNumberCleanUS(input) {
    const cleaned = input.replace(/\D/g, '').substring(0, 10); // Keep only digits, max 10
    if (cleaned.length === 10) {
        return '1' + cleaned;
    }
    return input; // fallback if not 10 digits
}

// Open WhatsApp with pre-filled message
function openWhatsApp(url) {
  window.open(url, '_blank');
}
/******************************************* */