<?php

/**
 * Created by Reliese Model.
 * Date: Tue, 06 Feb 2018 18:03:05 +0000.
 */

namespace App\Admin\Tickets;

use App\Admin\Payment\Payment;
use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Ticket
 * 
 * @property int $id
 * @property float $price
 * @property int $passengerID
 * @property string $code
 * @property \Carbon\Carbon $date
 * 
 * @property \App\Admin\Passengers\Passenger $passenger
 * @property \Illuminate\Database\Eloquent\Collection $payments
 *
 * @package App\Models
 */
class Ticket extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'price'       => 'float',
		'passengerID' => 'int'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'price',
//		'passengerID',
		'date',
		'code',
		'viewed'
	];

	protected $hidden = [ 'passengerID' ];

	public function passenger()
	{
		return $this->belongsTo(\App\Admin\Passengers\Passenger::class, 'passengerID');
	}

	public function payments()
	{
		return $this->hasMany(Payment::class, 'ticketID');
	}

	public function getViewedAttribute($value){
		return $value == 0 ? false : true;
	}
}
