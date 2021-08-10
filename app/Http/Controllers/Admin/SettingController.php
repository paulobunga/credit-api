<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;

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

    public function index(Request $request)
    {
        if ($request->get('key', null)) {
            return ['data' => $this->getSetting($request->key)->toArray()];
        } else {
            return $this->response->collection($this->model::all(), $this->transformer);
        }
    }

    public function update(Request $request)
    {
        $rules = [
            'admin' => [
                'white_lists' => 'array',
                'white_lists.*' => 'distinct|ipv4',
            ],
            'agent' => [
                'default_downline_slot' => 'required|numeric|gte:0',
                'max_downline_slot' => 'required|numeric|gte:default_downline_slot',
            ],
            'commission' => [
                'referrer_percentage' => 'required|numeric|gte:0',
                'master_agent_percentage' => 'required|numeric|gte:0',
                'agent_percentage' => 'required|numeric|gte:0',
                'reseller_percentage' => 'required|numeric|gte:0',
                'total_percentage' => 'required|numeric|min:' .
                    $request->get('referrer_percentage', 0) 
                    + $request->get('master_agent_percentage', 0)
                    + $request->get('agent_percentage', 0)
                    + $request->get('reseller_percentage', 0)
            ],
            'currency' => [
                'types' => 'array',
                'types.*' => 'required',
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
