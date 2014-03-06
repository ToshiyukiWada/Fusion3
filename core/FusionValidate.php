<?php
//==============================================================================
// FusionValidate
//------------------------------------------------------------------------------
// 入力値チェッククラス
// 
// 
// 
// 
//==============================================================================
class FusionValidate
{
	private $_parent;
	private $_action;
	private $_process;
	private $_validateXml;

	private $_inputLabels;

	//--------------------------------------------------------------------------
	// FusionValidate::__construct
	//--------------------------------------------------------------------------
	// FusionValidateコンストラクタ
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function __construct($parent, $action, $process, $validateXmlPath)
	{
		$this->_parent		= $parent;
		$this->_action		= $action;
		$this->_process		= $process;
		$this->_validateXml	= simplexml_load_file($validateXmlPath);

		$this->_inputLabels				= array();
		$this->_inputLabels["is"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// アイスランド語:
		$this->_inputLabels["ga"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// アイルランド語:
		$this->_inputLabels["af"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// アフリカーンス語:
		$this->_inputLabels["sq"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// アルバニア語:
		$this->_inputLabels["it"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// イタリア語:
		$this->_inputLabels["id"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// インドネシア語
		$this->_inputLabels["uk"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// ウクライナ語
		$this->_inputLabels["nl"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// オランダ語
		$this->_inputLabels["nl-BE"]	= array('INPUT' => 'input', 'SELECT' => 'select');		// ランダ語/ベルギー語
		$this->_inputLabels["ca"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// カタロニア語
		$this->_inputLabels["gl"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// ガリチア語
		$this->_inputLabels["el"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// ギリシア語
		$this->_inputLabels["hr"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// クロアチア語
		$this->_inputLabels["sv"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// スウェーデン語
		$this->_inputLabels["gd"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// スコッチ:ゲール語
		$this->_inputLabels["es"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// スペイン語
		$this->_inputLabels["es-AR"]	= array('INPUT' => 'input', 'SELECT' => 'select');		// ペイン語/アルゼンチン
		$this->_inputLabels["es-CO"]	= array('INPUT' => 'input', 'SELECT' => 'select');		// ペイン語/コロンビア
		$this->_inputLabels["es-ES"]	= array('INPUT' => 'input', 'SELECT' => 'select');		// ペイン語/スペイン
		$this->_inputLabels["es-MX"]	= array('INPUT' => 'input', 'SELECT' => 'select');		// ペイン語/メキシコ
		$this->_inputLabels["sk"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// スロヴァキア語
		$this->_inputLabels["sl"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// スロヴェニア語
		$this->_inputLabels["sr"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// セルビア語
		$this->_inputLabels["cs"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// チェコ語
		$this->_inputLabels["da"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// デンマーク語
		$this->_inputLabels["de"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// ドイツ語
		$this->_inputLabels["de-AU"]	= array('INPUT' => 'input', 'SELECT' => 'select');		// イツ語/オーストリア
		$this->_inputLabels["de-CH"]	= array('INPUT' => 'input', 'SELECT' => 'select');		// イツ語/スイス
		$this->_inputLabels["de-DE"]	= array('INPUT' => 'input', 'SELECT' => 'select');		// イツ語/ドイツ
		$this->_inputLabels["tr"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// トルコ語
		$this->_inputLabels["no"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// ノルウェー語
		$this->_inputLabels["eu"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// バスク語
		$this->_inputLabels["hu"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// ハンガリー語
		$this->_inputLabels["fi"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// フィンランド語
		$this->_inputLabels["fo"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// フェロー語
		$this->_inputLabels["fr"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// フランス語
		$this->_inputLabels["fr-CA"]	= array('INPUT' => 'input', 'SELECT' => 'select');		// ランス語/カナダ
		$this->_inputLabels["fr-CH"]	= array('INPUT' => 'input', 'SELECT' => 'select');		// ランス語/スイス
		$this->_inputLabels["fr-FR"]	= array('INPUT' => 'input', 'SELECT' => 'select');		// ランス語/フランス
		$this->_inputLabels["fr-BE"]	= array('INPUT' => 'input', 'SELECT' => 'select');		// ランス語/ベルギー
		$this->_inputLabels["bg"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// ブルアリア語
		$this->_inputLabels["pl"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// ポーランド語
		$this->_inputLabels["pt"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// ポルトガル語
		$this->_inputLabels["pt-BR"]	= array('INPUT' => 'input', 'SELECT' => 'select');		// ルトガル語/ブラジル
		$this->_inputLabels["mk"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// マケドニア語
		$this->_inputLabels["ro"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// ルーマニア語
		$this->_inputLabels["ru"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// ロシア語
		$this->_inputLabels["en"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// 英語
		$this->_inputLabels["en-GB"]	= array('INPUT' => 'input', 'SELECT' => 'select');		// 語/英国
		$this->_inputLabels["en-US"]	= array('INPUT' => 'input', 'SELECT' => 'select');		// 語/米国
		$this->_inputLabels["ko"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// 韓国語
		$this->_inputLabels["zh"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// 中国語
		$this->_inputLabels["zh-TW"]	= array('INPUT' => 'input', 'SELECT' => 'select');		// 国語/台湾
		$this->_inputLabels["zh-CN"]	= array('INPUT' => 'input', 'SELECT' => 'select');		// 国語/中国
		$this->_inputLabels["ja"]		= array('INPUT' => '入力' , 'SELECT' => '選択'  );		// 日本語
		$this->_inputLabels["be"]		= array('INPUT' => 'input', 'SELECT' => 'select');		// 白ロシア語
	}

	//--------------------------------------------------------------------------
	// FusionValidate::validate
	//--------------------------------------------------------------------------
	// 入力値チェック処理
	// 
	// 
	// 
	// 
	//--------------------------------------------------------------------------
	public function validate()
	{
		// 戻り値定義
		$fusionValidateResult			= new FusionValidateResult($this->_action, $this->_process);

		// 置換文字の設定
		$replaceInformation				= array();
		foreach($_POST as $key=>$val){ $replaceInformation[$key] = $val; }			// リクエストの値を保持しておく
		foreach($_GET  as $key=>$val){ $replaceInformation[$key] = $val; }			// リクエストの値を保持しておく

		// 入力値チェックXMLの解析
		for ($countProcess = 0 ; $countProcess < count($this->_validateXml->process) ; $countProcess++)
		{
			// 対象メソッドであるかどうかをチェックする
			$process			= $this->_validateXml->process[$countProcess];
			$processMethod		= $process["method"]."";
			if (strtoupper($processMethod) == strtoupper($this->_process))
			{
				// 変数一覧の取得
				for ($countVariable = 0 ; $countVariable < count($process->variable) ; $countVariable++)
				{
					// 変数情報の取得
					$variable				= $process->variable[$countVariable];
					$variableName			= $variable["name"]."";
					$variableDisplayname	= $variable["displayname"]."";
					$variableType			= "INPUT";
					$variableReadOnly		= false;

					// 型情報の取得
					$variableTypeAndReadOnly	= "";
					if (array_key_exists($variableName."__type", $_POST)){ $variableTypeAndReadOnly = $_POST[$variableName."__type"]; }
					if (array_key_exists($variableName."__type", $_GET )){ $variableTypeAndReadOnly = $_GET [$variableName."__type"]; }
					if ($variableTypeAndReadOnly != "")
					{
						if (strpos($variableTypeAndReadOnly, "::") !== false)
						{
							$separatorIndex		= strpos($variableTypeAndReadOnly, "::");
							$tempType			= strtoupper(substr($variableTypeAndReadOnly, 0, $separatorIndex));
							$tempReadOnly		= strtoupper(substr($variableTypeAndReadOnly, $separatorIndex + strlen("::")));

							if ($tempType == "SELECT-ONE" || $tempType == "SELECT-MULTIPLE" || $tempType == "RADIO"){ $variableType = "SELECT"; }
							else																					{ $variableType = "INPUT";  }
						}
					}

					// 変数値の取得
					$value				= "";
					if (array_key_exists($variableName, $_POST)){ $value = $_POST[$variableName]; }
					if (array_key_exists($variableName, $_GET )){ $value = $_GET [$variableName]; }

					// インスタンスを生成
					$fusionValidateResultVariable		= new FusionValidateResultVariable($variableName, $variableDisplayname, $variableType, $value, false);

					// 入力値チェック一覧の取得
					for ($countValidate = 0 ; $countValidate < count($variable->validate) ; $countValidate++)
					{
						$validate			= $variable->validate[$countValidate];					// validateタグの取得
						$validateClass		= $validate["className"]."";							// classNameプロパティの取得
						$validateIf			= $validate["if"]."";									// ifプロパティの取得
						$validateType		= $validate["type"]."";									// typeプロパティの取得
						$validateMessage	= "";													// メッセージ格納エリアの定義

						// 定義値調整
						if ($validateType == ""){ $validateType = "ERROR"; }						// validateTypeは"ERROR"か"WARNING"しか許可しない
						if (strtoupper($validateType) == "WARNING")	{ $validateType = "WARNING"; }	// validateTypeは"ERROR"か"WARNING"しか許可しない
						else										{ $validateType = "ERROR"; }	// validateTypeは"ERROR"か"WARNING"しか許可しない

						// IF判定を実施(IF判定が存在している場合、その条件に合致しない場合は、この回のエラーチェックをすっ飛ばす)
						if ($validateIf != "")
						{
							// 構文解処理を作成する
							
							
							
							
							
						}

						// パラメータ収集
						$params				= array();
						for ($countParam = 0 ; $countParam < count($validate->param) ; $countParam++)
						{
							$param			= $validate->param[$countParam];
							$paramName		= $param["name"]."";
							$paramValue		= $param."";

							$params[$paramName]		= $paramValue;
						}

						// 入力値チェックの結果
						$validateResult			= false;
						$validateMessage		= "";

						// 入力値チェッククラスのインスタンスを生成して、チェック処理を実施する
						// [ユーザー定義側の入力値チェッククラスの存在チェック]
						$validateFilePath	= $this->_parent->getConfig()->getValidatePath().$validateClass.".php";
						if (!file_exists($validateFilePath))
						{
							// ユーザー定義入力値チェッククラスが見つからない場合はフレームワーク側の入力値チェックを利用する
							$validateFilePath	= $this->_parent->getParentBasedir()."/FUSION3/validate/".$validateClass.".php";
						}

						if (file_exists($validateFilePath))
						{
							// 指定された入力値チェッククラスのインスタンスを生成し、入力値チェック処理を起動する
							try
							{
								// 入力値チェッククラスのインスタンスを生成する
								require_once $validateFilePath;
								if (strrpos($validateClass, "/") !== false)
								{
									$validateClass		= substr($validateClass, strrpos(validateClass, "/") + strlen("/"));
								}
								if (!class_exists($validateClass)){ throw new Exception("validate class is not found(".$validateClass.")"); }
								$validateObj = new $validateClass($this->_parent);

								// 入力値チェックを実施する
								$validateObj->setParams($params);
								$validateObj->init();
								$validateResult		= $validateObj->main($value);
								$validateObj->release();

								// paramsの値もメッセージ置換対象とする
								$validateParams		= $validateObj->getParams();
								foreach($validateParams as $key=>$val){ $replaceInformation[$key] = $val; }

								// 入力値チェック
								if ($validateResult == false)
								{
									$input								= "";
									if (array_key_exists(getLanguage(), $this->_inputLabels))
									{
										if ($variableType == "INPUT" ){ $input		= $this->_inputLabels[getLanguage()]["INPUT"];  }
										if ($variableType == "SELECT"){ $input		= $this->_inputLabels[getLanguage()]["SELECT"]; }
									}
									else
									{
										// 対象言語が見つからない場合は、とりあえず英語で表示しておく
										if ($variableType == "INPUT" ){ $input		= "input";  }
										if ($variableType == "SELECT"){ $input		= "select"; }
									}

									$replaceInformation["NAME"]				= $variableName;
									$replaceInformation["DISPLAYNAME"]		= $variableDisplayname;
									$replaceInformation["INPUT"]			= $input;

									// メッセージの文字を置換して格納する
									$validateMessage		= replaceConfig($validateObj->getMessage(), "{", "}", $replaceInformation);
								}
							}
							catch(Exception $exp)
							{
								throw $exp;
							}
						}
						else
						{
							throw new Exception("validate class is not found(".$validateClass.")");
						}

						// インスタンスを生成し変数インスタンスにチェック結果を追記していく
						$fusionValidateResultVariable->addValidate(new FusionValidateResultValidate($validateClass, $validateType, $validateResult, $validateMessage));
					}

					// 結果に変数毎の結果を追加していく
					$fusionValidateResult->addVariable($fusionValidateResultVariable);
				}
			}
		}

		// 結果返却
		return $fusionValidateResult;
	}
}

//==============================================================================
// FusionValidateResult
//------------------------------------------------------------------------------
// 入力値チェック結果クラス
// 
// 
// 
// 
//==============================================================================
class FusionValidateResult
{
	public $_action;						// 文字列：対象ACTION
	public $_process;						// 文字列：対象PROCESS
	public $_variables;						// 配列　：変数毎の入力値チェック結果

	public function __construct($action, $process)
	{
		$this->_action						= $action;
		$this->_process						= $process;
		$this->_variables					= array();
	}

	public function addVariable($fusionValidateResultVariable)
	{
		$this->_variables[]					= $fusionValidateResultVariable;
	}
}

//==============================================================================
// FusionValidateResult
//------------------------------------------------------------------------------
// 入力値チェック結果：変数クラス
// 
// 
// 
// 
//==============================================================================
class FusionValidateResultVariable
{
	public $_name;							// 文字列：変数のname
	public $_displayname;					// 文字列：変数の日本語名
	public $_type;							// 文字列：入力値の型
	public $_value;							// 文字列：変数値
	public $_validates;						// 配列　：入力値チェッククラス名等の情報を配列で保持
	public $_isMultiMessage;				// 真偽　：同一変数内で同一のvalidateTypeのメッセージを重複して表示するか否か

	public function __construct($name, $displayname, $type, $value, $isMultiMessage)
	{
		$this->_name					= $name;
		$this->_displayname				= $displayname;
		$this->_type					= $type;
		$this->_value					= $value;
		$this->_validates				= array();
		$this->_isMultiMessage			= $isMultiMessage;
	}

	public function addValidate($fusionValidateResultValidate)
	{
		$this->_validate[]				= $fusionValidateResultValidate;
	}
}

//==============================================================================
// FusionValidateResult
//------------------------------------------------------------------------------
// 入力値チェック結果：変数：チェック結果クラス
// 
// 
// 
// 
//==============================================================================
class FusionValidateResultValidate
{
	public $_validateClass;					// 文字列：入力値チェッククラス名
	public $_validateType;					// 文字列：入力値がエラー扱い(ERROR)か警告扱い(WARNING)か
	public $_result;						// 真偽　：入力値チェックの結果
	public $_messge;						// 文字列：入力値チェック結果のメッセージ

	public function __construct($validateClass, $validateType, $result, $message)
	{
		$this->_validateClass			= $validateClass;
		$this->_validateType			= $validateType;
		$this->_result					= $result;
		$this->_message					= $message;
	}
}

?>
