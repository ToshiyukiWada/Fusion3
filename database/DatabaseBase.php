<?php
//==============================================================================
// DatabaseBase
//------------------------------------------------------------------------------
// データベースドライバー基底クラス
// 全てのデータベースドライバー処理は、このクラスを継承して実装・作成してくださ
// い。
// 
// 
//==============================================================================
abstract class DatabaseBase
{
	// メンバ変数定義
	protected $_fusionDatabaseInfo		= null;						// 接続定義情報が格納されている
	protected $_preparedHash			= null;						// プリペアドステートメント
	protected $_preparedArray			= null;
	protected $_preparedMark			= ":BIND";

	//--------------------------------------------------------------------------
	// DatabaseBase::__construct
	//--------------------------------------------------------------------------
	// DatabaseBaseコンストラクタ
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function __construct($fusionDatabaseInfo)
	{
		$this->_fusionDatabaseInfo		= $fusionDatabaseInfo;
	}

	//--------------------------------------------------------------------------
	// DatabaseBase::setPreapredHash
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function setPreparedHash($preparedHash)
	{
		$this->_preparedHash		= $preparedHash;
	}

	//--------------------------------------------------------------------------
	// DatabaseBase::isPrevSelect
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function isPrevSelect()
	{
		return $this->_isPrevSelect;
	}

	//--------------------------------------------------------------------------
	// DatabaseBase::sqlForBind
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function sqlForBind($sql)
	{
		// 内部用バインド変数のクリア
		$this->_preparedArray					= array();

		// バインド変数で定義されている変数一覧でループする
		$bindCount								= 1;
		$matchResult							= array();

		while( preg_match('/<@(.*?)@>/', $sql, $matchResult)  == 1)
		{
			// 置換対象文字の取得
			$replaceTarget							= $matchResult[1];

			// SQL文字列の編集
			$sql									= str_replace('<@'.$replaceTarget.'@>', $this->_preparedMark.$bindCount, $sql);

			// バインド変数として登録
			$this->_preparedArray[$bindCount - 1]	= $this->_preparedHash[$replaceTarget];

			// バインドカウンタのインクリメント
			$bindCount++;
		}

		return $sql;
	}

	// FusionDatabaseInfoへのアクセッサ定義
	protected function getConnection()		{ return $this->_fusionDatabaseInfo->getConnection();		}
	protected function setNullConnection()	{ return $this->_fusionDatabaseInfo->setNullConnection();	}
	protected function getHost()			{ return $this->_fusionDatabaseInfo->getHost();				}
	protected function getPort()			{ return $this->_fusionDatabaseInfo->getPort();				}
	protected function getSchema()			{ return $this->_fusionDatabaseInfo->getSchema();			}
	protected function getUser()			{ return $this->_fusionDatabaseInfo->getUser();				}
	protected function getPass()			{ return $this->_fusionDatabaseInfo->getPass();				}

	// 実装しなければいけないメソッド
	abstract public function connect();											// 各DBMSのコネクション取得処理を記述する
	abstract public function release();											// 各DBMSのコネクション開放処理を記述する
	abstract public function querySEL($sql);									// 各DBMSのクエリー発行(SELECT)処理を記述する
	abstract public function queryIUD($sql);									// 各DBMSのクエリー発行(INSERT/UPDATE/DELETE/その他)処理を記述する
	abstract public function fetch();											// フェッチ処理を記述する
	abstract public function begin();											// トランザクションを開始する
	abstract public function commit();											// トランザクションをコミットする
	abstract public function rollback();										// トランザクションをロールバックする
}
?>
