<?php

namespace Drupal\eca_field_widget_actions\Plugin\ECA\Event;

use Drupal\eca\Attribute\EcaEvent;
use Drupal\eca\Attribute\Token;
use Drupal\eca\Entity\Objects\EcaEvent as EcaEventObject;
use Drupal\eca\Event\Tag;
use Drupal\eca\Event\TokenGenerateEvent;
use Drupal\eca\Plugin\ECA\Event\EventBase;
use Drupal\eca_field_widget_actions\Event\FieldWidgetEvent;
use Drupal\eca_field_widget_actions\FieldWidgetActionsEvents;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Plugin implementation of the ECA FieldWidgetActions Events.
 */
#[EcaEvent(
  id: 'eca_field_widget_actions',
  deriver: 'Drupal\eca_field_widget_actions\Plugin\ECA\Event\FieldWidgetActionsEventDeriver',
  version_introduced: '1.0.0',
)]
class FieldWidgetActionsEvent extends EventBase {

  /**
   * {@inheritdoc}
   */
  public static function definitions(): array {
    $actions = [];
    $actions['eca_field_widget'] = [
      'label' => 'ECA Field Widget',
      'event_name' => FieldWidgetActionsEvents::FIELD_WIDGET_ACTION,
      'event_class' => FieldWidgetEvent::class,
      'tags' => Tag::RUNTIME,
      'version_introduced' => '2.1.11',
    ];
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function generateWildcard(string $eca_config_id, EcaEventObject $ecaEvent): string {
    switch ($this->getDerivativeId()) {

      case 'eca_field_widget':
        // Prefix the event ID with the ECA model ID so that event IDs which
        // happen to be identical across different ECA models (e.g. when a
        // model gets cloned) do not collide. See issue #3588904.
        return $eca_config_id . '.' . $ecaEvent->getId();

      default:
        return parent::generateWildcard($eca_config_id, $ecaEvent);

    }
  }

  /**
   * {@inheritdoc}
   */
  public static function appliesForWildcard(Event $event, string $event_name, string $wildcard): bool {
    if ($event instanceof FieldWidgetEvent) {
      return $event->getEventId() === $wildcard;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  #[Token(
    name: 'entity',
    description: 'The entity being edited.',
    classes: [FieldWidgetEvent::class],
  )]
  #[Token(
    name: 'field_name',
    description: 'The name of the entity field for which the field widget is used.',
    classes: [FieldWidgetEvent::class],
  )]
  #[Token(
    name: 'field_key',
    description: 'The index of the field starting with zero and going up if it is a multi-value field.',
    classes: [FieldWidgetEvent::class],
  )]
  public function getData(string $key): mixed {
    if ($this->event instanceof TokenGenerateEvent) {
      return $this->event->getData()[$key] ?? parent::getData($key);
    }
    if ($this->event instanceof FieldWidgetEvent) {
      switch ($key) {
        case 'entity':
          return $this->event->getEntity();

        case 'field_name':
          return $this->event->getFieldName();

        case 'field_key':
          return $this->event->getFieldKey();

      }
    }
    return parent::getData($key);
  }

}
