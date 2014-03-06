<?php
require_once dirname(__FILE__) . "/../common/Enum.php";						// Enum
require_once dirname(__FILE__) . "/FusionValidate.php";						// FusionValidate
require_once dirname(__FILE__) . "/../database/DatabaseControl.php";		// DatabaseControl

//==============================================================================
// MODE
//------------------------------------------------------------------------------
// 処理モード列挙
// 
// 
// 
// 
//==============================================================================
final class MODE extends Enum
{
	const ACTION		= "ACTION";				// 通常の画面遷移による画面表示
	const PROCESS		= "PROCESS";			// 画面内のイベントによるメソッドの起動
	const DIALOG		= "DIALOG";				// ダイアログでの画面表示
	const SUGGEST		= "SUGGEST";			// 入力補助のサジェスト
	const DOWNLOAD		= "DOWNLOAD";			// ファイルのダウンロード
	const UPLOAD		= "UPLOAD";				// ファイルのアップロード
	const OTHER			= "OTHER";				// その他
	const VALIDATE		= "VALIDATE";			// 入力値チェック
	const JAVASCRIPT	= "JAVASCRIPT";			// JavaScriptを動的に取得
	const EXCEPTION		= "EXCEPTION";			// エラーが発生したときにエラー内容を表示する画面
}

//==============================================================================
// PROCESSING_MODE
//------------------------------------------------------------------------------
// 処理結果モード列挙
// 
// 
// 
// 
//==============================================================================
final class PROCESSING_MODE extends Enum
{
	const HTML			= "HTML";				// HTMLで結果を返却する
	const AJAX			= "AJAX";				// AJAX(XML)で結果を返却する
	const NONE			= "NONE";				// 結果の返却方法は実装に任せる
}

//==============================================================================
// FusionMain
//------------------------------------------------------------------------------
// 主クラス
// 
// 
// 
// 
//==============================================================================
class FusionMain
{
	private $_parentBasedir;					// 基本DIR

	private $_config;							// config構造体
	private $_controller;						// controller構造体

	private $_mode;								// 処理モード
	private $_processingMode;					// 処理結果モード
	private $_requestUri;						// REQUEST_URI
	private $_nowAction;						// 現在のAction
	private $_parentAction;						// 現在の親Action
	private $_realAction;						// 現在のAction(URLから取得したそのままの状態)
	private $_obj;								// 主処理のオブジェクト
	private $_className;						// 現在の処理クラス名
	private $_startTime;						// 処理開始時間
	private $_endTime;							// 処理終了時間

	private $_databaseControls;					// この処理で利用するDBコントローラー

	private $_errors;							// 内部で発生したエラー一覧

	private $_variables;						// 変数一覧

	private $_htmlContents;						// HTMLコンテンツ(HTMLで返却する内容を格納しておく)
	private $_ajaxContents;						// Ajaxコンテンツ(各Ajax処理の戻り値となる値を格納しておく)
	private $_id;								// Ajaxの非同期処理時に利用するID(各Ajax毎にクライアントで割り振られたIDが渡ってくる)

	//--------------------------------------------------------------------------
	// FusionMain::__construct
	//--------------------------------------------------------------------------
	// FusionMainコンストラクタ
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function __construct($parentBasedir)
	{
		// 基本DIRの設定
		$this->_parentBasedir					= $parentBasedir;					// 以降はindex.phpが動作しているディレクトリを基準として、各ファイルを読み込む

		// メンバ変数の初期化
		$this->_config							= null;								// CONFIG.XML
		$this->_controller						= null;								// CONTROLLER.XML

		$this->_mode							= null;								// 上記MODE列挙型より
		$this->_processingMode					= null;								// 上記PROCESSING_MODE列挙型より
		$this->_requestUri						= null;
		$this->_nowAction						= null;								// 現在のAction
		$this->_parentAction					= null;								// 親のAction
		$this->_realAction						= null;								// 現在のAction(URLから取得したそのままの状態)
		$this->_className						= "";								// 処理対象クラス名
		$this->_obj								= "";								// 処理オブジェクト
		$this->_startTime						= 0;								// 処理開始時間
		$this->_endTime							= 0;								// 処理終了時間

		$this->_databaseControls				= array();							// この処理で利用するDBコントローラー

		$this->_errors							= array();							// 発生したエラーの配列

		$this->_variables						= array();							// 変数一覧

		$this->_htmlContents					= "";								// HTMLコンテンツ
		
		$this->_id								= "";								// Ajaxの非同期処理時に利用するID
    }

	//--------------------------------------------------------------------------
	// FusionMain::fusionErrorHandler
	//--------------------------------------------------------------------------
	// FusionMainエラーハンドラ
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function fusionErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
	{
		throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	}

	//--------------------------------------------------------------------------
	// FusionMain::fromException
	//--------------------------------------------------------------------------
	// 処理中にExceptionに入ってしまった時の処理
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function fromException($exp)
	{
		// エラーの取得
		$this->_errors[]		= array('message' => $exp->getMessage(), 'errstr' => $exp->getTraceAsString(), 'errfile' => $exp->getFile(), 'errline' => $exp->getLine());
	}

	//--------------------------------------------------------------------------
	// FusionMain::getParentBasedir
	//--------------------------------------------------------------------------
	// 基底ディレクトリパスの取得
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getParentBasedir()
	{
		return ($this->_parentBasedir);
	}

	//--------------------------------------------------------------------------
	// FusionMain::getDB
	//--------------------------------------------------------------------------
	// 該当するデータベースコントロールクラスの返却
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getDB($name)
	{
		// DBコントロールに指定された名称のコントロールが存在している場合はそれを返却する
		if (!array_key_exists($name, $this->_databaseControls))
		{
			// 連想配列に存在していない場合は、新規のインスタンスを生成のうえ、連想配列に追加してから返却する
			$this->_databaseControls[$name]			= new DatabaseControl($this, $name);
		}

		// 連想配列の対象となるDBコントローラーを返却する
		return $this->_databaseControls[$name];
	}

	//--------------------------------------------------------------------------
	// FusionMain::getFusionDatabase
	//--------------------------------------------------------------------------
	// 定義済みのDB情報の返却
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getFusionDatabase($name)
	{
		$databases		= $this->_config->getDatabases();
		if (array_key_exists($name, $databases))
		{
			return $databases[$name];
		}
		return null;
	}

	//--------------------------------------------------------------------------
	// FusionMain::getVariables
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getVariables()
	{
		return $this->_variables;
	}

	//--------------------------------------------------------------------------
	// FusionMain::addVariable
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function addVariable($name, $value)
	{
		$this->_variables[$name]			= $value;
	}

	//--------------------------------------------------------------------------
	// FusionMain::getMessage
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getMessage()
	{
		
		
		
		
		
	}

	//--------------------------------------------------------------------------
	// FusionMain::getParameter
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getParameter($parameter)
	{
		$result		= "";

		if (array_key_exists($parameter, $_POST))
		{
			$result		= $_POST[$parameter];
		}
		else if (array_key_exists($parameter, $_GET))
		{
			$result		= $_GET[$parameter];
		}
		return $result;
	}

	//--------------------------------------------------------------------------
	// FusionMain::getResouceText
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getResouceText()
	{
		// 現在仕様未決定
		
		
		
		
		
		
		
		
	}

	//--------------------------------------------------------------------------
	// FusionMain::checkAuthority
	//--------------------------------------------------------------------------
	// 認証チェックを行う
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	private function checkAuthority()
	{
		
		
		
		
		
		
	}

	//--------------------------------------------------------------------------
	// FusionMain::init
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function init()
	{
		// CONFIGの読込
		$this->_config			= new FusionConfig();
		$this->_config->read($this->_parentBasedir);

		// CONTROLLERの読込
		$this->_controller		= new FusionController();
		$this->_controller->read();

		// REQUEST_URIの設定
		$this->_requestUri		= $_SERVER["REQUEST_URI"];

		// Action読込(付属の.htaccessが正常に動作していることが前提とする)
		$action					= "";						// action
		if (array_key_exists("action", $_GET))				// GETパラメータにactionが存在してるかチェック
		{
			$action				= $_GET["action"];			// GETパラメータにactionが存在している場合はそれを読み込む
		}
		if ($action == ""){ $action = "index"; }			// GETパラメータからactionを取得できない場合はトップページと判断する
		if (substr($action, strlen($action) - 1) == "/")
		{
			$action = substr($action, 0, strlen($action) - 1);
		}

		// Actionから動作モードを判定する
		$actions					= $this->_controller->getActions();			// 処理
		$dialogs					= $this->_controller->getDialogs();			// ダイアログ
		$downloads					= $this->_controller->getDownloads();		// ダウンロード
		$suggests					= $this->_controller->getSuggests();		// サジェスト
		$uploads					= $this->_controller->getUploads();			// アップロード
		$others						= $this->_controller->getOthers();			// その他

		// URLから処理を判定する
		$actionVariables		= explode("/", $action);
		switch(count($actionVariables))
		{
			case 1:
				$this->_mode				= new MODE(MODE::ACTION);								// MODE
				$this->_processingMode		= new PROCESSING_MODE(PROCESSING_MODE::HTML);			// PROCESSING_MODE
				$this->_nowAction			= strtoupper($actionVariables[0]);						// nowActio
				$this->_parentAction		= strtoupper($actionVariables[0]);						// parentAction
				$this->_realAction			= $actionVariables[0];

				$this->_className			= array_key_exists($this->_nowAction, $actions)?$actions[$this->_nowAction]->getClassName():null;			// className

				break;

			case 2:

				$this->_nowAction			= strtoupper($actionVariables[1]);
				$this->_parentAction		= array_key_exists("__FUSION_PARENTACTION", $_POST)?$_POST["__FUSION_PARENTACTION"]:"";
				$this->_realAction			= $actionVariables[1];
				$this->_id					= array_key_exists("__FUSINO_AJAXID", $_POST)?$_POST["__FUSINO_AJAXID"]:"";

					 if (strtoupper($actionVariables[0]) == "PROCESS")		{ $this->_mode = new MODE(MODE::PROCESS);		$this->_processingMode = new PROCESSING_MODE(PROCESSING_MODE::AJAX); $this->_className = array_key_exists($this->_parentAction, $actions)  ?$actions  [$this->_parentAction]->getClassName():null; }	// イベントはAJAXで結果を返却する
				else if (strtoupper($actionVariables[0]) == "DIALOG")		{ $this->_mode = new MODE(MODE::DIALOG);		$this->_processingMode = new PROCESSING_MODE(PROCESSING_MODE::HTML); $this->_className = array_key_exists($this->_nowAction,    $dialogs)  ?$dialogs  [$this->_nowAction]->getClassName()   :null; }	// ダイアログの表示はHTMLで結果を返却する
				else if (strtoupper($actionVariables[0]) == "SUGGEST")		{ $this->_mode = new MODE(MODE::SUGGEST);		$this->_processingMode = new PROCESSING_MODE(PROCESSING_MODE::AJAX); $this->_className = array_key_exists($this->_nowAction,    $suggests) ?$suggests [$this->_nowAction]->getClassName()   :null; }	// サジェストはAJAXで結果を返却する
				else if (strtoupper($actionVariables[0]) == "DOWNLOAD")		{ $this->_mode = new MODE(MODE::DOWNLOAD);		$this->_processingMode = new PROCESSING_MODE(PROCESSING_MODE::NONE); $this->_className = array_key_exists($this->_nowAction,    $downloads)?$downloads[$this->_nowAction]->getClassName()   :null; }	// ダウンロードは各処理で返却方式を設定する
				else if (strtoupper($actionVariables[0]) == "UPLOAD")		{ $this->_mode = new MODE(MODE::UPLOAD);		$this->_processingMode = new PROCESSING_MODE(PROCESSING_MODE::AJAX); $this->_className = array_key_exists($this->_nowAction,    $uploads)  ?$uploads  [$this->_nowAction]->getClassName()   :null; }	// アップロードはAJAXで結果を返却する
				else if (strtoupper($actionVariables[0]) == "OTHER")		{ $this->_mode = new MODE(MODE::OTHER);			$this->_processingMode = new PROCESSING_MODE(PROCESSING_MODE::NONE); $this->_className = array_key_exists($this->_nowAction,    $others)   ?$others   [$this->_nowAction]->getClassName()   :null; }	// その他はAJAXで結果を返却する
				else if (strtoupper($actionVariables[0]) == "VALIDATE")		{ $this->_mode = new MODE(MODE::VALIDATE);		$this->_processingMode = new PROCESSING_MODE(PROCESSING_MODE::AJAX); $this->_className = ""; }																											// 入力値チェックはAJAXで結果を返却する
				else if (strtoupper($actionVariables[0]) == "JAVASCRIPT")	{ $this->_mode = new MODE(MODE::JAVASCRIPT);	$this->_processingMode = new PROCESSING_MODE(PROCESSING_MODE::NONE); }																																	// JAVASCRIPTは各処理で返却方式を設定する
				else if (strtoupper($actionVariables[0]) == "EXCEPTION")	{ $this->_mode = new MODE(MODE::EXCEPTION);		$this->_processingMode = new PROCESSING_MODE(PROCESSING_MODE::HTML); }																																	// EXCEPTIONはHTMLで結果を返却する

				break;

			default:
				break;
		}

		// 上記で正常に取得できたか否か
		if ($this->_mode == null || $this->_processingMode == null || $this->_nowAction == null)
		{
			throw new Exception("ERROR");
		}

		// クラスのインスタンス生成
		$this->_obj			= $this->getInstance();
	}

	//--------------------------------------------------------------------------
	// FusionMain::start
	//--------------------------------------------------------------------------
	// メイン処理
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function start()
	{
		// セッション開始
		session_start();

		// 各処理
		switch($this->_mode->valueOf())
		{
			case "ACTION":
				
				$this->doAction();
				break;

			case "DIALOG":
				$this->doDialog();
				break;

			case "PROCESS":
				$this->doProcess();
				break;

			case "SUGGEST":
				$this->doSuggest();
				break;

			case "VALIDATE":
				$this->doValidate();
				break;

			case "DOWNLOAD":
				$this->doDownload();
				break;

			case "UPLOAD":
				$this->doUpload();
				break;

			case "OTHER":
				$this->doOther();
				break;

			case "VALIDATE":
				$this->doValidate();
				break;

			case "JAVASCRIPT":
				$this->doJavascript();
				break;

			case "EXCEPTION":
				$this->doException();
				break;
		}
	}

	//--------------------------------------------------------------------------
	// FusionMain::release
	//--------------------------------------------------------------------------
	// この処理内で利用した全てのリソースをリリースし、結果を返却する
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function release()
	{
		// DB接続クローズ
		
		
		
		
		

		// その他リソース開放
		
		
		
		

		// これまでの処理内でエラーが発生していたか否かを判断する
		if (count($this->_errors) > 0)
		{
			// エラー格納用変数の定義
			$exceptionMessage								= "";
			$exceptionMessageDetail							= "";

			// エラーの内容を取得
			$errorInformation								= $this->_errors[0];
			$exceptionMessage								= $errorInformation["message"];
			if ($this->_config->getProjectDebug() == "true")
			{
				$exceptionMessageDetail							= $errorInformation["errstr"]."(".$errorInformation["errfile"].":".$errorInformation["errline"].")";
			}
			else
			{
				$exceptionMessageDetail							= $errorInformation["errfile"].":".$errorInformation["errline"];
			}

			// 画面に引き渡す為に、エラー内容を画面の変数としても保持させる
			$this->_variables["exceptionMessage"]			= $exceptionMessage;
			$this->_variables["exceptionMessageDetail"]		= $exceptionMessageDetail;

			// 結果返却方法をチェック
			if ($this->_processingMode == "HTML" || $this->_processingMode == "NONE")
			{
				// エラー画面へ遷移
				template($this, $this->_parentBasedir."/FUSION3/view/FusionException.html");
				return;
			}
		}

		// 処理形態に応じた形式で結果を返却する
		switch($this->_processingMode->valueOf())
		{
			case "HTML":													// HTML系の返却
				header("Content-Type: text/html; charset=utf-8");			// Content-Typeの出力
				print($this->_htmlContents);								// 内容の出力
				break;

			case "AJAX":													// Ajax系の返却
				header("Content-Type: text/xml; charset=utf-8");			// Content-Typeの出力
				print("<?xml version=\"1.0\"  encoding=\"UTF-8\"?>\n");
				print("<FUSIONAjax>\n");
				print("		<id>"				.htmlspecialchars($this->_id).				"</id>\n");
				print("		<mode>"				.htmlspecialchars($this->_mode).			"</mode>\n");
				print("		<processingMode>"	.htmlspecialchars($this->_processingMode).	"</processingMode>\n");
				print("		<requestUrl>"		.htmlspecialchars($this->_requestUri).		"</requestUrl>\n");
				print("		<nowAction>"		.htmlspecialchars($this->_nowAction).		"</nowAction>\n");
				print("		<parentAction>"		.htmlspecialchars($this->_parentAction).	"</parentAction>\n");
				print("		<realAction>"		.htmlspecialchars($this->_realAction).		"</realAction>\n");
				print("		<className>"		.htmlspecialchars($this->_className).		"</className>\n");
				print("		<startTime>"		.htmlspecialchars($this->_startTime).		"</startTime>\n");
				print("		<endTime>"			.htmlspecialchars($this->_endTime).			"</endTime>\n");
				print("		<errors>\n");
				print("			".htmlspecialchars(json_encode($this->_errors))."\n");
				print("		</errors>\n");
				print("		<variables>\n");
				print("			".htmlspecialchars(json_encode($this->_variables))."\n");
				print("		</variables>\n");
				print("		<contents>\n");
				print("			".htmlspecialchars(json_encode($this->_ajaxContents))."\n");	// 各Ajax処理での戻り値として適切な値が格納される
				print("		</contents>\n");
				print("</FUSIONAjax>\n");
				break;

				//--------------------------------------------------------------
				// ajaxContentsについて
				//--------------------------------------------------------------
				// 各Ajaxのコンテンツでは、クライアントへ返却したい値がそれぞれ
				// 異なる為、各コンテンツでは適した変数の格納のみを実施し、その
				// 結果をJSON形式でクライアントが受信する。
				// 受信したデータをフレームワーク内の関数で適した形に展開し、そ
				// の結果を使って各コンテンツのクライアント処理を実施する。
				// 
				// [PROCESS]
				// 処理結果のみを返却すれば良いので、ここで返却する値は存在しない
				// 
				// [SUGGEST]
				// サジェストに表示する一覧を配列で返却する
				// 
				// [UPLOAD]
				// アップロードしたファイルの名前・サイズ等の情報を連想配列で返却する
				// 
				// [VALIDATE]
				// 入力値チェック処理の結果を連想配列の配列で返却する
				// 
				//--------------------------------------------------------------

			case "NONE":													// 何もしない(各処理で適切なContent-Typeを出力させる)
				break;
		}
	}

	//--------------------------------------------------------------------------
	// FusionMain::doAction
	//--------------------------------------------------------------------------
	// WEBページの表示を行う
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	private function doAction()
	{
		// 変数定義
		$onLoadScripts			= "";										// 画面読込後に実行したいJavaScript

		// 初期処理起動
		$this->_obj->init();

		// 主処理起動
		$actionReturn		= $this->_obj->main();

		// 終了処理起動
		$this->_obj->release();

		// リダイレクト判定
		if ($this->_obj->getRedirectUrl() != "")
		{
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: ".$this->_obj->getRedirectUrl());
			return;
		}

		//----------------------------------------------------------------------
		// Actionタグ情報の取得
		//----------------------------------------------------------------------
		$actions		= $this->_controller->getActions();
		$action			= $actions[$this->_nowAction];

		$actionGroupid			= $action->getGroup();

		//----------------------------------------------------------------------
		// 最終的に利用する要素を格納しておく変数の定義
		//----------------------------------------------------------------------
		$doctype		= "";
		$htmltag		= "";
		$header			= "";
		$footer			= "";
		$javascript		= "";
		$stylesheet		= "";
		$authority		= "";

		//----------------------------------------------------------------------
		// 共通情報を取得する
		//----------------------------------------------------------------------
		$commonDoctype			= $this->_controller->getCommon()->getDoctype();		// DOCTYPE
		$commonHtmltag			= $this->_controller->getCommon()->getHtmltag();		// HTMLTAG
		$commonHeader			= $this->_controller->getCommon()->getHeader();			// ヘッダー
		$commonFooter			= $this->_controller->getCommon()->getFooter();			// フッター
		$commonJavascript		= $this->_controller->getCommon()->getJavascript();		// JavaScript
		$commonStylesheet		= $this->_controller->getCommon()->getStylesheet();		// StyleSheet
		$commonAuthority		= $this->_controller->getCommon()->getAuthority();		// 認証クラス

		if ($commonDoctype		!= ""){ $doctype	= $commonDoctype; }
		if ($commonHtmltag		!= ""){ $htmltag	= $commonHtmltag; } 
		if ($commonHeader		!= ""){ $header		= $commonHeader; }
		if ($commonFooter		!= ""){ $footer		= $commonFooter; }
		if ($commonJavascript	!= ""){ $javascript	= $commonJavascript; }
		if ($commonStylesheet	!= ""){ $stylesheet	= $commonStylesheet; }
		if ($commonAuthority	!= ""){ $authority	= $commonAuthority; }

		//----------------------------------------------------------------------
		// 対象グループの情報を取得する(まずは、グループの情報を適用後に個別情報で上書きする)
		//----------------------------------------------------------------------
		$groupDoctype			= "";
		$groupHtmltag			= "";
		$groupHeader			= "";
		$groupFooter			= "";
		$groupJavascript		= "";
		$groupStylesheet		= "";
		$groupAuthority			= "";
		$groups					= $this->_controller->getGroups();

		if ($actionGroupid != "")
		{
			if (array_key_exists(strtoupper($actionGroupid), $groups))
			{
				$group				= $groups[strtoupper($actionGroupid)];

				$groupDoctype		= $group->getDoctype();
				$groupHtmltag		= $group->getHtmltag();
				$groupHeader		= $group->getHeader();
				$groupFooter		= $group->getFooter();
				$groupJavascript	= $group->getJavascript();
				$groupStylesheet	= $group->getStylesheet();
				$groupAuthority		= $group->getauthority();
			}
		}

		if (strtoupper($groupDoctype)		== "NULL")	{ $doctype		= ""; }
		if (strtoupper($groupHtmltag)		== "NULL")	{ $htmltag		= ""; }
		if (strtoupper($groupHeader)		== "NULL")	{ $header		= ""; }
		if (strtoupper($groupFooter)		== "NULL")	{ $footer		= ""; }
		if (strpos(strtoupper(",".$groupJavascript),"NULL") != false)	{ $javascript	= ""; }
		if (strpos(strtoupper(",".$groupStylesheet),"NULL") != false)	{ $stylesheet	= ""; }
		if (strpos(strtoupper(",".$groupAuthority) ,"NULL") != false)	{ $authority	= ""; }

		//----------------------------------------------------------------------
		// まずはgroupの定義を読込む(定義されていれば)
		//----------------------------------------------------------------------
		if ($groupDoctype		!= "" && strtoupper($groupDoctype)		!= "NULL"){ $doctype	= $groupDoctype; }
		if ($groupHtmltag		!= "" && strtoupper($groupHtmltag)		!= "NULL"){ $htmltag	= $groupHtmltag; } 
		if ($groupHeader		!= "" && strtoupper($groupHeader)		!= "NULL"){ $header		= $groupHeader; }
		if ($groupFooter		!= "" && strtoupper($groupFooter)		!= "NULL"){ $footer		= $groupFooter; }
		if ($groupJavascript	!= "" )
		{
			$tempJavascripts		= explode(",", $groupJavascript);
			for($countJavascript = 0 ; $countJavascript < count($tempJavascripts) ; $countJavascript++)
			{
				if ($tempJavascripts[$countJavascript] == ""){ continue; }
				if (strtoupper($tempJavascripts[$countJavascript]) == "NULL"){ continue; }
				$javascript .= ($javascript==""?"":",").$tempJavascripts[$countJavascript];
			}
		}
		if ($groupStylesheet	!= "" )
		{
			$tempStylesheets		= explode(",", $groupStylesheet);
			for($countStylesheet = 0 ; $countStylesheet < count($tempStylesheets) ; $countStylesheet++)
			{
				if ($tempStylesheets[$countStylesheet] == ""){ continue; }
				if (strtoupper($tempStylesheets[$countStylesheet]) == "NULL"){ continue; }
				$stylesheet .= ($stylesheet==""?"":",").$tempStylesheets[$countStylesheet];
			}
		}
		if ($groupAuthority	!= "" )
		{
			$tempAuthorities		= explode(",", $groupAuthority);
			for($countAuthority = 0 ; $countAuthority < count($tempAuthorities) ; $countAuthority++)
			{
				if ($tempAuthorities[$countAuthority] == ""){ continue; }
				if (strtoupper($tempAuthorities[$countAuthority]) == "NULL"){ continue; }
				$authority 	.= ($authority==""?"":",").$tempAuthorities[$countAuthority];
			}
		}

		//----------------------------------------------------------------------
		// 次にActionに定義されている定義が存在する場合はそれを読込むが、その前に"NULL"で定義されている場合はgroupで定義された内容を消去する
		//----------------------------------------------------------------------
		// controller.xmlからHTML系の情報を取得する
		$actionDoctype			= $action->getDoctype();								// DOCTYPE
		$actionHtmltag			= $action->getHtmltag();								// HTMLTAG
		$actionHeader			= $action->getHeader();									// ヘッダー
		$actionFooter			= $action->getFooter();									// フッター
		$actionJavascript		= $action->getJavascript();								// JavaScript
		$actionStylesheet		= $action->getStylesheet();								// StyleSheet
		$actionAuthority		= $action->getAuthority();								// 認証クラス

		if (strtoupper($actionDoctype)		== "NULL")	{ $doctype		= ""; }
		if (strtoupper($actionHtmltag)		== "NULL")	{ $htmltag		= ""; }
		if (strtoupper($actionHeader)		== "NULL")	{ $header		= ""; }
		if (strtoupper($actionFooter)		== "NULL")	{ $footer		= ""; }
		if (strpos(strtoupper(",".$actionJavascript),"NULL") != false)	{ $javascript	= ""; }
		if (strpos(strtoupper(",".$actionStylesheet),"NULL") != false)	{ $stylesheet	= ""; }
		if (strpos(strtoupper(",".$actionAuthority) ,"NULL") != false)	{ $authority	= ""; }

		//----------------------------------------------------------------------
		// 最終的な読み込み
		//----------------------------------------------------------------------
		if ($actionDoctype		!= "" && strtoupper($actionDoctype)		!= "NULL") 		{ $doctype		= $actionDoctype; }
		if ($actionHtmltag	 	!= "" && strtoupper($actionHtmltag)		!= "NULL") 		{ $htmltag		= $actionHtmltag; }
		if ($actionHeader		!= "" && strtoupper($actionHeader)		!= "NULL") 		{ $header		= $actionHeader; }
		if ($actionFooter		!= "" && strtoupper($actionFooter)		!= "NULL") 		{ $footer		= $actionFooter; }
		if ($actionJavascript	!= "" )
		{
			$tempJavascripts		= explode(",", $actionJavascript);
			for($countJavascript = 0 ; $countJavascript < count($tempJavascripts) ; $countJavascript++)
			{
				if ($tempJavascripts[$countJavascript] == ""){ continue; }
				if (strtoupper($tempJavascripts[$countJavascript]) == "NULL"){ continue; }
				$javascript .= ($javascript==""?"":",").$tempJavascripts[$countJavascript];
			}
		}
		if ($actionStylesheet	!= "" )
		{
			$tempStylesheets		= explode(",", $actionStylesheet);
			for($countStylesheet = 0 ; $countStylesheet < count($tempStylesheets) ; $countStylesheet++)
			{
				if ($tempStylesheets[$countStylesheet] == ""){ continue; }
				if (strtoupper($tempStylesheets[$countStylesheet]) == "NULL"){ continue; }
				$stylesheet .= ($stylesheet==""?"":",").$tempStylesheets[$countStylesheet];
			}
		}
		if ($actionAuthority	!= "" )
		{
			$tempAuthorities		= explode(",", $actionAuthority);
			for($countAuthority = 0 ; $countAuthority < count($tempAuthorities) ; $countAuthority++)
			{
				if ($tempAuthorities[$countAuthority] == ""){ continue; }
				if (strtoupper($tempAuthorities[$countAuthority]) == "NULL"){ continue; }
				$authority 	.= ($authority==""?"":",").$tempAuthorities[$countAuthority];
			}
		}

		//----------------------------------------
		// メインコンテンツ出力開始(ここから出力を制御する)
		//----------------------------------------
		ob_start();

		//----------------------------------------
		// HTMLヘッダーとフレームワーク共通部品読み込み
		//----------------------------------------
		print("<!DOCTYPE html>\n");
		print("<html>\n");
		print("	<head>\n");
		print("		<meta charset=\"UTF-8\">\n");
		print("		<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n");

		// タイトル
		print("		<title>".$action->getTitle()."</title>\n");

		// 定義済みのStylesheetを読込む
		if ($stylesheet != "")
		{
			$tempStylesheets		= explode(",", $stylesheet);
			for($countStylesheet = 0 ; $countStylesheet < count($tempStylesheets) ; $countStylesheet++)
			{
				if ($tempStylesheets[$countStylesheet] == ""){ continue; }
				if (strtoupper($tempStylesheets[$countStylesheet]) == "NULL"){ continue; }
				print("		<link rel=\"stylesheet\" href=\"".$tempStylesheets[$countStylesheet]."\" />\n");
			}
		}

		// フレームワークでの共通読み込み部品
		print("		<script type=\"text/javascript\" src=\"./javascript/FUSION3-script-jquery\"></script>\n");				// jQuery
		print("		<script type=\"text/javascript\" src=\"./javascript/FUSION3-script-jqueryupload\"></script>\n");		// jQuery Upload
		print("		<script type=\"text/javascript\" src=\"./javascript/FUSION3-script-FUSION\"></script>\n");				// FUSION共通関数
		print("		<script type=\"text/javascript\" src=\"./javascript/FUSION3-script-FUSIONAjaxBase\"></script>\n");		// FUSIONAjax共通関数
		print("		<script type=\"text/javascript\" src=\"./javascript/FUSION3-script-FUSIONDialog\"></script>\n");		// FUSIONダイアログ系クラス
		print("		<script type=\"text/javascript\" src=\"./javascript/FUSION3-script-FUSIONProcess\"></script>\n");		// FUSIONプロセス系クラス
		print("		<script type=\"text/javascript\" src=\"./javascript/FUSION3-script-FUSIONValidate\"></script>\n");		// FUSION入力値チェック系クラス
		print("		<script type=\"text/javascript\" src=\"./javascript/FUSION3-script-FUSIONSuggest\"></script>\n");		// FUSIONサジェストクラス
		print("		<script type=\"text/javascript\" src=\"./javascript/FUSION3-script-FUSIONUpload\"></script>\n");		// FUSIONアップロードクラス

		// 定義済みのJavaScriptを読込む
		if ($javascript != "")
		{
			$tempJavascripts		= explode(",", $javascript);
			for($countJavascript = 0 ; $countJavascript < count($tempJavascripts) ; $countJavascript++)
			{
				if ($tempJavascripts[$countJavascript] == ""){ continue; }
				if (strtoupper($tempJavascripts[$countJavascript]) == "NULL"){ continue; }
				print("		<script type=\"text/javascript\" src=\"".$tempJavascripts[$countJavascript]."\"></script>\n");
			}
		}


		print("	</head>\n");
		print("	<body>\n");
		print("		<form id=\"inputform\" onsubmit=\"return false;\" style=\"margin-bottom:-7px;\">\n");
		print("			<input type=\"hidden\" name=\"__FUSION_ACTION\"       id=\"__FUSION_ACTION\"       value=\"".$this->_nowAction."\">\n");
		print("			<input type=\"hidden\" name=\"__FUSION_PARENTACTION\" id=\"__FUSION_PARENTACTION\" value=\"".$this->_parentAction."\">\n");

		//----------------------------------------
		// ヘッダー出力
		//----------------------------------------

		if ($header != null)
		{
			if ($header != "")
			{
				if (file_exists($this->_config->getBasedirPath().$header))
				{
					$onLoadScripts		.= template($this, $this->_config->getBasedirPath().$header);
				}
			}
		}

		//----------------------------------------
		// 主画面出力
		//----------------------------------------
		$mainView		= null;
		$views			= $action->getViews();
		$mainView		= $views[$actionReturn]["common"];

		// クライアント指定が存在しているかチェックして、存在している場合はクライアント指定の画面を表示する
		     if (array_key_exists("PC"				, $views[$actionReturn]["client"]) && isPC()							){ $mainView	= $views[$actionReturn]["client"]["PC"];			}
		else if (array_key_exists("Smartphone"		, $views[$actionReturn]["client"]) && isSmartphone()					){ $mainView	= $views[$actionReturn]["client"]["Smartphone"];	}
		else if (array_key_exists("Tablet"			, $views[$actionReturn]["client"]) && isTablet()						){ $mainView	= $views[$actionReturn]["client"]["Tablet"];		}
		else if (array_key_exists("iPhone"			, $views[$actionReturn]["client"]) && getClient() == "iPhone"			){ $mainView	= $views[$actionReturn]["client"]["iPhone"];		}
		else if (array_key_exists("iPad"			, $views[$actionReturn]["client"]) && getClient() == "iPad"				){ $mainView	= $views[$actionReturn]["client"]["iPad"];			}
		else if (array_key_exists("AndroidMobile"	, $views[$actionReturn]["client"]) && getClient() == "AndroidMobile"	){ $mainView	= $views[$actionReturn]["client"]["AndroidMobile"];	}
		else if (array_key_exists("AndroidTablet"	, $views[$actionReturn]["client"]) && getClient() == "AndroidTablet"	){ $mainView	= $views[$actionReturn]["client"]["AndroidTablet"];	}
		else if (array_key_exists("MSIE"			, $views[$actionReturn]["client"]) && getClient() == "MSIE"				){ $mainView	= $views[$actionReturn]["client"]["MSIE"];			}
		else if (array_key_exists("Firefox"			, $views[$actionReturn]["client"]) && getClient() == "Firefox"			){ $mainView	= $views[$actionReturn]["client"]["Firefox"];		}
		else if (array_key_exists("Safari"			, $views[$actionReturn]["client"]) && getClient() == "Safari"			){ $mainView	= $views[$actionReturn]["client"]["Safari"];		}
		else if (array_key_exists("Chrome"			, $views[$actionReturn]["client"]) && getClient() == "Chrome" 			){ $mainView	= $views[$actionReturn]["client"]["Chrome"];		}

		if ($mainView != null)
		{
			if (file_exists($this->_config->getBasedirPath().$mainView->getPath()))
			{
				$onLoadScripts		.= template($this, $this->_config->getBasedirPath().$mainView->getPath());
			}
		}

		//----------------------------------------
		// フッター出力
		//----------------------------------------
		if ($footer != null)
		{
			if ($footer != "")
			{
				if (file_exists($this->_config->getBasedirPath().$footer))
				{
					$onLoadScripts		.= template($this, $this->_config->getBasedirPath().$footer);
				}
			}
		}

		//----------------------------------------
		// HTML終端出力
		//----------------------------------------
		print("		</form>\n");
		if ($onLoadScripts != "")
		{
			print("<script type='text/javascript'>".$onLoadScripts."</script>\n");
		}
		print("	</body>\n");
		print("</html>\n");

		//----------------------------------------
		// メインコンテンツ取得
		//----------------------------------------
		$this->_htmlContents		= ob_get_contents();			// コンテンツ内容の確保

		ob_clean();													// コンテンツ内容のクリア
		ob_flush();													// コンテンツ内容の出力(クリア後なので、ここでは何も出力されない)
	}

	//--------------------------------------------------------------------------
	// FusionMain::doDialog
	//--------------------------------------------------------------------------
	// ダイアログの表示を行う
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	private function doDialog()
	{
		// 内部で利用する定数宣言
		$CONTENTS_REPLACE_TAG		= "!%CONTENTS%!";
		$TITLE_REPLACE_TAG			= "!%TITLE%!";
		$CLOSE_REPLACE_TAG			= "!%CLOSE%!";

		// 変数定義
		$onLoadScripts				= "";									// 画面読込後に実行したいJavaScript

		// 初期処理起動
		$this->_obj->init();

		// 主処理起動
		$dialogReturn		= $this->_obj->main();

		// 終了処理起動
		$this->_obj->release();

		// ダイアログ情報の取得
		$dialogs		= $this->_controller->getDialogs();
		$dialog			= $dialogs[$this->_nowAction];

		// ダイアログ枠の取得
		$configDialogs			= $this->_config->getDialogs();				// ダイアログの枠デザインの定義を全て取得する
		$dialogFramePath		= "";										// ここにダイアログの枠デザインのHTMLパスが格納される(存在しない場合は空文字が格納される)
		$dialogName				= "";										// パラメータで指定されたダイアログ名を取得する
		if (array_key_exists("dialogName", $_POST)){ $dialogName = $_POST["dialogName"]; }		// POSTパラメータから取得
		if (array_key_exists("dialogName", $_GET )){ $dialogName = $_GET ["dialogName"]; }		// GETパラメータから取得
		if ($dialogName == "")
		{
			// パラメータからダイアログデザインを取得できない場合は、定義の中にある最上位のデザインを適用する
			if (count($configDialogs) > 0){ foreach( $configDialogs as $key => $value ){ $dialogName = $key; break; }}
		}
		if ($dialogName != "" && array_key_exists($dialogName, $configDialogs))
		{
			$dialogFramePath	= $configDialogs[$dialogName];
		}

		// ダイアログ枠HTMLファイルの解析
		$dialogFrameSource		= file_get_contents($dialogFramePath);	// ダイアログ枠のHTMLが格納される
		$dialogFrameHeader		= "";									// ダイアログ枠のコンテンツより上側が格納される
		$dialogFrameFooter		= "";									// ダイアログ枠のコンテンツより下側が格納される
		if (strpos($dialogFrameSource, $CONTENTS_REPLACE_TAG) !== false)
		{
			$contentsIndex		= strpos($dialogFrameSource, $CONTENTS_REPLACE_TAG);
			$dialogFrameHeader	= substr($dialogFrameSource, 0, $contentsIndex);
			$dialogFrameFooter	= substr($dialogFrameSource, $contentsIndex + strlen($CONTENTS_REPLACE_TAG));
		}

		//----------------------------------------
		// ダイアログ枠の置換文字の定義
		//----------------------------------------
		$replaceInformation					= array('CLOSE' => "closeDialog();", 'TITLE' => $dialog->getTitle());

		// メインコンテンツ出力開始(ここから出力を制御する)
		ob_start();

		//----------------------------------------
		// ダイアログ枠上部出力
		//----------------------------------------
		$dialogFrameHeader		= replaceConfig($dialogFrameHeader, "!%", "%!", $replaceInformation);
		eval("?>".$dialogFrameHeader);

		//----------------------------------------
		// 主画面出力
		//----------------------------------------
		$mainView		= null;
		$views			= $dialog->getViews();
		$mainView		= $views[$dialogReturn]["common"];

		// クライアント指定が存在しているかチェックして、存在している場合はクライアント指定の画面を表示する
		     if (array_key_exists("PC"				, $views[$actionReturn]["client"]) && isPC()							){ $mainView	= $views[$actionReturn]["client"]["PC"];			}
		else if (array_key_exists("Smartphone"		, $views[$actionReturn]["client"]) && isSmartphone()					){ $mainView	= $views[$actionReturn]["client"]["Smartphone"];	}
		else if (array_key_exists("Tablet"			, $views[$actionReturn]["client"]) && isTablet()						){ $mainView	= $views[$actionReturn]["client"]["Tablet"];		}
		else if (array_key_exists("iPhone"			, $views[$actionReturn]["client"]) && getClient() == "iPhone"			){ $mainView	= $views[$actionReturn]["client"]["iPhone"];		}
		else if (array_key_exists("iPad"			, $views[$actionReturn]["client"]) && getClient() == "iPad"				){ $mainView	= $views[$actionReturn]["client"]["iPad"];			}
		else if (array_key_exists("AndroidMobile"	, $views[$actionReturn]["client"]) && getClient() == "AndroidMobile"	){ $mainView	= $views[$actionReturn]["client"]["AndroidMobile"];	}
		else if (array_key_exists("AndroidTablet"	, $views[$actionReturn]["client"]) && getClient() == "AndroidTablet"	){ $mainView	= $views[$actionReturn]["client"]["AndroidTablet"];	}
		else if (array_key_exists("MSIE"			, $views[$actionReturn]["client"]) && getClient() == "MSIE"				){ $mainView	= $views[$actionReturn]["client"]["MSIE"];			}
		else if (array_key_exists("Firefox"			, $views[$actionReturn]["client"]) && getClient() == "Firefox"			){ $mainView	= $views[$actionReturn]["client"]["Firefox"];		}
		else if (array_key_exists("Safari"			, $views[$actionReturn]["client"]) && getClient() == "Safari"			){ $mainView	= $views[$actionReturn]["client"]["Safari"];		}
		else if (array_key_exists("Chrome"			, $views[$actionReturn]["client"]) && getClient() == "Chrome" 			){ $mainView	= $views[$actionReturn]["client"]["Chrome"];		}

		if ($mainView != null)
		{
			if (file_exists($this->_config->getBasedirPath().$mainView->getPath()))
			{
				$onLoadScripts		.= template($this, $this->_config->getBasedirPath().$mainView->getPath());
			}
		}

		//----------------------------------------
		// ダイアログ枠下部出力
		//----------------------------------------
		$dialogFrameFooter		= replaceConfig($dialogFrameFooter, "!%", "%!", $replaceInformation);
		eval("?>".$dialogFrameFooter);

		// メインコンテンツ取得
		$this->_htmlContents		= ob_get_contents();			// コンテンツ内容の確保
		ob_clean();													// コンテンツ内容のクリア
		ob_flush();													// コンテンツ内容の出力(クリア後なので、ここでは何も出力されない)
	}

	//--------------------------------------------------------------------------
	// FusionMain::doProcess
	//--------------------------------------------------------------------------
	// Ajax処理を行う
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	private function doProcess()
	{
		// 画面の定義クラス内に指定されたメソッドが存在するかをチェックする
		if (method_exists($this->_obj, $this->_realAction))
		{
			// 指定された処理の起動
			$this->_obj->{$this->_realAction}();
		}
		else
		{
			// 存在しない場合
			throw new Exception($this->_realAction." method is not found in ActionClass");
		}
	}

	//--------------------------------------------------------------------------
	// FusionMain::doSuggest
	//--------------------------------------------------------------------------
	// サジェスト処理を行う
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	private function doSuggest()
	{
		$value		= "";
		if (array_key_exists("suggestValue", $_POST)){ $value = $_POST["suggestValue"]; }

		// サジェスト処理をキックし、その結果を配列として受け取る
		$this->_obj->main($value);
		$suggestCandidates		= $this->_obj->getCandidates();
		$this->_ajaxContents	= $suggestCandidates;
	}

	//--------------------------------------------------------------------------
	// FusionMain::doValidate
	//--------------------------------------------------------------------------
	// 入力値チェック処理を行う
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	private function doValidate()
	{
		// 入力値チェック結果の格納領域
		$fusionValidateResult = null;

		// 対象となる入力値チェックXMLファイルの取得
		$validateXmlFilename		= "";
		if (file_exists($this->_config->getValidateXmlPath()))
		{
			if ($validateXmlHandle = opendir($this->_config->getValidateXmlPath()))
			{
				while (false !== ($file = readdir($validateXmlHandle)))
				{
					if ($file == "." || $file == ".."){ continue; }
					if (strtoupper($this->_parentAction.".xml") == strtoupper($file)){ $validateXmlFilename = $file; }
				}
			}
		}

		$validateXmlPath			= $this->_config->getValidateXmlPath().$validateXmlFilename;
		if (file_exists($validateXmlPath))
		{
			// XMLを使った入力値チェックを実施
			$fusionValidate				= new FusionValidate($this, $this->_parentAction, $this->_realAction, $validateXmlPath);
			$fusionValidateResult		= $fusionValidate->validate();
		}

		$this->_ajaxContents	= $fusionValidateResult;
	}

	//--------------------------------------------------------------------------
	// FusionMain::doDownload
	//--------------------------------------------------------------------------
	// ダウンロード処理を行う
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	private function doDownload()
	{
		$this->_obj->init();
		$downloadData			= $this->_obj->main();
		$this->_obj->release();
		$downloadFilename		= "";

		// ダウンロード
		header('Content-Type: application/octet-stream'); 
		header('Content-Disposition: attachment; filename=');
		header('Content-Length: '.strlen($data));
		ob_clean();
		flush();
		echo $data;
	}

	//--------------------------------------------------------------------------
	// FusionMain::doUpload
	//--------------------------------------------------------------------------
	// アップロード処理を行う
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	private function doUpload()
	{
		$this->_obj->init();
		$this->_obj->main();
		$this->_obj->release();
	}

	//--------------------------------------------------------------------------
	// FusionMain::doOther
	//--------------------------------------------------------------------------
	// その他処理を行う
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	private function doOther()
	{
		
		
		
		
		
	}

	//--------------------------------------------------------------------------
	// FusionMain::doJavascript
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	private function doJavascript()
	{
		// URLで指定された名称から適切なJavaScriptファイルのパスを導き出す
		$javascriptPaths		= explode("-", $this->_realAction);
		$javascriptPath			= $this->_parentBasedir."/";													// 基本ディレクトリ
		for ($count = 0 ; $count < count($javascriptPaths) ; $count++)											// 区切り文字の数だけ文字列をパス結合していく
		{
			$javascriptPath		.= $javascriptPaths[$count].($count==count($javascriptPaths)-1?".js":"/");		// パス結合処理
		}

		// 以降の返却値JavaScriptとして返却する
		header("Content-type: text/javascript charset=utf-8");

		// ファイルの存在チェック
		if (file_exists($javascriptPath))
		{
			// ファイルが存在した場合は、そのファイルを読込んでJavaScriptとして返却する
			print(file_get_contents($javascriptPath));
		}
	}

	//--------------------------------------------------------------------------
	// FusionMain::doException
	//--------------------------------------------------------------------------
	// クライアントからサーバーサイドのExceptionを実施させるには、以下のパラメー
	// タからエラー情報を引き渡してくれれば、サーバーサイドでエラーが発生したよう
	// に振舞ってくれる
	// 
	// 
	//--------------------------------------------------------------------------
	private function doException()
	{
		// 
		$exceptionMessage		= array_key_exists("exceptionMessage"	, $_POST)?$_POST["exceptionMessage"]	:"";
		$exceptionErrStr		= array_key_exists("exceptionErrStr"	, $_POST)?$_POST["exceptionErrStr"]		:"";
		$exceptionErrFile		= array_key_exists("exceptionErrFile"	, $_POST)?$_POST["exceptionErrFile"]	:"";
		$exceptionErrLine		= array_key_exists("exceptionErrLine"	, $_POST)?$_POST["exceptionErrLine"]	:"";

		$this->_errors[]		= array('message' => $exceptionMessage, 'errstr' => $exceptionErrStr, 'errfile' => $exceptionErrFile, 'errline' => $exceptionErrLine);
		throw new Exception("doException");

		// 
		// throw new FusionException($exceptionMessage, $exceptionErrStr, $exceptionErrFile, $exceptionErrLine);
	}

	//--------------------------------------------------------------------------
	// FusionMain::getInstance
	//--------------------------------------------------------------------------
	// 現在の設定値に対して、適切なアクションクラスのインスタンスを生成し返却
	// する。
	// アクションクラスのインスタンスが生成できない場合はnullを返却する。
	// 
	// 
	//--------------------------------------------------------------------------
	private function getInstance()
	{
		$obj				= null;

		// インスタンスの生成
		switch ($this->_mode->valueOf())
		{
			// 通常の画面遷移による画面表示 or 画面内のイベントによるメソッドの起動(これは画面表示クラス内のメソッドを呼び出す為、インスタンスの生成は共通処理となる)
			case "ACTION":
			case "PROCESS":
					// 動的クラスファイルのinclude
					if (!file_exists($this->_config->getBasedirPath().$this->_className.".php")){ throw new Exception(""); }
					require_once $this->_config->getBasedirPath().$this->_className.".php";				// 該当インスタンスファイルの読込

					// 動的クラスインスタンスの生成
					$className		= substr($this->_className, strrpos($this->_className, "/") + strlen("/"));
					if (!class_exists($className)){ throw new Exception(""); }
					$obj			= new $className($this);

				break;

			// ダイアログ
			case "DIALOG":
					// 動的クラスファイルのinclude
					if (!file_exists($this->_config->getBasedirPath().$this->_className.".php")){ throw new Exception(""); }
					require_once $this->_config->getBasedirPath().$this->_className.".php";				// 該当インスタンスファイルの読込

					// 動的クラスインスタンスの生成
					$className		= substr($this->_className, strrpos($this->_className, "/") + strlen("/"));
					if (!class_exists($className)){ throw new Exception(""); }
					$obj			= new $className($this);

				break;

			// サジェスト
			case "SUGGEST":
					// 動的クラスファイルのinclude
					if (!file_exists($this->_config->getBasedirPath().$this->_className.".php")){ throw new Exception(""); }
					require_once $this->_config->getBasedirPath().$this->_className.".php";				// 該当インスタンスファイルの読込

					// 動的クラスインスタンスの生成
					$className		= substr($this->_className, strrpos($this->_className, "/") + strlen("/"));
					if (!class_exists($className)){ throw new Exception(""); }
					$obj			= new $className($this);

				break;

			// ファイルのダウンロード
			case "DOWNLOAD":
					// 動的クラスファイルのinclude
					if (!file_exists($this->_config->getBasedirPath().$this->_className.".php")){ throw new Exception(""); }
					require_once $this->_config->getBasedirPath().$this->_className.".php";				// 該当インスタンスファイルの読込

					// 動的クラスインスタンスの生成
					$className		= substr($this->_className, strrpos($this->_className, "/") + strlen("/"));
					if (!class_exists($className)){ throw new Exception(""); }
					$obj			= new $className($this);

				break;

			// ファイルのアップロード
			case "UPLOAD":
					// 動的クラスファイルのinclude
					if (!file_exists($this->_config->getBasedirPath().$this->_className.".php")){ throw new Exception(""); }
					require_once $this->_config->getBasedirPath().$this->_className.".php";				// 該当インスタンスファイルの読込

					// 動的クラスインスタンスの生成
					$className		= substr($this->_className, strrpos($this->_className, "/") + strlen("/"));
					if (!class_exists($className)){ throw new Exception(""); }
					$obj			= new $className($this);

				break;

			// その他処理
			case "OTHER":
					// 動的クラスファイルのinclude
					if (!file_exists($this->_config->getBasedirPath().$this->_className.".php")){ throw new Exception(""); }
					require_once $this->_config->getBasedirPath().$this->_className.".php";				// 該当インスタンスファイルの読込

					// 動的クラスインスタンスの生成
					$className		= substr($this->_className, strrpos($this->_className, "/") + strlen("/"));
					if (!class_exists($className)){ throw new Exception(""); }
					$obj			= new $className($this);

				break;

			// 入力値チェック
			case "VALIDATE":
					// validateは実行するObjectは存在せず、FusionValidateに処理を委譲する
					return null;

				break;
		}

		// 作成したインスタンスを返却
		return $obj;
	}

	//--------------------------------------------------------------------------
	// FusionMain::getMode
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getMode()
	{
		return $this->_mode;
	}

	//--------------------------------------------------------------------------
	// FusionMain::getProcessingMode
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getProcessingMode()
	{
		return $this->_processingMode;
	}

	//--------------------------------------------------------------------------
	// FusionMain::getRequestUri
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getRequestUri()
	{
		return $this->_requestUri;
	}

	//--------------------------------------------------------------------------
	// FusionMain::getNowAction
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getNowAction()
	{
		return $this->_nowAction;
	}

	//--------------------------------------------------------------------------
	// FusionMain::getParentAction
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getParentAction()
	{
		return $this->_parentAction;
	}

	//--------------------------------------------------------------------------
	// FusionMain::getRealAction
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getRealAction()
	{
		return $this->_realAction;
	}

	//--------------------------------------------------------------------------
	// FusionMain::getConfig
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getConfig()
	{
		return $this->_config;
	}

	//--------------------------------------------------------------------------
	// FusionMain::getController
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getController()
	{
		return $this->_controller;
	}

	//--------------------------------------------------------------------------
	// FusionMain::putLog
	//--------------------------------------------------------------------------
	// ログの出力を行う
	// 直接このメソッドには触れず、ラップメソッドを利用してログを出力すること
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	private function putLog($logLevel, $logMessage)
	{
		try
		{
			// 設定ファイルで必要な情報が設定されていること
			if ($this->_config->getLogDir() == "" || $this->_config->getLogFilename() == "" || $this->_config->getLogMaxsize() == "" || $this->_config->getLogLevel() == "" || $this->_config->getLogFormat() == "")
			{
				return;
			}

			// 設定されているログレベル以上であれば以下のログ出力処理を実施する
			$isLogOutput		= false;
			if ($this->_config->getLogLevel() == "TRACE")	{ if($logLevel=="TRACE"	|| $logLevel=="DEBUG"	|| $logLevel=="INFO"	|| $logLevel=="WARN"	|| $logLevel=="ERROR"	|| $logLevel=="FATAL"){ $isLogOutput = true; } }
			if ($this->_config->getLogLevel() == "DEBUG")	{ if($logLevel=="DEBUG"	|| $logLevel=="INFO"	|| $logLevel=="WARN"	|| $logLevel=="ERROR"	|| $logLevel=="FATAL"){ $isLogOutput = true; } }
			if ($this->_config->getLogLevel() == "INFO")	{ if($logLevel=="INFO"	|| $logLevel=="WARN"	|| $logLevel=="ERROR"	|| $logLevel=="FATAL"){ $isLogOutput = true; } }
			if ($this->_config->getLogLevel() == "WARN")	{ if($logLevel=="WARN"	|| $logLevel=="ERROR"	|| $logLevel=="FATAL"){ $isLogOutput = true; } }
			if ($this->_config->getLogLevel() == "ERROR")	{ if($logLevel=="ERROR" || $logLevel=="FATAL"){ $isLogOutput = true; } }
			if ($this->_config->getLogLevel() == "FATAL")	{ if($logLevel=="FATAL"){ $isLogOutput = true; } }
			if ($isLogOutput == false){ return; }

			// ファイルの存在チェック
			if (file_exists($this->_config->getLogDir().$this->_config->getLogFilename()))
			{
				// ファイルサイズチェック
				if ( intval(filesize($this->_config->getLogDir().$this->_config->getLogFilename())) > intval($this->_config->getLogMaxsize()))
				{
					// 現在のログファイルを移動する
					copy($this->_config->getLogDir().$this->_config->getLogFilename(), $this->_config->getLogDir().$this->_config->getLogFilename()."_".date("YmdHis"));
					unlink($this->_config->getLogDir().$this->_config->getLogFilename());
				}
			}

			// 対象ファイルを書き込みモードでオープンする
			$fp = fopen($this->_config->getLogDir().$this->_config->getLogFilename(), "a");

			// ログフォーマットの作成
			$log	= $this->_config->getLogFormat()."\n";
			$log	= str_replace("@date@", date("Y/m/d"), $log);						// 日付の置換
			$log	= str_replace("@time@", date("H:i:s"), $log);						// 時刻の置換
			$log	= str_replace("@client@", $_SERVER["REMOTE_ADDR"], $log);			// クライアントIPアドレスの置換
			$log	= str_replace("@logLevel@", $logLevel, $log);						// ログレベル@
			$log	= str_replace("@logMessage@", $logMessage, $log);					// ログメッセージの置換

			// ログ出力
			fwrite($fp, $log);

			// ファイルクローズ
			fclose($fp);
		}
		catch(Exception $exp)
		{
			// ログなのでエラーが発生しても無視
		}
	}

	//--------------------------------------------------------------------------
	// FusionMain::trace
	//--------------------------------------------------------------------------
	// トレース情報の出力
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function trace($logMessage)
	{
		$this->putLog("TRACE", $logMessage);
	}

	//--------------------------------------------------------------------------
	// FusionMain::debug
	//--------------------------------------------------------------------------
	// デバッグ情報の出力
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function debug($logMessage)
	{
		$this->putLog("DEBUG", $logMessage);
	}

	//--------------------------------------------------------------------------
	// FusionMain::info
	//--------------------------------------------------------------------------
	// インフォメーション情報の出力
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function info($logMessage)
	{
		$this->putLog("INFO", $logMessage);
	}

	//--------------------------------------------------------------------------
	// FusionMain::warn
	//--------------------------------------------------------------------------
	// ワーニング情報の出力
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function warn($logMessage)
	{
		$this->putLog("WARN", $logMessage);
	}

	//--------------------------------------------------------------------------
	// FusionMain::error
	//--------------------------------------------------------------------------
	// エラー情報の出力
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function error($logMessage)
	{
		$this->putLog("ERROR", $logMessage);
	}

	//--------------------------------------------------------------------------
	// FusionMain::fatal
	//--------------------------------------------------------------------------
	// 致命的エラー情報の出力
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function fatal($logMessage)
	{
		$this->putLog("FATAL", $logMessage);
	}
}
?>
