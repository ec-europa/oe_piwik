<?php

namespace Drupal\oe_piwik\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for deleting a oe_piwik entity.
 *
 * @ingroup oe_piwik
 */
class OEPiwikRulesDeleteForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete entity %name?', ['%name' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   *
   * If the delete command is canceled, return to the contact list.
   */
  public function getCancelUrl() {
    return new Url('entity.oe_piwik_rule.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   *
   * Delete the entity and log the event. logger() replaces the watchdog.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity = $this->getEntity();
    $entity->delete();
    $this->logger('oe_piwik')->notice('Deleted Piwik Rule ID: %id.',
      [
        '%id' => $this->entity->id(),
      ]);
    // Redirect to term list after delete.
    $form_state->setRedirect('entity.oe_piwik_rule.collection');

    drupal_set_message(t('Rule deleted succesfully'), 'status', TRUE);
  }

}
