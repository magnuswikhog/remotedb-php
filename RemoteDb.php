<?php

	class RemoteDb{


		/** @var array **/
		protected $systemEntryFields = [
			 '_s' => ['_seq', 'INTEGER', ''],				// Sequence number
			 '_u' => ['_uuid', 'TEXT UNIQUE', ''],			// UUID
		];

		/** @var array **/
		protected $systemRequestFields = [
			 //'_pn' => ['_package_name', 'TEXT', ''],		// Package name
			 //'_vc' => ['_version_code', 'TEXT', ''],		// Version code			 
			 //'_vn' => ['_version_name', 'TEXT', ''],		// Version name
			 '_did' => ['_device_id', 'TEXT', ''],			// Device ID
		];

		/** @var array **/
		protected $systemServerFields = [
			 '_ip' => ['_ip', 'TEXT', ''],					// IP			 
		];







		/** @var string **/
		protected $dbFilename;

		/** @var string **/
		protected $dbTable;

		/** @var array **/
		protected $customEntryFields = [];

		/** @var array **/
		protected $customRequestFields = [];



		/** @var PDO **/
		protected $db;





		public function __construct(string $dbFilename, string $dbTable, array $customEntryFields, array $customRequestFields){
			$this->dbFilename = $dbFilename;
			$this->dbTable = $dbTable;
			$this->customEntryFields = $customEntryFields;
			$this->customRequestFields = $customRequestFields;

			// Open or create DB
			$this->db = $this->getDb($this->dbFilename);
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}


		protected function getDb(string $path) : PDO {
			$db = new PDO("sqlite:$path");
			$db->exec($this->getDbCreateSql());		
			return $db;
		}


		protected function getDbCreateSql() : string {
			$fields = [];				
			foreach ( array_merge($this->customEntryFields, $this->customRequestFields, $this->systemEntryFields, $this->systemRequestFields, $this->systemServerFields) as $key => $entryDefinition) { $fields[] = $entryDefinition[0].' '.$entryDefinition[1]; }
			return 'CREATE TABLE IF NOT EXISTS '.$this->dbTable.' ( _id INTEGER PRIMARY KEY, '.implode(', ', $fields).' )';
		}



		protected function requireKeys(array $arr, array $requiredKeys) : void {
			$missingKeys = [];
			foreach( $requiredKeys as $key ){
				if( !array_key_exists($key, $arr) ) 
					$missingKeys[] = $key;
			}		
			if( count($missingKeys) > 0 )
				$this->fail( 'Missing required fields: '.implode(', ', $missingKeys) );		
		}

		protected function fail(string $message) : void {
			exit( $responseData );	
		}



	}

