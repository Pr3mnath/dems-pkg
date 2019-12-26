<?php

namespace Uitoux\EYatra;

use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Uitoux\EYatra\LocalTripVisitDetail;
use Validator;

class LocalTrip extends Model {
	use SoftDeletes;
	protected $table = 'local_trips';

	public function visitDetails() {
		return $this->hasMany('Uitoux\EYatra\LocalTripVisitDetail', 'trip_id');
	}

	public function employee() {
		return $this->belongsTo('Uitoux\EYatra\Employee')->withTrashed();
	}

	public function purpose() {
		return $this->belongsTo('Uitoux\EYatra\Entity', 'purpose_id');
	}

	public function status() {
		return $this->belongsTo('Uitoux\EYatra\Config', 'status_id');
	}

	public static function getLocalTripFormData($trip_id) {
		$data = [];
		if (!$trip_id) {
			$data['action'] = 'New';
			$trip = new LocalTrip;
			$trip->visit_details = [];
			$data['success'] = true;
		} else {
			$data['action'] = 'Edit';
			$data['success'] = true;

			// $trip = LocalTrip::find($trip_id);

			$trip = LocalTrip::withTrashed()->with([
				'visitDetails',
			])->find($trip_id);

			if (!$trip) {
				$data['success'] = false;
				$data['message'] = 'Trip not found';
			}
		}

		$grade = Auth::user()->entity;

		$data['extras'] = [
			'city_list' => NCity::getList(),
			'travel_mode_list' => Entity::join('local_travel_mode_category_type', 'local_travel_mode_category_type.travel_mode_id', 'entities.id')->select('entities.name', 'entities.id')->where('entities.company_id', Auth::user()->company_id)->where('entities.entity_type_id', 503)->get()->prepend(['id' => '', 'name' => 'Select Travel Mode']),
			'eligible_travel_mode_list' => DB::table('local_travel_mode_category_type')->where('category_id', 3561)->pluck('travel_mode_id')->toArray(),
			'purpose_list' => DB::table('grade_trip_purpose')->select('trip_purpose_id', 'entities.name', 'entities.id')->join('entities', 'entities.id', 'grade_trip_purpose.trip_purpose_id')->where('grade_id', $grade->grade_id)->where('entities.company_id', Auth::user()->company_id)->get()->prepend(['id' => '', 'name' => 'Select Purpose']),
		];
		$data['trip'] = $trip;

		$data['success'] = true;
		$data['eligible_date'] = $eligible_date = date("Y-m-d", strtotime("-10 days"));

		return response()->json($data);
	}

	public static function saveTrip($request) {
		try {
			//validation
			$validator = Validator::make($request->all(), [
				'purpose_id' => [
					'required',
				],
				'start_date' => [
					'required',
				],
				'end_date' => [
					'required',
				],

			]);
			if ($validator->fails()) {
				return response()->json([
					'success' => false,
					'message' => 'Validation Errors',
					'errors' => $validator->errors()->all(),
				]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$trip = new LocalTrip;
				$trip->created_by = Auth::user()->id;
				$trip->created_at = Carbon::now();
				$trip->updated_at = NULL;
				$activity['activity'] = "add";
				$trip->status_id = 3540; //Manager Approval Pending
			} else {
				$trip = LocalTrip::find($request->id);
				$trip->updated_by = Auth::user()->id;
				$trip->updated_at = Carbon::now();
				$trip_visit_details = LocalTripVisitDetail::where('trip_id', $request->id)->count();
				if ($trip->status_id == 3541) {
					$trip->status_id = 3543;
				} elseif ($trip->status_id == 3542) {
					$trip->status_id = 3540;
				} elseif ($trip->status_id == 3545) {
					$trip->status_id = 3543;
				} else {
					$trip->status_id = 3540;
				}
				LocalTripVisitDetail::where('trip_id', $request->id)->forceDelete();
				$activity['activity'] = "edit";

			}

			$employee = Employee::where('id', Auth::user()->entity->id)->first();
			$trip->purpose_id = $request->purpose_id;
			$trip->start_date = date('Y-m-d', strtotime($request->start_date));
			$trip->end_date = date('Y-m-d', strtotime($request->end_date));
			$trip->description = $request->description;
			$trip->employee_id = Auth::user()->entity->id;
			$trip->claim_amount = $request->claim_total_amount;
			$trip->save();

			$trip->number = 'TRP' . $trip->id;
			$trip->save();
			$activity['entity_id'] = $trip->id;
			$activity['entity_type'] = 'trip';
			$activity['details'] = 'Local Trip is Added';

			//SAVING VISITS DETAILS
			if ($request->trip_detail) {
				$visit_count = count($request->trip_detail);
				$i = 0;
				foreach ($request->trip_detail as $key => $visit_data) {
					$visit = new LocalTripVisitDetail;
					$visit->trip_id = $trip->id;
					$visit->travel_mode_id = $visit_data['travel_mode_id'];
					$visit->travel_date = date('Y-m-d', strtotime($visit_data['travel_date']));
					$visit->from_place = $visit_data['from_place'];
					$visit->to_place = $visit_data['to_place'];
					$visit->amount = $visit_data['amount'];
					$visit->extra_amount = $visit_data['extra_amount'];
					$visit->description = $visit_data['description'];
					$visit->created_by = Auth::user()->id;
					$visit->created_at = Carbon::now();
					$visit->save();
					$i++;
				}
			}
			DB::commit();

			if (empty($request->id)) {
				return response()->json(['success' => true, 'message' => 'Trip added successfully!', 'trip' => $trip]);
			} else {
				return response()->json(['success' => true, 'message' => 'Trip updated successfully!', 'trip' => $trip]);
			}

		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public static function getFilterData() {

		$data = [];
		$data['employee_list'] = collect(Employee::select(DB::raw('CONCAT(users.name, " / ", employees.code) as name'), 'employees.id')
				->leftJoin('users', 'users.entity_id', 'employees.id')
				->where('users.user_type_id', 3121)
				->where('employees.reporting_to_id', Auth::user()->entity_id)
				->where('employees.company_id', Auth::user()->company_id)
				->get())->prepend(['id' => '-1', 'name' => 'Select Employee Code/Name']);
		$data['purpose_list'] = collect(Entity::select('name', 'id')->where('entity_type_id', 501)->where('company_id', Auth::user()->company_id)->get())->prepend(['id' => '-1', 'name' => 'Select Purpose']);
		$data['trip_status_list'] = collect(Config::select('name', 'id')->where('config_type_id', 531)->where(DB::raw('LOWER(name)'), '!=', strtolower("New"))->get())->prepend(['id' => '-1', 'name' => 'Select Status']);

		$data['outlet_list'] = collect(Outlet::select('name', 'id')->get())->prepend(['id' => '-1', 'name' => 'Select Outlet']);

		$data['success'] = true;
		//dd($data);
		return response()->json($data);
	}

	public static function getViewData($trip_id) {
		$data = [];
		$trip = LocalTrip::with([
			'visitDetails' => function ($q) {
				$q->orderBy('local_trip_visit_details.travel_date');
			},
			'visitDetails.travelMode',
			'employee',
			'employee.user',
			'employee.manager',
			'employee.manager.user',
			'employee.user',
			'employee.designation',
			'employee.grade',
			'employee.grade.gradeEligibility',
			'purpose',
			'status',
		])
			->find($trip_id);

		if (!$trip) {
			$data['success'] = false;
			$data['message'] = 'Trip not found';
			$data['errors'] = ['Trip not found'];
			return response()->json($data);
		}

		$days = Trip::select(DB::raw('DATEDIFF(end_date,start_date)+1 as days'))->where('id', $trip_id)->first();
		$trip->days = $days->days;
		$trip->purpose_name = $trip->purpose->name;
		$trip->status_name = $trip->status->name;
		$current_date = strtotime(date('d-m-Y'));
		$claim_date = $trip->employee->grade ? $trip->employee->grade->gradeEligibility->claim_active_days : 5;

		$claim_last_date = strtotime("+" . $claim_date . " day", strtotime($trip->end_date));

		$trip_end_date = strtotime($trip->end_date);

		if ($current_date < $trip_end_date) {
			$data['claim_status'] = 0;
		} else {
			if ($current_date <= $claim_last_date) {
				$data['claim_status'] = 1;
			} else {
				$data['claim_status'] = 0;
			}
		}
		$data['trip'] = $trip;
		$data['success'] = true;

		return response()->json($data);

	}

}