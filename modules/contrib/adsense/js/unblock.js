(function (Drupal, drupalSettings) {
  Drupal.behaviors.adSenseUnblock = {
    attach: () => {
      setTimeout(() => {
        document.querySelectorAll('.adsense').forEach((el) => {
          let insEl = el.querySelector('ins');
          if (insEl.innerHTML.length === 0) {
            el.innerHTML = Drupal.t("Please, enable ads on this site. By using ad-blocking software, you're depriving this site of revenue that is needed to keep it free and current. Thank you.");
            el.style.overflow = 'hidden';
            el.style.fontSize = 'smaller';
          }
        });
        // Wait 3 seconds for adsense async to execute.
      }, 3000);
    }
  };
})(Drupal, drupalSettings);
