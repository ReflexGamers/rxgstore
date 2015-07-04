<?php
App::uses('Helper', 'View');

/**
 * Item helper
 */
class ItemFuncsHelper extends AppHelper {

    /**
     * Calculates the maximum number of items that should display in each column given the number of
     * items to display and a hard maximum number of columns.
     *
     * The objective is to have as many items in each row as possible while avoiding rows with only
     * one or two items, but that may not always be possible due to the number of items in the list
     * and the provided maximum number of columns.
     *
     * @param int $numItems
     * @param int $maxPerColumn
     * @return int the new maximum number of items to place in each column
     */
    public function calcColumns($numItems = 0, $maxPerColumn) {

        if ($numItems <= $maxPerColumn) {
            return $numItems;
        }

        // find the highest number of columns
        for ($divisor = 2; true; $divisor++) {
            if (($cols = ceil($numItems / $divisor)) <= $maxPerColumn) {
                return $cols;
            }
        }
    }
}
