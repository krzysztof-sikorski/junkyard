<?php
//@author Krzysztof Sikorski
class Daemon_DbObject_Character extends Daemon_DbObject
{
	protected $_tableName = 'characters';
	protected $_index = array('character_id');
	public $character_id;
	public $player_id;
	public $name;
	public $gender;
	public $date_created;
	public $last_action;
	public $show_player;
	public $last_mail_id;
	public $clan_id;
	public $avatar_url;
	public $quote;
	public $description;
	private $_player;
	private $_characterData;


	public function attachPlayer(Daemon_DbObject_Player $player)
	{
		$this->_player = $player;
	}


	//checks character's inbox for new messages
	public function checkMail()
	{
		if(!$this->character_id)
			return false;
		$sql = "SELECT COUNT(1) FROM mail WHERE message_id > COALESCE(:lastMailId, 0) AND recipient_id = :charId";
		$params = array('lastMailId' => $this->last_mail_id, 'charId' => $this->character_id);
		return $this->_dbClient->selectValue($sql, $params);
	}


	//try to create a new clan (id or name may be already taken)
	public function createClan($clanId, $name)
	{
		$clanId = Daemon::normalizeString($clanId, false);
		$name = Daemon::normalizeString($name, false);
		$validId = $this->validateClanId($clanId);
		$validName = $this->validateClanId($name);
		if($validId && $validName)
		{
			$sql = "INSERT INTO clans(clan_id, name, leader_id) VALUES (:id, :name, :leaderId)";
			$params = array('id' => $clanId, 'name' => $name, 'leaderId' => $this->character_id);
			if($this->_dbClient->query($sql, $params, 'Wybrany ID lub nazwa klanu są już zajęte.'))
			{
				$this->clan_id = $clanId;
				$this->put();
			}
		}
	}


	//returns DbObject_CharacterData instance
	public function getCharacterData()
	{
		if(!$this->_characterData)
		{
			$this->_characterData = new Daemon_DbObject_CharacterData();
			$this->_characterData->attachDbClient($this->_dbClient);
			if($this->character_id)
				$this->_characterData->get(array('character_id' => $this->character_id));
			$this->_characterData->_characterName = $this->name;
			$this->_characterData->_gender = $this->gender;
		}
		return $this->_characterData;
	}


	//returns a channel=>writeAccess array of channels allowed for character
	public function getForumChannels()
	{
		$cdata = $this->getCharacterData();
		$channels = array('public' => array('name' => 'publiczne', 'writable' => false));
		if($this->character_id)
		{
			$channels['public']['writable'] = $this->_player->hasRole('chat');
		}
		if($cdata->faction_id)
		{
			$channelId = 'f/'.$cdata->faction_id;
			$channels[$channelId] = array('name' => 'frakcyjne', 'writable' => $this->_player->hasRole('chat'));
		}
		if($this->clan_id)
		{
			$channelId = 'c/'.$this->clan_id;
			$channels[$channelId] = array('name' => 'klanowe', 'writable' => $this->_player->hasRole('chat'));
		}
		return $channels;
	}


	//reads a list of invitations
	public function getInvitations()
	{
		$sql = "SELECT i.clan_id, c.name AS clan_name, i.description
			FROM clan_invitations i JOIN clans c USING(clan_id)
			WHERE i.character_id=:id";
		$params = array('id' => $this->character_id);
		return $this->_dbClient->selectAll($sql, $params);
	}


	//sends invitation to specified clan
	public function inviteClan($clanId, $description, Daemon_Forum $forum)
	{
		if(!$description)
			$description = null;
		$sql = "SELECT leader_id FROM clans WHERE clan_id=:id";
		$leaderId = $this->_dbClient->selectValue($sql, array('id' => $clanId));
		if($leaderId)
		{
			$sql = "INSERT IGNORE INTO clan_invitations(clan_id, character_id, description)
				VALUES (:clanId, :charId, :description) ON DUPLICATE KEY UPDATE description=:description";
			$params = array('clanId' => $clanId, 'charId' => $this->character_id,
				'description' => $description);
			$this->_dbClient->query($sql, $params);
			$msg = "Postać $this->name pragnie dołączyć do klanu.";
			$forum->addMailById(null, $leaderId, $msg);
		}
		else Daemon_MsgQueue::add('Wybrany klan nie istnieje lub nie ma przywódcy.');
	}


	public function updateLastAction()
	{
		if($this->character_id)
		{
			$sql = "UPDATE characters SET last_action = NOW() WHERE character_id=:id";
			$this->_dbClient->query($sql, array('id' => $this->character_id));
		}
	}


	//checks clan id validity
	private function validateClanId($input)
	{
		$maxLength = $this->_dbClient->getColumnMaxLength('clans', 'clan_id');
		if(!$input)
			Daemon_MsgQueue::add('Musisz podać ID klanu.');
		elseif(iconv_strlen($input) > $maxLength)
			Daemon_MsgQueue::add('Wybrany ID jest za długi.');
		else return true;
		return false;
	}


	//checks clan name validity
	private function validateClanName($input)
	{
		$maxLength = $this->_dbClient->getColumnMaxLength('clans', 'name');
		if(!$input)
			Daemon_MsgQueue::add('Musisz podać nazwę klanu.');
		elseif(iconv_strlen($input) > $maxLength)
			Daemon_MsgQueue::add('Wybrana nazwa jest za długa.');
		else return true;
		return false;
	}
}
