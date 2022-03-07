<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
*
* @author Cees de Gruijter
* @category SCC Hosted
* @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (https://www.coffeecup.com/)
*/

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

// field names, use '_' as first character for our 'private fields'
define( 'FSCCSYNC' , '_scc_sync_marker' );
define( 'FCARTSYNC' , '_cart_sync_marker' );


class Database extends PDO {

	public $isOk = false;

	// default table options
	var $mysql_table_options = array( 'charset' => 'utf8',
								      'collate' => 'utf8_unicode_ci',
								      'type'    => 'myisam',
								      'primary' => array( 'id' => 'id' ),
								      'drop_if_exists'  => 1 );


	function __construct ( ) {

		// Get a database connection info, this should be a call to the infra structure
		$options = array( PDO::ATTR_PERSISTENT => false,
						  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
		try {

			$cfg = Config::GetInstance()->GetConfig( 'settings', 'data_settings', 'save_database' );
			if( ! $cfg->is_present )		return;

			parent::__construct( 'mysql:host='. $cfg->server .';dbname=' . $cfg->database, $cfg->username, $cfg->password, $options );

		} catch( PDOException $e ) {

			die( $e->getMessage() );
		}

		// database is usable now
		$this->isOk = true;

		// check if the transactions table exist when it is needed
		if( Config::GetInstance()->UsePayments() && ! $this->tableExists( TTRANS ) )
		{
			$this->createTransactionTable();
		}
	}


	function __destruct ( ) {

	}


	function fetchAll ( $query, $mode = PDO::FETCH_ASSOC ) {

		$result = $this->query( $query );
		return $result->fetchAll( $mode );
	}


	function tableExists ( $table_name ) {

		$result = $this->query( 'SHOW TABLES' );

		while( ( $table = $result->fetchColumn(0) ) !== false ) {
			if( $table == $table_name ) return true;
		}

		return false;
	  }


	function getTableFields ( $table_name ) {

		$result = $this->query( 'DESCRIBE ' . $table_name  );

		$data = array();

		while( ( $field = $result->fetchColumn(0) ) !== false ) {
			$data[] = $field;
		}

		return $data;
	}


	function existsValue( $table_name, $field_name, $value ) {

		$sth = $this->prepare('SELECT count(*) FROM '. $table_name . ' WHERE ' . $field_name . ' = ?;' );

		$sth->execute( array( $value ) );

		return $sth->fetchColumn(0) != 0;
	}


	function emptyTable( $tablename ) {

		$qry = 'TRUNCATE TABLE ' . $tablename;
		try {
			$this->pdo->exec( $qry );
		} catch (Exception $e) {

			$this->isError = true;
			$this->errMessage = $e->getMessage();

			return false;
		}

		return true;
	}


	// fields are for any database options can be database specific
	// this implementation is not finished, it is just enough for what is needed now.
	function createTable( $tablename, $fields, $user_options = false ) {

		// use defaults and merge user defined option if needed
		$options = $this->mysql_table_options;

		if( $user_options ) {
			foreach( $user_options as $key => $value ) {
				$options[ $key ] = $value;
			}
		}

		if( isset( $options['drop_if_exists'] ) && $options['drop_if_exists'] ) {

			$qry = 'DROP TABLE IF EXISTS ' . $tablename . '; ';
			$this->exec( $qry );
		}

		$qry = 'CREATE TABLE ' . $tablename . ' (';

		foreach( $fields as $name => $parms ) {

			switch ( $parms['type'] ) {

			case 'bool':
				$qry .=  $name . ' tinyint(1) ';
				break;

			default:
				$qry .=  $name . ' ' . $parms['type'];
				if( isset( $parms['length'] ) )
					$qry .= '('. $parms['length'] . ')';
			}

			if( isset( $options['default'] ) )
				$qry .= ' default ' . $options['default'];

			if( isset( $options['collate'] ) && in_array( $parms['type'], array('varchar', 'char', 'text') ) )
				$qry .= ' collate ' . $options['collate'];

			if( isset( $parms['autoincrement'] ) ) {
				$qry .= ' auto_increment';
			}

			$qry .= ' NOT NULL,';
		}

		if( isset( $options['primary'] ) )
			$qry .= ' PRIMARY KEY (' . implode( $options['primary'] ) . '),';

		if( isset( $options['index'] ) ) {

			foreach( $options['index'] as $name => $field ) {
			 	$qry .=  ' KEY ' . $name . '(' . $field  . '),';
			}
		}

		$qry = substr( $qry, 0, -1 );		// remove last ','

		$qry .= ')';

		if( isset( $options['type'] ) )
			$qry .= ' ENGINE=' . $options['type'];

		if( isset( $options['charset'] ) )
			$qry .= ' DEFAULT CHARSET=' . $options['charset'];

		if( isset( $options['collate'] ) )
			$qry .= ' COLLATE=' . $options['collate'];

		$qry .= ';';

		$this->exec( $qry );

		// check the error object, because exec doesn't throw exceptions
		$erinf = $this->errorInfo();
		if( $erinf[1] != '' ) {
			 throw new Exception( 'Error executing: ' . $qry );
		}
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


}

?>
