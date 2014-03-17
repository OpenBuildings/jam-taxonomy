<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Unit test for Jam_Behavior_Taxonomable
 *
 * @package Jam
 * @group   jam-taxonomy
 * @group   jam-taxonomy.taxonomable
 */
class TaxonomableTest extends PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        Jam::create('taxonomy_event', array(
            'name' => 'abc',
            'terms' => array(
                array(
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
                    'name' => 'qwerty',
                    'slug' => 'qwerty',
                    'vocabulary' => 1,
                )
            ),
        ));
    }

    public static function tearDownAfterClass()
    {
        Jam::delete('taxonomy_event')
            ->where('name', 'IN', array(
                'abc',
                'xyz',
            ))
            ->execute();

        Jam::delete('term')
            ->where('slug', 'IN', array(
                'qwerty',
                'poiuy',
            ))
            ->execute();

        parent::tearDownAfterClass();
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
    }

    /**
     * @covers Jam_Behavior_Taxonomable::builder_call_with_terms
     */
    public function test_builder_method_with_terms()
    {
        $builder = Jam::all('taxonomy_event');
        $this->assertEquals(2, $builder->count_all());

        $this->assertEquals(1, $builder->with_terms('qwerty')->count_all());
        $builder->reset();

        $this->assertEquals(1, $builder->with_terms('poiuy')->count_all());
        $builder->reset();

        // Missing slugs are completely ignored and do not limit the query
        $this->assertEquals(2, $builder->with_terms('poiusadsfdsfy')->count_all());
    }
}
