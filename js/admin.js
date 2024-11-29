(function ($) {
  "use strict";

  $(document).ready(function () {
    $("#add-sentence").click(function () {
      var lastRow = $(".sentence-field").first().clone();
      lastRow.find('input[type="text"]').val("");
      lastRow.find('input[type="checkbox"]').prop("checked", true);
      lastRow.find(".remove-sentence").show();
      $("#sentence-fields").append(lastRow);
    });

    $(document).on("click", ".remove-sentence", function () {
      $(this).closest("tr").remove();
    });
  });
})(jQuery);
