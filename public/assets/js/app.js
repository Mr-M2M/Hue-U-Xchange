/* Hue U Xchange - progressive enhancement only. Every feature works
   without JavaScript; this file just prevents accidental double
   submission by disabling a form's submit button once it is sent. */
(function () {
  document.addEventListener('submit', function (event) {
    var form = event.target;
    if (form.getAttribute('data-once') === null) { return; }
    var button = form.querySelector('button[type="submit"]');
    if (button) {
      button.disabled = true;
      button.setAttribute('aria-disabled', 'true');
      if (button.getAttribute('data-busy-label')) {
        button.textContent = button.getAttribute('data-busy-label');
      }
    }
  });
})();
