<?php
App::uses('AppModel', 'Model');

/**
 * CreditLog Model
 */
class TotalCreditLog extends AppModel {

	public $useTable = 'total_credit_log';
	public $primaryKey = 'total_credit_log_id';

	public $order = 'TotalCreditLog.date asc';

	/**
	 * Gets the total credit at all points in history and puts it in a format friendly to HighCharts.
	 *
	 * @return array
	 */
	public function getAllTime() {

		$dateField = "date_format(date, '%Y-%m-%d') as date";
		$dateCondition = 'mod(hour(date), 4) = 0';

		$startDate = Hash::get($this->find('first', array(
			'fields' => array(
				$dateField
			),
			'conditions' => array(
				$dateCondition
			)
		)), '0.date');

		$creditLog = Hash::extract($this->find('all', array(
			'fields' => array(
				'amount'
			),
			'conditions' => array(
				$dateCondition
			)
		)), '{n}.TotalCreditLog.amount');

		$currencyMult = Configure::read('Store.CurrencyMultiplier');

		return compact('startDate', 'creditLog', 'currencyMult');
	}

	/**
	 * Validation rules
	 *
	 * @var array
	 */
	public $validate = array(
		'amount' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);
}