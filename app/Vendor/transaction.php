<?php

//-----------------------------------------------------------------------------------
class Item {
	public $id = 0;
	public $amount = 0;
	public $price = 0.0;
	public $name = "";
	
	function __construct( $pid, $pamt ) {
		$this->id = $pid;
		$this->amount = $pamt;
	}
}

//-----------------------------------------------------------------------------------
class ItemList {
	public $items = array();
	
	public function AddItem( $id, $amount=1 ) {
		$amount = (int)$amount;
		if( !isset($this->items[$id]) ) {
			$this->items[$id] = new Item( $id, $amount );
		} else {
			$this->items[$id]->amount += $amount;
		}
	}
	
	public function RemoveItem( $id, $amount=1 ) {
		$amount = (int)$amount;
		if( !isset($this->items[$id]) || $amount > $this->items[$id]->amount ) throw new Exception( "tried to remove nonexistant items" );
		$this->items[$id]->amount -= $amount;
		if( $this->items[$id]->amount == 0 ) unset( $this->items[$id] );
	}
	
	public function DebugPrint() {
		$content = "";
		foreach( $this->items as $item ) {
			$content = $content . $GLOBALS['item_data2'][$item->id]['name'] . " x " .$item->amount.'\n';
		}
		return $content;
	}
	
	public function totalcount() {
		$t = 0;
		foreach( $this->items as $item ) {
			$t += $item->amount;
		}
		return $t;
	}
}
 
//---- 

//-----------------------------------------------------------------------------------
class Transaction {
	public $mode;
	public $items; 
	public $subtotal = 0;
	public $shipping = 0;
	public $total = 0;
//	public $storecredit = 0.00;
//	public $payment_due = 0.00;
	public $date = "";
	
	public $steamid = "";
	public $saleid = 0;
//	public $paypalsaleid = "";
//	public $payment; // paypal payment struct
	public $challenge = 0;
	 
	function __construct(  ) {
		$this->items = new ItemList();
	}
	
	private function CacheItemData() {
		global $item_data2;
		foreach( $this->items->items as &$item ) {
			//echo '<br>';echo '<br>'; //wtf is this doing here
			$item->price = $item_data2[$item->id]['price'];	
			$item->name = $item_data2[$item->id]['usage'];
		}
		unset( $item );
	}
	
	public function Compute( ) {
		$this->CacheItemData();
		
		$this->shipping = $GLOBALS['shipping_fee'];
		$this->subtotal = 0;
		
		foreach( $this->items->items as $item ) {
			
			$this->subtotal += $item->price * $item->amount;
		}
		
		$this->total = $this->subtotal + $this->shipping;
		/*
		$this->payment_due = $this->total;
		
		if( $credit >= $this->payment_due ) {
			$this->storecredit = $this->payment_due;
			$this->payment_due = 0.0;
		} else {
			$this->storecredit = $credit;
			$this->payment_due -= $this->storecredit;
		}
		*/
		$this->challenge = mt_rand();
		$this->date = date("c");
	}
}
?>
