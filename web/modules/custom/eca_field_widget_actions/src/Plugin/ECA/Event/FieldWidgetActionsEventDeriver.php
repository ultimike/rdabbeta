<?php

namespace Drupal\eca_field_widget_actions\Plugin\ECA\Event;

use Drupal\eca\Plugin\ECA\Event\EventDeriverBase;

/**
 * Deriver for ECA FieldWidgetActions event plugins.
 */
class FieldWidgetActionsEventDeriver extends EventDeriverBase {

  /**
   * {@inheritdoc}
   */
  protected function definitions(): array {
    return FieldWidgetActionsEvent::definitions();
  }

}
