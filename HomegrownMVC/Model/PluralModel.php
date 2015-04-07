<?php
namespace HomegrownMVC\Model;

use HomegrownMVC\Error\PDOException as PDOException;

/*
 * A plural model is one that returns a number of singular models
 *
 * Author: Bremen Braun
 */
abstract class PluralModel {
	private $dbh;
	private $resultsToUpper = false;

	function __construct($dbh, $resultsToUpper=false) {
		$this->dbh = $dbh;
		$this->resultsToUpper = $resultsToUpper;
	}

	/*
	 * Setting uppercase to true means the associative array which is the result
	 * of runQuery will have its keys in all uppercase. This is a useful feature
	 * when wanting to switch between a case-insensitive database to a
	 * case-sensitive one without having to modify your queries.
	 */
	final function setResultsUppercase($uppercase=true) {
		$this->resultsToUpper = $uppercase;
	}

	/*
	 * Handles all the similar parts of running a query and casting the results
	 * to an array of singulars. If building intermediate results, you can pass
	 * `false` to this method to prevent autocasting to the proper type. If
	 * resultsToUpper is set to `true`, named results will be returned as
	 * uppercase.
	 */
	final protected function runQuery($query, $paramHash=array(), $cast=true) {
		$stmt = null;
		$dbh = $this->dbh;
		if (count($paramHash) == 0) {
			$stmt = $dbh->query($query);
		}
		else {
			$stmt = $dbh->prepare($query);
			if ($stmt !== false) {
  				foreach ($paramHash as $pkey => $pval) {
  					$stmt->bindValue($pkey, $pval);
  				}
  				$stmt->execute();
			}
		}
		if ($stmt === false) {
			$errorInfo = $dbh->errorinfo();
			$errorMsg = $errorInfo[2];
			throw new PDOException($errorMsg);
		}
		$results = $stmt->fetchAll();
		if ($this->resultsToUpper) {
			$results = $this->getResultsAsUppercase($results);
		}
		if ($cast) {
			$results = $this->castResults($results);
		}
		return $this->filterResults($results);
	}

	/*
	 * Like `runQuery`, but after preparing the query, runs it once for each
	 * array in $arrayOfParamHashes. If building intermediate results, you can
	 * pass `false` to this method to prevent autocasting to the proper type
	 */
	final protected function runMultiQuery($query, $arrayOfParamHashes, $cast=true) {
		$stmt = $this->dbh->prepare($query);

		$singulars = array();
		$results = array();
		foreach ($arrayOfParamHashes as $paramHash) {
			foreach ($paramHash as $pkey => $pval) {
				$stmt->bindParam($pkey, $pval);
			}
			$stmt->execute();
      
			$resultSet = $stmt->fetchAll();
			if ($this->resultsToUpper) {
				$resultSet = $this->getResultsAsUppercase($resultSet);
			}
			$results = array_merge($results, $resultSet);
		}

		if ($cast) {
			$results = $this->castResults($results);
		}
		return $results;
	}

	final protected function getDatabaseHandle() {
		return $this->dbh;
	}

	/*
	 * Cast an array of singulars to a hash type that can be consumed by Smarty
	 * - ex: $plural::hashify($singulars)
	 */
	static function hashify($singulars) {
		$hashedSingulars = array();
		foreach ($singulars as $singular) {
			array_push($hashedSingulars, $singular->hashify());
		}
		return $hashedSingulars;
	}

	/*
	 * Loop through singulars, creating an array of a single property
	 */
	static function arrayOnProperty($singulars, $property) {
		$array = array();
		foreach ($singulars as $singular) {
			array_push($array, $singular->getValue($property));
		}
		return $array;
	}

	/*
	 * Convert a hash to the type of this model's singular form
	 */
	abstract protected function castToProperType($hash);

	/*
	 * Define a function to run after results are prepared from the query and
	 * subsequently casted. This is useful if you need to throw anything away
	 * after casting
	 */
	protected function filterResults($results) {
		return $results;
	}

	/*
	 * Cast results of query to their proper type
	 */
	private function castResults($results) {
		$casted = array();
		foreach ($results as $result) {
			array_push($casted, $this->castToProperType($result));
		}
		return $casted;
	}

	/*
	 * Ensure all result array keys are uppercase so queries can be used across
	 * adapters more easily
	 */
	private function getResultsAsUppercase($results) {
		$resultsUpper = array();
		foreach ($results as $result) {
			$resultUpper = array();
			foreach ($result as $key => $value) {
				$resultUpper[strtoupper($key)] = $value;
			}
			array_push($resultsUpper, $resultUpper);
		}

		return $resultsUpper;
	}
}
?>
