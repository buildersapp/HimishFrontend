$(document).ready(function () {
    $('#saveTemplateCheckbox').on('change', function () {
        let isChecked = $(this).is(':checked') ? 1 : 0;

        $.ajax({
            url: '../ajax.php/?action=save_message_template_sp&isSalesRep=1', // your backend route
            type: 'POST',
            data: { save_template: isChecked },
            success: function (response) {
                let responseJson = JSON.parse(response);
                if(responseJson.success == 1){
                    $.toastr.success('Setting updated successfully.', {position: 'top-center',time: 2000});
                }else{
                    $.toastr.error('Failed to update setting. Please try again.', {position: 'top-center',time: 2000});
                }
            },
            error: function (xhr, status, error) {
                console.error("Error:", error);
            }
        });
    });
});