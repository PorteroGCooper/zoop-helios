<?php
/**
* @package mail
*/
// Copyright (c) 2008 Supernerd LLC and Contributors.
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

/**
 * message
 *
 * @uses gui
 * @package
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @author John Lesueur
 * @author Rick Gigger
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class message extends gui
{
	/**
	 * message
	 *
	 * @access public
	 * @return void
	 */
	function message()
	{
		$this->Smarty();
		
		$this->template_dir = Config::get('zoop.gui.directories.base');
		$this->compile_dir = APP_TEMP_DIR . "/gui";

		if ($look = Config::get('app.gui.look')) {
			$this->config_dir = $this->template_dir . "/" . $look . "/configs";
			$this->debug_tpl = "file:" . $look . "/debug.tpl";
			$this->assign("template_root", $look);
		} else {
			$this->config_dir = $this->template_dir . "/configs";
			$this->assign("template_root", $this->template_dir);
		}
		global $strings;
		$this->assign("strings", $strings);
	}

	/**
	 * send
	 * Send an Email using a Template
	 *
	 * @param mixed $from
	 * @param mixed $to
	 * @param mixed $cc
	 * @param mixed $subject
	 * @param mixed $inTemplateName
	 * @param string $inType
	 * @access public
	 * @return void
	 */
	function send($from, $to, $cc, $subject, $inTemplateName, $inType = "text")
	{
		$body = $this->fetch(gui_messages . "/" . $inTemplateName);

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
	/**
	 * display
	 * Works like send, but displays the email to the browser rather than sending it.
	 *
	 * @param mixed $from
	 * @param mixed $to
	 * @param mixed $cc
	 * @param mixed $subject
	 * @param mixed $inTemplateName
	 * @param string $inType
	 * @access public
	 * @return void
	 */
	function display($from, $to, $cc, $subject, $inTemplateName, $inType = "text")
	{
		gui::display(gui_messages . "/" . $inTemplateName);
	}

	/**
	 * sendEmail
	 * Send an Email without using a Template
	 *
	 * @param mixed $from
	 * @param mixed $to
	 * @param mixed $cc
	 * @param mixed $subject
	 * @param mixed $body
	 * @param string $type
	 * @access public
	 * @return void
	 */
	function sendEmail($from, $to, $cc, $subject, $body, $type = "text")
	{
		if(APP_STATUS == "dev")
		{
			if (defined("dev_email_address"))
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

	/**
	 * sendTextEmail
	 * Send a plain text Email without using a template
	 *
	 * @param mixed $from
	 * @param mixed $to
	 * @param mixed $cc
	 * @param mixed $subject
	 * @param mixed $body
	 * @access public
	 * @return void
	 */
	function sendTextEmail($from, $to, $cc, $subject, $body)
	{
		message::sendEmail($from, $to, $cc, $subject, $body, "text");
	}
	/**
	 * sendHTMLEmail
	 * Send an html Email without using a template
	 *
	 * @param mixed $from
	 * @param mixed $to
	 * @param mixed $cc
	 * @param mixed $subject
	 * @param mixed $body
	 * @access public
	 * @return void
	 */
	function sendHTMLEmail($from, $to, $cc, $subject, $body)
	{
		message::sendEmail($from, $to, $cc, $subject, $body, "html");
	}
	/**
	 * sendMultipartEmail
	 * Send a multipart Email without using a template
	 *
	 * @param mixed $from
	 * @param mixed $to
	 * @param mixed $cc
	 * @param mixed $subject
	 * @param mixed $body
	 * @access public
	 * @return void
	 */
	function sendMultipartEmail($from, $to, $cc, $subject, $body)
	{
		message::sendEmail($from, $to, $cc, $subject, $body, "multipart");
	}
}
?>
