<?php
include("databaseConnection.php");

class indexController
{
	public $db;
	
	//Database connection
	public function connectToDB(){
		$db = new databaseConnection();
		return $db::connectToDB();
	}
	
	/*
	Retorna el resultado de cada query
	Params: connection, query(string), parametros de la query (array)
	*/
	function getQueryResult($db, $query, $params = NULL){
		$stmt = $db->prepare($query);
		if($params === NULL){
			$stmt->execute();
			return $stmt->fetchAll();
		}else{
			$count = 1;
			foreach($params as $param){
				$stmt->bindValue($count,$param);
				$count++;
			}
			$stmt->execute();
			return $stmt;
		}
	}
	
	function fillDates($dates, $min)
	{
		ksort($dates['mentions']);
		ksort($dates['hashtags']);
		ksort($dates['user']);	
	
		$max = array_keys($dates['mentions']) [count($dates['mentions']) - 1];
		if ($max < array_keys($dates['hashtags']) [count($dates['hashtags']) - 1]) {
			$max = array_keys($dates['hashtags']) [count($dates['hashtags']) - 1];
		}
		if ($max < array_keys($dates['user']) [count($dates['hashtags']) - 1]) {
			$max = array_keys($dates['user']) [count($dates['hashtags']) - 1];
		}
	
		while ($min <= $max) {
	 
			if (isset($dates['mentions'][$min])) {
				$return['mentions'][$min] = $dates['mentions'][$min];
			} else {
				$return['mentions'][$min] = 0;
			}
	
			if (isset($dates['hashtags'][$min])) {
				$return['hashtags'][$min] = $dates['hashtags'][$min];
			} else {
				$return['hashtags'][$min] = 0;
			}
	
			if (isset($dates['user'][$min])) {
				$return['user'][$min] = $dates['user'][$min];
			} else {
				$return['user'][$min] = 0;
			}
	
			$return['dates'][$min] = $min;
			$min = date('Y-m-d', strtotime($min . ' + 1 day'));
		}
		
		return $return;
	}
}

?>