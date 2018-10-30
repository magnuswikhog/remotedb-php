<?php
	require_once 'RemoteDb.php';

	

	class RemoteDbReader extends RemoteDb {


		/** @var array **/
		private $systemRequiredNonDbFields = [
			'_pw',											// Password
		];



		public function getAll(string $password) : PDOStatement {
			$request = $_GET;

			$this->requireKeys( $request, ['_pw'] );
			$this->verifyPassword($request, $password);

			$params = $_GET;

			$filters = ['sqlparts'=>[], 'sqlparams'=>[], 'sql'=>''];
			foreach($params as $param => $paramValue){
				if( substr($param, 0, 7) == 'filter:' ){
					$filterColumn = substr($param, 7);
					$filterType = !empty($filterModifier) ? substr($paramValue, 1, strpos($paramValue, ':', 0)-1) : substr($paramValue, 0, strpos($paramValue, ':', 0));
					$filterValue = substr($paramValue, strpos($paramValue, ':', 0)+1);

					if( $filterType == 'exactly' ) $filters['sqlparts'][] = "$filterColumn=?";
					elseif( $filterType == '^exactly' ) $filters['sqlparts'][] = "$filterColumn!=?";
					elseif( $filterType == 'greaterthan' ) $filters['sqlparts'][] = "$filterColumn>?";
					elseif( $filterType == 'lessthan' ) $filters['sqlparts'][] = "$filterColumn<?";
					elseif( $filterType == 'contains' ) $filters['sqlparts'][] = "$filterColumn LIKE ?";
					elseif( $filterType == '^contains' ) $filters['sqlparts'][] = "$filterColumn NOT LIKE ?";
					elseif( $filterType == 'startswith' ) $filters['sqlparts'][] = "$filterColumn LIKE ?";
					elseif( $filterType == '^startswith' ) $filters['sqlparts'][] = "$filterColumn NOT LIKE ?";
					elseif( $filterType == 'endswith' ) $filters['sqlparts'][] = "$filterColumn LIKE ?";
					elseif( $filterType == '^endswith' ) $filters['sqlparts'][] = "$filterColumn NOT LIKE ?";


					if( $filterType == 'exactly' ) $filters['sqlparams'][] = $filterValue;
					elseif( $filterType == '^exactly' ) $filters['sqlparams'][] = $filterValue;
					elseif( $filterType == 'greaterthan' ) $filters['sqlparams'][] = $filterValue;
					elseif( $filterType == 'lessthan' ) $filters['sqlparams'][] = $filterValue;
					elseif( $filterType == 'contains' ) $filters['sqlparams'][] = '%'.$filterValue.'%';
					elseif( $filterType == '^contains' ) $filters['sqlparams'][] = '%'.$filterValue.'%';
					elseif( $filterType == 'startswith' ) $filters['sqlparams'][] = $filterValue.'%';
					elseif( $filterType == '^startswith' ) $filters['sqlparams'][] = $filterValue.'%';
					elseif( $filterType == 'endswith' ) $filters['sqlparams'][] = '%'.$filterValue;
					elseif( $filterType == '^endswith' ) $filters['sqlparams'][] = '%'.$filterValue;
				}
			}
			if( !empty($filters['sqlparts']) ){
				$filters['sql'] = ' WHERE '.implode(' AND ', $filters['sqlparts']);				
			}


			$stmt = $this->db->prepare("SELECT * FROM {$this->dbTable}{$filters['sql']} ORDER BY _seq ASC");
			return $stmt->execute($filters['sqlparams']) ? $stmt : false;
		}




		private function verifyPassword(array $request, string $providedPassword) : void {
			if( $request['_pw'] !== $providedPassword ) $this->fail('Incorrect password');
		}


		protected function fail(string $message) : void {
			exit( $message );	
		}


	}

