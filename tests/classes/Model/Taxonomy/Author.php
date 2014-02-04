<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Represents an author in the database.
 *
 * @package  Jam
 */
class Model_Taxonomy_Author extends Jam_Model {

	public static function initialize(Jam_Meta $meta)
	{
		$meta->associations(array(
			'styles' => Jam::association('taxonomy_terms', array('vocabulary' => 'Styles')),
			'types' => Jam::association('taxonomy_terms', array('vocabulary' => 'Types')),
		));


		// Define fields
		$meta->fields(array(
			'id'         => Jam::field('primary'),
			'name'       => Jam::field('string'),
		 ));
	}

} // End Model_Test_Author