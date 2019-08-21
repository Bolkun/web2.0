<?php
/**
 * @file
 * Contains \Drupal\custom_block\Plugin\Block\CustomBlock.
 */
namespace Drupal\custom_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
/**
 * Provides a custom_block.
 *
 * @Block(
 *   id = "custom_block",
 *   admin_label = @Translation("Custom block"),
 *   category = @Translation("Custom block example")
 * )
 */
class CustomBlock extends BlockBase {
    /**
     * {@inheritdoc}
     */
    public function build() {
        return array(
            '#type' => 'markup',
            '#markup' => 'This custom block content.',
        );
    }
}