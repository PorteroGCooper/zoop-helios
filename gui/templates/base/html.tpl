<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html{* $xmlns *}{* $xml_lang *}>
	<head>
		<title>{$title}</title>
		<base href="{$BASE_HREF}" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		{* css files=$css *}
		{* js files=$js *}
	</head>
	<body>
		{* foreach from=$regions key=$name item=$region_tpl}
		<div id="{$name}">
			{include file=$region_tpl}
		</div>
		{/foreach *}
	</body>
</html>==== ORIGINAL VERSION gui/templates/base/html.tpl 122584841537679
