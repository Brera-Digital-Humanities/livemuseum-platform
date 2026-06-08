<?php namespace Quivi\Poi\Models;

use Model;

/**
 * Model
 */
class Poi extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    
    use \Winter\Storm\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'quivi_poi_pois';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $hasMany = [
        'comments' => [\Quivi\Poi\Models\Comment::class, 'key' => 'target_id', 'conditions' => "target_type = 'poi'"],
    ];

    public function getSchedaTypeOptions()
    {
        return [
            'base' => 'Base',
            'unlockable' => 'Unlockable',
            'livemuseum' => 'LiveMuseum',
            'community' => 'Community',
        ];
    }
}
