<?php

namespace application\lib;

use PDO;

class Db {

	protected $db;
	
	public function __construct() {
		$config = require 'application/config/db.php';
		try {
		$this->db = new PDO('mysql:host='.$config['host'].';dbname='.$config['name'].'', $config['user'], $config['password']);
		$this->db->exec("set names utf8");
		
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (PDOException $e) {
            echo 'Подключение не удалось: ' . $e->getMessage();
        }
        
	}

	
	public function query($sql, $params = []) {
		$stmt = $this->db->prepare($sql);
		if (!empty($params)) {
			foreach ($params as $key => $val) {
				if (is_int($val)) {
					$type = PDO::PARAM_INT;
				} else {
					$type = PDO::PARAM_STR;
				}
				$stmt->bindValue(':'.$key, $val, $type);
			}
		}
		
		$stmt->execute();
		return $stmt;
	}
	
	public function row($sql, $params = []) {
		$result = $this->query($sql, $params);
		return $result->fetchAll(PDO::FETCH_ASSOC);
	}

	public function column($sql, $params = []) {
		$result = $this->query($sql, $params);
		return $result->fetchColumn();
	}
	
	public function lastInsertId() {
		return $this->db->lastInsertId();
	}


}