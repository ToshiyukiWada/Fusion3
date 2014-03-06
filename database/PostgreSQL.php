<?php
require_once dirname(__FILE__) . "/DatabaseBase.php";

//==============================================================================
// MySQL
//------------------------------------------------------------------------------
// 
// 
// 
// 
// 
//==============================================================================
class PostgreSQL extends DatabaseBase
{
	private   $_stmt;
	protected $_bindMark		= ":BIND";

	//--------------------------------------------------------------------------
	// MySQL::connect
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function connect()
	{
		$connection				= null;

		$connectionString		= "pgsql:dbname=".$this->getSchema()." host=".$this->getHost()." port=".$this->getPort();
		$connection				= new PDO($connectionString, $this->getUser(), $this->getPass());

		return $connection;
	}

	//--------------------------------------------------------------------------
	// MySQL::release
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function release()
	{
		// コネクションの開放を行う
		$this->setNullConnection();
	}

	//--------------------------------------------------------------------------
	// MySQL::querySEL
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function querySEL($sql)
	{
		$tempPreparedHash = array();
		for ($count = 0 ; $count < count($this->_preparedHash) ; $count++)
		{
			$tempPreparedHash[":BIND".($count + 1)] = $this->_preparedHash[$count];
		}
		$this->_stmt = $this->getConnection()->prepare($sql);
		$this->_stmt->execute($tempPreparedHash);

		// 直後にエラーが発生したか否かをチェックする
		$errorInfo		= $this->_stmt->errorInfo();
		if ($errorInfo[2] != null)
		{
			throw new Exception($errorInfo[2]);
		}
	}

	//--------------------------------------------------------------------------
	// MySQL::fetch
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function fetch()
	{
		return $this->_stmt->fetch(PDO::FETCH_ASSOC);
	}

	//--------------------------------------------------------------------------
	// MySQL::queryIUD
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function queryIUD($sql)
	{
		$tempPreparedHash = array();
		for ($count = 0 ; $count < count($this->_preparedHash) ; $count++)
		{
			$tempPreparedHash[":BIND".($count + 1)] = $this->_preparedHash[$count];
		}
		$this->_stmt = $this->getConnection()->prepare($sql);
		$this->_stmt->execute($tempPreparedHash);

		// 直後にエラーが発生したか否かをチェックする
		$errorInfo		= $this->_stmt->errorInfo();
		if ($errorInfo[2] != null)
		{
			throw new Exception($errorInfo[2]);
		}

		return $this->_stmt->rowCount();
	}

	//--------------------------------------------------------------------------
	// MySQL::begin
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function begin()
	{
		$this->getConnection()->beginTransaction();
	}
	
	//--------------------------------------------------------------------------
	// MySQL::commit
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function commit()
	{
		$this->getConnection()->commit();
	}

	//--------------------------------------------------------------------------
	// MySQL::rollback
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function rollback()
	{
		$this->getConnection()->rollBack();
	}
}
?>
