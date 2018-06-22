<?php
/**
 * Created by Adam Jakab.
 * Date: 21/06/18
 * Time: 12.39
 */

namespace Drupal\formatter_distance\Plugin\Field\FieldFormatter;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Field\Annotation\FieldFormatter;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;


/**
 * Class DistanceFieldFormatter
 *
 * @package Drupal\formatter_distance\Plugin\Field\FieldFormatter
 *
 * @FieldFormatter(
 *   id="distance_field_formatter",
 *   label=@Translation("Distance Field Formatter"),
 *   field_types={"integer"}
 * )
 */
class DistanceFieldFormatter extends FormatterBase {

  /**
   * @return array|string[]
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Formats a numeric value as distance.');
    return $summary;
  }

  /**
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   * @param string $langcode
   *
   * @return array
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      // Render each element as markup.
      $element[$delta] = [
        '#markup' => $this->getKilometers($item->value)
      ];
    }

    return $element;
  }

  /**
   * @param mixed $input
   *
   * @return string
   */
  protected function getKilometers($input)
  {
    $meters = $this->getMeters($input);
    $km = number_format(floatval($meters / 1000),1);
    $km .= " km";
    return $km;
  }

  /**
   * @param mixed $input
   *
   * @return float|int
   */
  protected function getMeters($input)
  {
    return is_numeric($input) ? floatval($input) : 0;
  }
}