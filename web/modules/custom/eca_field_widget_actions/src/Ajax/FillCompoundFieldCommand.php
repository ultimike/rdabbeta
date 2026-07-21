<?php

namespace Drupal\eca_field_widget_actions\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * AJAX command to fill a compound widget (e.g. Address) with immediate and
 * deferred values.
 *
 * Immediate values are written to existing DOM elements by CSS selector.
 * Deferred values are written by name-suffix after the widget's AJAX rebuild
 * (triggered by the change event fired on the last immediate element).
 */
class FillCompoundFieldCommand implements CommandInterface {

  /**
   * @param array $immediate
   *   Map of CSS selector => string value for fields already in the DOM.
   * @param array $deferred
   *   Map of field key => string value for fields not yet rendered.
   * @param string|null $wrapper
   *   CSS selector for the widget wrapper to scope deferred fills.
   */
  public function __construct(
    protected array $immediate,
    protected array $deferred,
    protected ?string $wrapper,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function render(): array {
    return [
      'command' => 'ecaFillCompoundField',
      'immediate' => $this->immediate,
      'deferred' => $this->deferred,
      'wrapper' => $this->wrapper,
    ];
  }

}
