<?php
//@author Krzysztof Sikorski
class Daemon_DbObject_Clan extends Daemon_DbObject
{
	protected $_tableName = 'clans';
	protected $_index = array('clan_id');
	public $clan_id;
	public $name;
	public $leader_id = null;
	public $description = null;


	//accept an invitation
	public function acceptCharacter($characterId, Daemon_Forum $forum)
	{
		$sql = "SELECT 1 FROM clan_invitations WHERE clan_id=:clanId AND character_id=:charId";
		$params = array('clanId' => $this->clan_id, 'charId' => $characterId);
		if($this->_dbClient->selectValue($sql, $params))
		{
			$sql = "UPDATE characters SET clan_id=:clanId WHERE character_id=:charId";
			$params = array('clanId' => $this->clan_id, 'charId' => $characterId);
			$this->_dbClient->query($sql, $params);
			$sql = "DELETE FROM clan_invitations WHERE character_id=:id";
			$this->_dbClient->query($sql, array('id' => $characterId));
			$msg = "Podanie do klanu $this->name zostało zaakceptowane.";
			$forum->addMailById(null, $characterId, $msg);
		}
		else Daemon_MsgQueue::add('Wybrane zaproszenie nie istnieje.');
	}


	//deletes clan and updates its members
	public function delete()
	{
		//TODO
		$params = array('id' => $this->clan_id);
		$sql = "DELETE FROM clans WHERE clan_id=:id";
		$this->_dbClient->query($sql, $params);
		$sql = "DELETE FROM clan_invitations WHERE clan_id=:id";
		$this->_dbClient->query($sql, $params);
		$sql = "UPDATE characters SET clan_id=NULL WHERE clan_id=:id";
		$this->_dbClient->query($sql, $params);
	}


	//reads a list of invitations
	public function getInvitations()
	{
		$sql = "SELECT i.*, c.name AS character_name, cd.level, cd.xp_used,
				cd.faction_id, COALESCE(cd.rank_id, 0) AS rank_id,
				date_format(c.date_created, '%Y-%m-%d') AS date_created
			FROM clan_invitations i JOIN characters c USING(character_id) JOIN character_data cd USING(character_id)
			WHERE i.clan_id=:id";
		return $this->_dbClient->selectAll($sql, array('id' => $this->clan_id));
	}


	//fetches leader's name
	public function getLeaderName()
	{
		$sql = "SELECT name FROM characters WHERE character_id=:id";
		return $this->_dbClient->selectValue($sql, array('id' => $this->leader_id));
	}


	//reads a list of members
	public function getMembers()
	{
		$sql = "SELECT cd.character_id, c.name, cd.level, cd.xp_used,
				cd.faction_id, COALESCE(cd.rank_id, 0) AS rank_id,
				date_format(c.date_created, '%Y-%m-%d') AS date_created
			FROM characters c JOIN character_data cd USING(character_id) WHERE c.clan_id=:id";
		$result = $this->_dbClient->selectAll($sql, array('id' => $this->clan_id));
		foreach($result as &$row)
			$row['_isLeader'] = ($row['character_id'] == $this->leader_id);
		return $result;
	}


	//removes member from clan
	public function kickMember($characterId, Daemon_Forum $forum)
	{
		if($characterId != $this->leader_id)
		{
			$sql = "UPDATE characters SET clan_id = NULL WHERE character_id=:id";
			$this->_dbClient->query($sql, array('id' => $characterId));
			$forum->addMailById(null, $characterId, 'Wyrzucono cię z klanu.');
		}
		else Daemon_MsgQueue::add('Przywódca nie może odejść, jedynie rozwiązać klan.');
	}
}
