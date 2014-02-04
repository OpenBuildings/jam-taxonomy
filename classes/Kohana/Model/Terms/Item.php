<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * @author     Ivan Kerin <ikerin@gmail.com>
 * @copyright  (c) 2014 Clippings Ltd.
 * @license    http://spdx.org/licenses/BSD-3-Clause
 */
class Kohana_Model_Terms_Item extends Jam_Model {

	public static function initialize(Jam_Meta $meta)
	{
		$meta
			->name_key('id');

		$meta->associations(array(
			'term' => Jam::association('belongsto'),
			'item' => Jam::association('belongsto', array('polymorphic' => TRUE)),
		));

		$meta->fields(array(
			'id' => Jam::field('primary'),
		));
	}
}
