//******************************************************************************
// FUSION
//******************************************************************************
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
// 
//******************************************************************************
//------------------------------------------------------------------------------
// イベントの追加
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
var appendEventListener = function (element, type, func, capture)
{ 
	var ret = null;
	if (element.addEventListener)
	{
		ret = func;
		element.addEventListener(type, func, capture);
	}
	else if (element.attachEvent)
	{
		ret = function ()
		{
			func.apply(element, [(element.document || element).parentWindow.event]);
		}
		element.attachEvent("on" + type, ret);
	}
	return ret;
}

//------------------------------------------------------------------------------
// イベントの削除
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
var deleteEventListener = function (element, type, func, capture)
{
	if (element.removeEventListener)
	{
		element.removeEventListener(type, func, capture);
	}
	else if (element.detachEvent)
	{
		element.detachEvent("on" + type, func);
	}
}

//------------------------------------------------------------------------------
// イベント伝播の停止
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
var stopPropagation = function (e)
{
	if (e.stopPropagation)
	{
		e.stopPropagation();
	}
	else
	{
		e.cancelBubble = true;
	}
}

//------------------------------------------------------------------------------
// 上位ノードへのイベントの 伝播を止めずに、そのイベントをキャンセルする
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
var preventDefault = function (e)
{ 
	if (e.preventDefault)
	{
		e.preventDefault();
	}
	else
	{
		e.returnValue = false;
	}
}

//------------------------------------------------------------------------------
// UUIDの作成
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
function getUUID()
{
	var uuid = (function()
	{
		var S4 = function()
		{
			return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
		}
		return (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4() +S4());
	})();
	return uuid;
}

//------------------------------------------------------------------------------
// 連想配列で渡されたパラメータをGETパラメータ形式に変換する
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
function arrayParams2getParam(params)
{
	// 結果を格納する変数を定義
	var param			= "";

	// 引数で渡された連想配列のパラメータが存在しているかチェックする
	if (params != undefined)
	{
		// 連想配列のパラメータを１つずつ取得する
		for (var key in params)
		{
			// GETパラメータ形式に変換して結果を格納する変数の追記していく
			param += (param.length!=0?"&":"") + key + "=" + encodeURIComponent(params[key]);
		}
	}

	// 結果返却
	return param;
}

//------------------------------------------------------------------------------
// 基本となる連想配列に、追加となる連想配列を追加する
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
function addHash(baseHash, addHash)
{
	if (addHash != undefined)
	{
		for (var key in addHash)
		{
			baseHash.push({name: key, value: addHash[key]});
		}
	}

	return baseHash;
}

//------------------------------------------------------------------------------
// 基本となる連想配列に、追加となる連想配列を追加する
//------------------------------------------------------------------------------
// 
// 
// 
//------------------------------------------------------------------------------
function getTextContent(node)
{
	if(window.ActiveXObject)
	{
		try
		{
			//MSXML2以降用
			return node.item(0).firstChild.nodeValue;
		}
		catch (e)
		{
			try
			{
				//旧MSXML用
				return node.item(0).firstChild.nodeValue;
			}
			catch (e2)
			{
				return "";
			}
		}
	}
	else if(window.XMLHttpRequest)
	{
		//Win ie以外のXMLHttpRequestオブジェクト実装ブラウザ用
		try
		{
			return node.item(0).textContent;
		}
		catch (e2)
		{
			return "";
		}
	}
	else
	{
		return "";
	}

	return (node.firstChild.textContent ? node.item(0).textContent : node.firstChild.nodeValue);
}
