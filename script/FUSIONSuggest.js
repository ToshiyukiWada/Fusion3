//******************************************************************************
// FUSIONSuggest
//******************************************************************************
// サジェスト制御
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
var FUSIONSuggest = function(parentObj)
{
	this._parentObj					= parentObj;			// 親となるボディオブジェクト
	this._suggestInformations		= new Object();			// サジェスト処理の情報を保持しておく連想配列(キーは対象オブジェクトのID)
}
FUSIONSuggest.prototype = new FUSIONAjaxBase();

//------------------------------------------------------------------------------
// サジェスト情報を登録する
//------------------------------------------------------------------------------
// registSuggest関数からスルー処理でこちらの処理をキックする
// (よって、registSuggest関数と引数を一致させる必要がある)
// 
//------------------------------------------------------------------------------
FUSIONSuggest.prototype.suggest = function(id, targetObj, suggestFunction)
{
	// 対象オブジェクトにIDが設定されていない場合はサジェスト機能を登録することができない
	if (targetObj.id == undefined){ return; }
	if (targetObj.id == ""){ return; }

	// 起動するサジェストクラス名を登録
	this._suggestInformations[targetObj.id]		= {  id			: id						// サジェストクラスを一意に指定する為のID
													,function	: suggestFunction			// サジェスト結果を受け取って表示・制御部分を引き受ける関数(とりあえずデフォルト関数をこのソース内に用意することで、指定しなくてもとりあえず動作する)
													,uuid		: getUUID()					// このサジェスト表示を一意にする為のキー(同時にサジェストを表示するDIVタグのIDにもなる)
												  };

	// 各種イベントの登録
	appendEventListener(targetObj	, "focus"	, this.openSuggest	, false);
	appendEventListener(targetObj	, "keyup"	, this.openSuggest	, false);
	appendEventListener(targetObj	, "keydown"	, this.openSuggest	, false);
	appendEventListener(targetObj	, "blur"	, this.closeSuggest	, false);
}

//------------------------------------------------------------------------------
// 
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
FUSIONSuggest.prototype.openSuggest = function(event)
{
	var key					= event.target.id;						// 対象オブジェクトのIDを取得
	var suggestInformation	= suggest._suggestInformations[key];	// サジェスト情報の取得
	var suggestID			= suggestInformation["id"];				// 対象オブジェクトのIDからサジェストクラス名を取得
	var suggestFunction		= suggestInformation["function"];		// 
	var suggestUUID			= suggestInformation["uuid"];			// 

	// サジェスト制御関数が定義されていない場合は、このソース内にサンプルとして定義されているサジェスト制御関数を利用してでもサジェストを表示する
	if (suggestFunction == undefined)
	{
		suggestFunction		= defaultSuggest;
	}

	// 既にDIVによるサジェストが表示されている場合はそれを一旦削除する
	if (document.getElementById(suggestUUID) != null)
	{
		$(document.getElementById(suggestUUID)).remove();
	}

	// Ajaxリクエスト送信
	id = suggest.sendServer(this
	,"./suggest/" + suggestID
	,false
	,{suggestValue:event.target.value}
	,function(variables, contents, result)
	{
		// 表示用DIVオブジェクトの生成
		var frameDivObj		= document.createElement("div");
		frameDivObj.id		= suggestUUID;
		suggest._parentObj.appendChild(frameDivObj);

		suggestFunction(contents, document.getElementById(key), frameDivObj);
		return true;
	}
	,undefined);
}

//------------------------------------------------------------------------------
// 
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
FUSIONSuggest.prototype.closeSuggest = function(event)
{
	var key					= event.target.id;						// 対象オブジェクトのIDを取得
	var suggestInformation	= suggest._suggestInformations[key];	// サジェスト情報の取得
	var suggestUUID			= suggestInformation["uuid"];			// 

	// サジェスト表示エリアのDIVを削除する
	if (document.getElementById(suggestUUID) != null)
	{
		$(document.getElementById(suggestUUID)).remove();
	}
}

//------------------------------------------------------------------------------
// サジェスト情報を登録する
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
function registSuggest(id, targetObj, suggestFunction)
{
	if (suggest == null)
	{
		suggest = new FUSIONSuggest(document.getElementById("inputform"));
	}
	suggest.suggest(id, targetObj, suggestFunction);
}

//------------------------------------------------------------------------------
// サジェストの動作定義関数(デフォルト)
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
function defaultSuggest(candidates, targetObj, frameObj)
{
	// サジェスト表示
	var targetLeft				= $(targetObj).position().left;
	var targetTop				= $(targetObj).position().top;
	var targetWidth				= targetObj.offsetWidth;
	var targetHeight			= targetObj.offsetHeight;

	frameObj.style.position		= "absolute";
	frameObj.style.width		= targetWidth + "px";
	frameObj.style.height		= "200px";
	frameObj.style.left			= targetLeft + "px";
	frameObj.style.top			= targetTop + targetHeight + "px";

	// ここにはサジェストの候補単語が全て格納される
	var suggestTableObj						= document.createElement("table");
	suggestTableObj.style.width				= "100%";
	suggestTableObj.cellPadding				= 2;
	suggestTableObj.cellSpacing				= 2;
	for (var count = 0 ; count < candidates.length ; count++)
	{
		var suggestRowObj					= suggestTableObj.insertRow(-1);
		var suggestCellObj					= suggestRowObj.insertCell(-1);
		suggestCellObj.style.width			= "100%";
		suggestCellObj.style.border			= "1px solid #000000";
		suggestCellObj.innerHTML			= candidates[count];
		appendEventListener(suggestCellObj	, "click"	, function(){ alert(candidates[count]); }	, false);
	}
	frameObj.appendChild(suggestTableObj);
}

// フレームワーク中で利用する為に予約語"validate"を定義しておく
var suggest = null;
appendEventListener(window, "load", function(){ if (suggest == null){ suggest = new FUSIONSuggest(document.getElementById("inputform")); } }, false);
