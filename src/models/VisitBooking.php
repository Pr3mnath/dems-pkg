<?php

namespace Uitoux\EYatra;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VisitBooking extends Model {
	use SoftDeletes;

	protected $fillable = [
		'visit_id',
		'type_id',
		'travel_mode_id',
		'reference_number',
		'amount',
		'tax',
		'service_charge',
		'total',
		'status_id',
		// 'claim_amount',
		// 'payment_status_id',
		// 'payment_id',
		'created_by',
		'updated_by',
		'deleted_by',
	];

	public function visit() {
		return $this->belongsTo('Uitoux\EYatra\Visit');
	}

	public function type() {
		return $this->belongsTo('Uitoux\EYatra\Config', 'type_id');
	}

	public function travelMode() {
		return $this->belongsTo('Uitoux\EYatra\Entity', 'travel_mode_id');
	}

	public function paymentStatus() {
		return $this->belongsTo('Uitoux\EYatra\Config', 'payment_status_id');
	}

	public function payment() {
		return $this->belongsTo('Uitoux\EYatra\Payment');
	}

	public static function create($visit, $faker, $booking_detail_status_id, $employee) {
		$booking = new VisitBooking;
		$booking->visit_id = $visit->id;
		$booking->type_id = 3100; // FRESH BOOKING
		$booking->travel_mode_id = $visit->travel_mode_id;
		$booking->reference_number = $faker->swiftBicNumber;
		$booking->amount = $faker->numberBetween(500, 2000);
		$booking->tax = $booking->amount * 10 / 100;
		$booking->total = $booking->amount + $booking->tax;
		$booking->status_id = $booking_detail_status_id;
		if ($visit->booking_method_id == 3042) {
			//AGENT
			// $agent = Tra::whereHas('travelModes', function ($query) use ($travel_mode) {
			// 	$query->where('id', $travel_mode->id);
			// })->inRandomOrder()->first();

			$booking->service_charge = $faker->randomElement([100, 200, 300, 400, 500]);
			$booking->created_by = $visit->agent->user->id;
		} else {
			$booking->created_by = $employee->user->id;
		}

		$booking->save();
		return $booking;

	}
}
