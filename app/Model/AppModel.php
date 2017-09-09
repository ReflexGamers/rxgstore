<?php
/**
 * Application model for CakePHP.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Model', 'Model');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 *
 * Magic Methods (for inspection):
 * @method query(string $query)
 */
class AppModel extends Model {

    // disable automatic joins by default
    public $recursive = -1;

    /**
     * Formats the provided time (or current time by default) into a timestamp that MySQL understands.
     *
     * @param int $time optional time (defaults to current time)
     * @return string the formatted date
     */
    protected function formatTimestamp($time) {
        return date('Y-m-d H:i:s', !empty($time) ? $time : time());
    }

    /**
     * Checks a save result for the false value in the top level as well as child levels. It does
     * not check grandchildren arrays.
     *
     * @param array $data the result of calling one of the model save methods
     * @return bool
     */
    protected function wasSaveSuccessful($data) {

        if (in_array(false, $data)) {
            return false;
        }

        foreach ($data as $row) {
            if (is_array($row) && in_array(false, $row)) {
                return false;
            }
        }

        return true;
    }

}
