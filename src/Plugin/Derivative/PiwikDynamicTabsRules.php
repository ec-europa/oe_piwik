<?php

namespace Drupal\oe_piwik\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;

/**
 * Defines dynamic local tasks.
 */
class PiwikDynamicTabsRules extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $state_advanced_rules = \Drupal::config('oe_piwik.settings')->get('oe_piwik_rules_state');
    if ($state_advanced_rules < 1) {
      return;
    }
    $this->derivatives['entity.oe_piwik_rule.collection'] = $base_plugin_definition;
    $this->derivatives['entity.oe_piwik_rule.collection']['title'] = t("Advanced rules");
    $this->derivatives['entity.oe_piwik_rule.collection']['route_name'] = 'entity.oe_piwik_rule.collection';
    $this->derivatives['entity.oe_piwik_rule.collection']['base_route'] = 'oe_piwik.settings';
    return $this->derivatives;
  }

}
