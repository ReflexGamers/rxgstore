<?php

/**
 * Class StockShell
 *
 * @property Stock $Stock
 */
class StockShell extends AppShell {
    public $uses = array('Stock');

	public function main() {
        $this->config();
	}

    /**
     * Prints the config values in the AutoStock section of the config.
     */
    public function config() {

        $config = Configure::read('Store.AutoStock');

        $this->out("AutoStock Config:\n");

        foreach($config as $key => $val) {
            $this->out("$key: $val");
        }
    }

    /**
     * Performs automatic stocking and prints the values added to stock. For a preview instead, use
     * autoPreview().
     *
     * Takes a command line argument of the number of days for which to stock. Default 7 days.
     */
    public function autoStock() {

        $days = !empty($this->args[0]) ? $this->args[0] : 7;

        $addedStock = $this->Stock->autoStock($days);

        if (empty($addedStock)) {
            echo 'Nothing needs to be stocked.';
            return;
        }

        $itemNames = $this->getItemNames(array_keys($addedStock));

        foreach ($itemNames as $item_id => $itemName) {
            $quantity = !empty($addedStock[$item_id]) ? $addedStock[$item_id] : 0;
            $itemName = str_pad($itemName, 20, ' ', STR_PAD_LEFT);
            $this->out("$itemName : $quantity");
        }
    }

    /**
     * Prints a preview of what the autoStock function would basically do. Completely safe to run.
     *
     * Takes a command line argument of the number of days for which to stock. Default 7 days.
     */
    public function autoPreview() {

        $days = !empty($this->args[0]) ? $this->args[0] : 7;

        $amounts = $this->Stock->getAutoStockAmounts($days);

        $oldStock = $amounts['old'];
        $newStock = $amounts['new'];
        $addStock = $amounts['add'];

        $itemNames = $this->getItemNames(Hash::extract($newStock, '{n}.item_id'));

        foreach ($itemNames as $item_id => $itemName) {
            if (empty($oldStock[$item_id])) {
                continue;
            }

            $old = $oldStock[$item_id];
            $oldQty = $old['quantity'];
            $oldMax = $old['maximum'];

            $newMax = $newStock[$item_id]['maximum'];
            $newQty = $newStock[$item_id]['quantity'];
            $addQty = !empty($addStock[$item_id]) ? $addStock[$item_id] : 0;

            $oldStr = str_pad($oldQty, 4, ' ', STR_PAD_LEFT);
            $addStr = str_pad($addQty, 4, ' ', STR_PAD_RIGHT);
            $newStr = $newQty = "$oldStr + $addStr = $newQty";
            $newStr = str_pad($newStr, 18, ' ', STR_PAD_RIGHT);

            $newStr = str_pad("$newStr / $newMax", 25, ' ', STR_PAD_RIGHT);

            $maxDiff = $newMax - $oldMax;

            if ($maxDiff != 0) {
                $maxOp = $maxDiff > 0 ? '+' : '';
                $maxStr = " (max $maxOp$maxDiff)";
            } else {
                $maxStr = '';
            }

            $itemName = str_pad($itemName, 20, ' ', STR_PAD_LEFT);
            $this->out("$itemName : $newStr $maxStr");
        }
    }

    /**
     * Prints the suggested stock values based on sales including the OverStockMult. Does not factor
     * in minimum stock.
     *
     * Takes a command line argument of the number of days for which to suggest. Default 7 days.
     */
    public function suggested() {

        $days = !empty($this->args[0]) ? $this->args[0] : 7;

        $suggested = $this->Stock->getSuggestedStock($days);
        $itemNames = $this->getItemNames(array_keys($suggested));

        $this->out("Suggested stock values based on sales including OverStockMult:\n");

        foreach ($itemNames as $item_id => $itemName) {
            $quantity = !empty($suggested[$item_id]) ? $suggested[$item_id] : 0;
            $itemName = str_pad($itemName, 20, ' ', STR_PAD_LEFT);
            $this->out("$itemName : $quantity");
        }
    }

    /**
     * Returns an array of item short names indexed by item_id.
     *
     * @param array $ids list of item ids to look up
     * @return array list of item names indexed by item_id
     */
    private function getItemNames($ids) {
        return $this->Stock->Item->find('list', array(
            'fields' => array('item_id', 'short_name'),
            'conditions' => array(
                'item_id' => $ids
            )
        ));
    }
}