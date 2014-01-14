<?php
namespace HomegrownMVC\Model;

/*
 * A plural model is one that returns a number of singular models
 *
 * Author: Bremen Braun
 */
abstract class PluralModel {
	private $dbh;
	
	function __construct($dbh) {
		$this->dbh = $dbh;
	}
	
	/*
	 * Handles all the similar parts of running a query and casting the results
	 * to an array of singulars. If building intermediate results, you can pass 
	 * `false` to this method to prevent autocasting to the proper type
	 */
	final protected function runQuery($query, $paramHash, $cast=true) {
		$stmt = $this->dbh->prepare($query);
		foreach ($paramHash as $pkey => $pval) {
			$stmt->bindParam($pkey, $pval);
		}
		
		$results = $stmt->fetchAll();
		if ($cast) {
			$results = $this->castResults($results);
		}
		return $results;
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
			
			$results = array_merge($results, $stmt->fetchAll());
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
	 * Convert a hash to the type of this model's singular form
	 */
	abstract protected function castToProperType($hash);
	
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
}
?>
