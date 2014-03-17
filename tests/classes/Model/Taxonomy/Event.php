<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Represents an event in the database.
 *
 * @package  Jam
 */
class Model_Taxonomy_Event extends Jam_Model {

	public static function initialize(Jam_Meta $meta)
	{
		$meta->behaviors(array(
			'taxonomable' => Jam::behavior('taxonomable'),
		));

		// Define fields
		$meta->fields(array(
			'id'         => Jam::field('primary'),
			'name'       => Jam::field('string'),
		 ));
	}
}
