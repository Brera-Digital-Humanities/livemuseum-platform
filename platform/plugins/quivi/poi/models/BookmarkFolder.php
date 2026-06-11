<?php namespace Quivi\Poi\Models;

use Model;

class BookmarkFolder extends Model
{
    use \Winter\Storm\Database\Traits\SoftDelete;

    public $table = 'quivi_poi_bookmark_folders';
    protected $dates = ['deleted_at'];

    protected $fillable = ['user_id', 'name', 'is_public'];
    protected $appends  = ['bookmarks_count'];

    public $belongsTo = [
        'user' => [\Winter\User\Models\User::class, 'key' => 'user_id'],
    ];

    public $hasMany = [
        'bookmarks' => [Bookmark::class, 'key' => 'folder_id'],
    ];

    public function getBookmarksCountAttribute()
    {
        return $this->bookmarks()->whereNull('deleted_at')->count();
    }
}
