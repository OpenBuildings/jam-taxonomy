<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Represents an author in the database.
 *
 * @package  Jam
 */
class Model_Taxonomy_Post extends Jam_Model {

	public static function initialize(Jam_Meta $meta)
	{
		$meta->associations(array(
			'types' => Jam::association('taxonomy_terms', array('vocabulary' => 'Types'))
		));

		// Define fields
		$meta->fields(array(
			'id'         => Jam::field('primary'),
			'name'       => Jam::field('string'),
		 ));
	}

} // End Model_Test_Author