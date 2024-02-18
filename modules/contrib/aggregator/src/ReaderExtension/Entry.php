<?php

namespace Drupal\aggregator\ReaderExtension;

use Laminas\Feed\Reader\Extension\AbstractEntry;

/**
 * @class
 * Extends laminas-feed's default parsing functions.
 */
class Entry extends AbstractEntry {

  /**
   * {@inheritdoc}
   */
  protected function registerNamespaces() {
    // Do nothing because we don't need to register anything.
  }

  /**
   * Returns the value of a single author from the item.
   *
   * @param int $index
   *   The index of the author field to fetch from the item.
   *
   * @return array|null
   *   An array of author metadata whose only key is 'name' to match other
   *   extensions.  Or NULL if the specified index does not exist.
   */
  public function getAggregatorAuthor($index = 0) {
    $authors = $this->getAggregatorAuthors();

    return isset($authors[$index]) && is_array($authors[$index])
      ? $authors[$index]
      : null;
  }

  /**
   * Returns an array of all author values from the item.
   *
   * @return array
   *   An array of arrays whose only key is 'name' to match other extensions.
   */
  public function getAggregatorAuthors() {
    $authors = [];
    $list = $this->getXpath()->evaluate($this->getXpathPrefix() . '//author');
    if ($list instanceof \DOMNodeList && $list->length) {
      foreach ($list as $author) {
        $authors[] = [
          'name' => trim($author->nodeValue),
        ];
      }
    }

    return $authors;
  }

}
