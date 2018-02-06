<?php

namespace Drupal\oe_piwik\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements an oe_piwik_rule form.
 */
class OEPiwikRulesForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $piwik_rule = $this->entity;
    $form['rule_section'] = [
      '#title' => t('Site section'),
      '#type' => 'textfield',
      '#default_value' => isset($piwik_rule->get('rule_section')->value) ? $piwik_rule->get('rule_section')->value : '',
      '#required' => TRUE,
      '#description' => t('Enter the name of the site section.'),
    ];
    // Preparing the PIWIK rule language options.
    $languages_enabled = \Drupal::languageManager()->getLanguages();
    foreach ($languages_enabled as $key_lang => $lang) {
      $languages_enabled[$key_lang] = t($lang->getName());
    }
    $languages_enabled = array_merge(['all' => t('All')], $languages_enabled);
    $form['rule_language'] = [
      '#title' => t('Language'),
      '#type' => 'select',
      '#options' => $languages_enabled,
      '#default_value' => isset($piwik_rule->get('rule_language')->value) ? $piwik_rule->get('rule_language')->value : 'all',
      '#required' => TRUE,
      '#description' => t(
        'Select the language for the section. You can use "All" if the given rule
        should be applied for all languages.'
      ),
    ];
    // Setting up default rule path type.
    $form['rule_type'] = [
      '#title' => t('Select rule type'),
      '#type' => 'radios',
      '#options' => [
        $piwik_rule::DIRECT_PATH => t('Direct path'),
        $piwik_rule::REGEXP_PATH => t('Path based on regular expression'),
      ],
      '#limit_validation_errors' => [],
      '#default_value' => $piwik_rule->isNew() ? $piwik_rule::DIRECT_PATH : $piwik_rule->get('rule_path_type')->value,
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::oePiwikRulePathTypeSelection',
        'wrapper' => 'specifics-for-piwik-rule-path-type',
        'effect' => 'fade',
        'event' => 'change',
        'progress' => [
          'type' => 'throbber',
          'message' => NULL,
        ],
      ],
    ];
    // This fieldset just serves as a container for the part of the form
    // that gets rebuilt.
    $form['specifics'] = [
      '#type' => 'item',
      '#prefix' => '<div id="specifics-for-piwik-rule-path-type">',
      '#suffix' => '</div>',
    ];
    // Setting up the dynamic values based on the PIWIK rule type.
    $rule_path_type = '';
    $rule_path_desc = '';
    $op = isset($form_state->getStorage()['form_display']) ? $form_state->getStorage()['form_display'] : NULL;
    $op = $op->getMode();
    $values = $form_state->getValues();
    switch ($op) {
      case 'add':
        if ($form_state->getValue('rule_type')) {
          $rule_path_type = $form_state->getValue('rule_type');
          $rule_path_desc = $this->oePiwikRulePathDescription($rule_path_type);
        }
        else {
          $rule_path_type = $piwik_rule::DIRECT_PATH;
          $rule_path_desc = $this->oePiwikRulePathDescription($rule_path_type);
        }
        break;

      case 'edit':
        if ($form_state->getValue('rule_type')) {
          $rule_path_type = $form_state->getValue('rule_type');
          $rule_path_desc = $this->oePiwikRulePathDescription($rule_path_type);
        }
        else {
          $rule_path_type = $piwik_rule->get('rule_path_type')->value;
          $rule_path_desc = $this->oePiwikRulePathDescription($rule_path_type);
        }
        break;
    }
    $form['specifics']['rule_path'] = [
      '#title' => t('Site path'),
      '#type' => 'textfield',
      '#default_value' => isset($piwik_rule->get('rule_path')->value) ? $piwik_rule->get('rule_path')->value : '',
      '#required' => TRUE,
      '#description' => $rule_path_desc,
    ];
    $form['rule_path_type'] = [
      '#type' => 'value',
      '#value' => $rule_path_type,
    ];
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Save'),
      '#weight' => 40,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    // Verify that every path is prefixed with a slash, but don't check PHP
    // code snippets.
    if ($form_state->getValue('rule_path') !== "" && $form_state->getValue('rule_type') == 'direct') {
      if (strpos($form_state->getValue('rule_path'), '/') !== 0 && $form_state->getValue('rule_path') !== '<front>') {
        $form_state->setErrorByName('rule_path', t('Path "@page" not prefixed with slash.', ['@page' => $form_state->getValue('rule_path')]));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    // Redirect to term list after save.
    $form_state->setRedirect('entity.oe_piwik_rule.collection');
    $entity = $this->getEntity();

    if ($entity->isNew()) {
      $rule_msg = t('Rule created succesfully');
    }
    else {
      $rule_msg = t('Rule edited succesfully');
    }

    $entity->save();

    drupal_set_message($rule_msg, 'status', TRUE);
  }

  /**
   * Ajax callback triggered when the cache purge type is changed.
   */
  public function oePiwikRulePathTypeSelection(array &$form, FormStateInterface $form_state) {
    return $form['specifics'];
  }

  /**
   * Get the description for the PIWIK rule path field.
   *
   * @param string $rule_path_type
   *   PIWIK rule patt type.
   *
   * @return string
   *   Description for the field.
   */
  public function oePiwikRulePathDescription($rule_path_type) {
    $piwik_rule = $this->entity;
    $description = '';
    switch ($rule_path_type) {
      case $piwik_rule::DIRECT_PATH:
        $description = t('Enter the direct path for the rule. The path should be relative to the base URL. <br/> Example: "/content/test-page"');
        break;

      case $piwik_rule::REGEXP_PATH:
        $wildcard_descriptions = [
          t('"^/admin/*" - All administrative pages'),
          t('"^/content/*" - All pages following "content" part in the path'),
        ];
        $description = '<p>' . t('Enter the regular expression path pattern for the rule.') . '</p>';
        $description .= '<p>' . t('You can check your expression at the
          <a href="@regex101">Regex101 page</a>.',
            ['@regex101' => 'https://regex101.com']
          ) . '</p>';
        $description .= '<p>' . t('Below you can find some examples:') . '</p>';
        $wildcard_description = [
          '#theme' => 'item_list',
          '#type' => 'ul',
          '#items' => $wildcard_descriptions,
        ];
        $description .= drupal_render(
          $wildcard_description
        );
        break;
    }
    return $description;
  }

}
