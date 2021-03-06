<?php

namespace DC\Thumbnail;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;

use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;

class Setup extends AbstractSetup
{
	use StepRunnerInstallTrait;
	use StepRunnerUpgradeTrait;
	use StepRunnerUninstallTrait;

	// ################################ INSTALLATION ####################

	public function installStep1()
	{
		$sm = $this->schemaManager();

		foreach ($this->getTables() AS $tableName => $closure)
		{
			$sm->createTable($tableName, $closure);
		}
	}

	public function postInstall(array &$stateChanges)
	{

	}

	// ############################################ FINAL UPGRADE ACTIONS ##########################

	public function postUpgrade($previousVersion, array &$stateChanges)
	{

	}

	// ############################################ UNINSTALL #########################

	public function uninstallStep1()
	{
		$sm = $this->schemaManager();

		foreach (array_keys($this->getTables()) AS $tableName)
		{
			$sm->dropTable($tableName);
		}
	}

	// ############################# TABLE / DATA DEFINITIONS ##############################

	protected function getTables()
	{
		$tables = [];

		$tables['xf_dcThumbnail_thumbail'] = function(Create $table)
		{
			$table->addColumn('thread_id', 'int', 10)->unsigned();
			$table->addColumn('thumbnail_url', 'mediumtext')->nullable(true);
			$table->addColumn('upload_url', 'mediumtext')->nullable(true);
			$table->addColumn('thumbnail_date', 'int', 10)->unsigned();
			$table->addPrimaryKey('thread_id');
		};

		return $tables;
	}
}