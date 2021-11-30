<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Models\ResellerSms;
use App\Trait\UserTimezone;

class ResellerSmsController extends Controller
{
    use UserTimezone;

    protected $model = ResellerSms::class;

    protected $transformer = \App\Transformers\Admin\ResellerSmsTransformer::class;

    protected $db_timezone;

    protected $user_timezone;

    public function __construct()
    {
        parent::__construct();
        $this->db_timezone = env('DB_TIMEZONE');
        $this->user_timezone = $this->userTimezoneOffset();
    }

    /**
     * Get SMS list
     *
     * @param \Dingo\Api\Http\Request $request
     * @method GET
     * @return json
     */
    public function index(Request $request)
    {
        $m = QueryBuilder::for($this->model)
            ->with([
                'reseller'
            ])
            ->join('resellers', 'resellers.id', '=', 'reseller_sms.reseller_id')
            ->select(
                'reseller_sms.*',
                'resellers.name'
            )
            ->allowedFilters([
                AllowedFilter::partial('name', 'resellers.name'),
                AllowedFilter::exact('status'),
                AllowedFilter::callback(
                    'created_at_between',
                    fn ($query, $v) => $query->whereRaw("CONVERT_TZ(reseller_sms.created_at, '{$this->db_timezone}', '{$this->user_timezone}') BETWEEN ? AND ?", $v)
                ),
            ])
            ->allowedSorts([
                'id',
                'status'
            ]);

        return $this->paginate($m, $this->transformer);
    }
}
