const DefaultFieldManager = {
  EVENTS: {
    FIELD_DISABLED: 'mbiz:config:disabled-field',
    FIELD_ENABLED: 'mbiz:config:enabled-field'
  },

  SELECTORS: {
    COMPONENTS: '[data-component]',
    FILE_MANAGER_FIELD: '.monsieurbiz-sylius-file-manager__field',
    DEFAULT_COMPONENT: 'mbiz-default',
  },

  CLASSES: {
    DISABLED_INPUT: 'disabled-input',
  },

  FOCUS_DELAY: 100,

  /**
   * @param {HTMLElement} relatedInput
   */
  disableRelatedInput(relatedInput) {
    relatedInput.disabled = true;
    relatedInput.parentNode.classList.add(this.CLASSES.DISABLED_INPUT);

    this.handleFileTypeField(relatedInput, true);
    this.dispatchFieldEvent(this.EVENTS.FIELD_DISABLED, relatedInput);
  },

  /**
   * @param {HTMLElement} relatedInput
   */
  enableRelatedInput(relatedInput) {
    relatedInput.disabled = false;
    relatedInput.parentNode.classList.remove(this.CLASSES.DISABLED_INPUT);

    this.handleFileTypeField(relatedInput, false);
    this.dispatchFieldEvent(this.EVENTS.FIELD_ENABLED, relatedInput);

    setTimeout(() => relatedInput.focus(), this.FOCUS_DELAY);
  },

  /**
   * Custom treatment for file type fields because the form type adds buttons to manage files
   *
   * @param {HTMLElement} relatedInput
   * @param {boolean} isDisabled
   */
  handleFileTypeField(relatedInput, isDisabled) {
    if (!relatedInput.dataset.fileType) return;

    const buttonContainer = relatedInput.closest(this.SELECTORS.FILE_MANAGER_FIELD)?.lastElementChild;
    if (buttonContainer) {
      if (isDisabled) {
        buttonContainer.classList.add(this.CLASSES.DISABLED_INPUT);
      } else {
        buttonContainer.classList.remove(this.CLASSES.DISABLED_INPUT);
      }
    }
  },

  /**
   * @param {string} eventName
   * @param {HTMLElement} relatedInput
   */
  dispatchFieldEvent(eventName, relatedInput) {
    document.dispatchEvent(new CustomEvent(eventName, {
      detail: { relatedInput }
    }));
  },

  /**
   * @param {HTMLElement} component
   */
  initDefaultComponent(component) {
    const relatedId = component.dataset.relatedId;
    const relatedInput = document.getElementById(relatedId);
    if (!relatedInput) {
      return;
    }

    if (component.checked) {
      this.disableRelatedInput(relatedInput);
    }
    component.addEventListener('change', (e) => {
      if (e.target.checked) {
        this.disableRelatedInput(relatedInput);
      } else {
        this.enableRelatedInput(relatedInput);
      }
    });
  },

  init() {
    document.addEventListener('DOMContentLoaded', () => {
      const components = document.querySelectorAll(this.SELECTORS.COMPONENTS);

      components.forEach(component => {
        if (component.dataset.component === this.SELECTORS.DEFAULT_COMPONENT) {
          this.initDefaultComponent(component);
        }
      });
    });
  }
};

DefaultFieldManager.init();
