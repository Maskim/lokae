(function ($) {
  $(document).ready(function () {
    var pickupTime = $("#pickup-time");
    var yesterday = $("#resource-get-before");

    pickupTime.on("change", function () {
      if (pickupTime.val() === "18:30") {
        yesterday.prop("checked", true);
        yesterday.parent().css("pointer-events", "none");
      } else {
        yesterday.prop("checked", false);
        yesterday.prop("readonly", false);
        yesterday.parent().css("pointer-events", "all");
      }
    });
  });
})(jQuery);
