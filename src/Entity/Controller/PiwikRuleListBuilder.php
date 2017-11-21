<?php

namespace Drupal\oe_piwik\Entity\Controller;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Entity UI controller for the OE PIWIK rules.
 */
class PiwikRuleListBuilder extends EntityListBuilder {

  /**
   * The redirect destination service.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  protected $redirectDestination;

  /**
   * Constructs a new NodeListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $redirect_destination
   *   The redirect destination service.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, DateFormatterInterface $date_formatter, RedirectDestinationInterface $redirect_destination) {
    parent::__construct($entity_type, $storage);
    $this->dateFormatter = $date_formatter;
    $this->redirectDestination = $redirect_destination;
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $entity_query = \Drupal::service('entity.query')->get('oe_piwik_rule');
    $header = $this->buildHeader();
    $entity_query->pager(20);
    $entity_query->tableSort($header);
    $uids = $entity_query->execute();
    return $this->storage->loadMultiple($uids);
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('date.formatter'),
      $container->get('redirect.destination')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [
      'id' => [
        'data' => $this->t('Label'),
        'field' => 'id',
        'specifier' => 'id',
        'class' => [
          RESPONSIVE_PRIORITY_LOW,
        ],
      ],
      'rule_section' => [
        'data' => $this->t('Section'),
        'field' => 'rule_section',
        'specifier' => 'rule_section',
      ],
      'rule_language' => [
        'data' => $this->t('Language'),
        'field' => 'rule_language',
        'specifier' => 'rule_language',
      ],
      'rule_path' => [
        'data' => $this->t('Path'),
        'field' => 'rule_path',
        'specifier' => 'rule_path',
      ],
      'rule_path_type' => [
        'data' => $this->t('Path type'),
        'field' => 'rule_path_type',
        'specifier' => 'rule_path_type',
      ],
    ];
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['id'] = 'Rule ID: [' . $entity->id->value . ']';
    $row['rule_section'] = $entity->rule_section->value;
    $row['rule_language'] = $entity->rule_language->value;
    $row['rule_path'] = $entity->rule_path->value;
    $row['rule_path_type'] = $entity->rule_path_type->value;
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations['edit'] = [
      'title' => $this->t('Edit'),
      'weight' => 0,
      'url' => $entity->urlInfo('edit-form'),
    ];
    $operations['delete'] = [
      'title' => $this->t('Delete'),
      'weight' => 1,
      'url' => $entity->urlInfo('delete-form'),
    ];
    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build['table'] = [
      '#type' => 'table',
      '#header' => $this->buildHeader(),
      '#title' => $this->getTitle(),
      '#rows' => [],
      '#empty' => $this->t('There is no @label yet.', ['@label' => $this->entityType->getLabel()]),
      '#cache' => [
        'contexts' => $this->entityType->getListCacheContexts(),
        'tags' => $this->entityType->getListCacheTags(),
      ],
      '#attributes' => [
        'id' => 'oe-piwik-rules',
      ],
    ];
    foreach ($this->load() as $entity) {
      if ($row = $this->buildRow($entity)) {
        $build['table']['#rows'][$entity->id()] = $row;
      }
    }
    if ($this->limit) {
      $build['pager'] = [
        '#type' => 'pager',
      ];
    }
    return $build;

  }

}
