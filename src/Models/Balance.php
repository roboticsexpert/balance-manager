<?php

namespace Roboticsexpert\BalanceManager\Models;

use Roboticsexpert\LaravelDecimal\DecimalCast;
use Decimal\Decimal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * Class Balance
 * @package App\Domains\Trade\Models
 * @property int user_id
 * @property string currency
 * @property Decimal value
 * @property Decimal locked_value
 *
 */
class Balance extends Model
{
    use HasFactory;

    protected $table = 'balances';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $attributes = [
        'value' => '0',
        'locked_value' => '0',
    ];

    protected $casts = [
        'value' => DecimalCast::class,
        'locked_value' => DecimalCast::class
    ];

    protected $primaryKey = 'user_id';

    public $incrementing = false;
}
