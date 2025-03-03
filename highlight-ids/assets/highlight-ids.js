jQuery(document).ready(function ($) {
    $(document).on("click", "#wp-admin-bar-highlight_ids a", function (e) {
        e.preventDefault();

        let idMap = {};
        $(highlightIDs.targetClasses + " [id]").each(function () {
            let elementId = $(this).attr("id");
            if (!idMap[elementId]) {
                idMap[elementId] = [];
            }
            idMap[elementId].push(this);
        });

        $(highlightIDs.targetClasses + " [id]").each(function () {
            let elementId = $(this).attr("id");
            let isDuplicate = idMap[elementId].length > 1;

            $(this).toggleClass("highlight-outline");
            let warningText = isDuplicate ? " ⚠️" : "";
            $(this).attr("data-id-warning", warningText);
        });
    });
});
