<?php defined('SYSPATH') OR die('No direct script access.');

class Kohana_Jam_Association_Taxonomy_Terms extends Jam_Association_Collection {

	public $vocabulary = NULL;

	public $vocabulary_model = 'vocabulary';

	public $vocabulary_foreign_key;

	public $foreign_model = 'term';

	public $join_table_dependent = TRUE;

	public $item_key = 'item_id';

	public $item_polymorphic_key = 'item_model';

	public $term_key = 'term_id';

	public $join_table = 'terms_items';

	protected $_vocabulary_ids = NULL;

	public function initialize(Jam_Meta $meta, $name)
	{
		parent::initialize($meta, $name);

		if ( ! $this->vocabulary_foreign_key)
		{
			$this->vocabulary_foreign_key = Jam::meta($this->foreign_model)->association('vocabulary')->foreign_key;
		}
	}
	
	public function vocabulary_ids()
	{
		if ($this->vocabulary AND $this->_vocabulary_ids === NULL)
		{
			$this->_vocabulary_ids = Jam::all($this->vocabulary_model)->where(':name_key', 'IN', (array) $this->vocabulary)->ids();
		}
		return $this->_vocabulary_ids;
	}

	public function join($alias, $type = NULL)
	{
		$join = Jam_Query_Builder_Join::factory($this->join_table, $type)
			->context_model($this->model)
			->model($this->foreign_model)
			->on($this->join_table.'.'.$this->item_key, '=', ':primary_key')
			->on($this->join_table.'.'.$this->item_polymorphic_key, '=', DB::expr(':model', array(':model' => $this->model)));

		$join_nested = $join
			->join_table($alias ? array($this->foreign_model, $alias) : $this->foreign_model, $type)
				->context_model($this->model)
				->on(':primary_key', '=' , $this->join_table.'.'.$this->term_key);

		if ($this->vocabulary_ids())
		{
			$join_nested->on($this->vocabulary_foreign_key, 'IN', DB::expr(':ids', array(':ids' => $this->vocabulary_ids())));
		}

		return $join;
	}

	public function collection(Jam_Model $model)
	{
		$collection = Jam::all($this->foreign_model);

		$collection	
			->join_table($this->join_table)
				->context_model($this->foreign_model)
				->on($this->join_table.'.'.$this->term_key, '=', ':primary_key')
			->end()
			->where($this->join_table.'.'.$this->item_polymorphic_key, '=', DB::expr(':model', array(':model' => $model->meta()->model())))
			->where($this->join_table.'.'.$this->item_key, '=' , $model->id());

		if ($this->vocabulary_ids())
		{
			$collection->where($this->vocabulary_foreign_key, 'IN', $this->vocabulary_ids());
		}

		return $collection;
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
			->where($this->item_key, '=', $model->id())
			->where($this->item_polymorphic_key, '=', $this->model);
	}

	public function remove_items_query(Jam_Model $model, array $ids)
	{
		return DB::delete($this->join_table)
			->where($this->item_key, '=', $model->id())
			->where($this->item_polymorphic_key, '=', $this->model)
			->where($this->term_key, 'IN', $ids);
	}

	public function add_items_query(Jam_Model $model, array $ids)
	{
		$query = DB::insert($this->join_table)
			->columns(array($this->item_key, $this->item_polymorphic_key, $this->term_key));

		foreach ($ids as $id) 
		{
			$query->values(array($model->id(), $this->model, $id));
		}

		return $query;
	}

	public function item_set(Jam_Model $model, Jam_Model $item)
	{
		if ( ! $item->loaded() AND count($this->vocabulary_ids()) === 1)
		{
			$item->{$this->vocabulary_foreign_key} = current($this->vocabulary_ids());
		}
	}

	public function item_get(Jam_Model $model, Jam_Model $item)
	{
		if ( ! $item->loaded() AND ! $item->vocabulary_id AND count($this->vocabulary_ids()) === 1)
		{
			$item->{$this->vocabulary_foreign_key} = current($this->vocabulary_ids());
		}
	}
}
