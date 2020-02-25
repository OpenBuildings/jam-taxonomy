<?php defined('SYSPATH') OR die('No direct script access.');

use PHPUnit\Framework\TestCase;

/**
 * Tests Manytomany fields.
 *
 * @package Jam
 * @group   jam-taxonomy
 * @group   jam-taxonomy.terms
 */
class TermsTest extends TestCase {

	public $meta;

	public function setUp()
	{
		parent::setUp();

		$this->meta = new Jam_Meta('taxonomy_post');
	}


	public function data_join()
	{
		return array(
			array(
				array('vocabulary_model' => 'vocabulary'), NULL, NULL,
				'JOIN `terms_items` ON (`terms_items`.`item_id` = `taxonomy_posts`.`id` AND `terms_items`.`item_model` = \'taxonomy_post\') JOIN `terms` ON (`terms`.`id` = `terms_items`.`term_id`)'
			),
			array(
				array('foreign_model' => 'term', 'term_key' => 'term_id', 'item_key' => 'item_id', 'item_polymorphic_key' => 'item_model', 'join_table' => 'terms_items', 'vocabulary_model' => 'vocabulary'), NULL, NULL,
				'JOIN `terms_items` ON (`terms_items`.`item_id` = `taxonomy_posts`.`id` AND `terms_items`.`item_model` = \'taxonomy_post\') JOIN `terms` ON (`terms`.`id` = `terms_items`.`term_id`)'
			),
			array(
				array('vocabulary' => 'Types', 'vocabulary_model' => 'vocabulary'), NULL, NULL,
				'JOIN `terms_items` ON (`terms_items`.`item_id` = `taxonomy_posts`.`id` AND `terms_items`.`item_model` = \'taxonomy_post\') JOIN `terms` ON (`terms`.`id` = `terms_items`.`term_id` AND `terms`.`vocabulary_id` IN (\'1\'))'
			),
			array(
				array('vocabulary' => array('Types', 'Styles'), 'vocabulary_model' => 'vocabulary'), NULL, NULL,
				'JOIN `terms_items` ON (`terms_items`.`item_id` = `taxonomy_posts`.`id` AND `terms_items`.`item_model` = \'taxonomy_post\') JOIN `terms` ON (`terms`.`id` = `terms_items`.`term_id` AND `terms`.`vocabulary_id` IN (\'1\', \'2\'))'
			),
			array(
				array('vocabulary_model' => 'vocabulary'), 'type_terms', NULL,
				'JOIN `terms_items` ON (`terms_items`.`item_id` = `taxonomy_posts`.`id` AND `terms_items`.`item_model` = \'taxonomy_post\') JOIN `terms` AS `type_terms` ON (`type_terms`.`id` = `terms_items`.`term_id`)'
			),
			array(
				array('vocabulary_model' => 'vocabulary'), 'type_terms', 'LEFT',
				'LEFT JOIN `terms_items` ON (`terms_items`.`item_id` = `taxonomy_posts`.`id` AND `terms_items`.`item_model` = \'taxonomy_post\') LEFT JOIN `terms` AS `type_terms` ON (`type_terms`.`id` = `terms_items`.`term_id`)'
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
				array('vocabulary_model' => 'vocabulary'), NULL, NULL,
				'SELECT `terms`.* FROM `terms` JOIN `terms_items` ON (`terms_items`.`term_id` = `terms`.`id`) WHERE `terms_items`.`item_model` = \'taxonomy_post\' AND `terms_items`.`item_id` = 1'
			),
			array(
				array('foreign_model' => 'term', 'term_key' => 'term_id', 'item_key' => 'item_id', 'item_polymorphic_key' => 'item_model', 'join_table' => 'terms_items', 'vocabulary_model' => 'vocabulary'), NULL, NULL,
				'SELECT `terms`.* FROM `terms` JOIN `terms_items` ON (`terms_items`.`term_id` = `terms`.`id`) WHERE `terms_items`.`item_model` = \'taxonomy_post\' AND `terms_items`.`item_id` = 1'
			),
			array(
				array('vocabulary' => 'Types', 'vocabulary_model' => 'vocabulary'), NULL, NULL,
				'SELECT `terms`.* FROM `terms` JOIN `terms_items` ON (`terms_items`.`term_id` = `terms`.`id`) WHERE `terms_items`.`item_model` = \'taxonomy_post\' AND `terms_items`.`item_id` = 1 AND `terms`.`vocabulary_id` IN (\'1\')'
			),
			array(
				array('vocabulary' => array('Types', 'Styles'), 'vocabulary_model' => 'vocabulary'), NULL, NULL,
				'SELECT `terms`.* FROM `terms` JOIN `terms_items` ON (`terms_items`.`term_id` = `terms`.`id`) WHERE `terms_items`.`item_model` = \'taxonomy_post\' AND `terms_items`.`item_id` = 1 AND `terms`.`vocabulary_id` IN (\'1\', \'2\')'
			),
			array(
				array('vocabulary_model' => 'vocabulary'), array(1, 2), array(1, 2),
				'SELECT `terms`.* FROM `terms` JOIN `terms_items` ON (`terms_items`.`term_id` = `terms`.`id`) WHERE `terms_items`.`item_model` = \'taxonomy_post\' AND `terms_items`.`item_id` = 1'
			),
			array(
				array('vocabulary_model' => 'vocabulary'), array(array('id' => 5), 2), array(5, 2),
				'SELECT `terms`.* FROM `terms` JOIN `terms_items` ON (`terms_items`.`term_id` = `terms`.`id`) WHERE `terms_items`.`item_model` = \'taxonomy_post\' AND `terms_items`.`item_id` = 1'
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

		$model = Jam::build('taxonomy_post')->load_fields(array('id' => 1));

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
				array('vocabulary_model' => 'vocabulary'),
				'DELETE FROM `terms_items` WHERE `item_id` = 1 AND `item_model` = \'taxonomy_post\''
			),
			array(
				array('foreign_model' => 'term', 'term_key' => 'term_id', 'item_key' => 'item_id', 'item_polymorphic_key' => 'item_model', 'join_table' => 'terms_items', 'vocabulary_model' => 'vocabulary'),
				'DELETE FROM `terms_items` WHERE `item_id` = 1 AND `item_model` = \'taxonomy_post\''
			),
			array(
				array('vocabulary' => 'Types', 'vocabulary_model' => 'vocabulary'),
				'DELETE FROM `terms_items` WHERE `item_id` = 1 AND `item_model` = \'taxonomy_post\''
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

		$model = Jam::build('taxonomy_post')->load_fields(array('id' => 1));

		$this->assertEquals($expected_sql, (string) $association->erase_query($model));
	}

	public function data_remove_items_query()
	{
		return array(
			array(
				array('vocabulary_model' => 'vocabulary'), array(1, 2, 3),
				'DELETE FROM `terms_items` WHERE `item_id` = 1 AND `item_model` = \'taxonomy_post\' AND `term_id` IN (1, 2, 3)'
			),
			array(
				array('foreign_model' => 'term', 'term_key' => 'term_id', 'item_key' => 'item_id', 'item_polymorphic_key' => 'item_model', 'join_table' => 'terms_items', 'vocabulary_model' => 'vocabulary'), array(1, 2, 3),
				'DELETE FROM `terms_items` WHERE `item_id` = 1 AND `item_model` = \'taxonomy_post\' AND `term_id` IN (1, 2, 3)'
			),
			array(
				array('vocabulary' => 'Types', 'vocabulary_model' => 'vocabulary'), array(1, 2, 3),
				'DELETE FROM `terms_items` WHERE `item_id` = 1 AND `item_model` = \'taxonomy_post\' AND `term_id` IN (1, 2, 3)'
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
		$model = Jam::build('taxonomy_post')->load_fields(array('id' => 1));

		$this->assertEquals($expected_sql, (string) $association->remove_items_query($model, $ids));
	}


	public function data_add_items_query()
	{
		return array(
			array(
				array('vocabulary_model' => 'vocabulary'), array(1, 2, 3),
				'INSERT INTO `terms_items` (`item_id`, `item_model`, `term_id`) VALUES (1, \'taxonomy_post\', 1), (1, \'taxonomy_post\', 2), (1, \'taxonomy_post\', 3)'
			),
			array(
				array('foreign_model' => 'term', 'term_key' => 'term_id', 'item_key' => 'item_id', 'item_polymorphic_key' => 'item_model', 'join_table' => 'terms_items', 'vocabulary_model' => 'vocabulary'), array(1, 2, 3),
				'INSERT INTO `terms_items` (`item_id`, `item_model`, `term_id`) VALUES (1, \'taxonomy_post\', 1), (1, \'taxonomy_post\', 2), (1, \'taxonomy_post\', 3)'
			),
			array(
				array('vocabulary' => 'Types', 'vocabulary_model' => 'vocabulary'), array(1, 2, 3),
				'INSERT INTO `terms_items` (`item_id`, `item_model`, `term_id`) VALUES (1, \'taxonomy_post\', 1), (1, \'taxonomy_post\', 2), (1, \'taxonomy_post\', 3)'
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

		$model = Jam::build('taxonomy_post')->load_fields(array('id' => 1));

		$this->assertEquals($expected_sql, (string) $association->add_items_query($model, $ids));
	}

	public function test_item_get_set()
	{
		$association = new Jam_Association_Taxonomy_Terms(array('vocabulary_model' => 'vocabulary', 'vocabulary' => 'Types'));
		$association->initialize($this->meta, 'terms');

		$model = Jam::build('taxonomy_post')->load_fields(array('id' => 1));

		$term = Jam::build('term', array('name' => 'Term1'));
		$this->assertNull($term->vocabulary_id);

		$this->assertEquals(array(1), $association->vocabulary_ids());

		$association->item_get($model, $term);
		$this->assertEquals(1, $term->vocabulary_id);

		$term = Jam::build('term', array('name' => 'Term1'));
		$this->assertNull($term->vocabulary_id);

		$association->item_set($model, $term);
		$this->assertEquals(1, $term->vocabulary_id);

	}

}

