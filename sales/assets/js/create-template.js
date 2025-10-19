// 🌍 Make editors accessible globally
let editors = []; // store editor instances

$(document).ready(function () {
    $('.template-message').each(function (index, element) {
        ClassicEditor
            .create(element)
            .then(editor => {
                editors.push(editor); // keep reference
                console.log("CKEditor initialized for:", element);
                console.log("Editors available:", editors);
            })
            .catch(error => {
                console.error("CKEditor init error:", error);
            });
    });
});

/******************************************* */

function validateCreateTemplate(form) {
    let errors = [];

    // ✅ 1. Run Parsley validation first
    if (!$(form).parsley().validate()) {
        return false;
    }

    // ✅ 2. Custom validation
    const title = document.getElementById("title").value.trim();
    if (!title) {
        errors.push("Please enter title.");
    }

    const type = document.getElementById("type").value.trim();
    if (!type) {
        errors.push("Please select share type.");
    }

    // ✅ 3. CKEditor validation
    Object.keys(editors).forEach(key => {
        const messageContent = editors[key].getData().trim();
        if (!messageContent) {
            errors.push("Please enter message content.");
        }
    });

    // ✅ 4. Show errors
    if (errors.length > 0) {
        errors.forEach(msg => {
            $.toastr.error(msg, {position: 'top-center',time: 3000});
        });
        return false; // prevent submit
    }

    // ✅ 5. Show HoldOn if everything is valid
    HoldOn.open({
        theme: "sk-bounce",
        message: "Hold on, we're submitting your request...",
    });

    return true; // allow submit
}