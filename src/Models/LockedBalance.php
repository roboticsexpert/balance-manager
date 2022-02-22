<?php

namespace Roboticsexpert\BalanceManager\Models;

use Roboticsexpert\LaravelDecimal\DecimalCast;
use Decimal\Decimal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * Class Balance
 * @package App\Domains\Trade\Models
 * @property string currency
 * @property int user_id
 * @property string owner_type
 * @property string owner_id
 * @property Decimal value
 *
 */
class LockedBalance extends Model
{
    use HasFactory;

    protected $table = 'locked_balances';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $attributes = [
        'value' => '0',
    ];

    protected $casts = [
        'value' => DecimalCast::class,
    ];

    public $incrementing=false;

}
