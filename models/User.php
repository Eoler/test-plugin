<?php namespace October\Test\Models;

use Model;

/**
 * User Model
 */
class User extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * implement the TranslatableModel behavior for attachments
     */
    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'october_test_users';

    /**
     * @var array Guarded fields
     */
    protected $guarded = [];

    /**
     * @var array fillable fields
     */
    protected $fillable = [];

    /**
     * @var array translatable attributes
     */
    public $translatable = [];

    /**
     * @var array jsonable attribute names that are json encoded and decoded from the database
     */
    protected $jsonable = [
        'media_files',
        'media_images',
    ];

    /**
     * @var array Rules
     */
    public $rules = [
        'username' => 'required',
        'photo' => 'required',
        'portfolio' => 'required',
        'roles' => 'required',
    ];

    /**
     * @var array customMessages
     */
    public $customMessages = [
       'required' => 'The :attribute field is WOW required.',
       'photo.required' => 'The :attribute field is required PHOTO.',
       // @see method beforeValidate
       // 'username.required' => 'Say hello (Username field required)',
    ];

    /**
     * beforeValidate uses hacky validation messages
     */
    public function beforeValidate()
    {
        \Lang::set('validation.custom.username.required', 'Say hello (Username field required)');
    }

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'country' => Country::class
    ];

    public $hasMany = [
        'posts' => Post::class
    ];

    public $belongsToMany = [
        'roles' => [
            Role::class,
            'table' => 'october_test_users_roles',
            'timestamps' => true,
            'order' => 'name'
        ],
        // @deprecated
        // 'roles_count' => [
        //     Role::class,
        //     'table' => 'october_test_users_roles',
        //     'count' => true
        // ],
        'roles_pivot' => [
            Role::class,
            'table' => 'october_test_users_roles',
            'pivot' => ['clearance_level', 'is_executive'],
            'timestamps' => true
        ],
        'roles_pivot_model' => [
            Role::class,
            'table' => 'october_test_users_roles',
            'pivot' => ['clearance_level', 'is_executive', 'country_id', 'evolution', 'salary'],
            'pivotKey' => 'id',
            'pivotModel' => UserRolePivot::class,
            'timestamps' => true,
        ],
    ];

    public $attachOne = [
        'photo' => \System\Models\File::class,
        'photo_secure' => [\System\Models\File::class, 'public' => false],
        'certificate' => [\System\Models\File::class],
        'certificate_secure' => [\System\Models\File::class, 'public' => false],
        'custom_file' => CustomFile::class
    ];

    public $attachMany = [
        'portfolio' => \System\Models\File::class,
        'portfolio_secure' => [\System\Models\File::class, 'public' => false],
        'files' => [\System\Models\File::class],
        'files_secure' => [\System\Models\File::class, 'public' => false],
    ];

    /**
     * __construct
     */
    public function __construct(...$args)
    {
        parent::__construct(...$args);

        $this->bindEvent('model.relation.detach', function ($relationName, $ids) {
            traceLog("Relation {$relationName} was removed", (array) $ids);
        });

        $this->bindEvent('model.relation.attach', function ($relationName, $ids, $attributes) {
            traceLog("New relation {$relationName} was created", $ids, $attributes);
        });
    }

    /**
     * filterScopes
     */
    public function filterScopes($scopes)
    {
        if ($scopes->disable_roles->value) {
            $scopes->roles->hidden(true);
        }
    }

    /**
     * scopeApplyRoleFilter
     */
    public function scopeApplyRoleFilter($query, $scope)
    {
        $action = $scope->mode === 'exclude' ? 'whereDoesntHave' : 'whereHas';

        return $query->{$action}('roles', function($q) use ($scope) {
            $q->whereIn('october_test_roles.id', $scope->value);
        });
    }

    /**
     * getUserAndCode
     */
    public function getUserAndCode()
    {
        return "{$this->username} ({$this->security_code})";
    }
}