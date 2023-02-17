<?php

declare(strict_types=1);

namespace OCA\AaoChat\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version0001Date20220720071957 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 */
	public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('aaochat_api_auth')) {
			$table = $schema->createTable('aaochat_api_auth');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 200,
			]);
			$table->addColumn('auth_base', 'string', [				
				'notnull' => true,
				'length' => 100,
			]);
			$table->addColumn('auth_token', 'string', [				
				'notnull' => true,
				'length' => 100,
			]);
			$table->addColumn('auth_key', 'string', [				
				'notnull' => true,
				'length' => 100,
				'default' => 0,
			]);
			$table->addColumn('timestamp', 'integer', [
				'notnull' => true,
				'length' => 11,
				'default' => 0,
			]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['auth_token'], 'aaochat_auth_token_index');
		}

		return $schema;
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 */
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
	}
}
