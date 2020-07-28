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
        var_dump($ability);
        return in_array('*', $this->abilities) || in_array($ability, $this->abilities);
    }

    // 查询权限 And
    public function canAnd($data)
    {
        foreach((array) $data as $ability)
        {
            // 有一项未通过将错误
            if(!$this->can($ability))
            {
                return false;
            }
        }

        return (bool) $data;
    }

    // 查询权限 Or
    public function canOr($data)
    {
        foreach((array) $data as $ability)
        {
            // 有一项通过将成功
            if($this->can($ability))
            {
                return true;
            }
        }

        return false;
    }

    // 验证名称 or
    public function nameOr($data)
    {
        foreach((array) $data as $name)
        {
            // 有一项通过将成功
            if($this->name == $name)
            {
                return true;
            }
        }

        return false;
    }
    
    // 更新权限
    public function setAbilities($abilities)
    {
        $this->abilities = $abilities;
        
        return $this->save();
    }
}
