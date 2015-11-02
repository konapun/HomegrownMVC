<?php
namespace HomegrownMVC\Error;

class MultipleResultsException extends \Exception {
  private $results;

  public function __construct($results, $message='', $code=0, Exception $previous=null) {
    $this->results = $results;
    parent::__construct($message, $code, $previous);
  }

  function getResults() {
    return $this->results;
  }
}
?>
