<?php

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
	 * to an array of singulars
	 */
	final protected function runQuery($query, $paramHash) {
		$stmt = $this->dbh->prepare($query);
		foreach ($paramHash as $pkey => $pval) {
			$stmt->bindParam($pkey, $pval);
		}
		
		return $this->castResults($stmt->fetchAll());
	}
	
	/*
	 * Like `runQuery`, but after preparing the query, runs it once for each
	 * array in $arrayOfParamHashes
	 */
	final protected function runMultiQuery($query, $arrayOfParamHashes) {
		$stmt = $this->dbh->prepare($query);
		
		$singulars = array();
		$results = array();
		foreach ($arrayOfParamHashes as $paramHash) {
			foreach ($paramHash as $pkey => $pval) {
				$stmt->bindParam($pkey, $pval);
			}
			
			$results = array_merge($results, $stmt->fetchAll());
		}
		
		return $this->castResults($results);
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
