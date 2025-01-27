<?php
/**
 * AVOLUTIONS
 *
 * Just another open source PHP framework.
 *
 * @copyright   Copyright (c) 2019 - 2021 AVOLUTIONS
 * @license     MIT License (https://avolutions.org/license)
 * @link        https://avolutions.org
 */

namespace Avolutions\Database;

use Avolutions\Config\Config;
use Avolutions\Core\Application;
use InvalidArgumentException;
use PDO;
use PDOException;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

/**
 * Database class
 *
 * The Database class provides some functions to connect to a MySQL database, execute queries
 * and perform schema changes (migrations) on the database.
 *
 * @author	Alexander Vogt <alexander.vogt@avolutions.org>
 * @since	0.1.0
 */
class Database extends PDO
{
	/**
	 * __construct
	 *
	 * Creates a database connection using the config values from database configuration file.
	 */
    public function __construct()
    {
		$host = Config::get('database/host');
		$database = Config::get('database/database');
		$port = Config::get('database/port');
		$user = Config::get('database/user');
		$password = Config::get('database/password');
		$charset  = Config::get('database/charset');
		$options  = [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.$charset,
			PDO::ATTR_PERSISTENT => true
        ];

		$dsn = 'mysql:dbname='.$database.';host='.$host.';port='.$port.'';

		parent::__construct($dsn, $user, $password, $options);
    }

    /**
     * migrate
     *
     * Executes all migrations from the applications database directory.
     *
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public static function migrate()
    {
		$migrationsToExecute = [];
		$migrationFiles = array_map('basename', glob(Application::getDatabasePath().'*.php'));

		$executedMigrations = self::getExecutedMigrations();

		foreach ($migrationFiles as $migrationFile) {
			$migrationClassName = Application::getDatabaseNamespace().pathinfo($migrationFile, PATHINFO_FILENAME);

            $Migration = new $migrationClassName;

            // only use Migration extending the AbstractMigration
            if (!$Migration instanceof AbstractMigration) {
                throw new RuntimeException('Migration "'.$migrationClassName.'" has to extend AbstractMigration');
            }

            // version has to be an integer use
            if (!is_int($Migration->version)) {
                throw new InvalidArgumentException('The version of the migration "'.$migrationClassName.'" has to be an integer.');
            }

            // only execute Migration if not already executed
			if (!in_array($Migration->version, array_keys($executedMigrations))) {
				$migrationsToExecute[$Migration->version] = $Migration;
			}
		}

		ksort($migrationsToExecute);

		$Database = new Database();
		foreach ($migrationsToExecute as $version => $Migration) {
			$Migration->migrate();

			$stmt = $Database->prepare('INSERT INTO migration (Version, Name) VALUES (?, ?)');
			$stmt->execute([$version, (new ReflectionClass($Migration))->getShortName()]);
		}
	}

	/**
	 * getExecutedMigrations
	 *
	 * Gets all executed migrations from the database and return the versions.
	 *
	 * @return array The version numbers of the executed migrations.
     *
     * @throws PDOException
	 */
    public static function getExecutedMigrations(): array
    {
		$executedMigrations = [];

		$Database = new Database();

		try {
            $stmt = $Database->prepare('SELECT * FROM migration');
            $stmt->execute();
            while ($row = $stmt->fetch(Database::FETCH_ASSOC)) {
                $executedMigrations[$row['Version']] = [
                    'name' => $row['Name'],
                    'date' => $row['CreateDate']
                ];
            }
        } catch (PDOException $ex) {
            // 1146 = Table 'migration' doesn't exist
		    if ($ex->errorInfo[1] == 1146) {
                self::createMigrationTable();
            } else {
                throw $ex;
            }
        }

		return $executedMigrations;
	}

    /**
     * createMigrationTable
     *
     * Creates the table to store executed migrations.
     */
	private static function createMigrationTable()
    {
        $columns = [];
        $columns[] = new Column('MigrationID', ColumnType::INT, 255, null, Column::NOT_NULL, true, true);
        $columns[] = new Column('Version', ColumnType::BIGINT, 255);
        $columns[] = new Column('Name', ColumnType::VARCHAR, 255);
        $columns[] = new Column('CreateDate', ColumnType::DATETIME, null, Column::CURRENT_TIMESTAMP);
        Table::create('migration', $columns);
    }
}