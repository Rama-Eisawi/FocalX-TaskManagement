<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tasks';
    protected $primaryKey = 'task_id';
    protected $fillable = ['title', 'description', 'priority', 'due_date', 'status', 'assigned_to', 'created_by'];

    protected $guarded = ['task_id'];

    //Task assigned to one user
    public function user()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'user_id');
    }

    //Task created by one manager/admin
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    /**
     * Accessor for `due_date`
     * This formats the `due_date` when it is retrieved (get).
     */
    public function getDueDateAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i'); // i for minutes
    }

    /**
     * Mutator for `due_date`
     * This formats the `due_date` when it is being set (before saving to the database).
     */
    public function setDueDateAttribute($value)
    {
        $this->attributes['due_date'] = Carbon::createFromFormat('d-m-Y H:i', $value)->format('Y-m-d H:i:s');
    }
}
