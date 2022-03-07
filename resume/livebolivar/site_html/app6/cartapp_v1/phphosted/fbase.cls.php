<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
*	FBase, but with storage in a database table instead of the file system.
*
*	Used the index fields to build a table and add the data as a
*	serialized object to the same table
*
*	Definiton of pagination array:
*	pagination = array(
*	    "page_current" => 2,			// first page is 1
*	    "page_previous" => 1,			// null if not available
*	    "page_next" => 3,				// null if not available
*	    "page_last" = > 8,
*	    "items_per_page" => 20,
*	    "items_total" => 78,
*	    "items_starting_index" => 21,	// index starts counting at 1
*	    "items_ending_index" => 30 )
*
* @author Cees de Gruijter
* @category SCC Hosted
* @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (https://www.coffeecup.com/)
*/

require CARTREVISION . '/php/fbase.cls.php';
require_once CARTREVISION . '/phppro/utilities.inc.php';


class HostedFBase extends FBase {

	var $tablename;
	var $db;
	var $pagination = false;
	private $appname = '';


	function __construct ( $tablename, $filenamelen = '') {

		$this->db = new Database( 'writeDB' );
		if( ! $this->db->isOk ) return;

		$this->tablename = $tablename;

		$this->fields = $this->db->getTableFields( $tablename );

		// use filenamelen as the length of the key field
		if( !empty( $filenamelen ) && (int) $filenamelen > 0 )
			$this->fnamelen = (int) $filenamelen;
	}


	function IsOk ( )
	{
		return $this->db->isOk;
	}


	// $route is the formatted form of the file location
	// only update existing item if $onlyIfExists is true
	function StoreData ( $data, &$route, $onlyIfExists = false, $appname = false ) {

		$this->onlyIfExists = $onlyIfExists;
		$this->appname = $appname;

		// do we need to build a new route?
		if( ! $route &&  $onlyIfExists ) {
			$this->_SetErrorMessage ( _('Cannot update an entry if no route has been defined.') );
			return false;
		}

		// careful, $route can be 'false' for new entries
		$route = $this->_saveRow( $route, $data );

		if( $route === false ) {

			if( $onlyIfExists ) 	$this->_SetErrorMessage ( _('Update failed because route does not exist.') );
			else					$this->_SetErrorMessage ( _('Insert new failed.') );
			
			return false;
		}
		return true;
	}


	// return the first route which data structure has a match fieldname, fieldname must
	// be part of fields array! Return false on failure
	function GetRouteByField ( $value, $fieldname ) {

		if( ! in_array( $fieldname, $this->fields) ) {
			$this->SetErrorMessage( 'Field ' . $fieldname . ' is not included in the index.' );
			return false;
		}

		$sql = 'SELECT route FROM ' . $this->tablename .
			   ' WHERE ' . $fieldname . '=' . $value . ';';

		$data = $this->db->query( $sql );
		$route = $data->fetchColumn( 0 );

		if( $route === false ) {
			$this->SetErrorMessage( 'FDB - No item value ' . $value . ' in field ' . $fieldname . ' is found.' );
		}

		return $route;
	}


	// retrieve a data structure from it's serialized form
	function RetrieveData ( $route, &$data ) {
		
		static $sth = false;
		if( $sth === false ) {
			$sql = 'SELECT object FROM  ' . $this->tablename . ' WHERE route=:route;';
			$sth = $this->db->prepare( $sql );
		}
		
		if( empty( $route ) || ! preg_match('/^[\d\w]{8,16}$/', $this->FormatRefField( $route ) ) ) {
			$this->_SetErrorMessage( _T('Empty item code or code could not be recognized.') );
			return false;
		}
		
		// for compatibility with Pro, make sure this is the formatted version of route
		$sth->bindParam(':route', $this->FormatRefField( $route ), PDO::PARAM_STR);
		$sth->execute( );

		$object = $sth->fetchColumn( 0 );

		if( $object === false  ) {
			$this->_SetErrorMessage( _('No data found.') );
			return false;
		}

		$data = unserialize( $object );
		return true;
	}


	// remove the data and update the index
	function RemoveData ( $route ) {

		$sth = $this->db->prepare('DELETE FROM '. $this->tablename . ' WHERE route = ?;' );
		$result = $sth->execute( array( $route ) );

		if( $result === false ) {
			$this->SetErrorMessage( _('Failed to delete entry: ') . $route );
			return false;
		}

		return true;
	}


	function DropData ( $appname )
	{
		if( empty( $appname ) )		return false;

		$sth = $this->db->prepare('DELETE FROM '. $this->tablename . ' WHERE appname = ?;' );
		$result = $sth->execute( array( $appname ) );

		if( $result === false ) {
			$this->SetErrorMessage( _('Failed to remove records for appname: ') . $appname );
			return false;
		}

		return true;
	}


	// return the contents of the table, without the object, as assoc array
	// $refresh option is ignored
	function GetIndexList ( &$index, $refresh = false, $page = -1, $pagelen = 20 ) {
		
		$items_total = 0;
		
		// some input valaidation
		if( ! is_numeric($page) || ! is_numeric( $pagelen ) ) {
			$this->_SetErrorMessage( 'Pagination parameters must be numeric.' );
			return false;
		}

		$sql = 'SELECT * FROM ' . $this->tablename . ' ORDER BY modified DESC';

		if( $page > 0 ) {
			
			$page -= 1;		// make 0-based
			
			$r = $this->db->fetchAll( 'SELECT count(*) FROM ' . $this->tablename . ';', PDO::FETCH_NUM );
			$items_total = (int)$r[0][0];

			if( $items_total < $page * $pagelen ) {
				$this->_SetErrorMessage( 'There are fewer pages than the number you are asking for.' );
				return false;				
			}
			
			$sql .= ' LIMIT ' . $pagelen . ' OFFSET ' . $page * $pagelen;
		}
		
		$sql .= ';';
		
		$index = array();

		foreach( $this->db->query( $sql, PDO::FETCH_ASSOC) as $row ) {

			// rename route to be compatible with Pro version
			$row['file_route'] = strtoupper( $row['route'] );

			// rename timestamp and change to ticks to be compatible with Pro version
			$row['file_modified'] = strtotime( $row['modified'] );
			$index[] = $row;
		}

		// fill the pagination array
		if( $page >= 0 ) {
			$this->pagination = array(
				    "page_current" => $page + 1,					// first page is 1
				    "page_previous" => $page > 0 ? $page : NULL,	// null if not available
				    "page_next" => $items_total > ($page + 1) * $pagelen ? $page + 2 : NULL,
				    "page_last" => ceil( $items_total / $pagelen ),
				    "items_per_page" => $pagelen,
				    "items_total" => $items_total,
				    "items_starting_index" => $pagelen * $page + 1,	// index starts counting at 1
				    "items_ending_index" => min( $pagelen * ( $page + 1 ), $items_total ) );
		}
				
		return true;
	}


	// can be used after calling a method that sets the pagianation object, such as GetIndexList()
	function GetPagination ( ) {
		return $this->pagination;
	}

	// read all data from all files and return as array, return true on success
	// output format:
	// array( route => array( grandtotal => ... ,
	//						  lines => array( line_id => values, ... ),
	//						  ... ),
	//					... )
	function RetrieveAllData ( &$data ) {

		$sql = 'SELECT route, object FROM ' . $this->tablename . ';';

		$data = array();

		foreach( $this->db->query( $sql, PDO::FETCH_ASSOC ) as $row ) {
			$data[ $row['route'] ] = unserialize( $row['object'] );
		}

    	return true;
	}


	function BuildIndex ( ) {
		// not needed for the database version
	}

	/*********************** private functions *******************************/

	// insert or update as needed, return $route on success or false on failure
	private function _saveRow ( $route, $data ) {

		// route should be upper case always
		$route = strtoupper( $route );
		
		if( $route !== false &&
			$this->db->existsValue( $this->tablename, $this->fields[0] , $route ) ) {

			// prepare variable part of the query
			$sqldata = array();
			$sql = '';
			foreach( $this->fields as $field ) {

				switch( $field ) {

				case 'route':
				case 'created':
					// do not touch, set automatically/later
					break;

				case 'modified':
					// don't use now because mysql and sqlite differ
					$sql .= 'modified=\'' . date('Y-m-d H:i:s') . '\',';
					break;

				case 'object':
					$sql .= 'object=:object,';
					break;

				default:
					if( isset( $data[$field] ) ) {
						$sql .= $field . '=:' . $field . ',';
						$sqldata[ ':' . $field ] = $data[ $field ];
					}
				}
			}

			$sql = substr( $sql, 0, -1 );			// remove last comma

			// update
			$sql = 'UPDATE ' . $this->tablename . ' SET ' . $sql . ' WHERE route=:route;';

			// merge the data in object, not simply overwrite it!
			$dbdata = array();
			$this->RetrieveData( $route, $dbdata );

			foreach( $data as $key => $value ) {
				$dbdata[ $key ] = $value;
			}

			$sqldata[ ':route' ] = $route;
			$sqldata[ ':object' ] = serialize( $dbdata );

		} else {

			if( $this->onlyIfExists ) return false;  	// insert not allowed by caller

			// prepare variable part of the query
			$sqldata = array();
			$fields = '';
			$values = '';
			foreach( $this->fields as $field ) {

				switch( $field ) {

				case 'route':
				case 'created':
					// do not touch, set automatically/later
					break;

				case 'modified':
					$fields .= 'modified,';
					// don't use now because mysql and sqlite differ
					$values .= '\'' . date('Y-m-d H:i:s') . '\',';
					break;

				case 'object':
					$fields .= 'object,';
					$values .= ':object,';
					break;

				default:
					if( isset( $data[$field] ) ) {
						$fields .= $field . ',';
						$values .= ':' . $field . ',';
						$sqldata[ ':' . $field ] = $data[$field];
					}
				}
			}

			$fields = substr( $fields, 0, -1 );			// remove last comma
			$values = substr( $values, 0, -1 );			// remove last comma

			// insert
			$sqldata[ ':route' ] = $this->_MakeRoute();
			$sqldata[ ':appname' ] = $this->appname;
			$sqldata[ ':object' ] = serialize( $data );

			$sql = 'INSERT INTO '
			   	 . $this->tablename
				 . ' (route, gatewayref, appname, ' . $fields . ') VALUES (:route,\'\',:appname,' . $values . ');';
		}

		// prepare takes care of quotes and escaping
		$sth = $this->db->prepare( $sql );

		if( $sth->execute( $sqldata ) === true ) {
			return $sqldata[ ':route'];
		} else {
			var_dump($data);
			var_dump($sth->errorInfo());
			return false;
		}
	}


	function _MakeRoute( ) {

		do {
			$route = strtoupper( RandomString( FB_DIRNAME_LEN  + FB_FILENAME_LEN ) );
		} while( $this->db->existsValue( $this->tablename, $this->fields[0], $route) );

		return $route;
	}


	function CheckRoute( $route ) {

		return $this->db->existsValue( $this->tablename, $this->fields[0] , $route );

	}

}

?>
