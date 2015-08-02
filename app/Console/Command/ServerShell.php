<?php

/**
 * Class ServerShell
 *
 * @property ComponentCollection $components
 * @property ServerUtilityComponent $ServerUtility
 */
class ServerShell extends AppShell {

    public function initialize() {
        App::import('Component', 'ServerUtility');
        $this->components = new ComponentCollection();
        $this->ServerUtility = new ServerUtilityComponent($this->components);
    }

	public function main() {
        $this->out('No main method.');
	}

    /**
     * Tests RCON passwords for all servers and prints the results.
     */
    public function test() {

        $this->ServerUtility->initServers();
        $servers = $this->ServerUtility->testServers();

        $this->out("\nServer RCON Test:\n");

        $statuses = array(
            'good' => array(),
            'bad' => array(),
            'down' => array()
        );

        foreach ($servers as $server_ip => $status) {
            if ($status === 2) {
                $statuses['good'][] = $server_ip;
            } else if ($status === 1) {
                $statuses['bad'][] = $server_ip;
            } else {
                $statuses['down'][] = $server_ip;
            }
        }

        $good = count($statuses['good']);
        $bad = count($statuses['bad']);
        $down = count($statuses['down']);

        $this->out("Servers good: $good");

        $this->out("Servers bad: $bad");
        foreach ($statuses['bad'] as $server_ip) {
            $this->out(" - $server_ip");
        }

        $this->out("Servers down: $down");
        foreach ($statuses['down'] as $server_ip) {
            $this->out(" - $server_ip");
        }
    }
}