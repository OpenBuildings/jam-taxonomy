<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * @author     Ivan Kerin <ikerin@gmail.com>
 * @copyright  (c) 2014 Clippings Ltd.
 * @license    http://spdx.org/licenses/BSD-3-Clause
 */
class Kohana_Jam_Association_Taxonomy_Items extends Jam_Association_Collection {

	public $join_table_dependent = TRUE;

	public $item_key = 'item_id';

	public $item_polymorphic_key = 'item_model';

	public $term_key = 'term_id';

	public $join_table = 'terms_items';

	public function join($alias, $type = NULL)
	{
		return Jam_Query_Builder_Join::factory($this->join_table, $type)
			->context_model($this->model)
			->model($this->foreign_model)
			->on($this->join_table.'.'.$this->item_key, '=', ':primary_key')
			->on(DB::expr(':model', array(':model' => $this->foreign_model)), '=' , $this->join_table.'.'.$this->item_polymorphic_key)
			->join_table($alias ? array($this->foreign_model, $alias) : $this->foreign_model, $type)
				->on(':primary_key', '=' , $this->join_table.'.'.$this->term_key)
				->context_model($this->model)
			->end();
	}

	public function collection(Jam_Model $model)
	{
		$collection = Jam::all($this->foreign_model);

		return $collection
			->join_table($this->join_table)
				->context_model($this->foreign_model)
				->on($this->join_table.'.'.$this->item_polymorphic_key, '=', DB::expr(':model', array(':model' => $this->foreign_model)))
				->on($this->join_table.'.'.$this->item_key, '=', ':primary_key')
			->end()
			->where($this->join_table.'.'.$this->term_key, '=' , $model->id());
	}

	public function model_after_delete(Jam_Model $model)
	{
		if ($model->loaded() AND $this->join_table_dependent)
		{
			$this->erase_query($model)
				->execute(Jam::meta($this->model)->db());
		}
	}

	public function erase_query(Jam_Model $model)
	{
		return DB::delete($this->join_table)
			->where($this->term_key, '=', $model->id());
	}

	public function remove_items_query(Jam_Model $model, array $ids)
	{
		return DB::delete($this->join_table)
			->where($this->term_key, '=', $model->id())
			->where($this->item_polymorphic_key, '=', $this->foreign_model)
			->where($this->item_key, 'IN', $ids);
	}

	public function add_items_query(Jam_Model $model, array $ids)
	{
		$query = DB::insert($this->join_table)
			->columns(array($this->term_key, $this->item_polymorphic_key, $this->item_key));

		foreach ($ids as $id)
		{
			$query->values(array($model->id(), $this->foreign_model, $id));
		}

		return $query;
	}
}
