<?php

namespace Uitoux\EYatra;

use Illuminate\Database\Eloquent\Model;

class Config extends Model {
	protected $fillable = [
		'id',
		'config_type_id',
		'name',
	];
	public $timestamps = false;

	public function configType() {
		return $this->belongsTo('App\ConfigType', 'config_type_id');
	}

	public static function expenseList() {
		return Config::where('config_type_id', 500)->select('id', 'name')->get()->keyBy('id');
	}

	public static function paymentModeList() {
		return Config::where('config_type_id', 514)->select('id', 'name')->get();
	}

	public static function walletModeList() {
		return Config::where('config_type_id', 515)->select('id', 'name')->get();
	}
}
