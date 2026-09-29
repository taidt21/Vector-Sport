document.addEventListener('DOMContentLoaded', function () {
  var toggleButtons = document.querySelectorAll('[data-toggle-password]');

  toggleButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      var targetSelector = button.getAttribute('data-toggle-password');
      if (!targetSelector) {
        return;
      }

      var input = document.querySelector(targetSelector);
      if (!input) {
        return;
      }

      var isPassword = input.getAttribute('type') === 'password';
      input.setAttribute('type', isPassword ? 'text' : 'password');
      button.classList.toggle('is-visible', isPassword);
      button.setAttribute('aria-label', isPassword ? 'Ẩn mật khẩu' : 'Hiện hoặc ẩn mật khẩu');
    });
  });
});
