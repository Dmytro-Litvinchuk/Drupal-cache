<?php

namespace Drupal\simple_cache\Plugin\Block;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableDependencyInterface;
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
class NodeListBlock extends BlockBase implements ContainerFactoryPluginInterface, CacheableDependencyInterface {

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
    // Load all nodes.
    $entity_type = 'node';
    $storage = $this->entityManager->getStorage($entity_type);
    $node = $storage->loadMultiple($nids);
    /**
    $view_mode = 'rss';
    $view_builder = $this->entityManager->getViewBuilder($entity_type);
    $build['content'] = $view_builder->viewMultiple($node, $view_mode);
     */
    // Get all titles and tags for cache.
    foreach ($nids as $nid) {
      $titles[$nid] = $node[$nid]->label();
      $tags[] = 'node:' . $nid;
    }
    $tags[] = 'node_list';
    $build['content'] = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#title' => 'Last Nodes',
      '#items' => $titles,
    ];
    // Cache permanent by default.
    $build['#cache'] = [
      'tags' => $tags,
      'contexts' => ['user.roles:role'],
    ];
    return $build;
  }

}
