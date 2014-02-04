<?php defined('SYSPATH') OR die('No direct access allowed.'); 

class Model_Test_Vocabulary extends Kohana_Model_Vocabulary {

	static public function initialize(Jam_Meta $meta)
	{
		$meta->db(Kohana::TESTING);

		parent::initialize($meta);

		$meta->table('test_vocabularies');
	}
}