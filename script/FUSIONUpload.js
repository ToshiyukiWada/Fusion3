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
var FUSIONUpload = function()
{
	this._results				= new Object();					// 各Process処理の結果が格納される(キーはID)
}
FUSIONUpload.prototype = new FUSIONAjaxBase();

//------------------------------------------------------------------------------
// 
//------------------------------------------------------------------------------
// プロセスハンドラの返却をしたい
// 
// 
//------------------------------------------------------------------------------
FUSIONUpload.prototype.upload = function(fileObj, uploadName, params, callbackFunction)
{
	// IDの生成
	id			= getUUID();

	// 入力フォームの値を集約
	var param	= $("#dummy").serializeArray();

	// フォームオブジェクトのsubmit無効化
	var formObj		= document.getElementById("inputform");
	formObj.setAttribute("onsubmit", "");

	// 呼び出し元オブジェクトの保持
	var invoker		= this;

	// 引数で与えられたパラメータを追加する(重複している場合は引数パラメータを優先する)
	if (params != undefined)
	{
		param		= addHash(param, params);
	}

	// IDの付与
	param.push({name: "__FUSION_AJAXID", value: id});

	// Ajaxリクエスト送信
	$(fileObj).upload(
		 "./upload/" + uploadName
		,param
		,function(request)
		 {
			// 戻り値解析
			invoker.parse(request, id);

			// 致命的なエラーが発生したか否か
			invoker.checkError(id);

			// コールバック関数の実行
			var result			= invoker.getResult(id);
			var variables		= result["variables"];
			var contents		= result["contents"];
		 	if (callbackFunction != undefined)
			{
				callbackFunction(variables, contents, result);
			}
		 }
		,"xml"
	);
	formObj.setAttribute("onsubmit", "return false;");
}

// フレームワーク中で利用する為に予約語"upload"を定義しておく
var upload = null;
appendEventListener(window, "load", function(){ upload = new FUSIONUpload(); }, false);
