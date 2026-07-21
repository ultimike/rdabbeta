<?php

namespace Drupal\eca_field_widget_actions\Plugin\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\eca\Attribute\EcaAction;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\eca\Plugin\DataType\DataTransferObject;
use Drupal\eca_field_widget_actions\Event\FieldWidgetEvent;

/**
 * Action plugin to set the value for the field widget event.
 */
#[Action(
  id: 'eca_set_field_widget_value',
  label: new TranslatableMarkup('Set field widget value'),
)]
#[EcaAction(
  description: new TranslatableMarkup('This action sets the value for the field widget event.'),
  version_introduced: '1.0.0',
)]
class SetWidgetValue extends ConfigurableActionBase {

  /**
   * {@inheritdoc}
   */
  public function access($object, ?AccountInterface $account = NULL, $return_as_object = FALSE) {
    $result = AccessResult::allowedIf($this->getEvent() instanceof FieldWidgetEvent);
    return $return_as_object ? $result : $result->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function execute(?object $object = NULL): void {
    /** @var \Drupal\eca_field_widget_actions\Event\FieldWidgetEvent $event */
    $event = $this->getEvent();
    $value = $this->configuration['widget_value'];
    if ($this->tokenService->hasTokenData($value)) {
      $value = $this->tokenService->getTokenData($value);
    }
    else {
      $value = $this->tokenService->replaceClear($value);
    }
    if ($value instanceof DataTransferObject) {
      $value = $value->getValue();
      if (is_array($value)) {
        if (isset($value['_string_representation'])) {
          $value = $value['_string_representation'];
        }
        elseif (isset($value['values'])) {
          $value = $value['values'];
        }
      }
    }
    // getTokenData() can return raw Typed Data objects (e.g. StringData,
    // IntegerData) when field property tokens like [my_field:0:value] are
    // used. These are not DataTransferObjects, so they fall through the check
    // above. getString() is defined on TypedDataInterface and always returns
    // a safe string regardless of the underlying primitive type.
    if ($value instanceof TypedDataInterface) {
      $value = $value->getString();
    }
    if (!is_array($value) && !is_string($value)) {
      $value = (string) $value;
    }
    $event->setWidgetValue($value);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'widget_value' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['widget_value'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Field widget value'),
      '#default_value' => $this->configuration['widget_value'],
      '#eca_token_replacement' => TRUE,
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['widget_value'] = $form_state->getValue('widget_value');
    parent::submitConfigurationForm($form, $form_state);
  }

}
