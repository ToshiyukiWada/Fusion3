<?php
//==============================================================================
// controllerファイルの読み込みと管理
//------------------------------------------------------------------------------
// 
// 
// 
// 
// 
// 
// groupで指定した要素と、各Actionで定義した要素の関係について
// group指定がある場合は根底にはgroupで指定された要素が効果を発揮する。
// ただし、groupで定義された内容をそのまま継承するのか、取消しするのか？を判断す
// る必要があるので、空文字とNULLで切り分けることとする。
// そもそもgroup定義が存在していない場合は、各actionでの定義をそのまま利用するの
// で問題ない。
// 
//==============================================================================
class FusionController
{
	// 
	private $_initPath;								// 必ず通過する初期化処理が記述されたパス

	private $_common;								// 共通定義
	private $_groups;								// グループ定義

	private $_errorViews;							// エラー時に表示される画面
	private $_actions;								// 動的ページ
	private $_dialogs;								// ダイアログ
	private $_downloads;							// ダウンロード
	private $_suggests;								// サジェスト
	private $_uploads;								// アップロード
	private $_others;								// その他

	//--------------------------------------------------------------------------
	// FusionController::__construct
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function __construct()
	{
		$this->_initPath				= "";									// 必ず通過する初期化処理が記述されたパス

		$this->_common					= new FusionControllerGroup("_common");	// 共通定義
		$this->_groups					= array();								// グループ定義

		$this->_errorViews				= array();								// エラー時に表示される画面
		$this->_actions					= array();								// 動的ページ
		$this->_dialogs					= array();								// ダイアログ
		$this->_downloads				= array();								// ダウンロード
		$this->_suggests				= array();								// サジェスト
		$this->_uploads					= array();								// アップロード
		$this->_others					= array();								// その他
	}

	//--------------------------------------------------------------------------
	// FusionController::__construct
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getInitPath()
	{
		return ($this->_initPath);
	}

	//--------------------------------------------------------------------------
	// FusionController::getCommon
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getCommon()
	{
		return ($this->_common);
	}

	//--------------------------------------------------------------------------
	// FusionController::getGroups
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getGroups()
	{
		return ($this->_groups);
	}

	//--------------------------------------------------------------------------
	// FusionController::getErrorViews
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getErrorViews()
	{
		return ($this->_errorViews);
	}

	//--------------------------------------------------------------------------
	// FusionController::getActions
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getActions()
	{
		return ($this->_actions);
	}

	//--------------------------------------------------------------------------
	// FusionController::getDialogs
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
	// FusionController::getDownloads
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getDownloads()
	{
		return ($this->_downloads);
	}

	//--------------------------------------------------------------------------
	// FusionController::getSuggests
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getSuggests()
	{
		return ($this->_suggests);
	}

	//--------------------------------------------------------------------------
	// FusionController::getUploads
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getUploads()
	{
		return ($this->_uploads);
	}

	//--------------------------------------------------------------------------
	// FusionController::getOthers
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function getOthers()
	{
		return ($this->_others);
	}

	//--------------------------------------------------------------------------
	// FusionController::read
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function read()
	{
		// 結果返却変数定義
		$result								= true;

		// グローバル変数の読込
		global $FUSION_CONTROLLER_XML;

		// XML読込開始
		try
		{
			//------------------------------------------------------------------
			// config.xmlファイル読込
			//------------------------------------------------------------------
			$controllerXml					= simplexml_load_string($FUSION_CONTROLLER_XML);
			if ($controllerXml === false)
			{
				throw new Exception("failed read controller-xml");
			}

			//------------------------------------------------------------------
			// 置換宣言
			//------------------------------------------------------------------
			$replaceInformation					= array('LANGUAGE' => getLanguage());

			//------------------------------------------------------------------
			// XML読込
			//------------------------------------------------------------------
			$this->_initPath					= replaceConfig($controllerXml->init->path."", "{", "}", $replaceInformation);					// プロジェクト初期処理

			//------------------------------------------------------------------
			// 共通定義読込
			//------------------------------------------------------------------
			for ($count = 0 ; $count < count($controllerXml->common) ; $count++)
			{
				$common			= $controllerXml->common[$count];

				// 変数定義
				$doctype		= "";
				$htmltag		= "";
				$header			= "";
				$footer			= "";
				$javascript		= "";
				$stylesheet		= "";
				$authority		= "";

				// 読み込み
				if (property_exists($common, "doctype"))
				{
					$doctypeInfo	= $this->readDetailClient($common, "doctype");
					$doctype		= $doctypeInfo["common"];
					foreach($doctypeInfo["client"] as $key => $value){ $this->_common->addClientDoctype($key, $value); }
				}

				if (property_exists($common, "htmltag"))
				{
					$htmltagInfo	= $this->readDetailClient($common, "htmltag");
					$htmltag		= $htmltagInfo["common"];
					foreach($htmltagInfo["client"] as $key => $value){ $this->_common->addClientHtmltag($key, $value); }
				}

				if (property_exists($common, "header"))
				{
					$headerInfo		= $this->readDetailClient($common, "header");
					$header			= $headerInfo["common"];
					foreach($headerInfo["client"] as $key => $value){ $this->_common->addClientHeader($key, $value); }
				}

				if (property_exists($common, "footer"))
				{
					$footerInfo		= $this->readDetailClient($common, "footer");
					$footer			= $footerInfo["common"];
					foreach($footerInfo["client"] as $key => $value){ $this->_common->addClientFooter($key, $value); }
				}

				if (property_exists($common, "javascript"))
				{
					$javascriptInfo	= $this->readDetailClient($common, "javascript");
					$javascript		= $javascriptInfo["common"];
					foreach($javascriptInfo["client"] as $key => $value){ $this->_common->addClientJavascript($key, $value); }
				}

				if (property_exists($common, "stylesheet"))
				{
					$stylesheetInfo	= $this->readDetailClient($common, "stylesheet");
					$stylesheet		= $stylesheetInfo["common"];
					foreach($stylesheetInfo["client"] as $key => $value){ $this->_common->addClientStylesheet($key, $value); }
				}

				if (property_exists($common, "authority"))
				{
					$authorityInfo	= $this->readDetailClient($common, "authority");
					$authority		= $authorityInfo["common"];
					foreach($authorityInfo["client"] as $key => $value){ $this->_common->addClientAuthority($key, $value); }
				}

				// 設定
				$this->_common->setDoctype($doctype);
				$this->_common->setHtmltag($htmltag);
				$this->_common->setHeader($header);
				$this->_common->setFooter($footer);
				$this->_common->setJavascript($javascript);
				$this->_common->setStylesheet($stylesheet);
				$this->_common->setAuthority($authority);
			}

			//------------------------------------------------------------------
			// グループ定義読込
			//------------------------------------------------------------------
			for ($count = 0 ; $count < count($controllerXml->group) ; $count++)
			{
				$id				= strtoupper($controllerXml->group[$count]["id"]."");
				$group			= new FusionControllerGroup($id);

				$doctype		= "";
				$htmltag		= "";
				$header			= "";
				$footer			= "";
				$javascript		= "";
				$stylesheet		= "";
				$authority		= "";

				// 読み込み
				if (property_exists($controllerXml->group[$count], "doctype"))
				{
					$doctypeInfo	= $this->readDetailClient($controllerXml->group[$count], "doctype");
					$doctype		= $doctypeInfo["common"];
					foreach($doctypeInfo["client"] as $key => $value){ $this->_common->addClientDoctype($key, $value); }
				}

				if (property_exists($controllerXml->group[$count], "htmltag"))
				{
					$htmltagInfo	= $this->readDetailClient($controllerXml->group[$count], "htmltag");
					$htmltag		= $htmltagInfo["common"];
					foreach($htmltagInfo["client"] as $key => $value){ $this->_common->addClientHtmltag($key, $value); }
				}

				if (property_exists($controllerXml->group[$count], "header"))
				{
					$headerInfo		= $this->readDetailClient($controllerXml->group[$count], "header");
					$header			= $headerInfo["common"];
					foreach($headerInfo["client"] as $key => $value){ $this->_common->addClientHeader($key, $value); }
				}

				if (property_exists($controllerXml->group[$count], "footer"))
				{
					$footerInfo		= $this->readDetailClient($controllerXml->group[$count], "footer");
					$footer			= $footerInfo["common"];
					foreach($footerInfo["client"] as $key => $value){ $this->_common->addClientFooter($key, $value); }
				}

				if (property_exists($controllerXml->group[$count], "javascript"))
				{
					$javascriptInfo	= $this->readDetailClient($controllerXml->group[$count], "javascript");
					$javascript		= $javascriptInfo["common"];
					foreach($javascriptInfo["client"] as $key => $value){ $this->_common->addClientJavascript($key, $value); }
				}

				if (property_exists($controllerXml->group[$count], "stylesheet"))
				{
					$stylesheetInfo	= $this->readDetailClient($controllerXml->group[$count], "stylesheet");
					$stylesheet		= $stylesheetInfo["common"];
					foreach($stylesheetInfo["client"] as $key => $value){ $this->_common->addClientStylesheet($key, $value); }
				}

				if (property_exists($controllerXml->group[$count], "authority"))
				{
					$authorityInfo	= $this->readDetailClient($controllerXml->group[$count], "authority");
					$authority		= $authorityInfo["common"];
					foreach($authorityInfo["client"] as $key => $value){ $this->_common->addClientAuthority($key, $value); }
				}

				$group->setDoctype($doctype);
				$group->setHtmltag($htmltag);
				$group->setHeader($header);
				$group->setFooter($footer);
				$group->setJavascript($javascript);
				$group->setStylesheet($stylesheet);
				$group->setAuthority($authority);

				// 要素に追加する
				$this->_groups[$id]		= $group;
			}

			//------------------------------------------------------------------
			// 画面定義(Action)読込
			//------------------------------------------------------------------
			for ($count = 0 ; $count < count($controllerXml->action) ; $count++ )
			{
				// インスタンスを作成するのに必要な情報を先に取得する
				$id 		= strtoupper($controllerXml->action[$count]["id"]."");
				$className	= $controllerXml->action[$count]["className"]."";

				// インスタンスの生成
				$action			= new FusionControllerAction($id, $className);

				// 定義値の設定
				$action->setTitle($controllerXml->action[$count]["title"]."");
				$action->setGroup($controllerXml->action[$count]["group"]."");
				$action->setDoctype($controllerXml->action[$count]["doctype"]."");
				$action->setHtmltag($controllerXml->action[$count]["htmltag"]."");
				$action->setHeader($controllerXml->action[$count]["header"]."");
				$action->setFooter($controllerXml->action[$count]["footer"]."");
				$action->setJavascript($controllerXml->action[$count]["javascript"]."");
				$action->setStylesheet($controllerXml->action[$count]["stylesheet"]."");
				$action->setAuthority($controllerXml->action[$count]["authority"]."");

				// view
				for ($countView = 0 ; $countView < count($controllerXml->action[$count]->view) ; $countView++)
				{
					$return		= $controllerXml->action[$count]->view[$countView]["return"]."";
					$path		= $controllerXml->action[$count]->view[$countView]["path"]."";
					$client		= $controllerXml->action[$count]->view[$countView]["client"]."";

					$view		= new FusionControllerView($return, $path);
					$view->setStartup($controllerXml->action[$count]->view[$countView]["startup"]);

					if ($client == "")
					{
						$action->addView($return, $view, "");
					}
					else
					{
						$clients		= explode(",", $client);
						for ($countClient = 0 ; $countClient < count($clients) ; $countClient++)
						{
							if ($clients[$countClient] != "")
							{
								$action->addView($return, $view, $clients[$countClient]);
							}
						}
					}
				}

				// 要素に追加する
				$this->_actions[$id]		= $action;
			}

			//------------------------------------------------------------------
			// ダイアログ定義(dialog)読込
			//------------------------------------------------------------------
			for ($count = 0 ; $count < count($controllerXml->dialog) ; $count++ )
			{
				// インスタンスを生成するのに必要な情報を先に取得する
				$id			= strtoupper($controllerXml->dialog[$count]["id"]."");
				$dialogName	= $controllerXml->dialog[$count]["dialogName"]."";
				$className	= $controllerXml->dialog[$count]["className"]."";

				// インスタンスの生成
				$dialog			= new FusionControllerDialog($id, $dialogName, $className);

				// 定義値の設定
				$dialog->setTitle($controllerXml->dialog[$count]["title"]."");

				// Javascript
				$javascript		= $controllerXml->dialog[$count]["javascript"]."";
				$javascripts	= explode(",", $javascript);
				for ($countJavascript = 0 ; $countJavascript < count($javascripts) ; $countJavascript++)
				{
					if ($javascripts[$countJavascript] == ""){ continue; }
					$dialog->addJavascript($javascripts[$countJavascript]);
				}

				// Stylesheet
				$stylesheet		= $controllerXml->dialog[$count]["stylesheet"]."";
				$stylesheets	= explode(",", $stylesheet);
				for ($countStylesheet = 0 ; $countStylesheet < count($stylesheets) ; $countStylesheet++)
				{
					if ($stylesheets[$countStylesheet] == ""){ continue; }
					$dialog->addStylesheet($stylesheets[$countStylesheet]);
				}

				// authority
				$authority		= $controllerXml->dialog[$count]["authority"]."";
				$authorities	= explode(",", $authority);
				for ($countAuthority = 0 ; $countAuthority < count($authorities) ; $countAuthority++)
				{
					if ($authorities[$countAuthority] == ""){ continue; }
					$dialog->addAuthoritie($authorities[$countAuthority]);
				}
				// view
				for ($countView = 0 ; $countView < count($controllerXml->dialog[$count]->view) ; $countView++)
				{
					$return		= $controllerXml->dialog[$count]->view[$countView]["return"]."";
					$path		= $controllerXml->dialog[$count]->view[$countView]["path"]."";
					$client		= $controllerXml->dialog[$count]->view[$countView]["client"]."";

					$view		= new FusionControllerView($return, $path);
					$view->setStartup($controllerXml->dialog[$count]->view[$countView]["startup"]);

					if ($client == "")
					{
						$dialog->addView($return, $view, "");
					}
					else
					{
						$clients		= explode(",", $client);
						for ($countClient = 0 ; $countClient < count($clients) ; $countClient++)
						{
							if ($clients[$countClient] != "")
							{
								$dialog->addView($return, $view, $clients[$countClient]);
							}
						}
					}
				}

				// 要素に追加する
				$this->_dialogs[$id]		= $dialog;
			}

			//------------------------------------------------------------------
			// ダウンロード処理(download)読込
			//------------------------------------------------------------------
			for ($count = 0 ; $count < count($controllerXml->download) ; $count++)
			{
				// インスタンスを生成するのに必要な情報を先に取得する
				$id			= strtoupper($controllerXml->download[$count]["id"]."");
				$className	= $controllerXml->download[$count]["className"]."";

				// インスタンスの生成
				$donwload		= new FusionControllerDownload($id, $className);

				// authority
				$authority		= $controllerXml->donwload[$count]["authority"]."";
				$authorities	= explode(",", $authority);
				for ($countAuthority = 0 ; $countAuthority < count($authorities) ; $countAuthority++)
				{
					if ($authorities[$countAuthority] == ""){ continue; }
					$donwload->addAuthoritie($authorities[$countAuthority]);
				}
				$this->_downloads[$id]		= $download;
			}

			//------------------------------------------------------------------
			// サジェスト処理(suggest)読込
			//------------------------------------------------------------------
			for ($count = 0 ; $count < count($controllerXml->suggest) ; $count++)
			{
				// インスタンスを生成するのに必要な情報を先に取得する
				$id			= strtoupper($controllerXml->suggest[$count]["id"]."");
				$className	= $controllerXml->suggest[$count]["className"]."";

				// インスタンスの生成
				$suggest		= new FusionControllerSuggest($id, $className);

				// authority
				$authority		= $controllerXml->suggest[$count]["authority"]."";
				$authorities	= explode(",", $authority);
				for ($countAuthority = 0 ; $countAuthority < count($authorities) ; $countAuthority++)
				{
					if ($authorities[$countAuthority] == ""){ continue; }
					$suggest->addAuthoritie($authorities[$countAuthority]);
				}
				$this->_suggests[$id]		= $suggest;
			}

			//------------------------------------------------------------------
			// アップロード処理(upload)読込
			//------------------------------------------------------------------
			for ($count = 0 ; $count < count($controllerXml->upload) ; $count++)
			{
				// インスタンスを生成するのに必要な情報を先に取得する
				$id			= strtoupper($controllerXml->upload[$count]["id"]."");
				$className	= $controllerXml->upload[$count]["className"]."";

				// インスタンスの生成
				$upload		= new FusionControllerUpload($id, $className);

				// authority
				$authority		= $controllerXml->upload[$count]["authority"]."";
				$authorities	= explode(",", $authority);
				for ($countAuthority = 0 ; $countAuthority < count($authorities) ; $countAuthority++)
				{
					if ($authorities[$countAuthority] == ""){ continue; }
					$upload->addAuthoritie($authorities[$countAuthority]);
				}
				$this->_uploads[$id]		= $upload;
			}

			//------------------------------------------------------------------
			// その他処理(other)読込
			//------------------------------------------------------------------
			for ($count = 0 ; $count < count($controllerXml->other) ; $count++)
			{
				// インスタンスを生成するのに必要な情報を先に取得する
				$id			= strtoupper($controllerXml->other[$count]["id"]."");
				$className	= $controllerXml->other[$count]["className"]."";

				// インスタンスの生成
				$other		= new FusionControllerOther($id, $className);

				// authority
				$authority		= $controllerXml->poother[$count]["authority"]."";
				$authorities	= explode(",", $authority);
				for ($countAuthority = 0 ; $countAuthority < count($authorities) ; $countAuthority++)
				{
					if ($authorities[$countAuthority] == ""){ continue; }
					$other->addAuthoritie($authorities[$countAuthority]);
				}
				$this->_others[$id]			= $other;
			}
		}
		catch(Exception $exp)
		{
			$result							= false;
		}

		// 結果返却
		return $result;
	}

	//--------------------------------------------------------------------------
	// FusionController::readDetailClient
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function readDetailClient($element, $tagName)
	{
		$commonValue		= "";
		$clientValues		= array();

		for ($countTag = 0 ; $countTag < count($element->{$tagName}) ; $countTag++)
		{
			$tempClient		= $element->{$tagName}[$countTag];
			if(isset($tempClient["client"]))
			{
				$client		= $tempClient["client"]."";
				$clients	= explode(",", $client);
				for($countClient=0;$countClient<count($clients);$countClient++)
				{
					$clientValues[$clients[$countClient]]		= $tempClient."";
				}
			}
			else
			{
				$commonValue		= $tempClient."";
			}
		}

		// 結果返却
		return array("common" => $commonValue				// 共通設定の返却
					,"client" => $clientValues);			// クライアント設定の返却
	}
}

//==============================================================================
// FusionControllerGroup
//------------------------------------------------------------------------------
// グループ定義
// 
// 
// 
// 
//==============================================================================
class FusionControllerGroup
{
	private $_id;											// グループID
	private $_doctype;										// DOCTYPE
	private $_htmltag;										// HTMLタグ
	private $_header;										// ヘッダー
	private $_footer;										// フッター
	private $_javascript;									// 読み込むJavaScript
	private $_stylesheet;									// 読み込むStylesheet
	private $_authority;									// 読み込む認証クラス

	// 以下client対応
	private $_clientDoctype;								// DOCTYPE(clientプロパティ対応)
	private $_clientHtmltag;								// HTMLタグ(clientプロパティ対応)
	private $_clientHeader;									// ヘッダー(clientプロパティ対応)
	private $_clientFooter;									// フッター(clientプロパティ対応)
	private $_clientJavascript;								// 読み込むJavaScript(clientプロパティ対応)
	private $_clientStylesheet;								// 読み込むStylesheet(clientプロパティ対応)
	private $_clientAuthority;								// 読み込む認証クラス(clientプロパティ対応)

	//--------------------------------------------------------------------------
	// FusionControllerGroup::__construct
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function __construct($id)
	{
		$this->_id					= $id;					// グループID
		$this->_doctype				= null;					// DOCTYPE
		$this->_htmltag				= null;					// HTMLタグ
		$this->_header				= null;					// ヘッダー
		$this->_footer				= null;					// フッター
		$this->_javascript			= null;					// 読み込むJavaScript
		$this->_stylesheet			= null;					// 読み込むStylesheet
		$this->_authority			= null;					// 読み込む認証クラス

		$this->_clientDoctype		= array();				// DOCTYPE(clientプロパティ対応)
		$this->_clientHtmltag		= array();				// HTMLタグ(clientプロパティ対応)
		$this->_clientHeader		= array();				// ヘッダー(clientプロパティ対応)
		$this->_clientFooter		= array();				// フッター(clientプロパティ対応)
		$this->_clientJavascript	= array();				// 読み込むJavaScript(clientプロパティ対応)
		$this->_clientStylesheet	= array();				// 読み込むStylesheet(clientプロパティ対応)
		$this->_clientAuthority		= array();				// 読み込む認証クラス(clientプロパティ対応)
	}

	// id
	public function getId(){ return ($this->_id); }

	// doctype
	public function setDoctype($value){ $this->_doctype = $value; }
	public function getDoctype(){ return ($this->_doctype); }
	public function addClientDoctype($client, $value){ $this->_clientDoctype[strtoupper($client)] = $value; }
	public function getClientDoctype($client){ return array_key_exists(strtoupper($client), $this->_clientDoctype)?$this->_clientDoctype[strtoupper($client)]:null; }

	// htmltag
	public function setHtmltag($value){ $this->_htmltag = $value; }
	public function getHtmltag(){ return ($this->_htmltag); }
	public function addClientHtmltag($client, $value){ $this->_clientHtmltag[strtoupper($client)] = $value; }
	public function getClientHtmltag($client){ return array_key_exists(strtoupper($client), $this->_clientHtmltag)?$this->_clientHtmltag[strtoupper($client)]:null; }

	// header
	public function setHeader($value){ $this->_header = $value; }
	public function getHeader(){ return ($this->_header); }
	public function addClientHeader($client, $value){ $this->_clientHeader[strtoupper($client)] = $value; }
	public function getClientHeader($client){ return array_key_exists(strtoupper($client), $this->_clientHeader)?$this->_clientHeader[strtoupper($client)]:null; }

	// footer
	public function setFooter($value){ $this->_footer = $value; }
	public function getFooter(){ return ($this->_footer); }
	public function addClientFooter($client, $value){ $this->_clientFooter[strtoupper($client)] = $value; }
	public function getClientFooter($client){ return array_key_exists(strtoupper($client), $this->_clientFooter)?$this->_clientFooter[strtoupper($client)]:null; }

	// javascripts
	public function setJavascript($value){ $this->_javascript = $value;	}
	public function getJavascript(){ return ($this->_javascript);	}
	public function addClientJavascript($client, $value){ $this->_clientJavascript[strtoupper($client)] = $value; }
	public function getClientJavascript($client){ return array_key_exists(strtoupper($client), $this->_clientJavascript)?$this->_clientJavascript[strtoupper($client)]:null; }

	// stylesheets
	public function setStylesheet($value){ $this->_stylesheet = $value; }
	public function getStylesheet(){ return ( $this->_stylesheet ); }
	public function addClientStylesheet($client, $value){ $this->_clientStylesheet[strtoupper($client)] = $value; }
	public function getClientStylesheet($client){ return array_key_exists(strtoupper($client), $this->_clientStylesheet)?$this->_clientStylesheet[strtoupper($client)]:null; }

	// authority
	public function setAuthority($value){ $this->_authority = $value ; }
	public function getAuthority(){ return ($this->_authority ) ; }
	public function addClientAuthority($client, $value){ $this->_clientAuthority[strtoupper($client)] = $value; }
	public function getClientAuthority($client){ return array_key_exists(strtoupper($client), $this->_clientAuthority)?$this->_clientAuthority[strtoupper($client)]:null; }
}

//==============================================================================
// FusionControllerView(clientプロパティ対応)
//------------------------------------------------------------------------------
// 画面表示定義
// 
// 
// 
// 
//==============================================================================
class FusionControllerView
{
	private $_return;										// Action/Dialogの戻り値
	private $_path;											// 表示する画面のパス
	private $_startup;										// 画面表示後の初期処理

	//--------------------------------------------------------------------------
	// FusionControllerView::__construct
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function __construct($return, $path)
	{
		$this->_return			= $return;
		$this->_path			= $path;
		$this->_startup			= null;
	}

	// return
	public function getReturn()
	{
		return ($this->_return);
	}

	// path
	public function getPath()
	{
		return ($this->_path);
	}

	// startup
	public function setStartup($value)
	{
		$this->_startup	= $value;
	}
	public function getStartup()
	{
		return ($this->_startup);
	}
}

//==============================================================================
// FusionControllerAction
//------------------------------------------------------------------------------
// アクション定義
// 
// 
// 
// 
//==============================================================================
class FusionControllerAction
{
	private $_id;											// 画面ID
	private $_className;									// 画面処理クラス名
	private $_title;										// タイトル
	private $_group;										// グループ
	private $_doctype;										// DOCTYPE
	private $_htmltag;										// HTMLタグ
	private $_header;										// ヘッダー
	private $_footer;										// フッター
	private $_javascript;									// この画面で利用するJavaScript(複数可)
	private $_stylesheet;									// この画面で利用するStylesheet(複数可)
	private $_authority;									// この画面で利用する認証クラス(複数可)
	private $_views;										// 画面

	//--------------------------------------------------------------------------
	// FusionControllerAction::__construct
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function __construct($id, $className)
	{
		$this->_id					= $id;					// 画面ID
		$this->_className			= $className;			// 画面処理クラス名
		$this->_title				= null;					// タイトル
		$this->_group				= null;					// グループ
		$this->_doctype				= null;					// DOCTYPE
		$this->_htmltag				= null;					// HTMLタグ
		$this->_header				= null;					// ヘッダー
		$this->_footer				= null;					// フッター
		$this->_javascript			= null;					// この画面で利用するJavaScript(複数可)
		$this->_stylesheet			= null;					// この画面で利用するStylesheet(複数可)
		$this->_authority			= null;					// この画面で利用する認証クラス(複数可)
		$this->_views				= array();				// 画面(nullではなく、インスタンスを生成)
	}

	// id
	public function getId(){ return ($this->_id); }

	// className
	public function getClassName(){ return ($this->_className); }

	// title
	public function setTitle($value){ $this->_title = $value; }
	public function getTitle(){ return($this->_title); }

	// group
	public function setGroup($value){ $this->_group = $value; }
	public function getGroup(){ return($this->_group); }

	// doctype
	public function setDoctype($value){ $this->_doctype = $value; }
	public function getDoctype(){ return ($this->_doctype); }

	// htmltag
	public function setHtmltag($value){ $this->_htmltag = $value; }
	public function getHtmltag(){ return ($this->_htmltag); }

	// header
	public function setHeader($value){ $this->_header = $value; }
	public function getHeader(){ return ($this->_header); }

	// footer
	public function setFooter($value){ $this->_footer = $value; }
	public function getFooter(){ return ($this->_footer); }

	// javascripts
	public function setJavascript($value){ $this->_javascript = $value; }
	public function getJavascript(){ return($this->_javascript); }

	// stylesheets
	public function setStylesheet($value){ $this->_stylesheet = $value; }
	public function getStylesheet(){ return($this->_stylesheet); }

	// authrity
	public function setAuthority($value){ $this->_authority = $value; }
	public function getAuthority(){ return($this->_authority); }

	// views
	public function addView($return, $value, $client)
	{
		if (!array_key_exists($return, $this->_views))
		{
			$this->_views[$return]		= array("common" => "", "client" => array());
		}

		$view					= $this->_views[$return];
		if ($client == "")	{ $view["common"]			= $value; }
		else				{ $view["client"][$client]	= $value; }
		$this->_views[$return]	= $view;
	}
	public function getViews(){ return($this->_views); }
}

//==============================================================================
// FusionControllerDialog
//------------------------------------------------------------------------------
// ダイアログ定義
// 
// 
// 
// 
//==============================================================================
class FusionControllerDialog
{
	private $_id;									// 画面ID
	private $_dialogName;							// 適用するダイアログデザイン
	private $_className;							// 画面処理クラス名
	private $_title;								// タイトル
	private $_javascripts;							// この画面で利用するJavaScript(複数可)
	private $_stylesheets;							// この画面で利用するStylesheet(複数可)
	private $_authorities;							// この画面で利用する認証クラス(複数可)
	private $_views;								// 画面

	//--------------------------------------------------------------------------
	// FusionControllerDialog::__construct
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function __construct($id, $dialogName, $className)
	{
		$this->_id			= $id;					// 画面ID
		$this->_dialogName	= $dialogName;			// 適用するダイアログデザイン
		$this->_className	= $className;			// 画面処理クラス名
		$this->_title		= null;					// タイトル
		$this->_javascripts	= null;					// この画面で利用するJavaScript(複数可)
		$this->_stylesheets	= null;					// この画面で利用するStylesheet(複数可)
		$this->_authorities	= null;					// この画面で利用する認証クラス(複数可)
		$this->_views		= array();				// 画面(nullではなく、インスタンスを生成)
	}

	// id
	public function getId(){ return ($this->_id); }

	// dialogName
	public function getDialogName(){ return ($this->_dialogName); }

	// className
	public function getClassName(){ return ($this->_className); }

	// title
	public function setTitle($value){ $this->_title = $value; }
	public function getTitle(){ return($this->_title); }

	// javascripts
	public function addJavascripts($value)
	{
		if ($this->_javascripts == null){ $this->_javascripts = array(); }
		$this->_javascripts[] = $value;
	}
	public function getJavascripts(){ return($this->_javascripts); }

	// stylesheets
	public function addStylesheets($value)
	{
		if ($this->_stylesheets == null){ $this->_stylesheets = array(); }
		$this->_stylesheets[] = $value;
	}
	public function getStylesheets(){ return($this->_stylesheets); }

	// authoritues
	public function addAuthoritues($value)
	{
		if ($this->_authorities == null){ $this->_authorities = array(); }
		$this->_authorities[] = $value;
	}
	public function getAuthoritues(){ return($this->_authorities); }

	// views
	public function addView($return, $value, $client)
	{
		if (!array_key_exists($return, $this->_views))
		{
			$this->_views[$return]		= array("common" => "", "client" => array());
		}

		$view					= $this->_views[$return];
		if ($client == "")	{ $view["common"]			= $value; }
		else				{ $view["client"][$client]	= $value; }
		$this->_views[$return]	= $view;
	}
	public function getViews(){ return($this->_views); }
}

//==============================================================================
// FusionControllerDonwload
//------------------------------------------------------------------------------
// ダウンロード定義
// 
// 
// 
// 
//==============================================================================
class FusionControllerDonwload
{
	// member
	private $_id;									// 処理ID
	private $_className;							// 処理クラス名
	private $_authorities;							// この処理で利用する認証クラス(複数可)

	//--------------------------------------------------------------------------
	// FusionControllerDonwload::__construct
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function __construct($id, $className)
	{
		$this->id			= $id;
		$this->_className	= $className;
	}

	// id
	public function getId(){ return ($this->_id); }

	// className
	public function getClassName(){ return ($this->_className); }

	// authoritues
	public function addAuthoritues($value)
	{
		if ($this->_authorities == null){ $this->_authorities = array(); }
		$this->_authorities[] = $value;
	}
	public function getAuthoritues(){ return($this->_authorities); }
}

//==============================================================================
// FusionControllerSuggest
//------------------------------------------------------------------------------
// サジェスト定義
// 
// 
// 
// 
//==============================================================================
class FusionControllerSuggest
{
	// member
	private $_id;									// 処理ID
	private $_className;							// 処理クラス名
	private $_authorities;							// この処理で利用する認証クラス(複数可)

	//--------------------------------------------------------------------------
	// FusionControllerSuggest::__construct
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function __construct($id, $className)
	{
		$this->id			= $id;
		$this->_className	= $className;
	}

	// id
	public function getId(){ return ($this->_id); }

	// className
	public function getClassName(){ return ($this->_className); }

	// authoritues
	public function addAuthoritues($value)
	{
		if ($this->_authorities == null){ $this->_authorities = array(); }
		$this->_authorities[] = $value;
	}
	public function getAuthoritues(){ return($this->_authorities); }
}

//==============================================================================
// FusionControllerUpload
//------------------------------------------------------------------------------
// アップロード定義
// 
// 
// 
// 
//==============================================================================
class FusionControllerUpload
{
	// member
	private $_id;									// 処理ID
	private $_className;							// 処理クラス名
	private $_authorities;							// この処理で利用する認証クラス(複数可)

	//--------------------------------------------------------------------------
	// FusionControllerUpload::__construct
	//--------------------------------------------------------------------------
	// 
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function __construct($id, $className)
	{
		$this->id			= $id;
		$this->_className	= $className;
	}

	// id
	public function getId(){ return ($this->_id); }

	// className
	public function getClassName(){ return ($this->_className); }

	// authoritues
	public function addAuthoritues($value)
	{
		if ($this->_authorities == null){ $this->_authorities = array(); }
		$this->_authorities[] = $value;
	}
	public function getAuthoritues(){ return($this->_authorities); }
}
?>
