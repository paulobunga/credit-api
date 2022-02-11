<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use App\Rules\ExistCurrency;

class SettingController extends Controller
{
    protected $model = \App\Models\Setting::class;

    protected $transformer = \App\Transformers\Admin\SettingTransformer::class;

    protected function getSetting($key = null)
    {
        if (empty($key)) {
            throw new \Exception('key is not found!', 405);
        }
        $key = ucwords($key);
        return app("\App\Settings\\{$key}Setting");
    }

    /**
     * Get setting list
     *
     * @param \Dingo\Api\Http\Request $request
     * @method GET
     *
     * @return json
     */
    public function index(Request $request)
    {
        if ($key = $request->get('key', null)) {
            return ['data' => $this->getSetting($key)->toArray()];
        } else {
            $settings = $this->model::select('group')->distinct()->pluck('group')->map(function ($s) {
                return [
                    'key' => $s,
                    'value' => $this->getSetting($s)->toArray()
                ];
            });

            return [
                'data' => $settings
            ];
        }
    }

    /**
     * update setting by key
     *
     * @param  \Dingo\Api\Http\Request $request
     * @method PUT
     *
     * @return json
     */
    public function update(Request $request)
    {
        $rules = [
            'admin' => [
                'white_lists' => 'array',
                'white_lists.*' => 'required|distinct|ipv4',
            ],
            'agent' => [
                'default_downline_slot' => 'required|numeric|gte:0',
                'max_downline_slot' => 'required|numeric|gte:default_downline_slot',
            ],
            'currency' => [
                'currency' => ['array', new ExistCurrency()],
                'currency.*.agent_percentage' => 'required|numeric',
                'currency.*.master_agent_percentage' => 'required|numeric',
                'currency.*.referrer_percentage' => 'required|numeric',
                'currency.*.reseller_percentage' => 'required|numeric',
                'currency.*.transaction_fee_percentage' => 'required|numeric',
            ],
            'reseller' => [
                'default_pending_limit' => 'required|numeric|gte:0',
                'max_pending_limit' => 'required|numeric|gte:default_pending_limit',
            ],
        ];
        $key = $this->parameters('setting');
        $this->validate($request, $rules[$key] ?? null);
        $setting = $this->getSetting($key);
        foreach ($request->all() as $key => $val) {
            $setting->{$key} = $val;
        }
        $setting->save();
        $setting->refresh();

        return ['data' => $setting->toArray()];
    }
}
