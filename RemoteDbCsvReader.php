<?php
	require_once 'RemoteDbReader.php';

	class RemoteDbCsvReader extends RemoteDbReader {

		public function output(string $password) : void {
			// We'll output plain text
			header("Content-Type: text/plain; charset=utf-8");

			date_default_timezone_set('Europe/Stockholm');


			$stmt = $this->getAll($password);
			$rowNr = 0;
			while( $row = $stmt->fetch(PDO::FETCH_ASSOC) ){
				if( $rowNr++ == 0 ){
					print '"'.implode('","', array_keys($row))."\"\n";
				}

				if( !isset($_GET['raw_timestamp']) && array_key_exists('timestamp', $row) && is_numeric($row['timestamp']) )
					$row['timestamp'] = date("Y-m-d H:i:s", $row['timestamp']/1000);

				print '"'.implode('","', $row)."\"\n";
		    }
		}

	}