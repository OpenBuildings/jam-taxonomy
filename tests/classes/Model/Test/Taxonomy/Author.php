<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Represents an author in the database.
 *
 * @package  Jam
 */
class Model_Test_Taxonomy_Author extends Jam_Model {

	public static function initialize(Jam_Meta $meta)
	{
		// Set database to connect to
		$meta->db(Kohana::TESTING);

		$meta->associations(array(
			'styles' => Jam::association('taxonomy_terms', array('vocabulary' => 'Styles', 'vocabulary_model' => 'test_vocabulary', 'join_table' => 'test_terms_items', 'foreign_model' => 'test_term')),
			'types' => Jam::association('taxonomy_terms', array('vocabulary' => 'Types', 'vocabulary_model' => 'test_vocabulary', 'join_table' => 'test_terms_items', 'foreign_model' => 'test_term')),
		));


		// Define fields
		$meta->fields(array(
			'id'         => Jam::field('primary'),
			'name'       => Jam::field('string'),
		 ));
	}

} // End Model_Test_Author