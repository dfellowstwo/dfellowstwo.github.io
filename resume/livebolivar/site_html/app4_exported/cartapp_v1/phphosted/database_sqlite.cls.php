<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
*
* @author Cees de Gruijter
* @category SCC Hosted
* @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
*/

// read/write database for transaction data
// the path MUST be provided by the server in the sdrive settings
define( 'WRITEDB', '/cartapptrans.dat' );

// read only database with shop data
define( 'READDB', 'ccdata/store/shop.dat' );

// table names
define( 'TCONFIG', 'config' );
define( 'TPRODUCT', 'products' );
define( 'TPRODSEARCH', 'searchproducts' );
define( 'TGROUP', 'groups' );
define( 'TPAGE', 'pages');
define( 'TESHIP', 'extrashipping' );
define( 'TTRANS', 'transactions' );
define( 'TSTOCK', 'soldstocks' );
define( 'TDOWNL', 'downloads' );
define( 'TCREDITCARD', 'creditcards' );

// field names
define( 'FSCCSYNC', 'sync_marker' );
define( 'FCARTSYNC', 'cart_sync_marker' );



class Database {

	public $isOk = false;

	// must be readDB or writeDB
	var $type;
	
	// default table create options
	var $table_options = array( 'primary' => array( 'id' => 'id' ),
								'drop_if_exists'  => 0 );
	
	function __construct ( $dbtype ) {
		
		// must be "readDB" or "writeDB"
		$this->type = $dbtype; 
		$this->isOk = true;
	}


	function readDB ( ) {

		static $readDB = false;				// contains shop definitions

		if( ! $readDB ) {
			try {
				global $absPath;		
				$readDB = new PDO( 'sqlite:' . $absPath . READDB );
			} catch( PDOException $e ) {
				die( $e->getMessage() . '   DSN sqlite:' . $absPath . READDB );
			}
		}
		return $readDB;
	}


	function writeDB ( ) {

		static $writeDB = false;			// contains transaction stuff, create if not present

		if( ! $writeDB ) {

			try {
				global $sdrive_config;
				
				$writeDB = new PDO( 'sqlite:' . $sdrive_config['sdrive_account_datastore_path'] . WRITEDB );

				// use this for stuff that should never be overwritten by the client
				if( ! $this->tableExists( TCONFIG ) ) {

					$this->createConfigTable( $writeDB );

					// check the other tables if config didn't exist 
					$this->createTransactionTable( $writeDB );
					$this->createStockTable( $writeDB );
				}
			} catch( PDOException $e ) {
				die( $e->getMessage() . '   DSN sqlite path in "SdriveConfig.php": ' . $sdrive_config['sdrive_account_datastore_path'] );
			}
		}
		return $writeDB;
	}


	function fetchAll ( $query, $mode = PDO::FETCH_ASSOC ) {

		$result = $this->query( $query );
		return $result->fetchAll( $mode );
	}


	function tableExists ( $table_name ) {

		$qry = 'SELECT name FROM sqlite_master WHERE type=\'table\' ORDER BY name;';
		$result = $this->query( $qry );

		if( ! $result ) 	return false;
		
		while( ( $table = $result->fetchColumn(0) ) !== false ) {
			if( $table == $table_name ) return true;
		}

		return false;
	}


	function getTableFields ( $table_name ) {

		$result = $this->query( 'pragma table_info(' . $table_name . ');' );

		$data = array();

		while( ( $field = $result->fetchColumn(1) ) !== false ) {
			$data[] = $field;
		}

		return $data;
	}


	function existsValue ( $table_name, $field_name, $value ) {

		$sth = $this->prepare('SELECT count(*) FROM '. $table_name . ' WHERE ' . $field_name . ' = ?;' );

		$sth->execute( array( $value ) );

		return $sth->fetchColumn(0) != 0;
	}


	function emptyTable ( $tablename ) {

		$qry = 'TRUNCATE TABLE ' . $tablename;
		
		try {
			$this->exec( $qry );
		} catch (Exception $e) {

			$this->isError = true;
			$this->errMessage = $e->getMessage();

			return false;
		}

		return true;
	}


	// fields are for any database options can be database specific
	// this implementation is not finished, it is just enough for what is needed now.
	function createTable ( $tablename, $fields, $user_options = false ) {

		// use defaults and merge user defined option if needed
		$options = $this->table_options;

		if( $user_options ) {
			foreach( $user_options as $key => $value ) {
				$options[ $key ] = $value;
			}
		}

		if( isset( $options['drop_if_exists'] ) && $options['drop_if_exists'] ) {

			$qry = 'DROP TABLE IF EXISTS ' . $tablename . '; ';
			$this->exec( $qry );
		}

		$qry = 'CREATE TABLE IF NOT EXISTS ' . $tablename . ' (';

		foreach( $fields as $name => $parms ) {

			switch ( $parms['type'] ) {

			case 'bool':
				$qry .=  $name . ' TINYINT';
				break;

			case 'timestamp':
			case 'datetime':
				$qry .= $name . ' VARCHAR(32)';
				break;

			default:
				$qry .=  $name . ' ' . strtoupper( $parms['type'] );
				if( isset( $parms['length'] ) ) 
				   	$qry .= '('. $parms['length'] . ')';
			}

			if( isset( $parms['default'] ) 	)
				$qry .= ' default ' . $parms['default'];

			if( isset( $parms['autoincrement'] ) ) {
				$qry .= ' auto_increment';
			}

			$qry .= ' NOT NULL,';
		}

		if( isset( $options['primary'] ) )
			$qry .= ' PRIMARY KEY (' . implode( $options['primary'] ) . '),';

		$qry = substr( $qry, 0, -1 );		// remove last ','

		$qry .= ');';

		$this->exec( $qry );
					
		// sqlite needs separate statements to add indexes
		if( isset( $options['index'] ) ) {

			foreach( $options['index'] as $name => $field ) {

			 	$qry = 'CREATE INDEX IF NOT EXISTS ' . $name . ' ON ' . $tablename . '(' . $field  . ');';
				$this->exec( $qry );
			}
		}

	}


	function query ( $sql ) {
	
	 	$db = call_user_func( array( $this, $this->type ) );
		$result = $db->query( $sql );

		if( ! $result ) {

			$erinf = $db->errorInfo();
			if( $erinf[1] != '' ) {
				throw new Exception( 'Error ' . $erinf[0] . ' ' . $erinf[2] . ' - sql: ' . $sql );
			}
			return false;
		}
		
		return $result;
	}
	
	
	function exec ( $sql ) {
	
	 	$db = call_user_func( array( $this, $this->type ) );
		$result = $db->exec( $sql );

		$erinf = $db->errorInfo();

		if( $erinf[0] != '00000' ) {
			throw new Exception( 'Error ' . $erinf[0] . ' ' . $erinf[2] . ' - sql: ' . $sql );
		}

		return $result;
	}
	
	
	function prepare ( $sql ) {

	 	$db = call_user_func( array( $this, $this->type ) );

		$sth =  $db->prepare( $sql );

		if( ! $sth ) {
			$erinf = $db->errorInfo();
			throw new Exception( 'Error ' . $erinf[0] . ' on ' . $this->type . '  ' . $erinf[2] . ' - sql: ' . $sql );
		}
		return $sth;
	}


	function createConfigTable ( ) {

		$fields = array(
			'id' =>		  array( 'type'		=> 'varchar', 'length'	=> 128 ),
		    'shortval' => array( 'type'     => 'varchar', 'length'   => 128 ),
		    'longval' =>  array( 'type'		=> 'blob'),
		    'type' =>	  array( 'type'		=> 'varchar', 'length'	=> 16 )
		);

		$this->createTable( TCONFIG, $fields );
	}
	
	
	function createTransactionTable ( ) {
	
		// gatewayref is needed to handle callbacks from payment gateways
		// appname is needed when FB and SCC share the storage
		// sqlite does have special datetime fields, use strings like: YYYY-MM-DD HH:MM:SS
		$fields = array(
			'route' =>			array( 'type' => 'varchar', 'length' => 32 ),
		    'appname' =>		array( 'type' => 'varchar', 'length' => 64 ),
			'lineitemcount' =>	array( 'type' => 'int' ),
		    'grandtotal' =>		array( 'type' => 'varchar', 'length' => 64 ),
		    'status' =>			array( 'type' => 'varchar', 'length' => 64 ),
		    'gatewayref' =>		array( 'type' => 'varchar', 'length' => 64 ),
		    'testmode' =>		array( 'type' => 'bool' ),
		    'created' =>		array( 'type' => 'timestamp', 'default' => 'current_timestamp' ),
		    'modified' =>		array( 'type' => 'datetime' ),
		    'object' =>			array( 'type' => 'blob')
		);

		$options = array( 'primary' =>	array( 'route' => 'route' ),
						  'index' =>	array( 'gatewayref' => 'gatewayref' ),
						  'index' =>	array( 'appname' => 'appname' )
						);

		$this->createTable( TTRANS, $fields, $options );
	}


	function createStockTable ( ) {

		$fields = array(
				'productid' => array( 'type' => 'varchar', 'length' => 32 ),
				'cart'	=> array( 'type' => 'int' ),			// sold since last sync
				'client'	=> array( 'type' => 'int')			// downloaded to SCC before sync
			);

		$options = array( 'primary' => array( 'productid' => 'productid' ) );

		$this->createTable( TSTOCK, $fields, $options );
	}


}


?>
