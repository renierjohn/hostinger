<?php

namespace Drupal\twig_attributes;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Render\Element;

/**
 * Defines Twig extensions.
 */
class TwigExtension extends \Twig_Extension {

  /**
   * {@inheritdoc}
   */
  public function getFilters() {
    return [
      new \Twig_SimpleFilter('add_attr', [$this, 'addAttributes']),
      new \Twig_SimpleFilter('with_attr', [$this, 'addAttributes']),
    ];
  }

  /**
   * Add attributes to a renderable array.
   *
   * @param array $build
   *   The renderable array.
   * @param string $key
   *   The key of the element to which the attributes should be added.
   * @param string|array $attributes
   *   The attributes to add.
   * @param bool $add_to_children
   *   Whether the attributes should be added to the array's children.
   * @param bool $overwrite
   *   Whether the attributes should be overwritten instead of merged.
   *
   * @return array
   *   The altered renderable array.
   */
  public function addAttributes(array $build, $key, $attributes, $add_to_children = TRUE, $overwrite = FALSE) {
    // Make sure the key starts with a hash, so it's treated as a property.
    if (strpos($key, '#') !== 0) {
      $key = '#' . $key;
    }

    if ($add_to_children) {
      foreach (Element::children($build) as $child_key) {
        $build[$child_key] = $this->addAttributes($build[$child_key], $key, $attributes, FALSE, $overwrite);
      }
      return $build;
    }

    // Set the default existing value and new value.
    $existing_value = NULL;
    $new_value = $attributes;

    // Get any existing value.
    if (!$overwrite && isset($build[$key])) {
      $existing_value = $build[$key];
    }

    // Merge values if necessary.
    if (is_array($existing_value)) {
      if (!is_array($attributes)) {
        $attributes = [$attributes];
      }
      $new_value = NestedArray::mergeDeepArray([$existing_value, $attributes]);
    }

    // Set the new value.
    $build[$key] = $new_value;

    // Special treatment of the "link" template.
    if (isset($build['#type']) && $build['#type'] == 'link' && isset($build['#link_attributes'])) {
      if (!isset($build['#options']['attributes'])) {
        $build['#options']['attributes'] = [];
      }
      $build['#options']['attributes'] = array_merge($build['#options']['attributes'], $build['#link_attributes']);
    }

    return $build;
  }

}
