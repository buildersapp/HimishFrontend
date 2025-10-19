document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".company-filter-btn").forEach(button => {
        button.addEventListener("click", function () {
            let filterValue = this.getAttribute("data-filter");
            let url = new URL(window.location.href);
            
            if (filterValue) {
                url.searchParams.set("product_service_id", filterValue);
            } else {
                url.searchParams.delete("product_service_id");
            }

            window.location.href = url.toString(); // Refresh page with new parameter
        });
    });
});
