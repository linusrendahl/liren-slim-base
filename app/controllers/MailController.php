<?php

namespace App\Controllers;


/**
* 
*/
class MailController
{

	protected $app;
	protected $transport;
	protected $mailer;
	protected $subject;
	protected $to;
	protected $from;
	protected $body;
	
	function __construct($app)
	{
		$this->app = $app;
	}

	public function setTransport($transport)
	{
		// Create the Transport
		$this->transport = \Swift_SmtpTransport::newInstance($transport['smtp'], $transport['port'])
		  ->setUsername($transport['email'])
		  ->setPassword($transport['password'])
		  ;

		$this->mailer = \Swift_Mailer::newInstance($this->transport);
	}


	public function subject($subject)
	{
		$this->subject = $subject;
		return $this;
	}

	public function to($to)
	{
		$this->to = $to;
		return $this;
	}

	public function from($from)
	{
		$this->from = $from;
		return $this;
	}

	public function body($body, $mime='text/plain')
	{
		$this->body = $body;
		$this->mime = $mime;
		return $this;
	}


	public function send()
	{
		$message = \Swift_Message::newInstance($this->subject)
		  ->setFrom($this->from)
		  ->setTo($this->to)
		  ->setBody($this->body, $this->mime)
		  ;

		// Send the message
		try {
			$result = $this->mailer->send($message);
		} catch (\Swift_TransportException $e) {
			$this->app->flashNow('info', array(
			'msg'	=> '<i class="fa fa-info-circle"></i> Mail kunde inte tyvärr inte skickas. Kontakta vår support för hjälp.',
			'class'	=> 'bg-danger'
			));
		}
		
		
	}

}