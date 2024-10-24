<?php
//@author Krzysztof Sikorski
//wrapper for mail() function
class Daemon_Email
{
	public $from;
	public $to;
	public $replyTo;
	public $subject;
	public $message;


	public function __construct()
	{
		$this->from = sprintf('no-reply@%s', getenv('SERVER_NAME'));
	}


	public function send()
	{
		$headers = implode("\r\n", array(
			sprintf('From: %s', $this->from),
			'Content-Type:text/plain;charset=UTF-8',
		));
		echo "mail($this->to, $this->subject, $this->message, $headers);";
		exit;
	}
}
