<?php
//@author Krzysztof Sikorski
class Daemon_News
{
	private $dbClient;


	public function __construct(Daemon_DbClient $dbClient)
	{
		$this->dbClient = $dbClient;
	}


	protected function callbackFormatDates(&$row)
	{
		$row['published'] = date(DATE_ATOM, $row['published_ts']);
		$row['updated'] = date(DATE_ATOM, $row['updated_ts']);
	}


	//deletes entry by ID
	public function deleteEntry($id)
	{
		$sql = "DELETE FROM newsfeed WHERE id=:id";
		$this->dbClient->query($sql, array('id' => $id));
	}


	//generates an ID for a new entry
	public function generateId($domain, $title)
	{
		$suffix = preg_replace('/\W+/', '-', $title);
		return sprintf('tag:%s,%s:%s', $domain, date('Y-m-d'), $suffix);
	}


	//fetches last entry's update time
	public function getLastUpdated()
	{
		$sql = "SELECT UNIX_TIMESTAMP(MAX(updated)) FROM newsfeed";
		return date(DATE_ATOM, (int) $this->dbClient->selectValue($sql, array()));
	}


	//fetches a list of last entries
	public function getEntries($limit, $format = false)
	{
		$params = array();
		$sql = "SELECT *, UNIX_TIMESTAMP(published) AS published_ts, UNIX_TIMESTAMP(updated) AS updated_ts
			FROM newsfeed ORDER BY published DESC";
		if($limit)
		{
			$params = array('limit' => $limit);
			$sql .= " LIMIT :limit";
		}
		$data = $this->dbClient->selectAll($sql, $params);
		if($format && $data)
			array_walk($data, array($this, 'callbackFormatDates'));
		return $data;
	}


	//creates or updates a feed entry
	public function updateEntry($id, $title, $author, $content)
	{
		$sql = "INSERT INTO newsfeed (id, published, title, author, content)
			VALUES (:id, NOW(), :title, :author, :content)
			ON DUPLICATE KEY UPDATE updated=NOW(), title=:title, author=:author, content=:content";
		$params = array('id' => $id, 'title' => $title, 'author' => $author, 'content' => $content);
		$this->dbClient->query($sql, $params);
	}
}
