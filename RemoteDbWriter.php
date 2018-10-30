<?php
	require_once 'RemoteDb.php';



	class RemoteDbWriter extends RemoteDb {



		/** @var array **/
		private $systemRequiredNonDbFields = [
			'_ent',											// Entries 
			'_pw',											// Password
		];




		/** @override **/		
		protected function fail(string $message) : void {
			$this->exitWithResult( ["status" => "error", "message" => $message] );
		}




		public function store(string $password, bool $storeIP) : void {
			// We'll output plain text (JSON)
			header("Content-Type: text/json; charset=utf-8");

			/*  When using application/json as the request Content-Type (for example using Volley on Android), we can't use $_POST.
				Instead, we have to extract the request parameters like this: */
			$request = json_decode(file_get_contents('php://input'), true);


			// Make sure required data is provided by the client
			$this->requireKeys( $request, array_merge( array_keys($this->systemRequestFields), $this->systemRequiredNonDbFields) );

			// Check password
			$this->verifyPassword($request, $password);			
						
			// Get entries and make sure it's an array
			$entries = $request['_ent'];
			if( !is_array($entries) ) $this->exitWithResult( ["status" => "error", "message" => "Parameter \"entries\" doesn't seem to be an array! ".__LINE__] );						


			// Bind request values and server values (same values will be used for all entries)
			$stmt = $this->db->prepare($this->getEntryInsertSql());
			$this->bindRequestValues($stmt, $request);
			$this->bindValues($stmt, ['_ip' => $storeIP && isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''] );

			// Insert entries
			$insertCount = 0;
			$storedUuids = [];
			foreach( $entries as $entry ){
				$this->bindEntryValues($stmt, $entry);

				if( $stmt->execute() === false ) $this->exitWithResult( ["status" => "error", "message" => "SQLite INSERT error on line ".__LINE__] );
				$storedUuids[] = $entry['_u'];
				$insertCount += $stmt->rowCount();
			}

			$totalEntryCount = $this->db->query('SELECT COUNT(*) FROM '.$this->dbTable)->fetchColumn(); 

			// Return OK message
			$this->exitWithResult( ["status" => "ok", "message" => "$insertCount of ".count($entries)." entries inserted.", "stored_uuids" => json_encode($storedUuids), "insert_count" => $insertCount, "total_count" => $totalEntryCount] );
		}






		private function optValue(array $data, string $key, string $default) : string {
			return array_key_exists($key, $data) ? $data[$key] : $default;
		}	

		private function getEntryInsertSql() : string {		
			$fieldNames = [];
			$fieldPlaceholders = [];
			foreach ( array_merge($this->customEntryFields, $this->customRequestFields, $this->systemEntryFields, $this->systemRequestFields, $this->systemServerFields) as $key => $entryDefinition) { $fieldNames[] = $entryDefinition[0]; $fieldPlaceholders[] = ":{$entryDefinition[0]}"; }
			return 'INSERT OR IGNORE INTO '.$this->dbTable.' ('.implode(', ', $fieldNames).') VALUES ('.implode(', ', $fieldPlaceholders).')';
		}


		private function bindRequestValues(PDOStatement &$stmt, array $request) : void {
			foreach ( array_merge($this->systemRequestFields, $this->customRequestFields) as $key => $entryDefinition) { $stmt->bindValue(":{$entryDefinition[0]}", $request[$key]); }
		}

		private function bindValues(PDOStatement &$stmt, array $values) : void {
			foreach ($values as $key => $value) { $stmt->bindValue(":$key", $value); }
		}

		private function bindEntryValues(PDOStatement &$stmt, array $entry) : void {
			foreach ( array_merge($this->customEntryFields, $this->systemEntryFields) as $key => $entryDefinition) { $stmt->bindValue(":{$entryDefinition[0]}", $this->optValue($entry, $key, $entryDefinition[2])); }
		}


		private function exitWithResult(array $responseData) : void {
			echo json_encode( $responseData );	
			exit;
		}

		private function verifyPassword(array $request, string $providedPassword) : void {
			if( $request['_pw'] !== $providedPassword ) $this->exitWithResult( ["status" => "error", "reason" => "not-authorized", "message" => "Not authorized. Provided password: ".$request['_pw']] );
		}


	}

