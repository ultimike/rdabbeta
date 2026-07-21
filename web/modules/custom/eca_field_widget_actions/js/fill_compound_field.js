(function (Drupal) {

  'use strict';

  /**
   * Returns the actual form control within el, or el itself if it is one.
   *
   * Drupal sometimes places data-drupal-selector on a wrapper div rather than
   * the <select>/<input> inside it.
   */
  function resolveFormControl(el) {
    if (!el) {
      return null;
    }
    const tag = el.tagName;
    if (tag === 'INPUT' || tag === 'SELECT' || tag === 'TEXTAREA') {
      return el;
    }
    return el.querySelector('select, input:not([type="hidden"]), textarea') || el;
  }

  /**
   * Fills a compound field widget that may require an intermediate AJAX rebuild.
   *
   * Immediate fields (already in the DOM) are set by CSS selector, then a
   * change event triggers the widget's AJAX rebuild.  Deferred values are
   * applied once that rebuild completes by matching inputs with
   * [name$="[key]"] inside the wrapper element.
   *
   * @param {Drupal.Ajax} [ajax]
   * @param {object} response
   * @param {object} response.immediate  - {selector: value} filled immediately.
   * @param {object} response.deferred   - {key: value} filled after AJAX.
   * @param {string|null} response.wrapper - CSS selector scoping deferred fills.
   * @param {number} [status]
   */
  Drupal.AjaxCommands.prototype.ecaFillCompoundField = function (ajax, response, status) {
    const immediate = response.immediate || {};
    const deferred = response.deferred || {};
    const wrapperSelector = response.wrapper || null;

    let lastEl = null;
    Object.entries(immediate).forEach(function ([selector, value]) {
      const container = document.querySelector(selector);
      if (!container) {
        return;
      }
      const formEl = resolveFormControl(container);
      formEl.value = value;
      lastEl = formEl;
    });

    const hasDeferredFills = Object.keys(deferred).length > 0;

    if (!hasDeferredFills) {
      if (lastEl) {
        lastEl.dispatchEvent(new Event('input', { bubbles: true }));
        // Caution: do not dispatch 'change' here: for compound widgets like
        // Address, as the country <select> has a Drupal AJAX handler bound to
        // 'change'. Firing it would rebuild the widget and clear the values we
        // just set.
      }
      return;
    }

    // Trigger the widget AJAX rebuild (e.g. Address country change).
    // lastEl is the actual <select> with its value already set, so the AJAX
    // serializes the correct value and the server returns the full widget.
    if (lastEl) {
      jQuery(lastEl).trigger('change');
    }

    // Register the ajaxComplete listener inside setTimeout(0) so it does not
    // fire for the current ECA button's own ajaxComplete, which fires
    // synchronously after the success callback before any setTimeout runs.
    setTimeout(function () {
      jQuery(document).one('ajaxComplete', function () {
        const scope = wrapperSelector ? document.querySelector(wrapperSelector) : document;
        if (!scope) {
          return;
        }
        Object.entries(deferred).forEach(function ([key, value]) {
          const el = scope.querySelector('[name$="[' + key + ']"]');
          if (el) {
            el.value = value;
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
          }
        });
      });
    }, 0);
  };

})(Drupal);
