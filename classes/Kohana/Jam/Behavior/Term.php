<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * @author     Ivan Kerin <ikerin@gmail.com>
 * @copyright  (c) 2014 Clippings Ltd.
 * @license    http://spdx.org/licenses/BSD-3-Clause
 */
class Kohana_Jam_Behavior_Term extends Jam_Behavior
{
	public function builder_call_vocabulary(Database_Query_Builder $query, Jam_Event_Data $data, $name)
	{
		$query
			->join('vocabulary')
			->where('vocabulary.:name_key', 'IN', (array) $name);
	}

	public function builder_call_visible(Database_Query_Builder $query, Jam_Event_Data $data, $is_visible = TRUE)
	{
		$query->where('is_hidden', '=', ! $is_visible);
	}

	public static function builder_call_slugs_children(Database_Query_Builder $builder, Jam_Event_Data $data, $slugs)
	{
		 $builder
			->join(array('parent', 'parent'), 'LEFT')
			->where_open()
				->or_where('term.slug', 'IN', (array) $slugs)
				->or_where('parent.slug', 'IN', (array) $slugs)
			->where_close();
	}
}
