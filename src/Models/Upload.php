<?php

namespace Sanil\FileManager\Models;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model {
	protected $fillable = [
		'upload_path',
		'ext',
		'disk',
		'mime_type',
		'collection',
		'size',
		'tags'
	];

	public function uploadable() {
		return $this->morphedByMany(Model::class, 'uploadable', 'uploadables');
	}
}
