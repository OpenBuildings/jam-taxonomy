<?php defined('SYSPATH') OR die('No direct script access.');

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
}