<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html{* $xmlns *}{* $xml_lang *}>
	<head>
		<title>{$title}</title>
		<base href="{$BASE_HREF}/" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		{resources}
	</head>
	<body>
{*
		{regions name='header'}
		{regions name='content'}
		{regions name='sidebar'}
*}
		{regions}
	</body>
</html>