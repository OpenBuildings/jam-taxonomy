<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * I am a taxonamable behavior.
 * Attach me to models you want to have the `terms` association
 * and the `with_terms()` builder method.
 *
 * @author Haralan Dobrev <hkdobrev@gmail.com>
 * @copyright 2014 Clippings Ltd.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class Kohana_Jam_Behavior_Taxonomable extends Jam_Behavior {

    protected $_terms_association_name = 'terms';

    protected $_terms_association_options = array();

    protected $_terms_items_association_name = 'terms_items';

    protected $_terms_items_association_options = array(
        'as' => 'item',
        'foreign_model' => 'terms_item'
    );

    public function initialize(Jam_Meta $meta, $name)
    {
        parent::initialize($meta, $name);

        $meta
            ->association(
                $this->_terms_association_name,
                Jam::association('taxonomy_terms', $this->_terms_association_options)
            )
            ->association(
                $this->_terms_items_association_name,
                Jam::association('hasmany', $this->_terms_items_association_options)
            );
    }

    public function builder_call_with_terms(Database_Query $builder, Jam_Event_Data $data, $term_slugs, $operator = 'IN', $nesting_level = 1)
    {
        if ($term_slugs)
        {
            if ( ! ($term_slugs instanceof Jam_Query_Builder_Collection)
             AND ! ($term_slugs instanceof Jam_Array_Association))
            {
                $term_slugs = Jam::all('term')
                    ->slugs_children($term_slugs)
                    ->order_by('id');
            }

            $terms = $term_slugs->as_array('id', 'slug');
            $terms_ids = array_keys($terms);

            if ($nesting_level > 1)
            {
                $terms = Jam::all('term')
                    ->slugs_children(array_values($terms))
                    ->order_by('id');

                $terms_ids = Arr::merge($terms_ids, $terms->ids());
            }

            if ($terms_ids)
            {
                $unique_alias = 'terms-'.join('-', $terms_ids);

                $builder
                    ->join(array('terms_items', $unique_alias))
                    ->on(
                        $unique_alias.'.term_id',
                        $operator,
                        DB::expr("(".join(',', $terms_ids).')')
                    );
            }
        }

        return $builder;
    }
}
