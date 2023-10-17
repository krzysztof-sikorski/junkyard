<?php
//@author Krzysztof Sikorski
//mail & chat related operations
class Daemon_Forum
{
	private $dbClient;


	public function __construct(Daemon_DbClient $dbClient)
	{
		$this->dbClient = $dbClient;
	}


	//adds new mail message
	public function addChat($senderId, $channelId, $content)
	{
		$content = Daemon::normalizeString($content, true);
		if($content)
		{
			$sql = "INSERT INTO chat (sender_id, channel_id, content) VALUES (:senderId, :channelId, :content)";
			$params = array('senderId' => $senderId, 'channelId' => $channelId, 'content' => $content);
			$this->dbClient->query($sql, $params);
		}
		else Daemon_MsgQueue::add('Podaj treść wiadomości.');
	}


	//adds new mail message
	public function addMail($senderId, $recipientName, $content)
	{
		if($recipientId = $this->getCharacterIdByName($recipientName))
			$this->addMailById($senderId, $recipientId, $content);
		else Daemon_MsgQueue::add('Wybrany adresat nie istnieje.');
	}


	//adds new mail message
	public function addMailById($senderId, $recipientId, $content)
	{
		$content = Daemon::normalizeString($content, true);
		if($content)
		{
			$sql = "INSERT INTO mail (sender_id, recipient_id, content) VALUES (:senderId, :recipientId, :content)";
			$params = array('senderId' => $senderId, 'recipientId' => $recipientId, 'content' => $content);
			$this->dbClient->query($sql, $params);
		}
		else Daemon_MsgQueue::add('Podaj treść wiadomości.');
	}


	//updates message with "reply" link
	private static function callbackReplyLink(&$row, $key, $characterId)
	{
		if($row['sender_id'] && ($row['sender_id'] != $characterId))
			$row['replyUrl'] = sprintf('?to=%s', urlencode($row['sender_name']));
	}


	//finds character Id by its name
	private function getCharacterIdByName($name)
	{
		$sql = "SELECT character_id FROM characters WHERE name = :name";
		return $this->dbClient->selectValue($sql, array('name' => Daemon::normalizeString($name)));
	}


	//fetches messages from selected channel
	public function getChat($limit, $from, $channelId)
	{
		$params = array('limit' => $limit + 1, 'channelId' => $channelId);
		$cond = 'channel_id = :channelId';
		if($from)
		{
			$cond .= ' AND message_id <= :from';
			$params['from'] = (int) $from;
		}
		$sql = "SELECT m.*, s.name AS sender_name
			FROM chat m LEFT JOIN characters s ON s.character_id = m.sender_id
			WHERE $cond ORDER BY message_id DESC LIMIT :limit";
		$list =  $this->dbClient->selectAll($sql, $params);
		if(count($list) > $limit)
		{
			$next = array_pop($list);
			$next = $next['message_id'];
		}
		else $next = null;
		return array('list' => $list, 'next' => $next);
	}


	//fetches character's mailbox
	public function getMail($limit, $from, $characterId)
	{
		$params = array('limit' => $limit + 1, 'cid1' => $characterId, 'cid2' => $characterId);
		$cond = '(sender_id = :cid1 OR recipient_id = :cid2)';
		if($from)
		{
			$cond .= ' AND message_id <= :from';
			$params['from'] = (int) $from;
		}
		$sql = "SELECT m.*, s.name AS sender_name, r.name AS recipient_name
			FROM mail m
			LEFT JOIN characters s ON s.character_id = m.sender_id
			LEFT JOIN characters r ON r.character_id = m.recipient_id
			WHERE $cond ORDER BY message_id DESC LIMIT :limit";

		$list =  $this->dbClient->selectAll($sql, $params);
		if(count($list) > $limit)
		{
			$next = array_pop($list);
			$next = $next['message_id'];
		}
		else $next = null;
		if($list)
			array_walk($list, array(get_class($this), 'callbackReplyLink'), $characterId);
		return array('list' => $list, 'next' => $next);
	}
}
