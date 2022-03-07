<?php

/**
 *	This class is all you need to save, retrieve and update transactions in a database.
 *
 *  Derive a class from this interface definition and supply the abstract methods
 *
 *  It is wrapper around the FBase classes that are defined in the cartapp and
 *  which can write to files, sqlite and mysql
 *  
 */


abstract class TransactionLoggerInterface
{
	/**
	 * Supply the Application name, this name is used to tag rows
	 *
	 * @return application using this logger
	 */
	abstract public function getApplicationName ( );


	/**
	 * Create the FBase instance that best fits the application
	 *
	 * @return a usable instance of FBase or HostedFBase or null on failure
	 */
	abstract public function createDBInstance ( );


	private $fdb = false;		// the database handle


	public function saveData ( $data, &$route, $onlyIfExists = false ) {

		if( $this->fdb === false )
		{
			$this->fdb = $this->createDBInstance();
			if( ! $this->fdb->IsOk() )
			{
				writeErrorLog( 'No database connection created. May there is none configured?');
				return false;
			}
		}

		return $this->fdb->StoreData( $data, $route, $onlyIfExists, $this->getApplicationName() );
	}


	public function readData ( $route, &$data )
	{
		if( $this->fdb === false )
		{
			$this->fdb = $this->createDBInstance();
			if( ! $this->fdb->IsOk() )
			{
				writeErrorLog( 'No database connection created. May there is none configured?');
				return false;
			}
		}

		return $this->fdb->RetrieveData( $route, $data );
	}


	public function dropData ( ) {

		$appname = $this->getApplicationName();
		if( empty( $appname ) )
		{
			writeErrorLog( 'Can\'t drop data if no application name is specified.');
			return false;
		}

		if( $this->fdb === false )
		{
			$this->fdb = $this->createDBInstance();
			if( ! $this->fdb->IsOk() )
			{
				writeErrorLog( 'No database connection created. May there is none configured?');
				return false;
			}
		}

		return $this->fdb->DropData( $appname );
	}


	public function getError ( ) {

		if( $this->fdb )
			return $this->fdb->GetErrorMessage();

		return '';
	}


}

?>