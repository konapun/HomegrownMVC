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
		foreach ($paramHash as $pkey, $pval) {
			$stmt->bindParam($pkey, $pval);
		}
		
		$singulars = array();
		$results = $stmt->fetchAll();
		foreach ($results as $result) {
			array_push($singulars, $this->castToProperType($result));
		}
		
		return $singulars;
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
	protected function castToProperType($hash);
}
?>
