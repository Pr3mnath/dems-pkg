<?php

namespace Uitoux\EYatra;

use Auth;
use Carbon\Carbon;
use DB;
use Entrust;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Validator;

class Trip extends Model {
	use SoftDeletes;

	protected $fillable = [
		'id',
		'number',
		'employee_id',
		'purpose_id',
		'description',
		'status_id',
		'advance_received',
		'claim_amount',
		'claimed_date',
		'paid_amount',
		'payment_date',
		'created_by',
	];

	public function getCreatedAtAttribute($value) {
		return empty($value) ? '' : date('d-m-Y', strtotime($value));
	}

	public function company() {
		return $this->belongsTo('App\Company');
	}

	public function visits() {
		return $this->hasMany('Uitoux\EYatra\Visit');
	}

	public function advanceRequestPayment() {
		return $this->hasOne('Uitoux\EYatra\Payment', 'entity_id')->where('payment_of_id', 3250); //Employee Advance Claim
	}

	public function employee() {
		return $this->belongsTo('Uitoux\EYatra\Employee');
	}

	public function purpose() {
		return $this->belongsTo('Uitoux\EYatra\Entity', 'purpose_id');
	}

	public function status() {
		return $this->belongsTo('Uitoux\EYatra\Config', 'status_id');
	}

	public function lodgings() {
		return $this->hasMany('Uitoux\EYatra\Lodging');
	}

	public function boardings() {
		return $this->hasMany('Uitoux\EYatra\Boarding');
	}

	public function localTravels() {
		return $this->hasMany('Uitoux\EYatra\LocalTravel');
	}

	public static function create($employee, $trip_number, $faker, $trip_status_id, $admin) {
		$trip = new Trip();
		$trip->employee_id = $employee->id;
		$trip->number = 'TRP' . $trip_number++;
		$trip->purpose_id = $employee->grade->tripPurposes()->inRandomOrder()->first()->id;
		$trip->description = $faker->sentence;
		$trip->manager_id = $employee->reporting_to_id;
		$trip->status_id = $trip_status_id; //NEW
		$trip->advance_received = $faker->randomElement([0, 500, 100, 1500, 2000]);
		$trip->created_by = $admin->id;
		$trip->save();
		return $trip;

	}

	public static function saveTrip($request) {
		try {
			//validation
			$validator = Validator::make($request->all(), [
				'purpose_id' => [
					'required',
				],
			]);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$trip = new Trip;
				$trip->created_by = Auth::user()->id;
				$trip->created_at = Carbon::now();
				$trip->updated_at = NULL;

			} else {
				$trip = Trip::find($request->id);

				$trip->updated_by = Auth::user()->id;
				$trip->updated_at = Carbon::now();

				$trip->visits()->sync([]);

			}
			$trip->fill($request->all());
			$trip->number = 'TRP' . rand();
			$trip->employee_id = Auth::user()->entity->id;
			// dd(Auth::user(), );
			$trip->manager_id = Auth::user()->entity->reporting_to_id;
			$trip->status_id = 3020; //NEW
			$trip->save();

			$trip->number = 'TRP' . $trip->id;
			$trip->save();

			//SAVING VISITS
			if ($request->visits) {
				$visit_count = count($request->visits);
				$i = 0;
				foreach ($request->visits as $key => $visit_data) {
					//if no agent found display visit count
					// dd(Auth::user()->entity->outlet->address);

					$visit_count = $i + 1;
					if ($i == 0) {
						$from_city_id = Auth::user()->entity->outlet->address->city->id;
					} else {
						$previous_value = $request->visits[$key - 1];
						$from_city_id = $previous_value['to_city_id'];
					}
					$visit = new Visit;
					$visit->fill($visit_data);
					$visit->from_city_id = $from_city_id;
					$visit->trip_id = $trip->id;
					$visit->booking_method_id = $visit_data['booking_method'] == 'Self' ? 3040 : 3042;
					$visit->booking_status_id = 3060; //PENDING
					$visit->status_id = 3220; //NEW
					$visit->manager_verification_status_id = 3080; //NEW
					if ($visit_data['booking_method'] == 'Agent') {
						$state = $trip->employee->outlet->address->city->state;

						$agent = $state->agents()->withPivot('travel_mode_id')->where('travel_mode_id', $visit_data['travel_mode_id'])->first();

						if (!$agent) {
							return response()->json(['success' => false, 'errors' => ['No agent found for visit - ' . $visit_count]]);
						}
						$visit->agent_id = $agent->id;
					}
					$visit->save();
					$i++;
				}
			}
			DB::commit();
			return response()->json(['success' => true, 'message' => 'Trip saved successfully!']);
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public static function getViewData($trip_id) {
		$data = [];
		$trip = Trip::with([
			'visits',
			'visits.fromCity',
			'visits.toCity',
			'visits.travelMode',
			'visits.bookingMethod',
			'visits.bookingStatus',
			'visits.agent',
			'visits.status',
			'visits.managerVerificationStatus',
			'employee',
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

		if (!Entrust::can('view-all-trips') && $trip->employee_id != Auth::user()->entity_id) {
			$data['success'] = false;
			$data['message'] = 'Trip belongs to you';
			$data['errors'] = ['Trip belongs to you'];
			return response()->json($data);
		}

		$start_date = $trip->visits()->select(DB::raw('DATE_FORMAT(MIN(visits.date),"%d/%m/%Y") as start_date'))->first();
		$end_date = $trip->visits()->select(DB::raw('DATE_FORMAT(MAX(visits.date),"%d/%m/%Y") as end_date'))->first();
		$days = $trip->visits()->select(DB::raw('DATEDIFF(MAX(visits.date),MIN(visits.date)) as days'))->first();
		$trip->start_date = $start_date->start_date;
		$trip->end_date = $end_date->end_date;
		$trip->days = $days->days;
		$data['trip'] = $trip;
		$data['success'] = true;
		return response()->json($data);

	}

	public static function getTripFormData($trip_id) {
		$data = [];
		if (!$trip_id) {
			$data['action'] = 'New';
			$trip = new Trip;
			$visit = new Visit;
			$visit->booking_method = 'Self';
			$trip->visits = [$visit];
			$data['success'] = true;
		} else {
			$data['action'] = 'Edit';
			$trip = Trip::find($trip_id);
			if (!$trip) {
				$data['success'] = false;
				$data['message'] = 'Trip not found';
			}
		}
		$grade = Auth::user()->entity;
		$grade_eligibility = DB::table('grade_advanced_eligibility')->select('advanced_eligibility')->where('grade_id', $grade->grade_id)->first();
		if ($grade_eligibility) {
			$data['employee_eligible_grade'] = $grade_eligibility;
		} else {
			$data['employee_eligible_grade'] = '';
		}

		$data['extras'] = [
			// 'purpose_list' => Entity::uiPurposeList(),
			'purpose_list' => DB::table('grade_trip_purpose')->select('trip_purpose_id', 'entities.name', 'entities.id')->join('entities', 'entities.id', 'grade_trip_purpose.trip_purpose_id')->where('grade_id', $grade->grade_id)->where('entities.company_id', Auth::user()->company_id)->get(),
			// 'travel_mode_list' => Entity::uiTravelModeList(),
			'travel_mode_list' => DB::table('grade_travel_mode')->select('travel_mode_id', 'entities.name', 'entities.id')->join('entities', 'entities.id', 'grade_travel_mode.travel_mode_id')->where('grade_id', $grade->grade_id)->where('entities.company_id', Auth::user()->company_id)->get(),
			'city_list' => NCity::getList(),
			'employee_city' => Auth::user()->entity->outlet->address->city,
		];
		$data['trip'] = $trip;

		return response()->json($data);
	}

	public static function getVerficationPendingList() {
		$trips = Trip::from('trips')
			->join('visits as v', 'v.trip_id', 'trips.id')
			->join('ncities as c', 'c.id', 'v.from_city_id')
			->join('employees as e', 'e.id', 'trips.employee_id')
			->join('entities as purpose', 'purpose.id', 'trips.purpose_id')
			->join('configs as status', 'status.id', 'trips.status_id')
			->select(
				'trips.id',
				'trips.number',
				'e.code as ecode',
				DB::raw('GROUP_CONCAT(DISTINCT(c.name)) as cities'),
				DB::raw('DATE_FORMAT(MIN(v.date),"%d/%m/%Y") as start_date'),
				DB::raw('DATE_FORMAT(MAX(v.date),"%d/%m/%Y") as end_date'),
				'purpose.name as purpose',
				'trips.advance_received',
				'trips.created_at',
				//DB::raw('DATE_FORMAT(trips.created_at,"%d/%m/%Y") as created_at'),
				'status.name as status'

			)
			->where('trips.status_id', 3021) //MANAGER APPROVAL PENDING
			->groupBy('trips.id')
			->orderBy('trips.created_at', 'desc')
			->orderBy('trips.status_id', 'desc')
		;

		if (!Entrust::can('trip-verification-all')) {
			$trips->where('trips.manager_id', Auth::user()->entity_id);
		}

		return $trips;
	}

	public static function saveTripVerification($r) {
		$trip = Trip::find($r->trip_id);
		if (!$trip) {
			return response()->json(['success' => false, 'errors' => ['Trip not found']]);
		}

		if (!Entrust::can('trip-verification-all') && $trip->manager_id != Auth::user()->entity_id) {
			return response()->json(['success' => false, 'errors' => ['You are nor authorized to view this trip']]);
		}

		$trip->status_id = 3021;
		$trip->save();

		$trip->visits()->update(['manager_verification_status_id' => 3080]);
		return response()->json(['success' => true]);
	}
}
