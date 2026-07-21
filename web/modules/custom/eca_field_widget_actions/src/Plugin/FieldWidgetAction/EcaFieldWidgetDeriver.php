<?php

namespace Drupal\eca_field_widget_actions\Plugin\FieldWidgetAction;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\State\StateInterface;
use Drupal\eca_field_widget_actions\FieldWidgetActionsEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Deriver for field widgets.
 */
final class EcaFieldWidgetDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The Drupal state.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected StateInterface $state;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id): EcaFieldWidgetDeriver {
    $instance = new self();
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->state = $container->get('state');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition): array {
    $this->derivatives = [];

    $subscribed = current($this->state->get('eca.subscribed', [])[FieldWidgetActionsEvents::FIELD_WIDGET_ACTION] ?? []);
    if (!$subscribed) {
      return $this->derivatives;
    }
    /** @var \Drupal\eca\Entity\EcaStorage $eca_storage */
    $eca_storage = $this->entityTypeManager->getStorage('eca');
    foreach ($subscribed as $eca_id => $wildcards) {
      /** @var \Drupal\eca\Entity\Eca|null $eca */
      $eca = $eca_storage->load($eca_id);
      if ($eca === NULL || !$eca->status()) {
        // If an ECA model got deleted or is disabled, we may end up here and
        // then ignore this model.
        continue;
      }
      foreach ($eca->getUsedEvents() as $usedEvent) {
        if ($usedEvent->getPlugin()->getPluginId() === 'eca_field_widget_actions:eca_field_widget') {
          // Use the wildcard (already namespaced by the ECA model ID) as the
          // derivative ID. This prevents collisions when event IDs are
          // identical across different ECA models (e.g. after cloning).
          // See issue #3588904.
          $derivative_id = $wildcards[$usedEvent->getId()] ?? ($eca_id . '.' . $usedEvent->getId());
          $this->derivatives[$derivative_id] = [
            'label' => $usedEvent->getLabel(),
          ] + $base_plugin_definition;
        }
      }
    }
    return $this->derivatives;
  }

}
