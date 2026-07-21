<?php

namespace Drupal\eca_field_widget_actions\Hook;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\eca\Entity\Eca;
use Drupal\field_widget_actions\PluginManager\FieldWidgetActionManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Implements ECA entity hooks for the ECA-FWA module.
 */
class EcaEntityHooks {

  public function __construct(
    #[Autowire(service: 'plugin.manager.field_widget_actions')]
    protected FieldWidgetActionManager $fieldWidgetActionManager,
  ) {}

  /**
   * Implements hook_entity_insert().
   */
  #[Hook('entity_insert')]
  public function entityInsert(EntityInterface $entity): void {
    $this->entityUpdate($entity);
  }

  /**
   * Implements hook_entity_update().
   */
  #[Hook('entity_update')]
  public function entityUpdate(EntityInterface $entity): void {
    if ($entity instanceof Eca) {
      foreach ($entity->getUsedEvents() as $usedEvent) {
        if ($usedEvent->getPlugin()->getPluginId() === 'eca_field_widget_actions:eca_field_widget') {
          $this->fieldWidgetActionManager->clearCachedDefinitions();
          return;
        }
      }
    }
  }

}
