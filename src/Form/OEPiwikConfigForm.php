<?php

namespace Drupal\oe_piwik\Form;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure OE Piwik settings for this site.
 */
class OEPiwikConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'oe_piwik_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'oe_piwik.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $default_config = \Drupal::config('oe_piwik.settings');
    return [
      'oe_piwik_visibility_roles' => $default_config->get('oe_piwik.oe_piwik_visibility_roles'),
      'oe_piwik_visibility_pages' => $default_config->get('oe_piwik.oe_piwik_visibility_pages'),
      'oe_piwik_site_search' => $default_config->get('oe_piwik.oe_piwik_site_search'),
      'oe_piwik_pages' => $default_config->get('oe_piwik.oe_piwik_pages'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('oe_piwik.settings');

    // Disabled HTML5 Validation, HTML5 Dosnt work when form has Tabs.
    $form['#attributes']['novalidate'] = 'novalidate';
    $form['#attached']['library'][] = 'oe_piwik/oe_piwik';
    // Basic account configuration.
    $form['account'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('General settings'),
    ];
    $form['account']['oe_piwik_site_id'] = [
      '#default_value' => $config->get('oe_piwik_site_id'),
      '#description' => $this->t('The user account number is unique to the websites domain. Click the <strong>Settings</strong> link in your Piwik account, then the <strong>Websites</strong> tab and enter the appropriate site <strong>ID</strong> into this field.'),
      '#maxlength' => 20,
      '#required' => TRUE,
      '#size' => 15,
      '#title' => $this->t('Piwik site ID'),
      '#type' => 'textfield',
    ];
    $form['account']['oe_piwik_smartloader_prurl'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Smarloader Protocol-Relative URL'),
      '#default_value' => $config->get('oe_piwik_smartloader_prurl'),
      '#size' => 80,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#description' => t("The URL of the default webtools smartloader is '//europa.eu/webtools/load.js'"),
    ];
    $form['account']['oe_piwik_site_instance'] = [
      '#type' => 'select',
      '#title' => $this->t('Piwik instance'),
      '#options' => [
        "europa.eu" => t("europa.eu"),
        "ec.europa.eu" => t("ec.europa.eu"),
        "testing" => t("Test instance"),
      ],
      '#default_value' => $config->get('oe_piwik_site_instance'),
      '#required' => TRUE,
      '#description' => t("Define the Piwik instance"),
    ];
    $form['account']['oe_piwik_utility'] = [
      '#type' => 'select',
      '#title' => $this->t('Piwik utility'),
      '#options' => [
        "piwik" => t("Piwik"),
        "piwiktest" => t("Piwiktest"),
      ],
      '#default_value' => $config->get('oe_piwik_utility'),
      '#required' => TRUE,
      '#description' => t("Use Piwik as default value for production sites, and only change to Piwiktest in case there of testing mode for new webtools smart loader versions"),
    ];

    // Visibility settings.
    $form['tracking_title'] = [
      '#type' => 'item',
      '#title' => $this->t('Tracking scope'),
    ];
    $form['tracking'] = [
      '#type' => 'vertical_tabs',
    ];

    // Site paths.
    $form['site_path'] = [
      '#type' => 'details',
      '#title' => $this->t('Site paths'),
      '#group' => 'tracking',
    ];
    $form['site_path']['oe_piwik_site_path'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Site paths'),
      '#default_value' => $config->get('oe_piwik_site_path'),
      '#required' => TRUE,
      '#description' => t("Specify site paths to be tracked in Piwik. Add paths by using their domain name e.g. <strong>ec.europa.eu/agriculture</strong>. Enter one path per line."),
      '#rows' => 10,
    ];

    // Site setion.
    $form['site_section'] = [
      '#type' => 'details',
      '#title' => $this->t('Site section'),
      '#group' => 'tracking',
    ];
    $form['site_section']['oe_piwik_site_section'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Site section'),
      '#default_value' => $config->get('oe_piwik_site_section'),
      '#size' => 80,
      '#maxlength' => 255,
      '#required' => FALSE,
      '#description' => t("Allows you to refine your statistics by indicating a category or section of your site"),
    ];

    // Search result pages.
    $form['search'] = [
      '#type' => 'details',
      '#title' => $this->t('Search'),
      '#group' => 'tracking',
    ];
    $site_search_dependencies = '<div class="admin-requirements">';
    $site_search_dependencies .= t('Requires: @module-list',
      [
        '@module-list' => (\Drupal::moduleHandler()->moduleExists('search') ? t('@module (<span class="admin-enabled">enabled</span>)',
        ['@module' => 'Search']) : t('@module (<span class="admin-missing">disabled</span>)', ['@module' => 'Search'])),
      ]
    );
    $site_search_dependencies .= '</div>';
    $form['search']['oe_piwik_site_search'] = [
      '#type' => 'checkbox',
      '#title' => t('Track internal search'),
      '#description' => $this->t('If checked, internal search keywords are tracked.') . $site_search_dependencies,
      '#default_value' => $config->get('oe_piwik_site_search'),
      '#disabled' => (\Drupal::moduleHandler()->moduleExists('search') ? FALSE : TRUE),

    ];

    // Pages visibility settings.
    $php_access = \Drupal::currentUser()->hasPermission('use PHP for tracking visibility');

    $form['page_vis_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Pages'),
      '#group' => 'tracking',
    ];

    if ($config->get('oe_piwik_visibility_pages') == 2 && !$php_access) {
      $form['page_vis_settings'] = [];
      $form['page_vis_settings']['oe_piwik_visibility_pages'] = ['#type' => 'value', '#value' => 2];
      $form['page_vis_settings']['oe_piwik_pages'] = ['#type' => 'value', '#value' => $config->get('oe_piwik_pages')];
    }
    else {
      $options = [
        t('Every page except the listed pages'),
        t('The listed pages only'),
      ];
      $description = t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. Example paths are %blog for the blog page and %blog-wildcard for every personal blog. %front is the front page.",
        [
          '%blog' => '/blog',
          '%blog-wildcard' => '/blog/*',
          '%front' => '<front>',
        ]
      );
      if (\Drupal::moduleHandler()->moduleExists('php') && $php_access) {
        $options[] = t('Pages on which this PHP code returns <code>TRUE</code> (experts only)');
        $title = t('Pages or PHP code');
        $description .= ' ' . t('If the PHP option is chosen, enter PHP code between %php. Note that executing incorrect PHP code can break your Drupal site.', ['%php' => '<?php ?>']);
      }
      else {
        $title = t('Pages');
      }
      $form['page_vis_settings']['oe_piwik_visibility_pages'] = [
        '#type' => 'radios',
        '#title' => $this->t('Add tracking to specific pages'),
        '#options' => $options,
        '#default_value' => $config->get('oe_piwik_visibility_pages'),
      ];
      $form['page_vis_settings']['oe_piwik_pages'] = [
        '#type' => 'textarea',
        '#title' => $this->$title,
        '#title_display' => 'invisible',
        '#default_value' => !empty($config->get('oe_piwik_pages')) ? $config->get('oe_piwik_pages') : '',
        '#description' => $description,
        '#rows' => 10,
      ];
    }

    // Render the role overview.
    $form['role_vis_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Roles'),
      '#group' => 'tracking',
    ];
    $form['role_vis_settings']['oe_piwik_visibility_roles'] = [
      '#type' => 'radios',
      '#title' => $this->t('Add tracking for specific roles'),
      '#options' => [
        t('Add to the selected roles only'),
        t('Add to every role except the selected ones'),
      ],
      '#default_value' => $config->get('oe_piwik_visibility_roles'),
    ];
    $form['role_vis_settings']['oe_piwik_roles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Roles'),
      '#default_value' => !empty($config->get('oe_piwik_roles')) ? $config->get('oe_piwik_roles') : [],
      '#options' => array_map('\Drupal\Component\Utility\Html::escape', user_role_names()),
      '#description' => $this->t('If none of the roles are selected, all users will be tracked. If a user has any of the roles checked, that user will be tracked (or excluded, depending on the setting above).'),
    ];

    // Render the advanced PIWIK rules.
    $form['advanced_rules'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced PIWIK rules'),
      '#group' => 'tracking',
    ];
    $form['advanced_rules']['oe_piwik_rules_state'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable advanced PIWIK rules'),
      '#description' => t('Activates the "Advanced PIWIK rules" configuration tab.'),
      '#default_value' => $config->get('oe_piwik_rules_state'),
    ];

    // Submit actions.
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!preg_match('/^\d{1,}$/', $form_state->getValue('oe_piwik_site_id'))) {
      $form_state->setErrorByName('oe_piwik_site_id', $this->t('A valid Piwik site ID is an integer only.'));
    }

    // Trim some text values.
    $form_state->setValue('oe_piwik_pages', trim($form_state->getValue('oe_piwik_pages')));
    $form_state->setValue('oe_piwik_roles', array_filter($form_state->getValue('oe_piwik_roles')));

    // Verify that every path is prefixed with a slash, but don't check PHP
    // code snippets.
    if ($form_state->getValue('oe_piwik_visibility_pages') != 2) {
      $pages = preg_split('/(\r\n?|\n)/', $form_state->getValue('oe_piwik_pages'));
      foreach ($pages as $page) {
        if (strpos($page, '/') !== 0 && $page !== '<front>') {
          $form_state->setErrorByName('oe_piwik_pages', t('Path "@page" not prefixed with slash.', ['@page' => $page]));
          // Drupal forms show one error only.
          break;
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('oe_piwik.settings');
    $current_rule_state = $config->get('oe_piwik_rules_state') ? $config->get('oe_piwik_rules_state') : FALSE;

    $config
      ->set('oe_piwik_site_id', $form_state->getValue('oe_piwik_site_id'))
      ->set('oe_piwik_smartloader_prurl', $form_state->getValue('oe_piwik_smartloader_prurl'))
      ->set('oe_piwik_site_instance', $form_state->getValue('oe_piwik_site_instance'))
      ->set('oe_piwik_utility', $form_state->getValue('oe_piwik_utility'))
      ->set('oe_piwik_site_path', $form_state->getValue('oe_piwik_site_path'))
      ->set('oe_piwik_site_section', $form_state->getValue('oe_piwik_site_section'))
      ->set('oe_piwik_site_search', $form_state->getValue('oe_piwik_site_search'))
      ->set('oe_piwik_visibility_pages', $form_state->getValue('oe_piwik_visibility_pages'))
      ->set('oe_piwik_pages', $form_state->getValue('oe_piwik_pages'))
      ->set('oe_piwik_visibility_roles', $form_state->getValue('oe_piwik_visibility_roles'))
      ->set('oe_piwik_roles', $form_state->getValue('oe_piwik_roles'))
      ->set('oe_piwik_rules_state', $form_state->getValue('oe_piwik_rules_state'))
      ->save();

    // 'Advanced rules' task, allow only when rules state status change.
    $this->oePiwikRulesStateCheck($form_state->getValue('oe_piwik_rules_state'), $current_rule_state);

    parent::submitForm($form, $form_state);
  }

  /**
   * Validate a form element that should have tokens in it.
   *
   * For example:
   * @code
   * $form['my_node_text_element'] = [
   *   '#type' => 'textfield',
   *   '#title' => $this->t('Some text to token-ize that has a node context.'),
   *   '#default_value' => 'The title of this node is [node:title].',
   *   '#element_validate' => [[get_class($this), 'tokenElementValidate']],
   * ];
   * @endcode
   */
  public static function tokenElementValidate(&$element, FormStateInterface $form_state) {
    $value = isset($element['#value']) ? $element['#value'] : $element['#default_value'];

    if (!Unicode::strlen($value)) {
      // Empty value needs no further validation since the element should depend
      // on using the '#required' FAPI property.
      return $element;
    }

    $tokens = \Drupal::token()->scan($value);
    $invalid_tokens = static::getForbiddenTokens($tokens);
    if ($invalid_tokens) {
      $form_state->setError($element,
        t('The %element-title is using the following forbidden tokens with personal identifying information: @invalid-tokens.',
          [
            '%element-title' => $element['#title'],
            '@invalid-tokens' => implode(', ', $invalid_tokens),
          ])
        );
    }

    return $element;
  }

  /**
   * Get an array of all forbidden tokens.
   *
   * @param array $value
   *   An array of token values.
   *
   * @return array
   *   A unique array of invalid tokens.
   */
  protected static function getForbiddenTokens(array $value) {
    $invalid_tokens = [];
    $value_tokens = is_string($value) ? \Drupal::token()->scan($value) : $value;

    foreach ($value_tokens as $tokens) {
      if (array_filter($tokens, 'static::containsForbiddenToken')) {
        $invalid_tokens = array_merge($invalid_tokens, array_values($tokens));
      }
    }

    array_unique($invalid_tokens);
    return $invalid_tokens;
  }

  /**
   * Validate if a string contains forbidden tokens not allowed by rules.
   *
   * @param string $token_string
   *   A string with one or more tokens to be validated.
   *
   * @return bool
   *   TRUE if blacklisted token has been found, otherwise FALSE.
   */
  protected static function containsForbiddenToken($token_string) {
    // List of strings in tokens with personal identifying information not
    // allowed for privacy reasons. See section 8.1 of the Google Analytics
    // terms of use for more detailed information.
    //
    // This list can never ever be complete. For this reason it tries to use a
    // regex and may kill a few other valid tokens, but it's the only way to
    // protect users as much as possible from admins with illegal ideas.
    //
    // User tokens are not prefixed with colon to catch 'current-user' and
    // 'user'.
    //
    // TODO: If someone have better ideas, share them, please!
    $token_blacklist = [
      ':account-name]',
      ':author]',
      ':author:edit-url]',
      ':author:url]',
      ':author:path]',
      ':current-user]',
      ':current-user:original]',
      ':display-name]',
      ':fid]',
      ':mail]',
      ':name]',
      ':uid]',
      ':one-time-login-url]',
      ':owner]',
      ':owner:cancel-url]',
      ':owner:edit-url]',
      ':owner:url]',
      ':owner:path]',
      'user:cancel-url]',
      'user:edit-url]',
      'user:url]',
      'user:path]',
      'user:picture]',
      // addressfield_tokens.module.
      ':first-name]',
      ':last-name]',
      ':name-line]',
      ':mc-address]',
      ':thoroughfare]',
      ':premise]',
      // realname.module.
      ':name-raw]',
      // token.module.
      ':ip-address]',
    ];

    return preg_match('/' . implode('|', array_map('preg_quote', $token_blacklist)) . '/i', $token_string);
  }

  /**
   * Helper submit function for checking the PIWIK advanced rules state.
   */
  public function oePiwikRulesStateCheck($rule_state, &$rule_state_var = FALSE) {
    // Checking the state of the advanced rules checkbox.
    switch ($rule_state) {
      case 0:
        $state = t('off');
        break;

      case 1:
        $state = t('on');
        break;
    }
    // Message resetting the entity cache info and rebuilding menu.
    // Runs only if the advanced PIWIK rules state has changed.
    if ($rule_state != $rule_state_var) {
      drupal_set_message(t('The PIWIK advanced rules are turned @state.', ['@state' => $state]));
      \Drupal::service('cache_tags.invalidator')->invalidateTags(['config:oe_piwik.settings', 'rendered']);
      \Drupal::service('router.builder')->rebuild();
    }
  }

}
