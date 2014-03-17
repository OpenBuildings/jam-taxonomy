<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Tests Manytomany fields.
 *
 * @package Jam
 * @group   jam-taxonomy
 * @group   jam-taxonomy.items
 */
class ItemsTest extends PHPUnit_Framework_TestCase {

	public $meta;

	public function setUp()
	{
		parent::setUp();

		$this->meta = new Jam_Meta('term');
	}


	public function data_join()
	{
		return array(
			array(
				array(), NULL, NULL,
				'JOIN `terms_items` ON (`terms_items`.`item_id` = `terms`.`id` AND \'taxonomy_post\' = `terms_items`.`item_model`) JOIN `taxonomy_posts` ON (`taxonomy_posts`.`id` = `terms_items`.`term_id`)'
			),
			array(
				array('foreign_model' => 'taxonomy_author', 'term_key' => 'term_id', 'item_key' => 'item_id', 'item_polymorphic_key' => 'item_model', 'join_table' => 'terms_items'), NULL, NULL,
				'JOIN `terms_items` ON (`terms_items`.`item_id` = `terms`.`id` AND \'taxonomy_author\' = `terms_items`.`item_model`) JOIN `taxonomy_authors` ON (`taxonomy_authors`.`id` = `terms_items`.`term_id`)'
			),
			array(
				array(), 'type_items', NULL,
				'JOIN `terms_items` ON (`terms_items`.`item_id` = `terms`.`id` AND \'taxonomy_post\' = `terms_items`.`item_model`) JOIN `taxonomy_posts` AS `type_items` ON (`type_items`.`id` = `terms_items`.`term_id`)'
			),
			array(
				array(), 'type_items', 'LEFT',
				'LEFT JOIN `terms_items` ON (`terms_items`.`item_id` = `terms`.`id` AND \'taxonomy_post\' = `terms_items`.`item_model`) LEFT JOIN `taxonomy_posts` AS `type_items` ON (`type_items`.`id` = `terms_items`.`term_id`)'
			),
		);
	}

	/**
	 * @dataProvider data_join
	 */
	public function test_join($options, $alias, $type, $expected_sql)
	{
		$association = new Jam_Association_Taxonomy_Items($options);
		$association->initialize($this->meta, 'taxonomy_posts');

		$this->assertEquals($expected_sql, (string) $association->join($alias, $type));
	}

	public function data_get()
	{
		return array(
			array(
				array(), NULL, NULL,
				'SELECT `taxonomy_posts`.* FROM `taxonomy_posts` JOIN `terms_items` ON (`terms_items`.`item_model` = \'taxonomy_post\' AND `terms_items`.`item_id` = `taxonomy_posts`.`id`) WHERE `terms_items`.`term_id` = 1'
			),
			array(
				array('foreign_model' => 'taxonomy_author', 'term_key' => 'term_id', 'item_key' => 'item_id', 'item_polymorphic_key' => 'item_model', 'join_table' => 'terms_items'), NULL, NULL,
				'SELECT `taxonomy_authors`.* FROM `taxonomy_authors` JOIN `terms_items` ON (`terms_items`.`item_model` = \'taxonomy_author\' AND `terms_items`.`item_id` = `taxonomy_authors`.`id`) WHERE `terms_items`.`term_id` = 1'
			),
			array(
				array(), array(1, 2), array(1, 2),
				'SELECT `taxonomy_posts`.* FROM `taxonomy_posts` JOIN `terms_items` ON (`terms_items`.`item_model` = \'taxonomy_post\' AND `terms_items`.`item_id` = `taxonomy_posts`.`id`) WHERE `terms_items`.`term_id` = 1'
			),
			array(
				array(), array(array('id' => 5), 2), array(5, 2),
				'SELECT `taxonomy_posts`.* FROM `taxonomy_posts` JOIN `terms_items` ON (`terms_items`.`item_model` = \'taxonomy_post\' AND `terms_items`.`item_id` = `taxonomy_posts`.`id`) WHERE `terms_items`.`term_id` = 1'
			),
		);
	}

	/**
	 * @dataProvider data_get
	 */
	public function test_get($options, $value, $expected_ids, $expected_sql)
	{
		$association = new Jam_Association_Taxonomy_Items($options);
		$association->initialize($this->meta, 'taxonomy_posts');

		$model = Jam::build('term')->load_fields(array('id' => 1));

		$result = $association->get($model, $value, (bool) $value);

		$this->assertInstanceOf('Jam_Array_Association', $result);

		$this->assertEquals($expected_sql, $result->__toString());

		if ($expected_ids !== NULL)
		{
			$this->assertEquals($expected_ids, $result->ids());
		}
	}


	public function data_erase_query()
	{
		return array(
			array(
				array(),
				'DELETE FROM `terms_items` WHERE `term_id` = 1'
			),
			array(
				array('foreign_model' => 'taxonomy_author', 'term_key' => 'term_id', 'item_key' => 'item_id', 'item_polymorphic_key' => 'item_model', 'join_table' => 'terms_items'),
				'DELETE FROM `terms_items` WHERE `term_id` = 1'
			),
		);
	}

	/**
	 * @dataProvider data_erase_query
	 */
	public function test_erase_query($options, $expected_sql)
	{
		$association = new Jam_Association_Taxonomy_Items($options);
		$association->initialize($this->meta, 'taxonomy_posts');

		$model = Jam::build('term')->load_fields(array('id' => 1));

		$this->assertEquals($expected_sql, (string) $association->erase_query($model));
	}

	public function data_remove_items_query()
	{
		return array(
			array(
				array(), array(1, 2, 3),
				'DELETE FROM `terms_items` WHERE `term_id` = 1 AND `item_model` = \'taxonomy_post\' AND `item_id` IN (1, 2, 3)'
			),
			array(
				array('foreign_model' => 'taxonomy_author', 'term_key' => 'term_id', 'item_key' => 'item_id', 'item_polymorphic_key' => 'item_model', 'join_table' => 'terms_items'), array(1, 2, 3),
				'DELETE FROM `terms_items` WHERE `term_id` = 1 AND `item_model` = \'taxonomy_author\' AND `item_id` IN (1, 2, 3)'
			),
		);
	}

	/**
	 * @dataProvider data_remove_items_query
	 */
	public function test_remove_items_query($options, $ids, $expected_sql)
	{
		$association = new Jam_Association_Taxonomy_Items($options);
		$association->initialize($this->meta, 'taxonomy_posts');
		$model = Jam::build('term')->load_fields(array('id' => 1));

		$this->assertEquals($expected_sql, (string) $association->remove_items_query($model, $ids));
	}


	public function data_add_items_query()
	{
		return array(
			array(
				array(), array(1, 2, 3),
				'INSERT INTO `terms_items` (`term_id`, `item_model`, `item_id`) VALUES (1, \'taxonomy_post\', 1), (1, \'taxonomy_post\', 2), (1, \'taxonomy_post\', 3)'
			),
			array(
				array('foreign_model' => 'taxonomy_author', 'term_key' => 'term_id', 'item_key' => 'item_id', 'item_polymorphic_key' => 'item_model', 'join_table' => 'terms_items'), array(1, 2, 3),
				'INSERT INTO `terms_items` (`term_id`, `item_model`, `item_id`) VALUES (1, \'taxonomy_author\', 1), (1, \'taxonomy_author\', 2), (1, \'taxonomy_author\', 3)'
			),
		);
	}

	/**
	 * @dataProvider data_add_items_query
	 */
	public function test_add_items_query($options, $ids, $expected_sql)
	{
		$association = new Jam_Association_Taxonomy_Items($options);
		$association->initialize($this->meta, 'taxonomy_posts');

		$model = Jam::build('term')->load_fields(array('id' => 1));

		$this->assertEquals($expected_sql, (string) $association->add_items_query($model, $ids));
	}
}

