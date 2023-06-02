<?php

namespace Uitoux\EYatra;
use App\Http\Controllers\Controller;
use Auth;
use Entrust;
use Illuminate\Http\Request;
use Session;
use Uitoux\EYatra\Business;
use Uitoux\EYatra\EmployeeClaim;
use Uitoux\EYatra\LocalTrip;
use Uitoux\EYatra\Outlet;
use Uitoux\EYatra\PettyCash;
use Uitoux\EYatra\Trip;

class DashboardController extends Controller {
	public function getDEMSDashboardData(Request $request) {

		if ($request->selected_year) {
			session(['fyc_year_session' => $request->selected_year]);
			$fyc_year_session = $request->selected_year;
		} else {
			if (date('m') >= 4) {
				$start_year = date('Y');
				$end_year = date('Y', strtotime('+1 year'));
			} else {
				$start_year = date('Y', strtotime('-1 year'));
				$end_year = date('Y');
			}

			$fyc_year_session = session('fyc_year_session');
			if (!$fyc_year_session) {
				$fyc_year_session = $start_year . '-' . $end_year;
			}

		}
		if ($request->selected_month) {
			session(['fyc_month_session' => $request->selected_month]);
			$fyc_month_session = $request->selected_month;
		} else {
			$fyc_month_session = session('fyc_month_session');
			if (!$fyc_month_session) {
				$fyc_month_session = date('m');
			}

		}
		if ($request->selected_outlet) {
			session(['outlet_session' => $request->selected_outlet]);
			$outlet_id = $request->selected_outlet;
		} else {
			session(['outlet_session' => '']);
			$outlet_id = '';
		}
		if ($request->selected_business) {
			session(['business_session' => $request->selected_business]);
			$business_id = $request->selected_business;
		} else {
			session(['business_session' => '']);
			$business_id = '';
		}

		if ($request->selected_date_range) {
			session(['date_range_session' => $request->selected_date_range]);
			$selected_date_range = $request->selected_date_range;
		} else {
			session(['date_range_session' => '']);
			$selected_date_range = '';
		}

		$this->data['current_fyc'] = $fyc_year_session;
		$this->data['fyc_year_session'] = $fyc_year_session;
		$this->data['fyc_month_session'] = $fyc_month_session;

		$this->data['business_show'] = '0';
		$this->data['business_list'] = [];
		//ADMIN
		if (Entrust::can('eyatra-masters')) {
			$this->data['outlet_list'] = collect(Outlet::get())->prepend(['id' => '', 'name' => 'All Outlet']);
			$this->data['outlet_show'] = '1';
			$this->data['business_list'] = collect(Business::get())->prepend(['id' => '', 'name' => 'All Business']);
			$this->data['business_show'] = '1';
		} else {
			$this->data['outlet_list'] = collect(Outlet::join('employees', 'employees.outlet_id', 'outlets.id')->where('employees.id', Auth::user()->entity_id)->get());
			$this->data['outlet_show'] = '0';
		}

		$selected_year = explode('-', $fyc_year_session);

		// if ($fyc_month_session && $fyc_month_session != '-1') {

		// 	$split_year = explode('-', $fyc_year_session);
		// 	$first_year = $split_year[0];
		// 	$second_year = $split_year[1];

		// 	if ($fyc_month_session <= 3) {
		// 		$start_date = date($second_year . '-' . $fyc_month_session . '-01');
		// 	} else {
		// 		$start_date = date($first_year . '-' . $fyc_month_session . '-01');
		// 	}

		// 	$end_date = date('Y-m-t', strtotime($start_date));

		// } else {
		$start_date = $selected_year[0] . '-04-01';
		$end_date = $selected_year[1] . '-03-31';

		// }

		$result = $this->trip_details($start_date, $end_date, $outlet_id, $business_id, $selected_date_range);

		$this->data['total_outstation_trips'] = $total_outstation_trips = $result['total_outstation_trips']->count();
		$this->data['outstation_total_trip_claim'] = $outstation_total_trip_claim = $result['outstation_total_trip_claim']->count();
		$this->data['outstation_total_payment_requested'] = $outstation_total_payment_requested = $result['outstation_total_payment_requested']->count();
		$this->data['total_outstation_ready_for_claim'] = $total_outstation_ready_for_claim = $result['total_outstation_ready_for_claim']->count();
		$this->data['total_outstation_upcoming_trips'] = $total_outstation_upcoming_trips = $result['total_outstation_upcoming_trips']->count();
		$this->data['total_outstation_advance_trips'] = $total_outstation_advance_trips = $result['total_outstation_advance_trips']->count();

		// dd($this->data);
		$this->data['outstation_trip_claim_pending'] = $outstation_trip_claim_pending = $total_outstation_trips - $outstation_total_trip_claim;
		$this->data['total_local_trip_count'] = $total_local_trips = $result['total_local_trips']->count();
		$this->data['claimed_local_trips_count'] = $total_local_trip_claim = $result['total_local_trip_claim']->count();
		$this->data['claim_requested_local_trips_count'] = $total_local_trip_claim_requested = $result['total_local_trip_claim_requested']->count();
		$this->data['ready_for_claimed_local_trips_count'] = $total_local_trip_ready_for_claim = $result['total_local_trip_ready_for_claim']->count();
		$this->data['upcoming_local_trips_count'] = $total_upcoming_local_trips = $result['total_upcoming_local_trips']->count();

		$this->data['total_petty_cash_count'] = $total_petty_cash = $result['total_petty_cash']->count();
		$this->data['claimed_petty_cash_count'] = $total_petty_cash_claim = $result['total_petty_cash_claim']->count();
		$this->data['not_claimed_petty_cash_count'] = $total_petty_cash_claim_pending = $total_petty_cash - $total_petty_cash_claim;

		$total_petty_cash_claim_pending_amount = ($result['total_petty_cash']->sum('petty_cash.total') - $result['total_petty_cash_claim']->sum('petty_cash.total'));

		//OUTSTATION TRIP
		if ($total_outstation_trips > 0) {
			if ($outstation_total_trip_claim > 0) {
				$this->data['outstation_total_trip_claim_percen'] = number_format((float) (($outstation_total_trip_claim / $total_outstation_trips) * 100), 2, '.', '') . "%";
				$this->data['total_claimed_outstation_trip_amount'] = ($result['outstation_total_trip_claim']->sum('trips.claim_amount')) > 0 ? '₹ ' . number_format($result['outstation_total_trip_claim']->sum('trips.claim_amount'), 2) : '--';
			} else {
				$this->data['outstation_total_trip_claim_percen'] = number_format((float) $outstation_total_trip_claim, 2, '.', '') . "%";
				$this->data['total_claimed_outstation_trip_amount'] = ($result['outstation_total_trip_claim']->sum('trips.claim_amount')) > 0 ? '₹ ' . number_format($result['outstation_total_trip_claim']->sum('trips.claim_amount'), 2) : '--';
			}
			$this->data['outstation_trip_claim_pending_percen'] = number_format((float) (($outstation_trip_claim_pending / $total_outstation_trips) * 100), 2, '.', '') . "%";

			$this->data['outstation_trip_payment_requested_per'] = number_format((float) (($outstation_total_payment_requested / $total_outstation_trips) * 100), 2, '.', '') . "%";
			$this->data['outstation_trip_payment_requested_amount'] = ($result['outstation_total_payment_requested']->sum('trips.claim_amount')) > 0 ? '₹ ' . number_format($result['outstation_total_payment_requested']->sum('trips.claim_amount'), 2) : '--';
			$this->data['outstation_trip_ready_for_claim_per'] = number_format((float) (($total_outstation_ready_for_claim / $total_outstation_trips) * 100), 2, '.', '') . "%";
			$this->data['outstation_trip_ready_for_claim_amount'] = ($result['total_outstation_ready_for_claim']->sum('trips.claim_amount')) > 0 ? '₹ ' . number_format($result['total_outstation_ready_for_claim']->sum('trips.claim_amount'), 2) : '--';
			$this->data['outstation_upcoming_trip_per'] = number_format((float) (($total_outstation_upcoming_trips / $total_outstation_trips) * 100), 2, '.', '') . "%";
			$this->data['outstation_upcoming_trip_amount'] = ($result['total_outstation_upcoming_trips']->sum('trips.claim_amount')) > 0 ? '₹ ' . number_format($result['total_outstation_upcoming_trips']->sum('trips.claim_amount'), 2) : '--';
			$this->data['outstation_advance_trip_per'] = number_format((float) (($total_outstation_advance_trips / $total_outstation_trips) * 100), 2, '.', '') . "%";
			$this->data['outstation_advance_trip_amount'] = ($result['total_outstation_advance_trips']->sum('trips.advance_received')) > 0 ? '₹ ' . number_format($result['total_outstation_advance_trips']->sum('trips.advance_received'), 2) : '--';

			$this->data['total_outstation_trip_percen'] = number_format((float) 100, 2, '.', '') . "%";
			$this->data['total_outstation_trip_amount'] = ($result['total_outstation_trips']->sum('trips.claim_amount')) > 0 ? '₹ ' . number_format($result['total_outstation_trips']->sum('trips.claim_amount'), 2) : '--';

		} else {
			$this->data['outstation_total_trip_claim_percen'] = number_format((float) $total_outstation_trips, 2, '.', '') . "%";
			$this->data['total_claimed_outstation_trip_amount'] = ($result['total_outstation_trips']->sum('trips.claim_amount')) > 0 ? '₹ ' . number_format($result['total_outstation_trips']->sum('trips.claim_amount'), 2) : '--';
			$this->data['outstation_trip_claim_pending_percen'] = number_format((float) $total_outstation_trips, 2, '.', '') . "%";
			$this->data['outstation_trip_payment_requested_per'] = number_format((float) 0, 2, '.', '') . "%";
			$this->data['outstation_trip_payment_requested_amount'] = ($result['total_outstation_trips']->sum('trips.claim_amount')) > 0 ? '₹ ' . number_format($result['total_outstation_trips']->sum('trips.claim_amount'), 2) : '--';
			$this->data['outstation_trip_ready_for_claim_per'] = number_format((float) 0, 2, '.', '') . "%";
			$this->data['outstation_trip_ready_for_claim_amount'] = ($result['total_outstation_trips']->sum('trips.claim_amount')) > 0 ? '₹ ' . number_format($result['total_outstation_trips']->sum('trips.claim_amount'), 2) : '--';
			$this->data['outstation_upcoming_trip_per'] = number_format((float) 0, 2, '.', '') . "%";
			$this->data['outstation_upcoming_trip_amount'] = ($result['total_outstation_trips']->sum('trips.claim_amount')) > 0 ? '₹ ' . number_format($result['total_outstation_trips']->sum('trips.claim_amount'), 2) : '--';

			$this->data['total_outstation_trip_percen'] = number_format((float) 0, 2, '.', '') . "%";
			$this->data['total_outstation_trip_amount'] = ($result['total_outstation_trips']->sum('trips.claim_amount')) > 0 ? '₹ ' . number_format($result['total_outstation_trips']->sum('trips.claim_amount'), 2) : '--';
		}

		//LOCAL TRIP
		if ($total_local_trips > 0) {
			if ($total_local_trip_claim > 0) {
				$this->data['claimed_local_trips_per'] = number_format((float) (($total_local_trip_claim / $total_local_trips) * 100), 2, '.', '') . "%";
				$this->data['claimed_local_trips_amount'] = ($result['total_local_trip_claim']->sum('local_trips.claim_amount')) > 0 ? '₹ ' . number_format($result['total_local_trip_claim']->sum('local_trips.claim_amount'), 2) : '--';
			} else {
				$this->data['claimed_local_trips_per'] = number_format((float) $total_local_trip_claim, 2, '.', '') . "%";
				$this->data['claimed_local_trips_amount'] = ($result['total_local_trip_claim']->sum('local_trips.claim_amount')) > 0 ? '₹ ' . number_format($result['total_local_trip_claim']->sum('local_trips.claim_amount'), 2) : '--';
			}

			$this->data['claim_requested_local_trips_per'] = number_format((float) (($total_local_trip_claim_requested / $total_local_trips) * 100), 2, '.', '') . "%";
			$this->data['claim_requested_local_trips_amount'] = ($result['total_local_trip_claim_requested']->sum('local_trips.claim_amount')) > 0 ? '₹ ' . number_format($result['total_local_trip_claim_requested']->sum('local_trips.claim_amount'), 2) : '--';
			$this->data['ready_for_claimed_local_trips_per'] = number_format((float) (($total_local_trip_ready_for_claim / $total_local_trips) * 100), 2, '.', '') . "%";
			$this->data['ready_for_claimed_local_trips_amount'] = ($result['total_local_trip_ready_for_claim']->sum('local_trips.claim_amount')) > 0 ? '₹ ' . number_format((float) $result['total_local_trip_ready_for_claim']->sum('local_trips.claim_amount'), 2) : '--';
			$this->data['upcoming_local_trips_per'] = number_format((float) (($total_upcoming_local_trips / $total_local_trips) * 100), 2, '.', '') . "%";
			$this->data['upcoming_local_trips_amount'] = ($result['total_upcoming_local_trips']->sum('local_trips.claim_amount')) > 0 ? '₹ ' . number_format((float) $result['total_upcoming_local_trips']->sum('local_trips.claim_amount'), 2) : '--';

			$this->data['total_local_trip_per'] = number_format((float) 100, 2, '.', '') . "%";
			$this->data['total_local_trip_amount'] = ($result['total_local_trips']->sum('local_trips.claim_amount')) > 0 ? '₹ ' . number_format($result['total_local_trips']->sum('local_trips.claim_amount'), 2) : '--';

		} else {
			$this->data['claimed_local_trips_per'] = number_format((float) $total_local_trips, 2, '.', '') . "%";
			$this->data['claimed_local_trips_amount'] = ($result['total_local_trips']->sum('local_trips.claim_amount')) > 0 ? '₹ ' . number_format($result['total_local_trips']->sum('local_trips.claim_amount'), 2) : '--';
			$this->data['claim_requested_local_trips_per'] = number_format((float) 0, 2, '.', '') . "%";
			$this->data['claim_requested_local_trips_amount'] = ($result['total_local_trips']->sum('local_trips.claim_amount')) > 0 ? '₹ ' . number_format($result['total_local_trips']->sum('local_trips.claim_amount'), 2) : '--';
			$this->data['ready_for_claimed_local_trips_per'] = number_format((float) 0, 2, '.', '') . "%";
			$this->data['ready_for_claimed_local_trips_amount'] = ($result['total_local_trips']->sum('local_trips.claim_amount')) > 0 ? '₹ ' . number_format($result['total_local_trips']->sum('local_trips.claim_amount'), 2) : '--';
			$this->data['upcoming_local_trips_per'] = number_format((float) 0, 2, '.', '') . "%";
			$this->data['upcoming_local_trips_amount'] = ($result['total_local_trips']->sum('local_trips.claim_amount')) > 0 ? '₹ ' . number_format($result['total_local_trips']->sum('local_trips.claim_amount'), 2) : '--';
			$this->data['total_local_trip_per'] = number_format((float) 0, 2, '.', '') . "%";
			$this->data['total_local_trip_amount'] = ($result['total_local_trips']->sum('local_trips.claim_amount')) > 0 ? '₹ ' . number_format($result['total_local_trips']->sum('local_trips.claim_amount'), 2) : '--';
		}

		//PETTY CASH
		if ($total_petty_cash > 0) {
			if ($total_petty_cash_claim > 0) {
				$this->data['claimed_petty_cash_per'] = number_format((float) (($total_petty_cash_claim / $total_petty_cash) * 100), 2, '.', '') . "%";
				$this->data['claimed_petty_cash_per'] = ($result['total_petty_cash_claim']->sum('petty_cash.total')) > 0 ? '₹ ' . number_format((float) $result['total_petty_cash_claim']->sum('petty_cash.total'), 2) : '--';
			} else {
				$this->data['claimed_petty_cash_per'] = number_format((float) $total_petty_cash_claim, 2, '.', '') . "%";
				$this->data['claimed_petty_cash_amount'] = ($result['total_petty_cash_claim']->sum('petty_cash.total')) > 0 ? '₹ ' . number_format((float) $result['total_petty_cash_claim']->sum('petty_cash.total'), 2) : '--';
			}
			$this->data['not_claimed_petty_cash_per'] = number_format((float) (($total_petty_cash_claim_pending / $total_petty_cash) * 100), 2, '.', '') . "%";
			$this->data['not_claimed_petty_cash_amount'] = ($total_petty_cash_claim_pending_amount > 0) ? "₹ " . number_format((float) $total_petty_cash_claim_pending_amount, 2) : '--';
			$this->data['total_petty_cash_per'] = number_format((float) 100, 2, '.', '') . "%";
			$this->data['total_petty_cash_amount'] = ($result['total_petty_cash']->sum('petty_cash.total')) > 0 ? "₹ " . number_format((float) $result['total_petty_cash']->sum('petty_cash.total'), 2) : '--';

		} else {
			$this->data['claimed_petty_cash_per'] = number_format((float) $total_petty_cash, 2, '.', '') . "%";
			$this->data['claimed_petty_cash_amount'] = ($result['total_petty_cash']->sum('petty_cash.total')) > 0 ? "₹ " . number_format($result['total_petty_cash']->sum('petty_cash.total'), 2) : '--';
			$this->data['not_claimed_petty_cash_per'] = number_format((float) $total_petty_cash, 2, '.', '') . "%";
			$this->data['not_claimed_petty_cash_amount'] = ($result['total_petty_cash']->sum('petty_cash.total')) > 0 ? "₹ " . number_format($result['total_petty_cash']->sum('petty_cash.total'), 2) : '--';
			$this->data['total_petty_cash_per'] = number_format((float) 0, 2, '.', '') . "%";
			$this->data['total_petty_cash_amount'] = ($result['total_petty_cash']->sum('petty_cash.total')) > 0 ? "₹ " . number_format($result['total_petty_cash']->sum('petty_cash.total'), 2) : '--';
		}

		$split_year = explode('-', $fyc_year_session);
		$first_year = $split_year[0];
		$second_year = $split_year[1];

		$month_list = array();
		$month_list[] = date($first_year . '-04-01');
		$month_list[] = date($first_year . '-05-01');
		$month_list[] = date($first_year . '-06-01');
		$month_list[] = date($first_year . '-07-01');
		$month_list[] = date($first_year . '-08-01');
		$month_list[] = date($first_year . '-09-01');
		$month_list[] = date($first_year . '-10-01');
		$month_list[] = date($first_year . '-11-01');
		$month_list[] = date($first_year . '-12-01');
		$month_list[] = date($second_year . '-01-01');
		$month_list[] = date($second_year . '-02-01');
		$month_list[] = date($second_year . '-03-01');

		//ACHIEVEMENT GRAPH
		$outstation_trip = array();
		$local_trip = array();
		$petty_cash = array();
		foreach ($month_list as $month_value) {
			$start_date = $month_value;
			$end_date = date('Y-m-t', strtotime($start_date));

			$result = $this->trip_details($start_date, $end_date, $outlet_id, $business_id, $selected_date_range);

			$outstation_total_trip_claim = $result['outstation_total_trip_claim']->sum('ey_employee_claims.total_amount');
			$local_total_trip_claim = $result['total_local_trip_claim']->sum('local_trips.claim_amount');
			$total_petty_cash_claim = $result['total_petty_cash_claim']->sum('petty_cash.total');

			$outstation_trip[] = (int) $outstation_total_trip_claim;
			$local_trip[] = (int) $local_total_trip_claim;
			$petty_cash[] = (int) $total_petty_cash_claim;
		}

		$this->data['outstation_trip'] = $outstation_trip;
		$this->data['local_trip'] = $local_trip;
		$this->data['petty_cash'] = $petty_cash;

		return response()->json($this->data);
	}

	public function trip_details($start_date, $end_date, $outlet_id, $business_id, $date_range) {

		$current_date = date('Y-m-d');

		//OUTSTATION TRIP
		//TOTAL OUTSTATION TRIP
		$total_outstation_trips = Trip::leftjoin('employees', 'employees.id', 'trips.employee_id')->where('trips.status_id', '!=', '3032')->where('trips.status_id', '!=', '3022')->where('trips.status_id', '!=', '3021')->where('trips.start_date', '>=', $start_date)->where('trips.end_date', '<=', $end_date);
		//TOTAL OUTSTATION TRIP PAID
		$total_outstation_trip_claim = EmployeeClaim::join('trips', 'trips.id', 'ey_employee_claims.trip_id')->leftjoin('employees', 'employees.id', 'trips.employee_id')->where('ey_employee_claims.status_id', 3026)->where('trips.start_date', '>=', $start_date)->where('trips.end_date', '<=', $end_date);
		//TOTAL OUTSTATION TRIP PAYMENT REQUESTED
		$total_outstation_claim_requested = Trip::leftjoin('employees', 'employees.id', 'trips.employee_id')->whereIN('trips.status_id', [3023, 3024, 3025, 3029, 3030, 3033, 3034, 3036])->where('trips.start_date', '>=', $start_date)->where('trips.end_date', '<=', $end_date);
		//TOTAL OUTSTATION TRIP READY FOR CLAIM
		$total_outstation_ready_for_claim = Trip::leftjoin('employees', 'employees.id', 'trips.employee_id')->where('trips.status_id', '=', '3028')->where('trips.start_date', '>=', $start_date)->where('trips.end_date', '<=', $end_date)->where('trips.end_date', '<=', $current_date);
		//TOTAL UPCOMING OUTSTATION TRIPS
		$total_upcoming_outstation_trips = Trip::leftjoin('employees', 'employees.id', 'trips.employee_id')->where('trips.status_id', '=', '3028')->where('trips.start_date', '>=', $start_date)->where('trips.end_date', '<=', $end_date)->where('trips.start_date', '>=', $current_date);
		//TOTAL ADVANCE OUTSTATION TRIPS
		$total_advance_outstation_trips = Trip::leftjoin('employees', 'employees.id', 'trips.employee_id')->where('trips.status_id', '=', '3028')->where('trips.advance_received', '>', 0)->where('trips.start_date', '>=', $start_date)->where('trips.end_date', '<=', $end_date)->where('trips.start_date', '>=', $current_date);

		//LOCAL TRIP
		//TOTAL LOCAL TRIP
		$total_local_trips = LocalTrip::leftjoin('employees', 'employees.id', 'local_trips.employee_id')->where('local_trips.status_id', '!=', '3032')->where('local_trips.status_id', '!=', '3022')->where('local_trips.status_id', '!=', '3021')->where('local_trips.start_date', '>=', $start_date)->where('local_trips.end_date', '<=', $end_date);
		//TOTAL LOCAL TRIP PAID
		$total_local_trip_claim = LocalTrip::leftjoin('employees', 'employees.id', 'local_trips.employee_id')->where('local_trips.status_id', 3026)->where('local_trips.start_date', '>=', $start_date)->where('local_trips.end_date', '<=', $end_date);
		//TOTAL LOCAL TRIP PAYMENT REQUESTED
		$total_local_trip_claim_requested = LocalTrip::leftjoin('employees', 'employees.id', 'local_trips.employee_id')->whereIN('local_trips.status_id', [3023, 3034, 3035, 3030, 3024, 3036])->where('local_trips.start_date', '>=', $start_date)->where('local_trips.end_date', '<=', $end_date);
		//TOTAL LOCAL TRIP READY FOR CLAIM
		$total_local_trip_ready_for_claim = LocalTrip::leftjoin('employees', 'employees.id', 'local_trips.employee_id')->where('local_trips.status_id', '=', '3028')->where('local_trips.start_date', '>=', $start_date)->where('local_trips.end_date', '<=', $end_date)->where('local_trips.end_date', '<=', $current_date);
		//TOTAL UPCOMING LOCAL TRIPS
		$total_upcoming_local_trips = LocalTrip::leftjoin('employees', 'employees.id', 'local_trips.employee_id')->where('local_trips.status_id', '=', '3028')->where('local_trips.status_id', '!=', 3026)->where('local_trips.start_date', '>=', $start_date)->where('local_trips.end_date', '<=', $end_date)->where('local_trips.start_date', '>=', $current_date);

		//PETTY CASH
		$total_petty_cash = PettyCash::leftjoin('employees', 'employees.id', 'petty_cash.employee_id')->whereBetween('petty_cash.date', [$start_date, $end_date]);
		$total_petty_cash_claim = PettyCash::leftjoin('employees', 'employees.id', 'petty_cash.employee_id')->whereBetween('petty_cash.date', [$start_date, $end_date])->where('petty_cash.status_id', 3283);

		if ($outlet_id && $outlet_id != '-1') {
			$total_outstation_trips = $total_outstation_trips->where('employees.outlet_id', $outlet_id);
			$total_outstation_trip_claim = $total_outstation_trip_claim->where('employees.outlet_id', $outlet_id);
			$total_outstation_claim_requested = $total_outstation_claim_requested->where('employees.outlet_id', $outlet_id);
			$total_outstation_ready_for_claim = $total_outstation_ready_for_claim->where('employees.outlet_id', $outlet_id);
			$total_upcoming_outstation_trips = $total_upcoming_outstation_trips->where('employees.outlet_id', $outlet_id);
			$total_advance_outstation_trips = $total_advance_outstation_trips->where('employees.outlet_id', $outlet_id);

			$total_local_trips = $total_local_trips->where('employees.outlet_id', $outlet_id);
			$total_local_trip_claim = $total_local_trip_claim->where('employees.outlet_id', $outlet_id);
			$total_local_trip_claim_requested = $total_local_trip_claim_requested->where('employees.outlet_id', $outlet_id);
			$total_local_trip_ready_for_claim = $total_local_trip_ready_for_claim->where('employees.outlet_id', $outlet_id);
			$total_upcoming_local_trips = $total_upcoming_local_trips->where('employees.outlet_id', $outlet_id);

			$total_petty_cash = $total_petty_cash->where('employees.outlet_id', $outlet_id);
			$total_petty_cash_claim = $total_petty_cash_claim->where('employees.outlet_id', $outlet_id);

		}
		if ($business_id && $business_id != '-1') {
			$total_outstation_trips = $total_outstation_trips->join('departments', 'departments.id', 'employees.department_id')->where('departments.business_id', $business_id);
			$total_outstation_trip_claim = $total_outstation_trip_claim->join('departments', 'departments.id', 'employees.department_id')->where('departments.business_id', $business_id);
			$total_outstation_claim_requested = $total_outstation_claim_requested->join('departments', 'departments.id', 'employees.department_id')->where('departments.business_id', $business_id);
			$total_outstation_ready_for_claim = $total_outstation_ready_for_claim->join('departments', 'departments.id', 'employees.department_id')->where('departments.business_id', $business_id);
			$total_upcoming_outstation_trips = $total_upcoming_outstation_trips->join('departments', 'departments.id', 'employees.department_id')->where('departments.business_id', $business_id);
			$total_advance_outstation_trips = $total_advance_outstation_trips->join('departments', 'departments.id', 'employees.department_id')->where('departments.business_id', $business_id);

			$total_local_trips = $total_local_trips->join('departments', 'departments.id', 'employees.department_id')->where('departments.business_id', $business_id);
			$total_local_trip_claim = $total_local_trip_claim->join('departments', 'departments.id', 'employees.department_id')->where('departments.business_id', $business_id);
			$total_local_trip_claim_requested = $total_local_trip_claim_requested->join('departments', 'departments.id', 'employees.department_id')->where('departments.business_id', $business_id);
			$total_local_trip_ready_for_claim = $total_local_trip_ready_for_claim->join('departments', 'departments.id', 'employees.department_id')->where('departments.business_id', $business_id);
			$total_upcoming_local_trips = $total_upcoming_local_trips->join('departments', 'departments.id', 'employees.department_id')->where('departments.business_id', $business_id);

			// $total_petty_cash = $total_petty_cash->where('employees.outlet_id', $business_id);
			// $total_petty_cash_claim = $total_petty_cash_claim->where('employees.outlet_id', $business_id);

		}

		if (!empty($date_range)) {
			$dates = explode(' to ', $date_range);
			$from_date = date('Y-m-d', strtotime($dates[0]));
			$to_date = date('Y-m-d', strtotime($dates[1]));

			$total_outstation_trips = $total_outstation_trips
				->where('trips.start_date', '>=', $from_date)
				->where('trips.end_date', '<=', $to_date);
			$total_outstation_trip_claim = $total_outstation_trip_claim
				->where('trips.start_date', '>=', $from_date)
				->where('trips.end_date', '<=', $to_date);
			$total_outstation_claim_requested = $total_outstation_claim_requested
				->where('trips.start_date', '>=', $from_date)
				->where('trips.end_date', '<=', $to_date);
			$total_outstation_ready_for_claim = $total_outstation_ready_for_claim
				->where('trips.start_date', '>=', $from_date)
				->where('trips.end_date', '<=', $to_date);
			$total_upcoming_outstation_trips = $total_upcoming_outstation_trips
				->where('trips.start_date', '>=', $from_date)
				->where('trips.end_date', '<=', $to_date);
			$total_advance_outstation_trips = $total_advance_outstation_trips
				->where('trips.start_date', '>=', $from_date)
				->where('trips.end_date', '<=', $to_date);

			$total_local_trips = $total_local_trips
				->where('local_trips.start_date', '>=', $from_date)
				->where('local_trips.end_date', '<=', $to_date);
			$total_local_trip_claim = $total_local_trip_claim
				->where('local_trips.start_date', '>=', $from_date)
				->where('local_trips.end_date', '<=', $to_date);
			$total_local_trip_claim_requested = $total_local_trip_claim_requested
				->where('local_trips.start_date', '>=', $from_date)
				->where('local_trips.end_date', '<=', $to_date);
			$total_local_trip_ready_for_claim = $total_local_trip_ready_for_claim
				->where('local_trips.start_date', '>=', $from_date)
				->where('local_trips.end_date', '<=', $to_date);
			$total_upcoming_local_trips = $total_upcoming_local_trips
				->where('local_trips.start_date', '>=', $from_date)
				->where('local_trips.end_date', '<=', $to_date);
			// $total_petty_cash = $total_petty_cash->where('employees.outlet_id', $business_id);
			// $total_petty_cash_claim = $total_petty_cash_claim->where('employees.outlet_id', $business_id);
		}

		if (!Entrust::can('eyatra-masters')) {
			$total_outstation_trips = $total_outstation_trips->where('employees.id', Auth::user()->entity_id);
			$total_outstation_trip_claim = $total_outstation_trip_claim->where('employees.id', Auth::user()->entity_id);
			$total_outstation_claim_requested = $total_outstation_claim_requested->where('employees.id', Auth::user()->entity_id);
			$total_outstation_ready_for_claim = $total_outstation_ready_for_claim->where('employees.id', Auth::user()->entity_id);
			$total_upcoming_outstation_trips = $total_upcoming_outstation_trips->where('employees.id', Auth::user()->entity_id);
			$total_advance_outstation_trips = $total_advance_outstation_trips->where('employees.id', Auth::user()->entity_id);

			$total_local_trips = $total_local_trips->where('employees.id', Auth::user()->entity_id);
			$total_local_trip_claim = $total_local_trip_claim->where('employees.id', Auth::user()->entity_id);
			$total_local_trip_claim_requested = $total_local_trip_claim_requested->where('employees.id', Auth::user()->entity_id);
			$total_local_trip_ready_for_claim = $total_local_trip_ready_for_claim->where('employees.id', Auth::user()->entity_id);
			$total_upcoming_local_trips = $total_upcoming_local_trips->where('employees.id', Auth::user()->entity_id);

			$total_petty_cash = $total_petty_cash->where('employees.id', Auth::user()->entity_id);
			$total_petty_cash_claim = $total_petty_cash_claim->where('employees.id', Auth::user()->entity_id);
		}

		$result = array();

		$result['total_outstation_trips'] = $total_outstation_trips;
		$result['outstation_total_trip_claim'] = $total_outstation_trip_claim;
		$result['outstation_total_payment_requested'] = $total_outstation_claim_requested;
		$result['total_outstation_ready_for_claim'] = $total_outstation_ready_for_claim;
		$result['total_outstation_upcoming_trips'] = $total_upcoming_outstation_trips;
		$result['total_outstation_advance_trips'] = $total_advance_outstation_trips;

		$result['total_local_trips'] = $total_local_trips;
		$result['total_local_trip_claim'] = $total_local_trip_claim;
		$result['total_local_trip_claim_requested'] = $total_local_trip_claim_requested;
		$result['total_local_trip_ready_for_claim'] = $total_local_trip_ready_for_claim;
		$result['total_upcoming_local_trips'] = $total_upcoming_local_trips;

		$result['total_petty_cash'] = $total_petty_cash;
		$result['total_petty_cash_claim'] = $total_petty_cash_claim;

		return $result;

	}
	public function trip_details_old($start_date, $end_date, $outlet_id) {

		$current_date = date('Y-m-d');

		//OUTSTATION TRIP
		//TOTAL OUTSTATION TRIP
		$total_outstation_trips = Trip::where('trips.status_id', '!=', '3032')->where('trips.status_id', '!=', '3022')->where('trips.status_id', '!=', '3021')->where('trips.start_date', '>=', $start_date)->where('trips.end_date', '<=', $end_date);
		//TOTAL OUTSTATION TRIP PAID
		$total_outstation_trip_claim = EmployeeClaim::join('trips', 'trips.id', 'ey_employee_claims.trip_id')->where('ey_employee_claims.status_id', 3026)->where('trips.start_date', '>=', $start_date)->where('trips.end_date', '<=', $end_date);
		//TOTAL OUTSTATION TRIP PAYMENT REQUESTED
		$total_outstation_claim_requested = Trip::whereIN('trips.status_id', [3023, 3024, 3025, 3029, 3030, 3033, 3034, 3036])->where('trips.start_date', '>=', $start_date)->where('trips.end_date', '<=', $end_date);
		//TOTAL OUTSTATION TRIP READY FOR CLAIM
		$total_outstation_ready_for_claim = Trip::where('trips.status_id', '=', '3028')->where('trips.start_date', '>=', $start_date)->where('trips.end_date', '<=', $end_date)->where('trips.end_date', '<=', $current_date);
		//TOTAL UPCOMING OUTSTATION TRIPS
		$total_upcoming_outstation_trips = Trip::where('trips.status_id', '=', '3028')->where('trips.start_date', '>=', $start_date)->where('trips.end_date', '<=', $end_date)->where('trips.start_date', '>=', $current_date);

		//LOCAL TRIP
		//TOTAL LOCAL TRIP
		$total_local_trips = LocalTrip::where('local_trips.status_id', '!=', '3032')->where('local_trips.status_id', '!=', '3022')->where('local_trips.status_id', '!=', '3021')->where('local_trips.start_date', '>=', $start_date)->where('local_trips.end_date', '<=', $end_date);
		//TOTAL LOCAL TRIP PAID
		$total_local_trip_claim = LocalTrip::where('local_trips.status_id', 3026)->where('local_trips.start_date', '>=', $start_date)->where('local_trips.end_date', '<=', $end_date);
		//TOTAL LOCAL TRIP PAYMENT REQUESTED
		$total_local_trip_claim_requested = LocalTrip::whereIN('local_trips.status_id', [3023, 3034, 3035, 3030, 3024, 3036])->where('local_trips.start_date', '>=', $start_date)->where('local_trips.end_date', '<=', $end_date);
		//TOTAL LOCAL TRIP READY FOR CLAIM
		$total_local_trip_ready_for_claim = LocalTrip::where('local_trips.status_id', '=', '3028')->where('local_trips.start_date', '>=', $start_date)->where('local_trips.end_date', '<=', $end_date)->where('local_trips.end_date', '<=', $current_date);
		//TOTAL UPCOMING LOCAL TRIPS
		$total_upcoming_local_trips = LocalTrip::where('local_trips.status_id', '=', '3028')->where('local_trips.status_id', '!=', 3026)->where('local_trips.start_date', '>=', $start_date)->where('local_trips.end_date', '<=', $end_date)->where('local_trips.start_date', '>=', $current_date);

		//PETTY CASH
		$total_petty_cash = PettyCash::whereBetween('petty_cash.date', [$start_date, $end_date]);
		$total_petty_cash_claim = PettyCash::whereBetween('petty_cash.date', [$start_date, $end_date])->where('petty_cash.status_id', 3283);

		if ($outlet_id && $outlet_id != '-1') {
			$total_outstation_trips = $total_outstation_trips->leftjoin('employees', 'employees.id', 'trips.employee_id')->where('employees.outlet_id', $outlet_id);
			$total_outstation_trip_claim = $total_outstation_trip_claim->leftjoin('employees', 'employees.id', 'trips.employee_id')->where('employees.outlet_id', $outlet_id);
			$total_outstation_claim_requested = $total_outstation_claim_requested->leftjoin('employees', 'employees.id', 'trips.employee_id')->where('employees.outlet_id', $outlet_id);
			$total_outstation_ready_for_claim = $total_outstation_ready_for_claim->leftjoin('employees', 'employees.id', 'trips.employee_id')->where('employees.outlet_id', $outlet_id);
			$total_upcoming_outstation_trips = $total_upcoming_outstation_trips->leftjoin('employees', 'employees.id', 'trips.employee_id')->where('employees.outlet_id', $outlet_id);

			$total_local_trips = $total_local_trips->leftjoin('employees', 'employees.id', 'local_trips.employee_id')->where('employees.outlet_id', $outlet_id);
			$total_local_trip_claim = $total_local_trip_claim->leftjoin('employees', 'employees.id', 'local_trips.employee_id')->where('employees.outlet_id', $outlet_id);
			$total_local_trip_claim_requested = $total_local_trip_claim_requested->leftjoin('employees', 'employees.id', 'local_trips.employee_id')->where('employees.outlet_id', $outlet_id);
			$total_local_trip_ready_for_claim = $total_local_trip_ready_for_claim->leftjoin('employees', 'employees.id', 'local_trips.employee_id')->where('employees.outlet_id', $outlet_id);
			$total_upcoming_local_trips = $total_upcoming_local_trips->leftjoin('employees', 'employees.id', 'local_trips.employee_id')->where('employees.outlet_id', $outlet_id);

			$total_petty_cash = $total_petty_cash->leftjoin('employees', 'employees.id', 'petty_cash.employee_id')->where('employees.outlet_id', $outlet_id);
			$total_petty_cash_claim = $total_petty_cash_claim->leftjoin('employees', 'employees.id', 'petty_cash.employee_id')->where('employees.outlet_id', $outlet_id);

		}

		if (!Entrust::can('eyatra-masters')) {
			$total_outstation_trips = $total_outstation_trips->leftjoin('employees', 'employees.id', 'trips.employee_id')->where('employees.id', Auth::user()->entity_id);
			$total_outstation_trip_claim = $total_outstation_trip_claim->leftjoin('employees', 'employees.id', 'trips.employee_id')->where('employees.id', Auth::user()->entity_id);
			$total_outstation_claim_requested = $total_outstation_claim_requested->leftjoin('employees', 'employees.id', 'trips.employee_id')->where('employees.id', Auth::user()->entity_id);
			$total_outstation_ready_for_claim = $total_outstation_ready_for_claim->leftjoin('employees', 'employees.id', 'trips.employee_id')->where('employees.id', Auth::user()->entity_id);
			$total_upcoming_outstation_trips = $total_upcoming_outstation_trips->leftjoin('employees', 'employees.id', 'trips.employee_id')->where('employees.id', Auth::user()->entity_id);

			$total_local_trips = $total_local_trips->leftjoin('employees', 'employees.id', 'local_trips.employee_id')->where('employees.id', Auth::user()->entity_id);
			$total_local_trip_claim = $total_local_trip_claim->leftjoin('employees', 'employees.id', 'local_trips.employee_id')->where('employees.id', Auth::user()->entity_id);
			$total_local_trip_claim_requested = $total_local_trip_claim_requested->leftjoin('employees', 'employees.id', 'local_trips.employee_id')->where('employees.id', Auth::user()->entity_id);
			$total_local_trip_ready_for_claim = $total_local_trip_ready_for_claim->leftjoin('employees', 'employees.id', 'local_trips.employee_id')->where('employees.id', Auth::user()->entity_id);
			$total_upcoming_local_trips = $total_upcoming_local_trips->leftjoin('employees', 'employees.id', 'local_trips.employee_id')->where('employees.id', Auth::user()->entity_id);

			$total_petty_cash = $total_petty_cash->leftjoin('employees', 'employees.id', 'petty_cash.employee_id')->where('employees.id', Auth::user()->entity_id);
			$total_petty_cash_claim = $total_petty_cash_claim->leftjoin('employees', 'employees.id', 'petty_cash.employee_id')->where('employees.id', Auth::user()->entity_id);
		}

		$result = array();

		$result['total_outstation_trips'] = $total_outstation_trips;
		$result['outstation_total_trip_claim'] = $total_outstation_trip_claim;
		$result['outstation_total_payment_requested'] = $total_outstation_claim_requested;
		$result['total_outstation_ready_for_claim'] = $total_outstation_ready_for_claim;
		$result['total_outstation_upcoming_trips'] = $total_upcoming_outstation_trips;

		$result['total_local_trips'] = $total_local_trips;
		$result['total_local_trip_claim'] = $total_local_trip_claim;
		$result['total_local_trip_claim_requested'] = $total_local_trip_claim_requested;
		$result['total_local_trip_ready_for_claim'] = $total_local_trip_ready_for_claim;
		$result['total_upcoming_local_trips'] = $total_upcoming_local_trips;

		$result['total_petty_cash'] = $total_petty_cash;
		$result['total_petty_cash_claim'] = $total_petty_cash_claim;

		return $result;

	}
}
