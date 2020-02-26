<?php

namespace Drupal\simple_cache\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NodeListBlock must implement ContainerFactoryPluginInterface for DI.
 *
 * @Block(
 *   id = "node_list_block",
 *   admin_label = @Translation("Block with cache"),
 *   category = @Translation("Custom"),
 * )
 */
class NodeListBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * NodeListBlock constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityManager
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              EntityTypeManagerInterface $entityManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityManager = $entityManager;
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container,
                                array $configuration,
                                $plugin_id,
                                $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * @inheritDoc
   */
  public function build() {
    // Get id last 10 node.
    $query = $this->entityManager->getStorage('node')->getQuery();
    $query->sort('created', 'DESC');
    $query->range(0, 10);
    $nids = $query->execute();
    // Check exist.
    if (isset($nids)) {
      // Load all nodes.
      $entity_type = 'node';
      $storage = $this->entityManager->getStorage($entity_type);
      $node = $storage->loadMultiple($nids);
      $view_mode = 'rss';
      $view_builder = $this->entityManager->getViewBuilder($entity_type);
      $build = $view_builder->viewMultiple($node, $view_mode);
      return $build;
    }
    else {
      return [
        '#markup' => $this->t('You did not create any nodes!'),
      ];
    }
  }

  /**
   * @inheritDoc
   */
  public function getCacheTags() {
    $old_tags = parent::getCacheTags();
    $new_tag = ['node_list'];
    return Cache::mergeTags($old_tags, $new_tag);
  }

  /**
   * @inheritDoc
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['user.roles']);
  }

}
