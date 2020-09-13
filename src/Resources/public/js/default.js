(function () {
  document.addEventListener("DOMContentLoaded", function() {
    const components = document.querySelectorAll('[data-component]');
    for (const component of components) {
      switch (component.dataset.component) {
        case 'mbiz-default':
          (function (component) {
            const relatedId = component.dataset.relatedId;
            const relatedInput = document.getElementById(relatedId);
            if (component.checked) {
              relatedInput.disabled = 'disabled';
            }
            component.addEventListener('change', function (e) {
              if (!e.target.checked) {
                relatedInput.disabled = '';
                window.setTimeout(function () {
                  relatedInput.focus();
                }, 100);
              } else {
                relatedInput.disabled = 'disabled';
              }
            });

            // Reorganize the two fields
            if (component.dataset.reorganize) {
              var valueField = relatedInput.closest('.field');
              var defaultField = component.closest('.field');
              var fieldsContainer = document.createElement('div');
              var grid = document.createElement('div');

              valueField.parentNode.insertBefore(fieldsContainer, valueField);
              fieldsContainer.appendChild(grid);
              grid.appendChild(valueField);
              grid.appendChild(defaultField);

              fieldsContainer.className = 'field';
              grid.className = 'ui grid';
              valueField.className = 'field twelve wide column';
              defaultField.className = 'field four wide column';
            }
          })(component);
          break;
        default:
      }
    };
  });
})();
