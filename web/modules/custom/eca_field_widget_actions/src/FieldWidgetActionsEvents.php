<?php

namespace Drupal\eca_field_widget_actions;

/**
 * Defines events provided by the ECA FieldWidgetActions module.
 */
final class FieldWidgetActionsEvents {

  /**
   * Dispatches a field widget event.
   *
   * @Event
   *
   * @var string
   */
  public const string FIELD_WIDGET_ACTION = 'eca_field_widget_actions.field_widget_action';

}
