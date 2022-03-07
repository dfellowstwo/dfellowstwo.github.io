<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
*
*
* @author Cees de Gruijter
* @category SCC Hosted
* @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
*/

// Split the database in 2 parts, a part that is only readable and a part that is read/write.
// This allows us to upload the readable part as a sqlite file directly.

class DatabaseLoader {

	var $readDB;
	var $writeDB;
	
	function __construct ( )
	{
		$this->readDB = new Database( 'readDB' );
		$this->writeDB = new Database( 'writeDB' );
	}

	/************* create tables ****************/


	/* Config table
	 *  - some items have string values - use fixed char field
	 *  - some items have a hashmap as value -> construct key as key1:key2
	 *  - some items are really large -> put in blob and leave char field empty.
	 * This is lookup only, so use MyIsam engine.
	 */
	function createConfigTable( ) {

		$fields = array(
			'id' =>		  array( 'type'		=> 'varchar', 'length'	=> 128 ),
		    'shortval' => array( 'type'     => 'varchar', 'length'   => 128 ),
		    'longval' =>  array( 'type'		=> 'blob'),
		    'type' =>	  array( 'type'		=> 'varchar', 'length'	=> 16 )
		);

		$this->readDB->createTable( TCONFIG, $fields );
	}


	/* Products table
	 * Store the project object serialized and only create the columns that we need for querying
	 * This is lookup only, so use MyIsam engine.
	 */
	function createProductTable( ) {

		$fields = array(
			'productid' => array( 'type' => 'bigint'),
			'groupid' 	=> array( 'type' => 'bigint'),
			'starred'	=> array( 'type' => 'bool'),
			'catprod'	=> array( 'type' => 'int'),
		    'object'    => array( 'type' => 'blob')
		);

		$options['primary'] = array( 'productid' => 'productid' );
		$options['index'] = array( 'groupid' => 'groupid');

		$this->readDB->createTable( TPRODUCT, $fields, $options );
	}


	/*
	 * Create the name, price, short and long description fields, because we need them for
	 * searching.
	 *
	 * Use a seperate table for performance - for most request we don't need these fields
	 * and now we can store everything html decoded and in lower case to speed up searching
	 */
	function createProductSearchTable( ) {

		$fields = array(
			'productid' => array( 'type' => 'bigint'),
			'groupid' 	=> array( 'type' => 'bigint'),
			'name'	=> array( 'type' => 'varchar', 'length' => 256),
			'price'	=> array( 'type' => 'int'),
			'shortdescr'	=> array( 'type' => 'text'),
		    'longdescr'    => array( 'type' => 'text')
		);

		// must be a myisam table to allow full text search
		$options['primary'] = array( 'productid' => 'productid' );

		$this->readDB->createTable( TPRODSEARCH, $fields, $options );
	}


	/* Add full text indexes after filling the table, MySQL Manuals
	 * says it is a lot quicker than defining it at creation time
	 */
	function alterProductSearchTable ( ) {

		$sql = 'ALTER TABLE ' . TPRODSEARCH .
			   ' ADD FULLTEXT name ( name ),' .
			   ' ADD FULLTEXT shortdescr ( shortdescr ),' .
			   ' ADD FULLTEXT longdescr ( longdescr );';

		$this->readDB->exec( $sql );
	}


	function createPagesTable ( ) {

		$fields = array(
			'id'		=> array( 'type' => 'int'),
			'name'		=> array( 'type' => 'varchar', 'length' => 128),
		    'type'		=> array( 'type' => 'varchar', 'length' => 64 ),
		    'pagehref'	=> array( 'type' => 'varchar', 'length' => 255 ),
		    'metadescr' => array( 'type' => 'blob'),
      		'metakeyw'	=> array( 'type' => 'blob'),
      		'content'	=> array( 'type' => 'blob') );

		$this->readDB->createTable( TPAGE, $fields );

	}


	function createGroupTable ( ) {

		// not needed:
		//  - productsIds		get them from the products page
		//  - subgroupsIds
		//	- parentname
		//	- parenthref		get all these from this table
	  	$fields = array(
			'id'		=> array( 'type' => 'int'),
	  		'parentid'	=> array( 'type' => 'int'),
			'name'		=> array( 'type' => 'varchar', 'length' => 128),
		    'pagehref'	=> array( 'type' => 'varchar', 'length' => 255 ),
		    'metadescr' => array( 'type' => 'blob'),
      		'metakeyw'	=> array( 'type' => 'blob'),
  	  		'image'		=> array( 'type' => 'varchar', 'length' => 128) );

    	$this->readDB->createTable( TGROUP, $fields );

	}


	function createTableExtraShipping ( ) {

		$fields = array(
			'id'		=> array( 'type' => 'int'),
			'visible'	=> array( 'type' => 'bool' ),	// show is a protected word in MySQL
		    'type'		=> array( 'type' => 'int' ),
		    'amount'	=> array( 'type' => 'int'),
		    'descr'		=> array( 'type' => 'varchar', 'length' => 128) );

		$this->readDB->createTable( TESHIP, $fields );

	}


	function createTableTransactions ( ) {

		// gatewayref is needed to handle callbacks from payment gateways
		$fields = array(
			'route' =>			array( 'type' => 'varchar', 'length' => 32 ),
			'lineitemcount' =>	array( 'type' => 'int' ),
		    'grandtotal' =>		array( 'type' => 'varchar', 'length' => 64 ),
		    'status' =>			array( 'type' => 'varchar', 'length' => 64 ),
		    'gatewayref' =>		array( 'type' => 'varchar', 'length' => 64 ),
		    'testmode' =>		array( 'type' => 'bool' ),
		    'created' =>		array( 'type' => 'timestamp', 'default' => 'current_timestamp' ),
		    'modified' =>		array( 'type' => 'datetime' ),
		    'object' =>			array( 'type' => 'blob')
		);

		$options = array( 'type' =>		'innodb',
						  'primary' =>	array( 'route' => 'route' ),
						  'index' =>	array( 'gatewayref' => 'gatewayref' )
						);

		$this->writeDB->createTable( TTRANS, $fields, $options );

	}


	function createTableDownloads ( ) {

		$fields = array(
			'id' =>				array( 'type' => 'int', 'autoincrement' => 1 ),
			'route' =>			array( 'type' => 'varchar', 'length' => 32 ),
			'prodid' =>			array( 'type' => 'bigint' ),
			'name' =>			array( 'type' => 'varchar', 'length' => 128 ),
		    'source' =>			array( 'type' => 'varchar', 'length' => 255 ),
		    'dlcount' =>		array( 'type' => 'int' ),
		    'dlmax' =>			array( 'type' => 'int' ),
		    'expire' =>			array( 'type' => 'datetime' )
		);

		$options = array( 'primary' 	  => array( 'id' => 'id' ),
						  'index' 		  => array( 'route' => 'route', 'prodid' =>'prodid' )
						);

		$this->writeDB->createTable( TDOWNL, $fields, $options );
	}


	function createStocksTabel ( ) {

		$fields = array(
				'productid' => array( 'type' => 'bigint' ),
				'stock'	=> array( 'type' => 'int' ),			// uploaded by SCC
				'cart'	=> array( 'type' => 'int' ),			// sold since last sync
				'client'	=> array( 'type' => 'int')			// downloaded to SCC before sync
			);


			$options = array( 'type' =>	   'innodb',
							  'primary' => array( 'productid' => 'productid' )
							);

			$this->writeDB->createTable( TSTOCK, $fields, $options );
	}


	/************* insert data ****************/

	function insertConfigRow ( $key, $value ) {

		static $sth = false;

		if( !$sth ) {
			$sql = 'INSERT INTO ' . TCONFIG . ' (id, shortval, longval, type) VALUES ( :id, :shortval, :longval, :type );';
			$sth = $this->readDB->prepare( $sql );
		}

		$data[':id'] = $key;

		if( is_array( $value ) ) {

			$value = serialize( $value );
			$data[':type'] = 'array';
			$data[':longval'] = $value;
			$data[':shortval'] = '';

		} else if( is_bool( $value ) ) {

			$data[':type'] = 'bool';
			$data[':longval'] = '';
			$data[':shortval'] = $value ? 1 : 0;

		} else {

			if( strlen( $value ) > 128 ) {

				$data[':type'] = 'longval';
				$data[':longval'] = $value;
				$data[':shortval'] = '';

			} else {

				$data[':type'] = '';
				$data[':longval'] = '';
				$data[':shortval'] = $value;

			}
		}

		return $sth->execute( $data );			// number of affected rows

	}


	function insertProductRow ( $key, $prod ) {

		static $sth = false;

		if( ! $sth ) {
			$sql = 'INSERT INTO ' . TPRODUCT . ' (productid, groupid, starred, catprod, object) VALUES ( :productid, :groupid, :starred, 0, :object );';
			$sth = $this->readDB->prepare( $sql );
		}

		$data[':productid'] = $key;
		$data[':groupid'] = (int)$prod['groupid'];
		$data[':starred'] = $prod['isstarred'];
		$data[':object'] = serialize( $prod );

		return $sth->execute( $data );				// number of affected rows
	}


	function updateProductRow ( $key, $data ) {

		$sql = 'UPDATE ' . TPRODUCT . ' SET ';
		foreach( $data as $field => $value ) {
			$sql .= $field . '= ' . $value . ',';
		}
		$sql = substr( $sql, 0, -1 );
		$sql .= ' WHERE productid = ' . $key . ';';

		return $this->readDB->exec( $sql );				// number of affected rows
	}


	function insertProductSearchRow ( $key, $prod ) {

		static $sth = false;

		if( ! $sth ) {
			$sql = 'INSERT INTO ' . TPRODSEARCH .
				   ' (productid, groupid, name, price, shortdescr, longdescr) VALUES ( :productid, :groupid, :name, :price, :shortdescr, :longdescr );';
			$sth = $this->readDB->prepare( $sql );
		}

		$data[':productid'] = $key;
		$data[':groupid'] = (int)$prod['groupid'];
		$data[':name'] = strtolower( html_entity_decode( $prod['name'] ) );
		$data[':price'] =  $prod['yourprice'];
		$data[':shortdescr'] = strtolower( html_entity_decode( $prod['shortdescription'] ) );

		// strip tags, they don't help us with searching
		$data[':longdescr'] = strtolower( trim( strip_tags( html_entity_decode( $prod['longdescription'] ) ) ) );

		return $sth->execute( $data );				// number of affected rows
	}


	function insertPagesRow ( $key, $value ) {

		static $sth = false;

		if( ! $sth ) {
			$sql = 'INSERT INTO ' . TPAGE . ' (id, name, type, pagehref, metadescr, metakeyw, content) VALUES ( :id, :name, :type, :pagehref, :metadescr, :metakeyw, :content );';
			$sth = $this->readDB->prepare( $sql );
		}

		$data[':id'] = $key;
		$data[':name'] = $value['name'];
		$data[':type'] = $value['type'];
		$data[':pagehref'] = $value['pagehref'];
		$data[':metadescr'] = $value['metadescription'];
		$data[':metakeyw'] = $value['metakeywords'];
		$data[':content'] = $value['content'];

		return $sth->execute( $data );				// number of affected rows
	}


	function insertExtraShippingRow ( $key, $value ) {

		static $sth = false;

		if( ! $sth ) {
			$sql = 'INSERT INTO ' . TESHIP . ' (id, visible, type, amount, descr) VALUES ( :id, :show, :type, :amount, :descr);';
			$sth = $this->readDB->prepare( $sql );
		}

		$sth->bindParam( ':id', $key, PDO::PARAM_INT);
		$sth->bindParam( ':descr', $value['description'], PDO::PARAM_STR );
		$sth->bindParam( ':type', $value['type'], PDO::PARAM_STR );
		$sth->bindParam( ':show', $value['show'], PDO::PARAM_BOOL );
		$sth->bindParam( ':amount', $value['amount'], PDO::PARAM_INT);

		return $sth->execute( );					// number of affected rows
	}


	function insertGroupRow ( $key, $value ) {

		static $sth = false;
		if( ! $sth ) {
			$sql = 'INSERT INTO ' . TGROUP . ' (id, name, pagehref, metadescr, metakeyw, parentid, image) VALUES ( :id, :name, :pagehref, :metadescr, :metakeyw, :parentid, :image );';
			$sth = $this->readDB->prepare( $sql );
		}

		$sth->bindParam(':id', $key, PDO::PARAM_INT);
		$sth->bindParam(':name', $value['name'], PDO::PARAM_STR );
		$sth->bindParam(':pagehref', $value['pagehref'], PDO::PARAM_STR );
		$sth->bindParam(':metadescr', $value['metadescription'], PDO::PARAM_STR );
		$sth->bindParam(':metakeyw', $value['metakeywords'], PDO::PARAM_STR );
		$sth->bindParam(':parentid', $value['parentId'], PDO::PARAM_STR );
		$sth->bindParam(':image', $value['image'], PDO::PARAM_STR );

		return $sth->execute( );						// number of affected rows
	}


	function insertStocksTableRow ( $key, $stock ) {

		static $sth = false;
		if( ! $sth ) {
			$sql = 'INSERT INTO ' . TSTOCK . ' (productid, stock) VALUES (:productid, :stock);';
			$sth = $this->writeDB->prepare( $sql );
		}

		$sth->bindParam(':productid', $key, PDO::PARAM_INT);
		$sth->bindParam(':stock', $stock, PDO::PARAM_INT);
		return $sth->execute();							// number of affected rows
	}
}

?>
