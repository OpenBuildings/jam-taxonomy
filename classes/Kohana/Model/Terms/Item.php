<?php defined('SYSPATH') OR die('No direct script access.');

class Kohana_Model_Terms_Item extends Jam_Model {

	public static function initialize(Jam_Meta $meta)
	{
		$meta
			->name_key('id');

		$meta->associations(array(
			'term' => Jam::association('belongsto'),
			'item' => Jam::association('belongsto', array('polymorphic' => TRUE)),
		));

		$meta->fields(array(
			'id' => Jam::field('primary'),
		));
	}
	
}
