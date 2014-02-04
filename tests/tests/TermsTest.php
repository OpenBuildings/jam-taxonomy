<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Tests Manytomany fields.
 *
 * @package Jam
 * @group   jam-taxonomy
 * @group   jam-taxonomy.terms
 */
class Jam_Taxonomy_TermsTest extends PHPUnit_Framework_TestCase {

	public $meta;

	public function setUp()
	{
		parent::setUp();

		$this->meta = new Jam_Meta('test_taxonomy_post');
	}


	public function data_join()
	{
		return array(
			array(
				array('vocabulary_model' => 'test_vocabulary'), NULL, NULL, 
				'JOIN `terms_items` ON (`terms_items`.`item_id` = `test_taxonomy_posts`.`id` AND `terms_items`.`item_model` = \'test_taxonomy_post\') JOIN `terms` ON (`terms`.`id` = `terms_items`.`term_id`)'
			),
			array(
				array('foreign_model' => 'test_term', 'term_key' => 'test_term_id', 'item_key' => 'test_item_id', 'item_polymorphic_key' => 'test_item_model', 'join_table' => 'test_terms_items', 'vocabulary_model' => 'test_vocabulary'), NULL, NULL, 
				'JOIN `test_terms_items` ON (`test_terms_items`.`test_item_id` = `test_taxonomy_posts`.`id` AND `test_terms_items`.`test_item_model` = \'test_taxonomy_post\') JOIN `test_terms` ON (`test_terms`.`id` = `test_terms_items`.`test_term_id`)'
			),
			array(
				array('vocabulary' => 'Types', 'vocabulary_model' => 'test_vocabulary'), NULL, NULL, 
				'JOIN `terms_items` ON (`terms_items`.`item_id` = `test_taxonomy_posts`.`id` AND `terms_items`.`item_model` = \'test_taxonomy_post\') JOIN `terms` ON (`terms`.`id` = `terms_items`.`term_id` AND `terms`.`vocabulary_id` IN (\'1\'))'
			),
			array(
				array('vocabulary' => array('Types', 'Styles'), 'vocabulary_model' => 'test_vocabulary'), NULL, NULL, 
				'JOIN `terms_items` ON (`terms_items`.`item_id` = `test_taxonomy_posts`.`id` AND `terms_items`.`item_model` = \'test_taxonomy_post\') JOIN `terms` ON (`terms`.`id` = `terms_items`.`term_id` AND `terms`.`vocabulary_id` IN (\'1\', \'2\'))'
			),
			array(
				array('vocabulary_model' => 'test_vocabulary'), 'type_terms', NULL, 
				'JOIN `terms_items` ON (`terms_items`.`item_id` = `test_taxonomy_posts`.`id` AND `terms_items`.`item_model` = \'test_taxonomy_post\') JOIN `terms` AS `type_terms` ON (`type_terms`.`id` = `terms_items`.`term_id`)'
			),
			array(
				array('vocabulary_model' => 'test_vocabulary'), 'type_terms', 'LEFT', 
				'LEFT JOIN `terms_items` ON (`terms_items`.`item_id` = `test_taxonomy_posts`.`id` AND `terms_items`.`item_model` = \'test_taxonomy_post\') LEFT JOIN `terms` AS `type_terms` ON (`type_terms`.`id` = `terms_items`.`term_id`)'
			),
		);
	}

	/**
	 * @dataProvider data_join
	 */
	public function test_join($options, $alias, $type, $expected_sql)
	{
		$association = new Jam_Association_Taxonomy_Terms($options);
		$association->initialize($this->meta, 'terms');

		$this->assertEquals($expected_sql, (string) $association->join($alias, $type));
	}

	public function data_get()
	{
		return array(
			array(
				array('vocabulary_model' => 'test_vocabulary'), NULL, NULL, 
				'SELECT `terms`.* FROM `terms` JOIN `terms_items` ON (`terms_items`.`term_id` = `terms`.`id`) WHERE `terms_items`.`item_model` = \'test_taxonomy_post\' AND `terms_items`.`item_id` = 1 ORDER BY `terms`.`position`'
			),
			array(
				array('foreign_model' => 'test_term', 'term_key' => 'test_term_id', 'item_key' => 'test_item_id', 'item_polymorphic_key' => 'test_item_model', 'join_table' => 'test_terms_items', 'vocabulary_model' => 'test_vocabulary'), NULL, NULL, 
				'SELECT `test_terms`.* FROM `test_terms` JOIN `test_terms_items` ON (`test_terms_items`.`test_term_id` = `test_terms`.`id`) WHERE `test_terms_items`.`test_item_model` = \'test_taxonomy_post\' AND `test_terms_items`.`test_item_id` = 1'
			),
			array(
				array('vocabulary' => 'Types', 'vocabulary_model' => 'test_vocabulary'), NULL, NULL, 
				'SELECT `terms`.* FROM `terms` JOIN `terms_items` ON (`terms_items`.`term_id` = `terms`.`id`) WHERE `terms_items`.`item_model` = \'test_taxonomy_post\' AND `terms_items`.`item_id` = 1 AND `terms`.`vocabulary_id` IN (\'1\') ORDER BY `terms`.`position`'
			),
			array(
				array('vocabulary' => array('Types', 'Styles'), 'vocabulary_model' => 'test_vocabulary'), NULL, NULL, 
				'SELECT `terms`.* FROM `terms` JOIN `terms_items` ON (`terms_items`.`term_id` = `terms`.`id`) WHERE `terms_items`.`item_model` = \'test_taxonomy_post\' AND `terms_items`.`item_id` = 1 AND `terms`.`vocabulary_id` IN (\'1\', \'2\') ORDER BY `terms`.`position`'
			),
			array(
				array('vocabulary_model' => 'test_vocabulary'), array(1, 2), array(1, 2), 
				'SELECT `terms`.* FROM `terms` JOIN `terms_items` ON (`terms_items`.`term_id` = `terms`.`id`) WHERE `terms_items`.`item_model` = \'test_taxonomy_post\' AND `terms_items`.`item_id` = 1 ORDER BY `terms`.`position`'
			),
			array(
				array('vocabulary_model' => 'test_vocabulary'), array(array('id' => 5), 2), array(5, 2), 
				'SELECT `terms`.* FROM `terms` JOIN `terms_items` ON (`terms_items`.`term_id` = `terms`.`id`) WHERE `terms_items`.`item_model` = \'test_taxonomy_post\' AND `terms_items`.`item_id` = 1 ORDER BY `terms`.`position`'
			),
		);
	}

	/**
	 * @dataProvider data_get
	 */
	public function test_get($options, $value, $expected_ids, $expected_sql)
	{
		$association = new Jam_Association_Taxonomy_Terms($options);
		$association->initialize($this->meta, 'terms');

		$model = Jam::build('test_taxonomy_post')->load_fields(array('id' => 1));

		$result = $association->get($model, $value, (bool) $value);

		$this->assertInstanceOf('Jam_Array_Association', $result);

		$this->assertEquals($expected_sql, (string) $result);

		if ($expected_ids !== NULL)
		{
			$this->assertEquals($expected_ids, $result->ids());
		}
	}


	public function data_erase_query()
	{
		return array(
			array(
				array('vocabulary_model' => 'test_vocabulary'), 
				'DELETE FROM `terms_items` WHERE `item_id` = 1 AND `item_model` = \'test_taxonomy_post\''
			),
			array(
				array('foreign_model' => 'test_term', 'term_key' => 'test_term_id', 'item_key' => 'test_item_id', 'item_polymorphic_key' => 'test_item_model', 'join_table' => 'test_terms_items', 'vocabulary_model' => 'test_vocabulary'), 
				'DELETE FROM `test_terms_items` WHERE `test_item_id` = 1 AND `test_item_model` = \'test_taxonomy_post\''
			),
			array(
				array('vocabulary' => 'Types', 'vocabulary_model' => 'test_vocabulary'), 
				'DELETE FROM `terms_items` WHERE `item_id` = 1 AND `item_model` = \'test_taxonomy_post\''
			),
		);
	}

	/**
	 * @dataProvider data_erase_query
	 */
	public function test_erase_query($options, $expected_sql)
	{
		$association = new Jam_Association_Taxonomy_Terms($options);
		$association->initialize($this->meta, 'terms');

		$model = Jam::build('test_taxonomy_post')->load_fields(array('id' => 1));

		$this->assertEquals($expected_sql, (string) $association->erase_query($model));
	}

	public function data_remove_items_query()
	{
		return array(
			array(
				array('vocabulary_model' => 'test_vocabulary'), array(1, 2, 3), 
				'DELETE FROM `terms_items` WHERE `item_id` = 1 AND `item_model` = \'test_taxonomy_post\' AND `term_id` IN (1, 2, 3)'
			),
			array(
				array('foreign_model' => 'test_term', 'term_key' => 'test_term_id', 'item_key' => 'test_item_id', 'item_polymorphic_key' => 'test_item_model', 'join_table' => 'test_terms_items', 'vocabulary_model' => 'test_vocabulary'), array(1, 2, 3), 
				'DELETE FROM `test_terms_items` WHERE `test_item_id` = 1 AND `test_item_model` = \'test_taxonomy_post\' AND `test_term_id` IN (1, 2, 3)'
			),
			array(
				array('vocabulary' => 'Types', 'vocabulary_model' => 'test_vocabulary'), array(1, 2, 3), 
				'DELETE FROM `terms_items` WHERE `item_id` = 1 AND `item_model` = \'test_taxonomy_post\' AND `term_id` IN (1, 2, 3)'
			),
		);
	}

	/**
	 * @dataProvider data_remove_items_query
	 */
	public function test_remove_items_query($options, $ids, $expected_sql)
	{
		$association = new Jam_Association_Taxonomy_Terms($options);
		$association->initialize($this->meta, 'terms');
		$model = Jam::build('test_taxonomy_post')->load_fields(array('id' => 1));

		$this->assertEquals($expected_sql, (string) $association->remove_items_query($model, $ids));
	}


	public function data_add_items_query()
	{
		return array(
			array(
				array('vocabulary_model' => 'test_vocabulary'), array(1, 2, 3), 
				'INSERT INTO `terms_items` (`item_id`, `item_model`, `term_id`) VALUES (1, \'test_taxonomy_post\', 1), (1, \'test_taxonomy_post\', 2), (1, \'test_taxonomy_post\', 3)'
			),
			array(
				array('foreign_model' => 'test_term', 'term_key' => 'test_term_id', 'item_key' => 'test_item_id', 'item_polymorphic_key' => 'test_item_model', 'join_table' => 'test_terms_items', 'vocabulary_model' => 'test_vocabulary'), array(1, 2, 3), 
				'INSERT INTO `test_terms_items` (`test_item_id`, `test_item_model`, `test_term_id`) VALUES (1, \'test_taxonomy_post\', 1), (1, \'test_taxonomy_post\', 2), (1, \'test_taxonomy_post\', 3)'
			),
			array(
				array('vocabulary' => 'Types', 'vocabulary_model' => 'test_vocabulary'), array(1, 2, 3), 
				'INSERT INTO `terms_items` (`item_id`, `item_model`, `term_id`) VALUES (1, \'test_taxonomy_post\', 1), (1, \'test_taxonomy_post\', 2), (1, \'test_taxonomy_post\', 3)'
			),
		);
	}

	/**
	 * @dataProvider data_add_items_query
	 */
	public function test_add_items_query($options, $ids, $expected_sql)
	{
		$association = new Jam_Association_Taxonomy_Terms($options);
		$association->initialize($this->meta, 'terms');

		$model = Jam::build('test_taxonomy_post')->load_fields(array('id' => 1));

		$this->assertEquals($expected_sql, (string) $association->add_items_query($model, $ids));
	}

	public function test_item_get_set()
	{
		$association = new Jam_Association_Taxonomy_Terms(array('vocabulary_model' => 'test_vocabulary', 'vocabulary' => 'Types'));
		$association->initialize($this->meta, 'terms');

		$model = Jam::build('test_taxonomy_post')->load_fields(array('id' => 1));

		$term = Jam::build('test_term', array('name' => 'Term1'));
		$this->assertNull($term->vocabulary_id);

		$this->assertEquals(array(1), $association->vocabulary_ids());
		
		$association->item_get($model, $term);
		$this->assertEquals(1, $term->vocabulary_id);

		$term = Jam::build('test_term', array('name' => 'Term1'));
		$this->assertNull($term->vocabulary_id);

		$association->item_set($model, $term);
		$this->assertEquals(1, $term->vocabulary_id);

	}

}

