<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
*	Class FBase is a file-based data storage engine that stores
*	data structures without paying (much) attention to the contents.
*
*   All public methods false on failure.
*   If possible the $errorMsg is set on failure.
*
* @version $Revision: 1866 $
* @author Cees de Gruijter
* @category SCC PRO
* @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (https://www.coffeecup.com/)
*/

define( 'FB_DIR_PREFIX', 'fb.');
define( 'FB_INDEX_FILE', 'fb_index.php' );
define( 'FB_DIRNAME_LEN', 1);
define( 'FB_FILENAME_LEN', 7);
define( 'FB_NOTFOUND', -1 );
define( 'PHP_ACCESS_DENIED', '<?php echo "Access denied."; exit(0); ?>');
define( 'FB_STOCKUPDATED' , '_stockupdated' );		// field names that start with a '_' are meant for internal use
define( 'FB_STOCKRETURNED' , '_stockreturned' );

class FBase {

	// private
	var $errorMsg;						// may be set if a methods returns false due to an error
	var $path;							// points to the root of our file structure
	var $index;							// index cache
	var $fields;						// fields to include in the index
	var $fhandle;						// file handle, to maintain lock across method invocations
	var $classlock = false;				// use $this->fhandle in exclusive read/write mode until SafeFile() is called.
	var $fnamelen = FB_FILENAME_LEN;	// the number of characters to use for the filename
	var $dirprefix = FB_DIR_PREFIX;		// folder prefix, can be used as sort of 'table name'
	var $fnameindex = FB_INDEX_FILE;	// index file name
	var	$onlyIfExists;					// only update existing entries


	/* The $fields array contains the 'keys' of the data items that should be included
	 * in the index file that is maintained. The index always contains creation-time, modification-time
	 * and route as the last 3 fields of each row.
	 */
	function FBase ( $path, $fields, $filenamelen = '' , $dirprefix = '' ) {

		$this->path = rtrim( $path, '/') . '/';	// now last char is '/' for sure
		$this->fields = $fields;

		if( ! is_dir( $this->path ) ) {
			$this->_SetErrorMessage( sprintf( _T("Path '%s' does not exist or is not a directory."), $this->path ) );
			return false;
		}

		if( ! empty( $filenamelen ) && (int) $filenamelen > 0 )
			$this->fnamelen = (int) $filenamelen;

		if( ! empty( $dirprefix ) ) {
			$base = rtrim( $dirprefix, '.');
			$this->dirprefix = $base . '.';
			$this->fnameindex = $base . '_index';
		}
	}


/***************************** Public Methods ********************************/

	// store a data structure as-is in serialized form
	// $route is the formatted file location where the data is stored
	//		relative to our root, it is always in it's formatted form external to this class
	// only update existing item if $onlyIfExists is true
	// PHP4 does not allow default values for parameters passed by reference
	function StoreData ( $data, &$route, $onlyIfExists = false ) {

		$this->onlyIfExists = $onlyIfExists;

		// do we need to build a new route like 'fb_x/xxxxxxx', x being alfa-numeric?
		if( ! $route ) {

			if( $onlyIfExists ) {
				$this->_SetErrorMessage ( 'Cannot update an entry if no route has been defined.' );
				return false;
			}

			$mroute = $this->_MakeRoute();		// this is still unformated

			if( $mroute === false ) {
				$this->_SetErrorMessage( 'Cannot create new route.' );
				return false;
			}

			$route = $this->FormatRefField( $mroute );

		} else {

			$mroute = $this->UnformatRefField( $route );
		}

		$myfile = $this->path . $mroute;

		if( ! $this->_SaveFile( $myfile, $data ) ) {
			return false;
		}

		return $this->_UpdateIndex ( $mroute, 'add' );
	}


	// return the first route which data structure has a match fieldname, fieldname must be part of index!
	// return false on failure
	function GetRouteByField ( $data, $fieldname ) {

		$fieldnameindex = array_search( $fieldname, $this->fields );

		if( $fieldnameindex === false ) {
			$this->_SetErrorMessage( 'Field ' . $fieldname . ' is not included in the index.' );
			return false;
		}


		$items = $this->FindMatches( $data[$fieldname], $fieldnameindex );

		if( empty($items) ) {

			// may be the index is out-of-date, let's try that as ultimate attempt
			$this->BuildIndex();
			$items = $this->FindMatches( $data[$fieldname], $fieldname );

			if( empty($items) ) {
				$this->_SetErrorMessage( 'FDB - No item value ' . $data[$fieldname] . ' in field ' . $fieldname . ' is found.' );
				return false;
			}
		}

		return $items[0]['file_route'];
	}


	// retrieve a data structure from it's serialized form
	// route is the formated route
	function RetrieveData ( $route, &$data ) {

		$route = $this->UnformatRefField( $route );

		if( file_exists( $this->path . $route ) )
			return $this->_GetFileContents( $this->path . $route, $data );

		$this->_SetErrorMessage( 'No data found.' );
		return false;
	}


	// remove the data and update the index
	function RemoveData ( $route ) {

		// ensure the route has the proper format
		$myfile = $this->path . $this->UnformatRefField( $route );

		if( ! file_exists( $myfile ) )
			return true;		// data has already disappeared

		// get a lock
		$fhandle = fopen( $myfile, 'r+' );
		if( ! $fhandle || ! $this->_GetFileLock( LOCK_EX, $fhandle ) ) {
			$this->_SetErrorMessage( 'Could not open or lock data file: ' . $myfile );
			flock( $fhandle, LOCK_UN );
			fclose( $fhandle );
			return false;
		}

		// windows doesn't like unlink on a locked file
		if( strpos( strtoupper(PHP_OS), 'WIN' ) === 0 ) {
			flock( $fhandle, LOCK_UN );
			fclose( $fhandle );
			$result = @unlink( $myfile );
		} else {
			$result = @unlink( $myfile );
			flock( $fhandle, LOCK_UN );
			fclose( $fhandle );
		}

		if( $result ) {
			$this->_UpdateIndex( $route, 'del');
		} else {
			$this->_SetErrorMessage( 'Could not delete data file: ' . $myfile );
		}
		return $result;
	}


	// rebuild the index file and include the $this->fields (for showing lists, etc.)
	function BuildIndex (  ) {

		$dhandle = opendir( $this->path );

		if( ! $dhandle ) {
			$this->_SetErrorMessage( 'Could not open folder: ' . $this->path );
			return false;
		}

		$this->index = array();
		$prefixLen = strlen( $this->dirprefix );

		while( false !== ( $dirname = readdir( $dhandle ) ) ) {

			if( substr($dirname, 0, $prefixLen) != $this->dirprefix )
				continue;

			if( ! $this->_ScanFolderContents( $this->path . $dirname ) )
				return false;
    	}

    	closedir( $dhandle );

		$this->_SaveFile(  $this->path . $this->fnameindex, $this->index, true );

    	return true;
	}


	// find all matches on a specific field
	function FindMatches ( $match, $field ) {

		$start = 0;
		$result = array();

		if( empty( $this->index ) &&
			! $this->_GetFileContents( $this->path . $this->fnameindex, $this->index ) )
			return false;

		while( ($idx = $this->_FindFirstIndex ( $match, $start, $field ) ) != FB_NOTFOUND ) {

			$result[] = $this->index[ $idx ];
			$start = $idx + 1;
		}
		return $result;
	}


	// read all data from all files and return as array, return true on success
	// output format:
	// array( route => array( grandtotal => ... ,
	//						  lines => array( line_id => values, ... ),
	//						  ... ),
	//					... )
	function RetrieveAllData ( &$data ) {

		$dhandle = opendir( $this->path );

		if( ! $dhandle ) {
			$this->_SetErrorMessage( 'Could not open folder: ' . $this->path . ' for scanning.' );
			return false;
		}

		$this->classlock = false;

		$data = array();

		// loop over all folders
		while( false !== ( $foldername = readdir( $dhandle ) ) ) {

			$mpath = $this->path . '/' . $foldername;

			if( strpos( $foldername, '.') === 0 ||			// ignore names that start with '.'
				! is_dir( $mpath )							// name must point to a folder
			  )
				continue;

			$datafolderhandle = opendir( $mpath );

			if( ! $datafolderhandle ) {
				writeErrorLog( 'FDB - Can not open folder: ' . $mpath . ' for scanning.' );
				continue;
			}

			// loop over all files in each folder
			while( false !== ( $filename = readdir( $datafolderhandle ) ) ) {

				$mfilepath = $mpath . '/' . $filename;

				if( strpos( $filename, '.' ) === 0 || ! is_file( $mfilepath) )
					continue;

				$myref = $this->FormatRefField( $this->_GetRouteFromPath( $mfilepath ) );
				$data[ $myref ] = $this->_ExtractFieldsFromFile( $mfilepath, true );
			}

			closedir( $datafolderhandle );
		}

    	closedir( $dhandle );

    	return true;
	}

	function CheckRoute( $route ) {

		$mroute = $this->UnformatRefField( $route );
		return file_exists( $this->path . $mroute );

	}


/************************ Private Methods (start with a _) ***************************/

	function _MakeRoute( ) {

		$dir = $this->dirprefix . RandomString( FB_DIRNAME_LEN ) . '/';
		$this->errorMsg = '';

		// ensure the folder exists
		if( ! file_exists ( $this->path . $dir ) ) {

			// switch off error reporting, because it is ugly when it fails
			$old_error_level = error_reporting( 0 );

			if( ! mkdir( $this->path . $dir ) ||
				! chmod( $this->path . $dir, 0777 ) ) {
				$this->_SetErrorMessage( 'A folder for storing transaction data could not be created.' );
			}

			// restore error reporting
			error_reporting( $old_error_level );

			if(	$this->errorMsg != '' ) return false;

		} else if ( ! is_dir( $this->path . $dir ) ) {

			$this->_SetErrorMessage( ' Target folder name ' . $this->path . $dir
								  . ' exists, but is not a folder.' );

			return false;
		}

		do {
			$file = RandomString( FB_FILENAME_LEN );
		} while ( file_exists ( $this->path . $dir . $file . '.php' ) );

		$route = $dir . $file . '.php';

		return $route;
	}


	// returns route without '/' at the beginning
	function _GetRouteFromPath ( $mpath ) {
		return substr( $mpath, strlen( $this->path ) );
	}



	// update 1 entry in the index file
	function _UpdateIndex ( $route, $task ) {

		if( ! file_exists( $this->path . $this->fnameindex ) )
			return $this->BuildIndex();

		// set exclusive and continous access to index file
		$this->classlock = true;

		if( ! $this->_GetFileContents( $this->path . $this->fnameindex, $this->index ) ) {
			$this->_SetErrorMessage( 'Index file exists, but it\'s contents is not accessible.' );
			return false;
		}

		$idx = $this->_FindFirstIndex( $route );

		// files opened hereafter should not use our class level file handle
		$this->classlock = false;

		switch( $task ) {

		case 'del':			// search the entry and delete it

			if( $idx != FB_NOTFOUND ) {
				unset( $this->index[$idx] );
			}
			break;

		case 'add':			// scan the file and add or update the entry

			if( $idx == FB_NOTFOUND ) {
				$idx = count( $this->index );
			}

			$this->index[ $idx ] = $this->_ExtractFieldsFromFile( $this->path . $route );
			break;
		}

		// class level file handle should still point to the index file
		$this->classlock = true;
		return $this->_SaveFile( '', $this->index, true );
	}


	// return the contents of the index file as associative array
	function GetIndexList ( &$index, $refresh = false ) {

		$result = true;

		if( empty( $this->index ) || $refresh )
			$result = $this->_GetFileContents( $this->path . $this->fnameindex, $this->index );

		if( $result ) {

			$tmp = array();
			$index = array();

			// build temp array with mod-date <=> index
			foreach( $this->index as $idx => $value ){
				$tmp[ $value['file_modified'] ] = $idx;
			}

			// sort on mod-date, newest on top
			krsort( $tmp );

			// build output
			$row = array();
			foreach( array_values( $tmp ) as $idx ) {
				for( $i = 0; $i < count( $this->fields) ; ++$i ) {
					$row[ $this->fields[$i] ] = $this->index[ $idx ][$i];
				}
				$row['file_modified'] = $this->index[ $idx ]['file_modified'];
				$row['file_route'] = $this->index[ $idx ]['file_route'];
				$index[] = $row;
			}

		}
		return $result;
	}


	// find the first index in $this->index for a specific field match
	// return the index or FB_NOTFOUND if not found
	function _FindFirstIndex ( $match, $start = 0, $field = -1 ) {

		if( empty( $this->index ) ) {
			$this->_SetErrorMessage( 'Nothing in the database.' );
			return FB_NOTFOUND;
		}

		if( $field == -1 ) {								// means route field
			$field = 'file_route';
		}

		if( ! isset( $this->index[0][$field] ) ) {
			$this->_SetErrorMessage( 'No such field, may be you need the index of this fieldname in the fieds array?' );
			return FB_NOTFOUND;								// no such field
		}

		$match = $this->FormatRefField( $match );

		$max = count( $this->index );

		for( $i = $start; $i < $max; $i++ ) {

			$row =& $this->index[$i];

			if( $row[ $field ] == $match ) return $i;
		}

		return FB_NOTFOUND;
	}


	// visit all files in a folder, cleanup empty folders
	function _ScanFolderContents ( $path ) {

		$dhandle = opendir( $path );

		if( ! $dhandle ) {
			$this->_SetErrorMessage( 'Could not open folder: ' . $path . ' for scanning.' );
			return false;
		}

		$filecount = 0;
		while( false !== ( $filename = readdir( $dhandle ) ) ) {

			if( strpos( $filename, '.' ) === 0 )
				continue;

			$mpath = $path . '/' . $filename;
			$this->index[] = $this->_ExtractFieldsFromFile( $mpath );
			$filecount++;
		}
    	closedir( $dhandle );

		// remove empty folders if possible
		if( $filecount == 0 ) {
			if( ! rmdir( $path ) ) {

				// annoying, but it should not impact functioning of fbase
				writeErrorLog( 'FDB - Could not remove empty folder: ' . $path );
			}
		}

    	return true;
	}


	// reads file contents and retuns file handle to class if this->classlock is set to true to keep file locked between read & write.
	function _GetFileContents ( $mfile , &$data ) {

		if( ! file_exists( $mfile ) ) {
			$data = array();
			return true;
		}

		$bytes = filesize( $mfile );
		if( $bytes == 0 ) {
			$data = array();
			return true;
		}

		if( $this->classlock ) {
			$mode = 'r+';
		} else {
			$mode = 'r';
		}

		$fhandle = fopen( $mfile, $mode );
		if( ! $fhandle || ! $this->_GetFileLock( LOCK_SH, $fhandle ) ) {

			$this->_SetErrorMessage( 'Could not open file: ' . $mfile );
			return false;
		}

		$sdat = fread( $fhandle, $bytes );

		if( $this->classlock ) {

			// give the handle to the class
			$this->fhandle =& $fhandle;

		} else {

			// handle no longer needed
	    	flock( $fhandle, LOCK_UN );
			fclose( $fhandle );

		}
		if( strpos( $sdat, '<?php' ) !== false ) {
			$sdat = substr( $sdat, strlen( PHP_ACCESS_DENIED ) );
		}

		$data = unserialize( $sdat );

		return true;
	}


	// get the fields defined in $this->fields or all from a data file
	function _ExtractFieldsFromFile ( $mpath, $allFields = false ) {

		$mydata = array();
		$this->_GetFileContents( $mpath, $mydata );

		$route = $this->_GetRouteFromPath( $mpath );

		if( empty( $this->fields ) || $allFields ) {

			// all fields
			$md = $mydata;

		} else {

			// only the requested fields
			$md = array();
			foreach( $this->fields as $field ) {
				$md[] = isset( $mydata[ $field ] ) ? $mydata[ $field ] : '';
			}

		}
		//include mtime too
		$md['file_modified'] = filemtime( $mpath ); 			// data modified
		$md['file_route'] = $this->FormatRefField( $route );	// our reference

		return $md;
	}


	// store or update a data file
	function _SaveFile ( $myfile, $data, $overwrite = false ) {

		$mydata = array();

		// always use files with .php extension and some php code to hide contents
		if( strpos( $myfile, '.php' ) === false ) {
			$myfile . '.php';
		}

		if( $this->classlock ) {

			rewind( $this->fhandle );
			$fhandle =& $this->fhandle;

		} else {

			// do we open an existing file or create a new one?
			if( file_exists( $myfile ) ) {
				$mode = 'r+';		// existing file
			} else {

				if( $this->onlyIfExists ) {
					writeErrorLog( 'FDB - Cannot update a non-existing entry.');
					return false;
				}

				$mode = 'w';		// new file
			}

			$fhandle = fopen( $myfile, $mode );
			if( ! $fhandle ) {

				// may be the folder doesn't exist anymore
				$dir = substr( $myfile, 0, strrpos( $myfile, '/' ) );

				if( ! file_exists ( $dir ) ) {

					if( ! mkdir( $dir ) ||
						! chmod( $dir, 0777 ) ) {
						$this->_SetErrorMessage( ' Target folder ' . $this->path . $dir
											  . ' could not be created.' );

						return false;
					}

					// now try again
					$fhandle = fopen( $myfile, $mode );
				}
			}

			if( ! $fhandle || ! $this->_GetFileLock( LOCK_EX, $fhandle ) ) {
				$this->_SetErrorMessage( 'Could not create, open or lock data file: ' . $myfile );
				return false;
			}
		}

		// do we need to read old data?
		if( ( empty( $mode ) || $mode != 'w' ) && ! $overwrite ) {

			$bytes = filesize( $myfile );
			if( $bytes > 0 ) {

				// merge stored data with new data
				$sdat = fread( $fhandle, $bytes );
				rewind( $fhandle );

				// remove the php code
				$sdat = substr( $sdat, strlen( PHP_ACCESS_DENIED ) );

				$mydata = unserialize( $sdat );

				// this adds any key that exists in the input to the stored data
				// and updates any value that already existed.
				foreach( $data as $key => $value ) {
					$mydata[ $key ] = $value;
				}
			}
		}

		$mydata = empty( $mydata ) ? $data : $mydata;

		$sdat = serialize( $mydata );

		fwrite( $fhandle, PHP_ACCESS_DENIED . $sdat );
		flock( $fhandle, LOCK_UN );
		fclose( $fhandle );

		$this->classlock = false;

		return true;
	}


	// uses getFileLock() in utilities.inc.php
	function _GetFileLock ( $lockType, &$fhandle ) {

		// class-lock over rules lockType parameter
		if( $this->classlock ) $lockType = LOCK_EX;

		return getFileLock( $fhandle, $lockType );
	}


	function _SetErrorMessage ( $message ) {
		$this->errorMsg = $message;
	}

	function GetErrorMessage ( ) {
		if( trim( $this->errorMsg ) == '' ) return false;
		return $this->errorMsg;
	}


	/********************************  Utilities  ********************************/

	// format the route to a string that is url-safe and more or less user friendly to show
	function FormatRefField ( $text ) {

		if( strpos( $text, '/' ) === false ) {
			// nothing to do
			return strtoupper( $text );
		}

		// remove the prefix, skip folder seperator
		$l = strlen(FB_DIR_PREFIX);
		$result = substr( $text, $l, FB_DIRNAME_LEN )
				. substr( $text, $l + FB_DIRNAME_LEN + 1 );

		// remove .php
		if( strpos( $result, '.php' ) !== false ) {
			$result = substr( $result, 0, -4 );
		}

		return strtoupper( $result );
	}


	// return the original route from a string that was processed by FormatRefField
	function UnformatRefField ( $text ) {

		if( strpos( $text, '/' ) === false ) {

			$result = FB_DIR_PREFIX . substr( $text , 0, FB_DIRNAME_LEN ) . '/' . substr( $text , FB_DIRNAME_LEN );
			return strtolower( $result . '.php' );
		}

		// seems the $text was already unformated because a formated text doesn't contain a '/'
		return strtolower( $text );
	}

}

?>
