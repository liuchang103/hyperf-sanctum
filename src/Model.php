<?php

declare(strict_types=1);

namespace HyperfSanctum;

use Hyperf\DbConnection\Model\Model as BaseModel;

class Model extends BaseModel
{
    protected $table = 'sanctum';
    
    protected $primaryKey = 'token';

    protected $fillable = [
        'name',
        'token',
        'abilities'
    ];

    protected $casts = [
        'abilities' => 'json'
    ];
    
    protected $hidden = [
        'token',
    ];
    
    protected $dateFormat = 'U';
    
    public $incrementing = false;
    
    const UPDATED_AT = null;
    
    // 反向多态关联
    public function tokenable()
    {
        return $this->morphTo('tokenable');
    }
    
    // 查询token
    public static function findToken($token)
    {
        return static::where('token', hash('sha256', $token))->first();
    }

    // 查询权限
    public function can($ability)
    {
        return in_array('*', $this->abilities) || array_key_exists($ability, array_flip($this->abilities));
    }
    
    // 更新权限
    public function setAbilities($abilities)
    {
        $this->abilities = $abilities;
        
        return $this->save();
    }
}
