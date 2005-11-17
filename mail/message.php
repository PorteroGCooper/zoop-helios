<?
/**
* @package mail
*/
// Copyright (c) 2005 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

include(dirname(__file__) . "/html2text.php");
require_once 'Mail.php';
/**
* @package mail
* @todo -c Remove reliance on html2text(uses GPL).
*/
class message extends gui
{
	function message()
	{
		$this->Smarty();
   		$this->template_dir = fw_gui_base;
		$this->compile_dir = fw_temp_dir . "/gui";

		if (defined("fw_gui_look") )
		{
			$this->config_dir = $this->template_dir . "/" . fw_gui_look . "/configs";
			$this->debug_tpl = "file:" . fw_gui_look . "/debug.tpl";
			$this->assign("template_root", fw_gui_look);
		}
		else
		{
			$this->config_dir = $this->template_dir . "/configs";
			$this->assign("template_root", fw_gui_base);
		}
		global $strings;
		$this->assign("strings", $strings);
	}

	function send($from, $to, $cc, $subject, $inTemplateName, $inType = "text")
	{
		$body = $this->fetch(fw_gui_messages . "/" . $inTemplateName);

			switch ($inType)
			{
				case "text":
					message::sendTextEmail($from, $to, $cc, $subject, $body);
					break;
				case "html":
					message::sendHTMLEmail($from, $to, $cc, $subject, $body);
					break;
				case "multipart":
					message::sendMultipartEmail($from, $to, $cc, $subject, $body);
					break;
				default:
					message::sendTextEmail($from, $to, $cc, $subject, $body);
					break;
			}
	}

	//	this is mostly just for debugging
	function display($from, $to, $cc, $subject, $inTemplateName, $inType = "text")
	{
		gui::display(fw_gui_messages . "/" . $inTemplateName);
	}

	function sendEmail($from, $to, $cc, $subject, $body, $type = "text")
	{

		if(app_status == "dev")
		{
			if (defined(dev_email_address))
			{
				$to = dev_email_address;
				if( $cc != NULL)
					$cc = dev_email_address;
			}
		}

		$crlf = "\r\n";

		$hdrs = array(
		          'From'    => $from,
		          'Subject' => $subject,
		          'To'		=> $to
		          );

		if (!empty($cc))
		{
			$hdrs["Cc"] = $cc;
		}
		require_once 'Mail/mime.php';
		$mime = new Mail_mime($crlf);

		switch ($type)
		{
			case "text":
				$mime->setTXTBody($body);
				break;
			case "html":
				$mime->setHTMLBody($body);
				break;
			case "multipart":
				$mime->setHTMLBody($body);
				$h2t =& new html2text($body);
				$textbody = $h2t->get_text();
				$mime->setTXTBody($textbody);
				break;
			default:
				$mime->setTXTBody($body);
				break;
		}

		$body = $mime->get();
		$hdrs = $mime->headers($hdrs);

		if(mail_type == "smtp")
		{
			if(mail_smtp_auth_use)
			{
				//die("Before mail");
				$mail =& Mail::factory('smtp', array(
						"host" => mail_smtp_host,
						"port" => mail_smtp_port,
						"auth" => true,
						"username" => mail_smtp_auth_username,
						"password" => mail_smtp_auth_password));
			}
			else
			{
				$mail =& Mail::factory('smtp', array(
						"host" => mail_smtp_host,
						"port" => mail_smtp_port,
						"auth" => false));
			}
		}
		else
		{
			$mail =& Mail::factory("sendmail");
		}
 		$tmp = $mail->send($to, $hdrs, $body);
	}

	function sendTextEmail($from, $to, $cc, $subject, $body)
	{
		message::sendEmail($from, $to, $cc, $subject, $body, "text");
	}
	function sendHTMLEmail($from, $to, $cc, $subject, $body)
	{
		message::sendEmail($from, $to, $cc, $subject, $body, "html");
	}
	function sendMultipartEmail($from, $to, $cc, $subject, $body)
	{
		message::sendEmail($from, $to, $cc, $subject, $body, "multipart");
	}
}
?>
