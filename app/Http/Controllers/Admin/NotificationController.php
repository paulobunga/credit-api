<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Filters\DateFilter;
use Dingo\Api\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
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
      $notification = QueryBuilder::for($this->model)
        ->allowedFilters([
          AllowedFilter::exact('id'),
          AllowedFilter::custom('created_at_between', new DateFilter('notifications')),
          AllowedFilter::callback('status', function (Builder $query, $value) {
            if($value) {
              $query->whereNotNull('read_at');
            } else {
              $query->whereNull('read_at');
            }
          })
        ])
        ->where('notifiable_id', auth()->id())
        ->where('notifiable_type', 'admin');

      return $this->paginate($notification, $this->transformer);
    }

    /**
     * Read notification
     *
     * @return \Dingo\Api\Http\JsonResponse
     */
    public function update(Request $request)
    {
      $notification = $this->model::findOrFail($this->parameters('notification'));
      $notification->read_at = Carbon::now();
      $notification->save();
      
      return $this->response->item($notification, $this->transformer);
    }

  /**
   * Destroy notification
   *
   * @return \Dingo\Api\Http\JsonResponse
   */
    public function destroy(Request $request)
    {
      $bank = $this->model::findOrFail($this->parameters('notification'));
      $bank->delete();

      return $this->success();
    }
}
