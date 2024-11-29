(function ($) {
  "use strict";

  $(document).ready(function () {
    var slider = $("#sentence-slider");
    var sentences = slider.find(".slider-sentence");
    var currentIndex = 0;
    var timing = parseInt(slider.data("timing")) || 5000; // Default if not set

    function showNextSentence() {
      sentences.removeClass("active");
      $(sentences[currentIndex]).addClass("active");
      currentIndex = (currentIndex + 1) % sentences.length;
    }

    if (sentences.length > 0) {
      showNextSentence(); // Show first sentence
      setInterval(showNextSentence, timing);
    }
  });
})(jQuery);
