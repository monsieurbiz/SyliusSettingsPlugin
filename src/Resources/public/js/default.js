(function () {
  document.addEventListener("DOMContentLoaded", function() {
    const components = document.querySelectorAll('[data-component]');
    for (const component of components) {
      switch (component.dataset.component) {
        case 'mbiz-default':
          (function (component) {
            const relatedId = component.dataset.relatedId;
            const relatedField = document.getElementById(relatedId);
            if (component.checked) {
              relatedField.disabled = 'disabled';
            }
            component.addEventListener('change', function (e) {
              if (!e.target.checked) {
                relatedField.disabled = '';
                window.setTimeout(function () {
                  relatedField.focus();
                }, 100);
              } else {
                relatedField.disabled = 'disabled';
              }
            });
          })(component);
          break;
        default:
      }
    };
  });
})();
