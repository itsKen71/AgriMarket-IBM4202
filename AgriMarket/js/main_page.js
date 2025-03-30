$(document).ready(function () {
    let categories = $(".category-btn");
    let currentIndex = 0;
    const itemsPerView = 3;
    const totalItems = categories.length;

    function updateCategoryView() {
        categories.hide();
        categories.slice(currentIndex, currentIndex + itemsPerView).show();
    }

    $("#prevCategory").click(function () {
        if (currentIndex > 0) {
            currentIndex--;
            updateCategoryView();
        }
    });

    $("#nextCategory").click(function () {
        if (currentIndex + itemsPerView < totalItems) {
            currentIndex++;
            updateCategoryView();
        }
    });

    $(".category-btn").click(function () {
        let categoryId = $(this).data("category");
        window.location.href = "?category_id=" + categoryId;
    });

    const selectedCategoryId = new URLSearchParams(window.location.search).get("category_id");
    if (selectedCategoryId) {
        const selectedCategoryIndex = categories.index($(`.category-btn[data-category="${selectedCategoryId}"]`));
        if (selectedCategoryIndex >= 0) {
            currentIndex = Math.max(0, Math.min(selectedCategoryIndex, totalItems - itemsPerView));
        }
    }

    updateCategoryView();
});