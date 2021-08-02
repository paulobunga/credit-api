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

    // public function store(Request $request)
    // {
    //     $this->validate($request, [
    //         'name' => 'required|unique:admins,name',
    //         'username' => 'required|unique:admins,username',
    //         'role' => 'required',
    //         'password' => 'required|confirmed',
    //         'status' => 'boolean'
    //     ]);

    //     $role = \App\Models\Role::findOrFail($request->role);

    //     $admin = $this->model::create([
    //         'name' => $request->name,
    //         'username' => $request->username,
    //         'password' => $request->password,
    //         'status' => $request->status,
    //     ]);
    //     $admin->syncRoles($role);

    //     return $this->response->item($admin, $this->transformer);
    // }

    public function update(Request $request)
    {
        $setting = $this->getSetting($this->parameters('setting'));
        foreach ($request->all() as $key => $val) {
            $setting->{$key} = $val;
        }
        $setting->save();
        $setting->refresh();

        return ['data' => $setting->toArray()];
    }

    // public function destroy(Request $request)
    // {
    //     $admin = $this->model::where('name', $this->parameters('admin'))->firstOrFail();
    //     if ($admin->id == 1) {
    //         throw new \Exception('Default Administrator cannot be removed!', 405);
    //     }
    //     $admin->delete();

    //     return $this->success();
    // }
}
