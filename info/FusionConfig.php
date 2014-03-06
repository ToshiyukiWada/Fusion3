<?php
//==============================================================================
// 
//------------------------------------------------------------------------------
// 
// 
// 
// 
// 
//==============================================================================
class FusionConfig
{
	// 
	private $_htmlversion;										// HTMLバージョン
	private $_projectName;										// プロジェクトな
	private $_projectDebug;										// プロジェクトデバッグ
	private $_projectUrl;										// プロジェクトURL
	private $_developerName;									// 開発者名
	private $_developerMailaddress;								// 開発者メールアドレス
	private $_customerName;										// 顧客名
	private $_customerMailaddress;								// 顧客メールアドレス
	private $_administratorName;								// 管理者名
	private $_administratorMailaddress;							// 管理者メールアドレス
	private $_messagePaths;										// メッセージXMLのパス(文字列配列)
	private $_databases;										// データベース定義(データベース構造体配列)
	private $_sessiontableUse;									// セッションテーブルを利用するか否か
	private $_sessionTableName;									// セッションテーブルを利用する場合のセッションテーブル名
	private $_basedirPath;										// ベースディレクトリの配置パス
	private $_validatePath;										// カスタム入力値チェッククラスの配置パス
	private $_validateXmlPath;									// 入力値チェック設定ファイルの配置パス
	private $_templatePath;										// テンプレート配置パス
	private $_dialogs;											// ダイアログデザイン名とそのデザインファイルの設置パス
	private $_exceptionViewPath;								// 致命的エラー時に表示される画面の設置パス
	private $_logDir;											// ログ出力ディレクトリ
	private $_logFilename;										// ログファイル名
	private $_logMaxsize;										// ログ１ファイルあたりの最大ファイルサイズ
	private $_logLevel;											// ログ出力足切りレベル
	private $_logFormat;										// ログフォーマット

	// 
	public function __construct()
	{
		$this->_htmlversion					= "4";				// HTMLバージョン
		$this->_projectName					= "";				// プロジェクト名
		$this->_projectDebug				= false;			// プロジェクトデバッグ
		$this->_projectUrl					= "";				// プロジェクトURL
		$this->_developerName				= "";				// 開発者名
		$this->_developerMailaddress		= "";				// 開発者メールアドレス
		$this->_customerName				= "";				// 顧客名
		$this->_customerMailaddress			= "";				// 顧客メールアドレス
		$this->_administratorName			= "";				// 管理者名
		$this->_administratorMailaddress	= "";				// 管理者メールアドレス
		$this->_messagePaths				= array();			// メッセージXMLのパス(文字列配列)
		$this->_databases					= array();			// データベース定義(データベース構造体配列)
		$this->_sessiontableUse				= false;			// セッションテーブルを利用するか否か
		$this->_sessionTableName			= "";				// セッションテーブルを利用する場合のセッションテーブル名
		$this->_basedirPath					= "";				// ベースディレクトリの配置パス
		$this->_validatePath				= "";				// カスタム入力値チェッククラスの配置パス
		$this->_validateXmlPath				= "";				// 入力値チェック設定ファイルの配置パス
		$this->_templatePath				= "";				// テンプレート配置パス
		$this->_dialogs						= array();			// ダイアログデザイン名とそのデザインファイルの設置パス
		$this->_exceptionViewPath			= "";				// 致命的エラー時に表示される画面の設置パス
		$this->_logDir						= "";				// ログ出力ディレクトリ
		$this->_logFilename					= "";				// ログファイル名
		$this->_logMaxsize					= 10;				// ログ１ファイルあたりの最大ファイルサイズ
		$this->_logLevel					= "TRACE";			// ログ出力足切りレベル
		$this->_logFormat					= "";				// ログフォーマット
	}

	//--------------------------------------------------------------------------
	// FusionConfig::
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getHtmlVersion()
	{
		return ($this->_htmlversion);
	}

	//--------------------------------------------------------------------------
	// FusionConfig::
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getProjectName()
	{
		return ($this->_projectName);
	}

	//--------------------------------------------------------------------------
	// FusionConfig::
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getProjectDebug()
	{
		return ($this->_projectDebug);
	}

	//--------------------------------------------------------------------------
	// FusionConfig::
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getProjectUrl()
	{
		return ($this->_projectUrl);
	}

	//--------------------------------------------------------------------------
	// FusionConfig::
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getDeveloperName()
	{
		return ($this->_developerName);
	}

	//--------------------------------------------------------------------------
	// FusionConfig::
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getDeveloperMailaddress()
	{
		return ($this->_developerMailaddress);
	}

	//--------------------------------------------------------------------------
	// FusionConfig::
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getCustomerName()
	{
		return ($this->_customerName);
	}

	//--------------------------------------------------------------------------
	// FusionConfig::
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getCustomerMailaddress()
	{
		return ($this->_customerMailaddress);
	}

	//--------------------------------------------------------------------------
	// FusionConfig::
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getAdministratorName()
	{
		return ($this->_administratorName);
	}

	//--------------------------------------------------------------------------
	// FusionConfig::
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getAdministratorMailaddress()
	{
		return ($this->_administratorMailaddress);
	}

	//--------------------------------------------------------------------------
	// FusionConfig::
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getMessagePaths()
	{
		return ($this->_messagePaths);
	}
	//--------------------------------------------------------------------------
	// FusionConfig::
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getDatabases()
	{
		return ($this->_databases);
	}

	//--------------------------------------------------------------------------
	// FusionConfig::
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getSessionTableUse()
	{
		return ($this->_sessiontableUse);
	}

	//--------------------------------------------------------------------------
	// FusionConfig::
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getSessionTableName()
	{
		return ($this->_sessionTableName);
	}

	//--------------------------------------------------------------------------
	// FusionConfig::
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getBasedirPath()
	{
		return ($this->_basedirPath);
	}

	//--------------------------------------------------------------------------
	// FusionConfig::
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getValidatePath()
	{
		return ($this->_validatePath);
	}

	//--------------------------------------------------------------------------
	// FusionConfig::
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getValidateXmlPath()
	{
		return ($this->_validateXmlPath);
	}

	//--------------------------------------------------------------------------
	// FusionConfig::
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getTemplatePath()
	{
		return ($this->_templatePath);
	}

	//--------------------------------------------------------------------------
	// FusionConfig::
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getDialogs()
	{
		return ($this->_dialogs);
	}

	//--------------------------------------------------------------------------
	// FusionConfig::
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getExceptionViewPath()
	{
		return ($this->_exceptionViewPath);
	}

	//--------------------------------------------------------------------------
	// FusionConfig::
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getLogDir()
	{
		return ($this->_logDir);
	}

	//--------------------------------------------------------------------------
	// FusionConfig::
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getLogFilename()
	{
		return ($this->_logFilename);
	}

	//--------------------------------------------------------------------------
	// FusionConfig::
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getLogMaxsize()
	{
		return ($this->_logMaxsize);
	}

	//--------------------------------------------------------------------------
	// FusionConfig::
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getLogLevel()
	{
		return ($this->_logLevel);
	}

	//--------------------------------------------------------------------------
	// FusionConfig::
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getLogFormat()
	{
		return ($this->_logFormat);
	}

	//--------------------------------------------------------------------------
	// FusionConfig::read
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function read($parentBasedir)
	{
		// グローバル変数の読込
		global $FUSION_CONFIG_XML;

		// XML読込開始
		try
		{
			//------------------------------------------------------------------
			// config.xmlファイル読込
			//------------------------------------------------------------------
			$configXml							= simplexml_load_string($FUSION_CONFIG_XML);
			if ($configXml === false)
			{
				throw new Exception("failed read config-xml");
			}

			//------------------------------------------------------------------
			// 置換宣言
			//------------------------------------------------------------------
			$replaceInformation					= array('PROJECT_DIR' => $parentBasedir, 'LANGUAGE' => getLanguage());

			//------------------------------------------------------------------
			// XML読込
			//------------------------------------------------------------------
			$this->_htmlversion					= (replaceConfig($configXml->htmlversion."", "{", "}", $replaceInformation)=="5"?5:4);			// HTMLバージョン
			$this->_projectName					= replaceConfig($configXml->project->name."", "{", "}", $replaceInformation);					// プロジェクト名
			$this->_projectDebug				= strtoupper(replaceConfig($configXml->project->debug."", "{", "}", $replaceInformation))=="TRUE"?true:false;			// プロジェクトデバッグ
			$this->_projectUrl					= replaceConfig($configXml->project->url."", "{", "}", $replaceInformation);					// プロジェクトURL
			$this->_developerName				= replaceConfig($configXml->developer->name."", "{", "}", $replaceInformation);					// 開発者名
			$this->_developerMailaddress		= replaceConfig($configXml->developer->mailaddress."", "{", "}", $replaceInformation);			// 開発者メールアドレス
			$this->_customerName				= replaceConfig($configXml->customer->name."", "{", "}", $replaceInformation);					// 顧客名
			$this->_customerMailaddress			= replaceConfig($configXml->customer->mailaddress."", "{", "}", $replaceInformation);			// 顧客メールアドレス
			$this->_administratorName			= replaceConfig($configXml->administrator->name."", "{", "}", $replaceInformation);				// 管理者名
			$this->_administratorMailaddress	= replaceConfig($configXml->administrator->mailaddress."", "{", "}", $replaceInformation);		// 管理者メールアドレス
			for ($count = 0 ; $count < count($configXml->messages->path) ; $count++)															// メッセージXMLのパス(文字列配列)
			{
				$this->_messagePaths[]			= replaceConfig($configXml->messages->path[$count]."", "{", "}", $replaceInformation);
			}
			for ($count = 0 ; $count < count($configXml->database->resource) ; $count++)														// データベース定義(データベース構造体配列)
			{
				$databaseResource			= $configXml->database->resource[$count];
				$name						= replaceConfig($databaseResource["name"]."", "{", "}", $replaceInformation);
				$driver						= replaceConfig($databaseResource["driver"]."", "{", "}", $replaceInformation);

				$this->_databases[$name]	= new FusionDatabase($name, $driver);

				for ($countServer = 0 ; $countServer < count($databaseResource->server) ; $countServer++)
				{
					$type					= replaceConfig($databaseResource->server[$countServer]["type"]."", "{", "}", $replaceInformation);
					$host					= replaceConfig($databaseResource->server[$countServer]->host."", "{", "}", $replaceInformation);
					$port					= replaceConfig($databaseResource->server[$countServer]->port."", "{", "}", $replaceInformation);
					$schema					= replaceConfig($databaseResource->server[$countServer]->schema."", "{", "}", $replaceInformation);
					$user					= replaceConfig($databaseResource->server[$countServer]->user."", "{", "}", $replaceInformation);
					$pass					= replaceConfig($databaseResource->server[$countServer]->pass."", "{", "}", $replaceInformation);

					// 入力値チェック
					if ($type == "" || $host == "" || $port == "" || $schema == "" || $user == "" || $pass == ""){ continue; }

					// 登録
					if (strtoupper($type) == "MASTER")
					{
						$this->_databases[$name]->setMasterDatabase($host, $port, $schema, $user, $pass, $parentBasedir);
					}
					else
					{
						$this->_databases[$name]->addSlaveDatabase($host, $port, $schema, $user, $pass, $parentBasedir);
					}
				}
			}
			$this->_sessiontableUse				= replaceConfig($configXml->sessiontable["use"]."", "{", "}", $replaceInformation);				// セッションテーブルを利用するか否か
			$this->_sessionTableName			= replaceConfig($configXml->sessiontable->tablename."", "{", "}", $replaceInformation);			// セッションテーブルを利用する場合のセッションテーブル名
			$this->_basedirPath					= replaceConfig($configXml->basedir->path."", "{", "}", $replaceInformation);					// ベースディレクトリの配置パス
			$this->_validatePath				= replaceConfig($configXml->validate->path."", "{", "}", $replaceInformation);					// カスタム入力値チェッククラスの配置パス
			$this->_validateXmlPath				= replaceConfig($configXml->validateXml->path."", "{", "}", $replaceInformation);				// 入力値チェック設定ファイルの配置パス
			$this->_templatePath				= replaceConfig($configXml->template->path."", "{", "}", $replaceInformation);					// テンプレート配置パス
			for ($count = 0 ; $count < count($configXml->dialog->path) ; $count++)																// ダイアログデザイン名とそのデザインファイルの設置パス
			{
				$this->_dialogs[replaceConfig($configXml->dialog->path[$count]["name"]."", "{", "}", $replaceInformation)]		= replaceConfig($configXml->dialog->path[$count]."", "{", "}", $replaceInformation);
			}
			$this->_exceptionViewPath			= replaceConfig($configXml->exceptionView->path."", "{", "}", $replaceInformation);				// 致命的エラー時に表示される画面の設置パス
			$this->_logDir						= replaceConfig($configXml->log->dir."", "{", "}", $replaceInformation);						// ログ出力ディレクトリ
			$this->_logFilename					= replaceConfig($configXml->log->filename."", "{", "}", $replaceInformation);					// ログファイル名
			$this->_logMaxsize					= replaceConfig($configXml->log->maxsize."", "{", "}", $replaceInformation);					// ログ１ファイルあたりの最大ファイルサイズ
			$this->_logLevel					= replaceConfig($configXml->log->level."", "{", "}", $replaceInformation);						// ログ出力足切りレベル
			$this->_logFormat					= replaceConfig($configXml->log->format."", "{", "}", $replaceInformation);						// ログフォーマット
		}
		catch(Exception $exp)
		{
			throw $exp;
		}
	}
}
?>
