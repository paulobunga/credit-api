<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Controller;

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
        $notification = QueryBuilder::for($this->model)
            ->allowedFilters([
                AllowedFilter::callback('unread', function (Builder $query, $value) {
                    if ($value) {
                        $query->whereNull('read_at');
                    } else {
                        $query->whereNotNull('read_at');
                    }
                })
            ])
            ->allowedSorts([
                'id',
                'created_at',
            ])
            ->where('notifiable_id', auth()->id())
            ->where('notifiable_type', 'admin');

        return $this->paginate($notification, $this->transformer);
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

        return $this->paginate(
            QueryBuilder::for($notifications),
            $this->transformer,
            ['notifications' => auth()->user()->unreadNotifications->count()]
        );
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

        return $this->success([
            'notifications' => auth()->user()->unreadNotifications->count()
        ]);
    }
}
