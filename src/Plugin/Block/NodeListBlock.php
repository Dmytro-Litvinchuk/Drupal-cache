<?php

namespace Drupal\simple_cache\Plugin\Block;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
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
    $nid = $query->execute();
    // Load all nodes.
    $entity_type = 'node';
    $view_mode = 'rss';
    $view_builder = $this->entityManager->getViewBuilder($entity_type);
    $storage = $this->entityManager->getStorage($entity_type);
    $node = $storage->loadMultiple($nid);
    $build = $view_builder->viewMultiple($node, $view_mode);
    return $build;
  }

}
