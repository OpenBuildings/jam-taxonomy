<?php defined('SYSPATH') OR die('No direct script access.');

use PHPUnit\Framework\TestCase;

/**
 * Unit test for Jam_Behavior_Taxonomable
 *
 * @package Jam
 * @group   jam-taxonomy
 * @group   jam-taxonomy.taxonomable
 */
class TaxonomableTest extends TestCase {

    public function setUp()
    {
        parent::setUp();

        Database::instance()->begin();
    }

    public function tearDown()
    {
        Database::instance()->commit();

        parent::tearDown();
    }

    /**
     * @covers Jam_Behavior_Taxonomable::initialize
     */
    public function test_initialize()
    {
        $meta = Jam::meta('taxonomy_event');
        $this->assertInstanceOf(
            'Jam_Association_Taxonomy_Terms',
            $meta->association('terms')
        );
        $this->assertInstanceOf(
            'Jam_Association_Hasmany',
            $meta->association('terms_items')
        );
    }

    /**
     * @covers Jam_Behavior_Taxonomable::builder_call_with_terms
     */
    public function test_builder_method_with_terms()
    {
        Jam::create('taxonomy_event', array(
            'name' => 'abc',
            'terms' => array(
                array(
                    'id' => 100,
                    'name' => 'poiuy',
                    'slug' => 'poiuy',
                    'vocabulary' => 1,
                )
            ),
        ));

        Jam::create('taxonomy_event', array(
            'name' => 'xyz',
            'terms' => array(
                array(
                    'id' => 101,
                    'name' => 'qwerty',
                    'slug' => 'qwerty',
                    'vocabulary' => 1,
                )
            ),
        ));

        $builder = Jam::all('taxonomy_event');
        $this->assertEquals(
            'SELECT `taxonomy_events`.* FROM `taxonomy_events`',
            $builder
        );

        $builder->with_terms('qwerty');
        $this->assertEquals(
            "SELECT `taxonomy_events`.* FROM `taxonomy_events` JOIN `terms_items` AS `terms-101` ON (`terms-101`.`item_id` = `taxonomy_events`.`id` AND `terms-101`.`item_model` = 'taxonomy_event' AND `terms-101`.`term_id` IN (101))",
            (string) $builder
        );

        $builder->reset();
        $builder->with_terms('poiuy');
        $this->assertEquals(
            "SELECT `taxonomy_events`.* FROM `taxonomy_events` JOIN `terms_items` AS `terms-100` ON (`terms-100`.`item_id` = `taxonomy_events`.`id` AND `terms-100`.`item_model` = 'taxonomy_event' AND `terms-100`.`term_id` IN (100))",
            (string) $builder
        );

        $builder->reset();
        $builder->with_terms(array('qwerty', 'poiuy'));
        $this->assertEquals(
            "SELECT `taxonomy_events`.* FROM `taxonomy_events` JOIN `terms_items` AS `terms-100-101` ON (`terms-100-101`.`item_id` = `taxonomy_events`.`id` AND `terms-100-101`.`item_model` = 'taxonomy_event' AND `terms-100-101`.`term_id` IN (100,101))",
            (string) $builder
        );

        $builder->reset();
        $builder->with_terms(array('qwerty', 'poiuy'), 'NOT IN');
        $this->assertEquals(
            "SELECT `taxonomy_events`.* FROM `taxonomy_events` JOIN `terms_items` AS `terms-100-101` ON (`terms-100-101`.`item_id` = `taxonomy_events`.`id` AND `terms-100-101`.`item_model` = 'taxonomy_event' AND `terms-100-101`.`term_id` NOT IN (100,101))",
            (string) $builder
        );

        $builder->reset();
        $builder->with_terms('poiusadsfdsfy');

        // Missing slugs are completely ignored and do not limit the query
        $this->assertEquals(
            "SELECT `taxonomy_events`.* FROM `taxonomy_events`",
            (string) $builder
        );
    }
}
