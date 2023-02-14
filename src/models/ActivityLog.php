<?php

namespace Uitoux\EYatra;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Model;
use Uitoux\EYatra\Config;

class ActivityLog extends Model {
	public $timestamps = false;

	public function user() {
		return $this->hasOne('App\User', 'id', 'user_id')->withTrashed();
	}
	protected $appends = ['log_date'];
	public function getLogDateAttribute() {
		return !empty($this->date_time) ? date('d-m-Y h:m:i A', strtotime($this->date_time)) : '';
	}

	public static function saveLog($data) {
		$activities_config_type = DB::table('config_types')->where(DB::raw('name'), 'Activity Log Activities - EYatra')->first();
		$entities_config_type = DB::table('config_types')->where(DB::raw('name'), 'Activity Log Entity Types - EYatra')->first();
		// dd($entities_config_type->name);
		$entity_type_data = Config::where('config_type_id', $entities_config_type->id)->where(DB::raw('LOWER(name)'), $data['entity_type'])->first();

		$activity_data = Config::where('config_type_id', $activities_config_type->id)->where(DB::raw('LOWER(name)'), strtolower($data['activity']))->first();
		//dd($data, $activities_config_type, $entities_config_type, $entity_type_data, $activity_data, strtolower($data['entity_type']));
		// dd($entity_type_data, $entities_config_type->id, $data['entity_type']);
		$activity = new ActivityLog;
		$activity->date_time = date("Y-m-d H:i:s");
		$activity->user_id = Auth::user()->id;
		$activity->entity_id = $data['entity_id'];
		$activity->entity_type_id = $entity_type_data->id;
		$activity->activity_id = $activity_data->id;
		$activity->details = $data['details'];
		$activity->save();

	}
}
