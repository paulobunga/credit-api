<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Log extends Model
{
    public $timestamps = false;

    protected $connection = 'log';
    protected $table = null;

    protected $fillable = [
        'message',
        'channel',
        'level',
        'level_name',
        'context',
        'datetime',
        'extra'
    ];

    protected $casts = [
        'context' => 'array',
        'extra' => 'array',
        'created_at'  => 'datetime:Y-m-d H:i:s',
    ];

    public const LEVELS = [
        "total",
        "emergency",
        "alert",
        "critical",
        "error",
        "warning",
        "notice",
        "info",
        "debug"
    ];

    /**
     * Set the table associated with the model.
     *
     * @param  string  $table
     * @return $this
     */
    public function setTable($table)
    {
        $this->table = empty($table) ? date('Y-m-d') : $table;
        if (Schema::connection('log')->hasTable($table)) {
            return $this;
        }
        Schema::connection('log')->create($table, function (Blueprint $table) {
            $table->id();
            $table->text('message')->nullable();
            $table->string('channel')->nullable();
            $table->integer('level')->default(0);
            $table->string('level_name');
            $table->longText('context')->nullable();
            $table->string('datetime')->nullable();
            $table->text('extra')->nullable();
            $table->datetime('created_at')->useCurrent();
        });
        return $this;
    }

    public function getTable()
    {
        return $this->table ?? date('Y-m-d');
    }

    public static function getAllTables(): array
    {
        return array_reverse(array_map('head', DB::connection('log')->select('SHOW TABLES;')));
    }

    public function drop()
    {
        Schema::connection('log')->dropIfExists($this->table);
    }
}
