<?php

/**
 * Class CronShell
 *
 * @property ComponentCollection $components
 * @property PermissionsComponent $Permissions
 */
class PermissionsShell extends AppShell {

	public function initialize() {
		App::import('Component', 'Permissions');
		$this->components = new ComponentCollection();
		$this->Permissions = new PermissionsComponent($this->components);
	}

	public function main() {
		$this->out('No main task.');
	}

	/**
	 * Synchronizes permissions with the Sourcebans and Forum databases.
	 */
	public function sync() {

		$this->Permissions->syncAll();
		$this->out('Permissions synced.');
	}

	/**
	 * Rebuilds all permissions by dumping, re-initializing, then syncing them.
	 */
	public function rebuild() {

		$this->Permissions->dumpAll();
		$this->out('Permissions dumped.');

		$this->Permissions->initAll();
		$this->out('Permissions initialized.');

		$this->Permissions->syncAll();
		$this->out('Permissions synced.');
	}
}