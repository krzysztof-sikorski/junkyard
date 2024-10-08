<?php
//@author Krzysztof Sikorski
$cfg = require_once'./_init.php';


class Daemon_Controller_Rules extends Daemon_Controller
{
	protected $disablePlayer = true;
	protected $pageSubtitle = 'Regulamin';
	protected $pageTemplatePath = 'rules.xml';


	public function prepareView()
	{
		$this->view->lastModified = date(DATE_RFC1123, filemtime($this->cfg->getFilePath('tpl', 'rules.xml')));
	}
}


$ctrl = new Daemon_Controller_Rules($cfg);
$ctrl->execute();
