<?php
//@author Krzysztof Sikorski
//manages player data, authentication and character list
class Daemon_DbObject_Player extends Daemon_DbObject
{
	protected $_tableName = 'players';
	protected $_index = array('player_id');
	public $player_id;
	public $login;
	public $password;
	public $roles;
	public $name;
	public $date_created;
	public $last_login;
	public $skin;
	public $email;
	//session settings
	const SESSION_TIMEOUT = 900; //seconds
	const VARNAME_CHARACTER_ID = 'characterId';
	const VARNAME_PLAYER_ID = 'playerId';
	const VARNAME_PLAYER_ADDR = 'playerAddr';
	const VARNAME_TIMESTAMP = 'lastAction';


	public function __construct(Daemon_DbClient $dbClient)
	{
		parent::__construct();
		$this->attachDbClient($dbClient);
		$this->checkSession();
		if($id = $this->getPlayerId())
			parent::get(array('player_id' => $id));
	}


	//creates a new character
	public function addCharacter($name, $gender, $turnDelta, $turnLimit)
	{
		if(!$this->getPlayerId())
			return false;
		$maxLength = $this->_dbClient->getColumnMaxLength('characters', 'name');
		$name = Daemon::normalizeString($name, false);
		$gender = Daemon::normalizeString($gender, false);
		$validName = $this->validateName($name, $maxLength);
		$validGender = $this->validateGender($gender);
		if($validName && $validGender)
		{
			$sql = "INSERT INTO characters (player_id, name, gender, last_action) VALUES (:playerId, :name, :gender, now())";
			$params = array(
				'playerId' => $this->getPlayerId(),
				'name' => $name, 'gender' => $gender,
			);
			$this->_dbClient->query($sql, $params, 'Wybrane imię jest już zajęte.');
			if($id = $this->_dbClient->lastInsertId())
			{
				$turns = $this->getStartingTurns($turnDelta, $turnLimit);
				$sql = "INSERT INTO character_data(character_id, turns) VALUES (:id, :turns)";
				$this->_dbClient->query($sql, array('id' => $id, 'turns' => $turns));
				$sql = "INSERT INTO character_statistics(character_id) VALUES (:id)";
				$this->_dbClient->query($sql, array('id' => $id));
			}
		}
	}


	//checks and stores authentication data (logs in)
	public function authenticate($login, $password)
	{
		$this->get(array('login' => $login));
		if($this->player_id)
		{
			//check password
			list($salt, $hash) = explode(':', $this->password.':');
			if($hash == Daemon::passwordHash($salt, $password))
			{
				session_regenerate_id(true);
				$_SESSION[self::VARNAME_PLAYER_ID] = (int) $this->player_id;
				$_SESSION[self::VARNAME_PLAYER_ADDR] = getenv('REMOTE_ADDR');
				$_SESSION[self::VARNAME_TIMESTAMP] = time();
				$this->last_login = $this->_dbClient->selectValue("SELECT NOW()");
				$this->put();
			}
			else $this->player_id = null;
		}
		if(!$this->player_id)
		{
			Daemon_MsgQueue::add('Nieprawidłowy login lub hasło.');
			$this->unauthenticate();
		}
	}


	//compares request data with stored auth data
	private function checkSession()
	{
		$prevAddr = $currentAddr = getenv('REMOTE_ADDR');
		$prevTime = $currentTime = time();
		if(isset($_SESSION[self::VARNAME_PLAYER_ADDR]))
			$prevAddr = $_SESSION[self::VARNAME_PLAYER_ADDR];
		if(isset($_SESSION[self::VARNAME_TIMESTAMP]))
			$prevTime = $_SESSION[self::VARNAME_TIMESTAMP];
		$validAddr = ($currentAddr == $prevAddr);
		$validTime = ($currentTime < $prevTime + self::SESSION_TIMEOUT);
		if($validAddr && $validTime)
			$_SESSION[self::VARNAME_TIMESTAMP] = $currentTime;
		else $this->unauthenticate();
	}


	//resets or deletes selected character
	public function deleteCharacter($characterId, $reset, $turnDelta, $turnLimit)
	{
		if(!$this->getPlayerId())
			return false;
		$sql = "SELECT character_id FROM characters WHERE character_id=:id AND player_id=:playerId";
		$params = array('id' => $characterId, 'playerId' => $this->getPlayerId());
		if($id = $this->_dbClient->selectValue($sql, $params))
		{
			$params = array('id' => $characterId);
			$tables = array('character_data', 'character_missions', 'character_regions',
				'character_statistics', 'character_titles', 'inventory');
			foreach($tables as $table)
			{
				$sql = "DELETE FROM $table WHERE character_id=:id";
				if ($table == 'character_titles')
					$sql .= " AND title_id NOT IN (SELECT title_id FROM titles WHERE type='special')";
				$this->_dbClient->query($sql, $params);
			}
			if ($reset)
			{
				$turns = $this->getStartingTurns($turnDelta, $turnLimit);
				$sql = "INSERT INTO character_data(character_id, turns) VALUES (:id, :turns)";
				$this->_dbClient->query($sql, array('id' => $id, 'turns' => $turns));
				$sql = "INSERT INTO character_statistics(character_id) VALUES (:id)";
				$this->_dbClient->query($sql, array('id' => $id));
			}
			else
			{
				$sql = "DELETE FROM characters WHERE character_id=:id";
				$this->_dbClient->query($sql, $params);
			}
		}
	}


	//returns basic data of active character
	public function getActiveCharacter()
	{
		$char = new Daemon_DbObject_Character;
		$char->attachDbClient($this->_dbClient);
		if($id = $this->getCharacterId())
			$char->get(array('character_id' => $id));
		$this->setCharacterId($char->character_id);
		$char->attachPlayer($this);
		return $char;
	}


	//returns active character's ID
	public function getCharacterId()
	{
		if(isset($_SESSION[self::VARNAME_CHARACTER_ID]))
			return $_SESSION[self::VARNAME_CHARACTER_ID];
		else return null;
	}


	//returns a list of player's characters
	public function getCharacters()
	{
		$sql = "SELECT c.character_id, c.name,
				cp.level, cp.turns, cp.health, cp.health_max, l.name AS location_name
			FROM characters c
			LEFT JOIN character_data cp USING(character_id)
			LEFT JOIN locations l USING(location_id)
			WHERE player_id = :playerId";
		$params = array('playerId' => $this->getPlayerId());
		$result = array();
		foreach((array) $this->_dbClient->selectAll($sql, $params) as $row)
			$result[$row['character_id']] = $row;
		return $result;
	}


	//returns authenticated player's ID
	public function getPlayerId()
	{
		if(isset($_SESSION[self::VARNAME_PLAYER_ID]))
			$this->player_id = $_SESSION[self::VARNAME_PLAYER_ID];
		else $this->player_id = null;
		return $this->player_id;
	}


	//returns a list of player's access roles
	public function getRoles()
	{
		if(!$id = $this->getPlayerId())
			return array();
		if(!is_array($this->roles))
		{
			$sql = "SELECT roles FROM players WHERE player_id=:playerId";
			$params = array('playerId' => $this->getPlayerId());
			$this->roles = explode(',', (string) $this->_dbClient->selectValue($sql, $params));
		}
		return $this->roles;
	}


	protected function getStartingTurns($turnDelta, $turnLimit)
	{
		$sql = "SELECT COUNT(rollover_id) FROM rollovers";
		$n = (int) $this->_dbClient->selectValue($sql);
		return min($turnLimit, $turnDelta * (1 + $n));
	}


	//checks if player has selected access role
	public function hasRole($name)
	{
		return in_array($name, $this->getRoles());
	}


	//stores a new password and sends mail with reset key
	public function preparePasswordReset($login, $email, $password, $passwordCopy)
	{
		if (!$this->validatePassword($password, $passwordCopy))
			return false;
		if (!$login || !$email)
		{
			Daemon_MsgQueue::add('Musisz podać login oraz email.');
			return false;
		}
		//validate login+email
		$sql = "SELECT player_id FROM players WHERE login=:login AND email=:email";
		$params = array('login' => $login, 'email' => $email);
		$playerId = $this->_dbClient->selectValue($sql, $params);
		if (!$playerId)
		{
			Daemon_MsgQueue::add('Nieprawidłowy login lub hasło.');
			return false;
		}
		//store password
		$key = sha1(Daemon::passwordSalt() . $login . $email);
		$salt = Daemon::passwordSalt();
		$passwordSql = sprintf('%s:%s', $salt, Daemon::passwordHash($salt, $password));
		$sql = "UPDATE players SET reset_key = :key, reset_password = :password,
			reset_until = now() + INTERVAL 1 WEEK WHERE player_id = :id";
		$params = array('id' => $playerId, 'key' => $key, 'password' => $passwordSql);
		$ok = $this->_dbClient->query($sql, $params);
		if (!$ok)
		{
			Daemon_MsgQueue::add('Nie udało się zapisać nowego hasła.');
			return false;
		}
		//send mail
		$url = sprintf('%sreset-password?key=%s', $GLOBALS['cfg']->applicationUrl, $key);
		$subject = "Daemon 2: reset hasla";
		$message = "Aby zresetowac haslo przejdz pod adres:\n$url\n";
		$from = $GLOBALS['cfg']->applicationMail;
		$headers = "From: $from\r\nReply-To: $from";
		$ok = mail($email, $subject, $message, $headers);
		if ($ok)
			$msg = 'Na podany email wysłana została wiadomość z kluczem resetującym hasło.';
		else
			$msg = 'Niestety mailer nie działa, reset hasła jest chwilowo niemozliwy.';
		Daemon_MsgQueue::add($msg);
		return $ok;
	}


	//creates a new player
	public function register($login, $password, $passwordCopy)
	{
		$maxLength = $this->_dbClient->getColumnMaxLength('players', 'login');
		$validLogin = $this->validateLogin($login, $maxLength);
		$validPassword = $this->validatePassword($password, $passwordCopy);
		if($validLogin && $validPassword)
		{
			$salt = Daemon::passwordSalt();
			$passwordSql = sprintf('%s:%s', $salt, Daemon::passwordHash($salt, $password));
			$sql = "INSERT INTO players (login, password, roles) VALUES (:login, :password, 'chat')";
			$params = array('login' => $login, 'password' => $passwordSql);
			$ok = $this->_dbClient->query($sql, $params, 'Wybrany login jest już zajęty.');
			Daemon_MsgQueue::add(sprintf('Rejestracja zakończona %s.', $ok ? 'powodzeniem' : 'niepowodzeniem'));
			return $ok;
		}
		return false;
	}


	//resets password based on a hash key
	public function resetPassword($key)
	{
		$sql = "SELECT player_id FROM players WHERE reset_key = :key AND reset_until >= current_date";
		$params = array('key' => $key);
		$playerId = $this->_dbClient->selectValue($sql, $params);
		if ($playerId)
		{
			$sql = "UPDATE players SET password = reset_password, reset_password = null,
				reset_key = null, reset_until = null WHERE player_id = :id";
			$params = array('id' => $playerId);
			$ok = $this->_dbClient->query($sql, $params);
			Daemon_MsgQueue::add(sprintf('Zmiana hasła zakończona %s.', $ok ? 'powodzeniem' : 'niepowodzeniem'));
		}
		else
			Daemon_MsgQueue::add('Podany kod jest nieprawidłowy lub nieaktualny.');
	}


	//stores selected character's ID
	public function setCharacterId($id)
	{
		$_SESSION[self::VARNAME_CHARACTER_ID] = (int) $id;
	}


	//updates password
	public function setPassword($password, $passwordCopy)
	{
		if(!$password && !$passwordCopy)
			return;
		if($this->validatePassword($password, $passwordCopy))
		{
			$salt = Daemon::passwordSalt();
			$this->password = sprintf('%s:%s', $salt, Daemon::passwordHash($salt, $password));
			$this->put();
			Daemon_MsgQueue::add('Hasło zostało zmienione.');
		}
	}


	//deletes stored authentication data (logs out)
	public function unauthenticate()
	{
		unset($_SESSION[self::VARNAME_CHARACTER_ID]);
		unset($_SESSION[self::VARNAME_PLAYER_ID]);
		unset($_SESSION[self::VARNAME_PLAYER_ADDR]);
		unset($_SESSION[self::VARNAME_TIMESTAMP]);
	}


	//checks login validity
	private function validateLogin($input, $maxLength)
	{
		if(!$input)
			Daemon_MsgQueue::add('Musisz podać login.');
		elseif(iconv_strlen($input) > $maxLength)
			Daemon_MsgQueue::add('Wybrany login jest za długi.');
		else return true;
		return false;
	}


	//checks gender validity
	private function validateGender($input)
	{
		if(!in_array($input, array_keys(Daemon_Dictionary::$genders)))
			Daemon_MsgQueue::add('Wybrana płeć nie jest dostępna.');
		else return true;
		return false;
	}


	//checks name validity
	private function validateName($input, $maxLength)
	{
		if(!$input)
			Daemon_MsgQueue::add('Musisz podać imię.');
		elseif(iconv_strlen($input) > $maxLength)
			Daemon_MsgQueue::add('Wybrane imię jest za długie.');
		else return true;
		return false;
	}


	//checks password validity
	private function validatePassword($input, $inputCopy)
	{
		if(!$input)
			Daemon_MsgQueue::add('Musisz podać hasło.');
		elseif($input != $inputCopy)
			Daemon_MsgQueue::add('Źle powtórzone hasło.');
		else return true;
		return false;
	}
}
