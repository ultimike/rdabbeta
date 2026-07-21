# ECA Field Widget Actions

Integrates [ECA](https://www.drupal.org/project/eca) with the
[Field Widget Actions](https://www.drupal.org/project/field_widget_actions)
module, allowing you to build custom field widget action buttons powered by
ECA models.

## How it works

The Field Widget Actions module attaches action buttons to form fields -- for
example a "Suggest" button next to a text field. This module bridges those
buttons to ECA so you can define what happens when a button is clicked using
an ECA model instead of custom code.

1. Create an ECA model with the **ECA Field Widget** event.
2. The module automatically registers a Field Widget Action plugin for that
   model. It appears in the list of available actions for any field widget.
3. When a user clicks the action button on a form, the ECA event fires with
   the current entity, field name, and field index as context.
4. Your ECA model evaluates conditions and executes actions -- for example
   calling an external API or processing the entity's existing field values.
5. Use the **Set field widget value** action to return a list of suggestions
   to the field widget.

This is useful for content suggestions, AI-powered field filling, data
lookups, or any scenario where a field value should be computed on demand.
