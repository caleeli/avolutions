<?php
/**
 * AVOLUTIONS
 * 
 * Just another open source PHP framework.
 * 
 * @author		Alexander Vogt <alexander.vogt@avolutions.de>
 * @copyright	2019 avolutions (http://avolutions.de)
 * @license		MIT License (https://opensource.org/licenses/MIT)
 * @link		https://github.com/avolutions/avolutions
 */
 
namespace core\orm;

use core\database\Database;
use core\logging\Logger;

/**
 * Entity class
 *
 * An entity represents a clearly identified object from an entity collection.
 * It provides the methods for manipulating the Entity with CRUD operations.
 *
 * @package		core
 * @author		Alexander Vogt <alexander.vogt@avolutions.de>
 */
class Entity
{
	/**
	 * @var mixed $id The unique identifier of the entity.
	 */
	public $id;

	/**
	 * @var string $EntityConfiguration The configuration of the entity.
	 */
	private $EntityConfiguration;

	/**
	 * @var string $EntityMapping The mapping of the entity.
	 */
	private $EntityMapping;
		
	
	/**
	 * __construct
	 * 
	 * Creates a new Entity object and loads the corresponding EntityConfiguration
	 * and EntityMapping.
	 */
	public function __construct() {
		$this->EntityConfiguration = new EntityConfiguration(get_class($this));
		$this->EntityMapping = $this->EntityConfiguration->getMapping();
	}	
		
	/**
	 * save
	 * 
	 * Saves the Entity object to the database. It will be either updated or inserted,
	 * depending on whether the Entity already exists or not.
	 */
	public function save() {		
		if($this->exists()) {
			$this->update();
		} else {
			$this->insert();
		}
	}	

	/**
	 * delete
	 * 
	 * Deletes the Entity object from the database.
	 */
	public function delete() {
		$values = array("id" => $this->id);	

		$query = "DELETE FROM ";
		$query .= $this->EntityConfiguration->getTable();
		$query .= " WHERE ";
		$query .= $this->EntityConfiguration->getIdColumn();
		$query .= " = :id";

		$this->execute($query, $values);
	}	

	/**
	 * insert
	 * 
	 * Inserts the Entity object into the database.
	 */
	private function insert() {
		$values = array();
		$columns = array();
		$parameters = array();

		foreach($this->EntityMapping as $key => $value) {
			$columns[] = $value["column"];
			$parameters[] = ":$key";
			$values[$key] = $this->$key;
		}	

		$query = "INSERT INTO ";
		$query .= $this->EntityConfiguration->getTable();
		$query .= " (";
		$query .= implode(", ", $columns);	
		$query .= ") VALUES (";
		$query .= implode(", ", $parameters);	
		$query .= ")";
		
		$this->execute($query, $values);
	}	

	/**
	 * update
	 * 
	 * Updateds the existing database entry for the Entity object.
	 */
	private function update() {
		$values = array();

		$query = "UPDATE ";
		$query .= $this->EntityConfiguration->getTable();
		$query .= " SET ";
		foreach($this->EntityMapping as $key => $value) {
			$query .= $value["column"]." = :$key, ";
			$values[$key] = $this->$key;
		}
		$query = rtrim($query, ", ");
		$query .= " WHERE ";
		$query .= $this->EntityConfiguration->getIdColumn();
		$query .= " = :id";
		
		$this->execute($query, $values);
	}
	
	/**
	 * exists
	 * 
	 * Checks if the Entity already exists in the database.
	 * 
	 * @return bool Returns true if the entity exists in the database, false if not.
	 */
	private function exists() {
		return $this->id != null;	
	}

	/**
	 * execute
	 * 
	 * Executes the previously created database query with the provided values. 
	 * 
	 * @param string $query The query string that will be executed.
	 * @param array $values The values for the query.
	 */
	private function execute($query, $values) {
		Logger::debug($query);
		Logger::debug("Values: ".print_r($values, true));

		$Database = new Database();
		$stmt = $Database->prepare($query);
		$stmt->execute($values);	
	}
}
?>