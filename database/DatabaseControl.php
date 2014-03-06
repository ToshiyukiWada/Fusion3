<?php
//==============================================================================
// DatabaseControl
//------------------------------------------------------------------------------
// データベース制御
// 
// 
// 
// 
//==============================================================================
class DatabaseControl
{
	// メンバ変数定義
	private $_parent;						// FusionMain
	private $_fusionDatabase;				// FusionDatabase
	private $_nowFusionDatabaseInfo;		// 今現在(直近の)fusionDatabase
	private $_preparedHash;

	//--------------------------------------------------------------------------
	// DatabaseControl::__construct
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function __construct($parent, $name)
	{
		// パラメータの設定
		$this->_parent			= $parent;
		$this->_fusionDatabase	= $parent->getFusionDatabase($name);
		if ($this->_fusionDatabase == null)
		{
			throw new Exception("DatabaseControl Error!");
		}
		$this->_preparedHash	= array();
	}

	//--------------------------------------------------------------------------
	// DatabaseControl::release
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function release()
	{
		// Master開放
		$master		= $this->_fusionDatabase->getMaster();
		if ($master->getConnection() != null)
		{
			$master->getDriverInstance()->release($master->getConnection());
		}

		// Slave開放
		$slaves		= $this->_fusionDatabase->getSlaves();
		for ($count = 0 ; $count < count($slaves) ; $count++ )
		{
			$slave	= $slaves[$count];
			if ($slave->getConnection() != null)
			{
				$slave->getDriverInstance()->release($slave->getConnection());
			}
		}
	}

	//--------------------------------------------------------------------------
	// DatabaseControl::query
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function query($sql)
	{
		// 発行するSQLの種類を判別する
		if ($this->isSelect($sql))
		{
			// SELECTの場合、SLAVEサーバーの登録があればSLAVEサーバーに対してSQLの発行を実施する(SLAVEサーバーが存在しない場合はMASTERに対してSQLを発行する)
			if (count($this->_fusionDatabase->getSlaves()) > 0)
			{
				return $this->queryMasterOrSlave($sql, false);
			}
		}

		// MASTER経由でSQLを発行する
		return $this->queryMasterOrSlave($sql, true);
	}

	//--------------------------------------------------------------------------
	// DatabaseControl::querySlave
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function querySlave($sql)
	{
		// SLAVEサーバーを対象にしたSQL発行
		if (count($this->_fusionDatabase->getSlaves()) > 0)
		{
			return $this->queryMasterOrSlave($sql, false);
		}

		// MASTER経由でSQLを発行する
		return $this->queryMasterOrSlave($sql, true);
	}

	//--------------------------------------------------------------------------
	// DatabaseControl::querySlave
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	private function queryMasterOrSlave($sql, $isMaster)
	{
		// これからSQLを発行するデータベース接続情報を格納する領域を定義しておく
		$this->_nowFusionDatabaseInfo		= null;

		if ($isMaster == true)
		{
			// MASTERに対して発行する場合はMASTERの情報を取得する
			$this->_nowFusionDatabaseInfo	= $this->_fusionDatabase->getMaster();
		}
		else
		{
			// SLAVEに対して発行する場合はSLAVEに定義されている情報の中から1つだけランダムに情報を取得する
			$slaves				= $this->_fusionDatabase->getSlaves();
			$this->_nowFusionDatabaseInfo	= $slaves[0];
		}

		// SQLの発行と結果取得
		$this->_nowFusionDatabaseInfo->setPreparedHash($this->_preparedHash);
		if ($this->isSelect($sql))
		{
			return $this->_nowFusionDatabaseInfo->querySEL($sql);
		}
		else
		{
			return $this->_nowFusionDatabaseInfo->queryIUD($sql);
		}
	}


	//--------------------------------------------------------------------------
	// DatabaseControl::fetch
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function fetch()
	{
		// フェッチ
		return $this->_nowFusionDatabaseInfo->fetch();
	}

	//--------------------------------------------------------------------------
	// DatabaseControl::begin
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function begin()
	{
		// Masterでbegin
		return $this->_fusionDatabase->getMaster()->begin();
	}

	//--------------------------------------------------------------------------
	// DatabaseControl::commit
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function commit()
	{
		// Masterでcommit
		return $this->_fusionDatabase->getMaster()->commit();
	}

	//--------------------------------------------------------------------------
	// DatabaseControl::rollback
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function rollback()
	{
		// Masterでrollback
		return $this->_fusionDatabase->getMaster()->rollback();
	}

	//--------------------------------------------------------------------------
	// DatabaseControl::clearBind
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function clearBind()
	{
		$this->_preparedHash			= array();
	}

	//--------------------------------------------------------------------------
	// DatabaseControl::addBind
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function addBind($name, $value)
	{
		$this->_preparedHash[$name]		= $value;
	}

	//--------------------------------------------------------------------------
	// DatabaseControl::subBind
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function subBind($name)
	{
		unset($this->_preparedHash[$name]);
	}

	//--------------------------------------------------------------------------
	// DatabaseControl::isSelect
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	private function isSelect($sql)
	{
		// SELECTか否かのフラグを持つ
		$result			= false;

		// SQLをTRIMして解析を行う
		$tempSql		= trim($sql);
		$tempSql		= str_replace("(", "", $tempSql);
		$tempSql		= str_replace(")", "", $tempSql);

		// 先頭3文字で判断する為、SQLが3文字以上で構成されているかチェックする
		if (strlen($tempSql) >= 3)
		{
				if (strtoupper(substr($tempSql, 0, 3)) == "SEL"){ $result = true; }
		}
		return $result;
	}
}
?>
