<?php
//@author Krzysztof Sikorski
class Daemon_DbObject_Event extends Daemon_DbObject
{
	protected $_tableName = 'events';
	protected $_index = array('event_id');
	public $event_id;
	public $name;
	public $handle = null;
	public $description = null;
}
