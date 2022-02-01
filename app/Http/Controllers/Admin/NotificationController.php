<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Carbon\Carbon;

class NotificationController extends Controller
{
    protected $model = \App\Models\Notification::class;

    protected $transformer = \App\Transformers\Admin\NotificationTransformer::class;

    /**
     * Get notification list
     * @param \Dingo\Api\Http\Request $request
     * @method GET
     * @return json
     */
    public function index(Request $request)
    {
      return $this->response->collection(auth()->user()->notifications, $this->transformer);
    }


  /**
   * Get unread notification list
   * @param \Dingo\Api\Http\Request $request
   * @method GET
   * @return json
   */
    public function unread(Request $request)
    {
      return $this->response->collection(auth()->user()->unreadNotifications, $this->transformer);
    }


    /**
     * Mark notification
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function mark(Request $request)
    {
        $this->validate($request, [
            'mark_type' => 'required|in:read,unread',
            'id' => 'required|array'
        ]);

        $notifications = auth()->user()->notifications()->whereIn('id', $request->id);
        if ($request->mark_type == "read") {
            $notifications->update(['read_at' => Carbon::now()]);
        } else {
            $notifications->update(['read_at' => null]);
        }
        
        return $this->response->collection($notifications->get(), $this->transformer);
    }

  /**
   * Destroy notification
   *
   * @return \Dingo\Api\Http\JsonResponse
   */
    public function destroy(Request $request)
    {
        $notification = auth()->user()->notifications()->where('id', $this->parameters('notification'))->first();
        if ($notification->count() === 0) {
          throw new \Exception("Notification not found!");
        }
        $notification->delete();

        return $this->success();
    }
}
