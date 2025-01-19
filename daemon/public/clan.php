<?php
//@author Krzysztof Sikorski
$cfg = require_once'./_init.php';


class Daemon_Controller_Page extends Daemon_Controller
{
	protected $pageSubtitle = 'Klan';
	protected $pageTemplatePath = 'clan.xml';
	protected $requireActiveChar = true;
	protected $requireAuthentication = true;
	protected $requireNoEvents = true;
	private $clan;
	private $isLeader = false;


	public function prepareModel()
	{
		$this->clan = new Daemon_DbObject_Clan;
		if($this->activeCharacter->clan_id)
		{
			$this->clan->attachDbClient($this->dbClient);
			$this->clan->get(array('clan_id' => $this->activeCharacter->clan_id));
			if($this->clan->leader_id)
				$this->isLeader = ($this->clan->leader_id == $this->activeCharacter->character_id);
		}
	}


	public function prepareView()
	{
		if($this->clan->clan_id)
			$this->prepareViewMember();
		else $this->prepareViewSolo();
	}


	private function prepareViewMember()
	{
		$this->view->clan = $this->clan;
		$this->view->preview = Daemon::formatMessage($this->clan->description, true);
		$this->view->isLeader = $this->isLeader;
		$this->view->members = $this->clan->getMembers($this->activeCharacter->character_id);
		$invitations = $this->clan->getInvitations();
		foreach($invitations as &$row)
			$row['description'] = Daemon::formatMessage($row['description'], true);
		$this->view->invitations = $invitations;
	}


	private function prepareViewSolo()
	{
		$this->view->invitations = $this->activeCharacter->getInvitations();
	}


	public function runCommands()
	{
		if($this->clan->clan_id)
			return $this->runCommandsMember();
		else return $this->runCommandsSolo();
	}


	private function runCommandsMember()
	{
		if($this->isLeader)
		{
			if(isset($_POST['accept']))
			{
				$forum = new Daemon_Forum($this->dbClient);
				$this->clan->acceptCharacter($_POST['accept'], $forum);
				return true;
			}
			if(isset($_POST['kick']))
			{
				$forum = new Daemon_Forum($this->dbClient);
				$this->clan->kickMember($_POST['kick'], $forum);
				return true;
			}
			if(isset($_POST['setLeader'], $_POST['desc']))
			{
				if($_POST['setLeader'] && ($_POST['setLeader'] != $this->clan->leader_id))
				{
					$this->clan->leader_id = $_POST['setLeader'];
					$this->isLeader = false;
				}
				$this->clan->description = $_POST['desc'];
				$this->clan->put();
				return true;
			}
			if(isset($_POST['disband']))
			{
				$this->clan->delete();
				$this->clan = new Daemon_DbObject_Clan;
				$this->activeCharacter->clan_id = null;
				return true;
			}
		}
		if(isset($_POST['leave']))
		{
			$this->activeCharacter->clan_id = null;
			$this->activeCharacter->put();
			return true;
		}
	}


	private function runCommandsSolo()
	{
		if(isset($_POST['join'], $_POST['desc']))
		{
			$forum = new Daemon_Forum($this->dbClient);
			$this->activeCharacter->inviteClan($_POST['join'], $_POST['desc'], $forum);
			return true;
		}
		if(isset($_POST['create'], $_POST['id'], $_POST['name']))
		{
			$this->activeCharacter->createClan($_POST['id'], $_POST['name']);
			$this->prepareModel();
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Controller_Page($cfg);
$ctrl->execute();
