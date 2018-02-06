<?php

namespace Drupal\oe_piwik\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the PiwikRule entity.
 *
 * @ingroup oe_piwik_rule
 *
 * @ContentEntityType(
 *   id = "oe_piwik_rule",
 *   label = @Translation("OE Piwik - Rules"),
 *   label_collection = @Translation("OE Piwik rules"),
 *   label_singular = @Translation("OE Piwik rules item"),
 *   label_plural = @Translation("OE Piwik rules items"),
 *   label_count = @PluralTranslation(
 *     singular = "@count OE Piwik rules item",
 *     plural = "@count OE Piwik rules items"
 *   ),
 *   handlers = {
 *     "storage_schema" = "Drupal\oe_piwik\OEPiwikSchema",
 *     "list_builder" = "Drupal\oe_piwik\Entity\Controller\PiwikRuleListBuilder",
 *     "form" = {
 *        "add" = "Drupal\oe_piwik\Form\OEPiwikRulesForm",
 *        "edit" = "Drupal\oe_piwik\Form\OEPiwikRulesForm",
 *        "delete" = "Drupal\oe_piwik\Form\OEPiwikRulesDeleteForm",
 *     },
 *   },
 *   list_cache_contexts = { "user" },
 *   base_table = "oe_piwik_rule",
 *   admin_permission = "administer piwik settings",
 *   entity_keys = {
 *    "id" = "id",
 *   },
 *   links = {
 *    "add-form" = "/admin/config/system/webtools/piwik/advanced_rules/{oe_piwik_rule}/add",
 *    "edit-form" = "/admin/config/system/webtools/piwik/advanced_rules/{oe_piwik_rule}/edit",
 *    "delete-form" = "/admin/config/system/webtools/piwik/advanced_rules/{oe_piwik_rule}/delete",
 *   },
 * )
 */
class PiwikRule extends ContentEntityBase {

  const DIRECT_PATH = 'direct';
  const REGEXP_PATH = 'regexp';

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Piwik rule entity.'))
      ->setReadOnly(TRUE);
    $fields['rule_language'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Language'))
      ->setDescription(t('The language of the Piwik rule entity.'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 12,
        'text_processing' => 0,
      ]);
    $fields['rule_path'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Site path'))
      ->setDescription(t('The site path of the Piwik rule entity.'))
      ->setSettings([
        'default_value' => '',
        'text_processing' => 0,
      ]);
    $fields['rule_path_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Rule path type'))
      ->setDescription(t('The rule path type of the Piwik rule entity.'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 16,
        'text_processing' => 0,
      ]);
    $fields['rule_section'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Site section'))
      ->setDescription(t('The site section of the Piwik rule entity.'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 128,
        'text_processing' => 0,
      ]);
    return $fields;
  }

}
