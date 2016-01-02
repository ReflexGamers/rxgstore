<?php

/**
 * Class PermissionsShell
 *
 * @property Reward $Reward
 */
class RewardsShell extends AppShell {
    public $uses = array('Reward');

    public function main() {
        $this->out('No main task.');
    }

    /**
     * Sends a recruitment reward.
     */
    public function recruiting() {

        if (empty($this->args[0])) {
            $this->out('Please specify a recipient.');
            CakeLog::write('recruiting', 'ERROR: Recipient not specified.');
            return;
        }

        if (empty($this->args[1])) {
            $this->out('Please specify an amount.');
            CakeLog::write('recruiting', 'ERROR: Amount not specified.');
            return;
        }

        $steamid_raw = $this->args[0];
        $amount = (int)$this->args[1];

        $steamid_parsed = SteamID::Parse($steamid_raw, SteamID::FORMAT_AUTO);

        if ($steamid_parsed === false) {
            CakeLog::write('recruiting', "Unrecognized Steam ID format: $steamid_raw");
            $this->out('ERROR: Unrecognized Steam ID format.');
            return;
        }

        $sender_id = 0;  // send as robots
        $user_id = $steamid_parsed->Format(SteamID::FORMAT_S32);

        if ($this->Reward->sendCashReward($sender_id, $user_id, 'Reward For Recruiting', $amount)) {
            $message = "SUCCESS: Reward sent to $steamid_raw ($user_id) for $amount.";
        } else {
            $message = "ERROR: Failed to send Reward to $steamid_raw for $amount.";
        }

        $this->out($message);
        CakeLog::write('recruiting', $message);
    }
}