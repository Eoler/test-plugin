<?php namespace October\Test\Models;

use Model;

/**
 * Role Model
 */
class Role extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'october_test_roles';

    /**
     * @var array Guarded fields
     */
    protected $guarded = [];

    /**
     * @var array fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Relations
     */
    public $belongsToMany = [
        'users' => [User::class, 'table' => 'october_test_users_roles']
    ];

    public $attachMany = [
        'photos' => \System\Models\File::class,
    ];

    /**
     * scopeApplyRoleOptionsFilter
     */
    public function scopeApplyRoleOptionsFilter($query)
    {
        return $query->where('id', '<>', 1);
    }
}
