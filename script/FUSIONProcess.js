//******************************************************************************
// FUSIONProcess
//******************************************************************************
// プロセス制御
// 
// 
// 
// 
// 
// 
// 
// 
// 
// 
//******************************************************************************
//------------------------------------------------------------------------------
// コンストラクタ
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
var FUSIONProcess = function()
{
	this._results				= new Object();					// 各Process処理の結果が格納される(キーはID)
}
FUSIONProcess.prototype = new FUSIONAjaxBase();

//------------------------------------------------------------------------------
// 
//------------------------------------------------------------------------------
// プロセスハンドラの返却をしたい
// 
// 
//------------------------------------------------------------------------------
FUSIONProcess.prototype.process = function(processName, params, callbackFunction, isAsync, isValidate, errorOutputFunction)
{
	// 必須でない引数の調整
	if (isAsync == undefined){ isAsync = false; }
	if (isValidate == undefined){ isValidate = false; }

	// IDの定義
	var id			= "";

	// プロセス起動
	if (isValidate == true)
	{
		// 入力値チェック起動後にプロセスを起動する
		validate.validate(processName, params, function()
		{
			// 入力値チェックが正常に終了した場合にコールされる処理としてプロセスを定義
			id = process.process(processName, params, callbackFunction, isAsync, false);
		}
		,errorOutputFunction);

		return id;
	}
	else
	{
		// Ajaxリクエスト送信
		id = this.sendServer(this
		,"./process/" + processName
		,isAsync
		,params
		,function(variables, contents, result)
		{
			// プロセス後にコールバック関数を呼び出す
			return true;
		}
		,callbackFunction);
	}

	return id;
}

// フレームワーク中で利用する為に予約語"process"を定義しておく
var process = null;
appendEventListener(window, "load", function(){ process = new FUSIONProcess(); }, false);
