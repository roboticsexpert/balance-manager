<?php

namespace Roboticsexpert\BalanceManager\Models;

use Roboticsexpert\LaravelDecimal\DecimalCast;
use Decimal\Decimal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BalanceHistory
 * @package App\Domains\Trade\Models
 * @property int id
 * @property int user_id
 * @property int currency
 * @property Decimal value
 * @property Decimal change_value
 * @property string reason
 * @property string related_model_type
 * @property string related_model_id
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class BalanceHistory extends Model
{
    protected $table = 'balance_histories';



    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    protected $casts = [
        'change_value' => DecimalCast::class,
        'value' => DecimalCast::class,
    ];

}
