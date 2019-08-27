<?php

namespace Uitoux\EYatra;

use Auth;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class ReimbursementTranscations extends Model {
	//use SoftDeletes;
	protected $table = 'reimbursement_transcations';
	protected $fillable = [
		'outlet_id',
		'transcation_id',
		'transaction_date',
		'transcation_type',
		'amount',
		'balance_amount'
	];
}
