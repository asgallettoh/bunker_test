<?php

class databaseConnection
{
	//Database connection
	public function connectToDB(){
		return new PDO('mysql:host=localhost;port=3306;dbname=bunker'
			, 'root'
			, 'bunker'
			, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8; SET SESSION query_cache_type = OFF;"));
	}
}

?>