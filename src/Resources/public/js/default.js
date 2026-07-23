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

const SettingsCardSearch = {
  SELECTORS: {
    INPUT: '[data-mbiz-settings-search]',
    CARD: '[data-mbiz-settings-card]',
    GROUP: '[data-mbiz-settings-group]',
    EMPTY: '[data-mbiz-settings-search-empty]',
    FIELD_MATCH_INDICATOR: '[data-mbiz-settings-field-match-indicator]',
  },

  CLASSES: {
    HIDDEN: 'd-none',
  },

  normalize(value) {
    return value
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '');
  },

  getCardMatches(card, query) {
    if (!query) {
      return {
        metadata: false,
        fields: false,
      };
    }

    const metadataText = this.normalize(card.dataset.searchMetadata || '');
    const fieldsText = this.normalize(card.dataset.searchFields || '');
    const fallbackText = this.normalize(card.dataset.searchText || '');

    return {
      metadata: metadataText ? metadataText.includes(query) : fallbackText.includes(query),
      fields: fieldsText ? fieldsText.includes(query) : false,
    };
  },

  toggleFieldMatchIndicator(card, isVisible) {
    const indicator = card.querySelector(this.SELECTORS.FIELD_MATCH_INDICATOR);

    if (!indicator) {
      return;
    }

    indicator.classList.toggle(this.CLASSES.HIDDEN, !isVisible);
    indicator.setAttribute('aria-hidden', String(!isVisible));
  },

  filterCards(input, cards, groups, emptyMessage) {
    const query = this.normalize(input.value.trim());
    let visibleCardsCount = 0;

    cards.forEach((card) => {
      const matches = this.getCardMatches(card, query);
      const isVisible = !query || matches.metadata || matches.fields;

      card.classList.toggle(this.CLASSES.HIDDEN, !isVisible);
      this.toggleFieldMatchIndicator(card, Boolean(query && matches.fields));

      if (isVisible) {
        visibleCardsCount += 1;
      }
    });

    groups.forEach((group) => {
      const hasVisibleCard = Array.from(group.querySelectorAll(this.SELECTORS.CARD))
        .some((card) => !card.classList.contains(this.CLASSES.HIDDEN));

      group.classList.toggle(this.CLASSES.HIDDEN, !hasVisibleCard);
    });

    emptyMessage?.classList.toggle(this.CLASSES.HIDDEN, visibleCardsCount > 0);
  },

  autofocus(input) {
    window.requestAnimationFrame(() => {
      if (document.activeElement && document.body !== document.activeElement) {
        return;
      }

      input.focus({ preventScroll: true });
    });
  },

  init() {
    const initialize = () => {
      const input = document.querySelector(this.SELECTORS.INPUT);
      const cards = document.querySelectorAll(this.SELECTORS.CARD);

      if (!input || 0 === cards.length) {
        return;
      }

      const emptyMessage = document.querySelector(this.SELECTORS.EMPTY);
      const groups = document.querySelectorAll(this.SELECTORS.GROUP);

      input.addEventListener('input', () => {
        this.filterCards(input, cards, groups, emptyMessage);
      });

      this.filterCards(input, cards, groups, emptyMessage);
      this.autofocus(input);
    };

    if ('loading' === document.readyState) {
      document.addEventListener('DOMContentLoaded', initialize);

      return;
    }

    initialize();
  }
};

DefaultFieldManager.init();
SettingsCardSearch.init();
