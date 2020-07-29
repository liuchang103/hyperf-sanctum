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
        // 所有权
        if(!in_array('*', $this->abilities))
        {
            foreach((array) $ability as $item)
            {
                // 有一项未通过将错误
                if(!in_array($item, $this->abilities))
                {
                    return false;
                }
            }
        }

        return true;
    }

    // 查询权限 白名单
    public function canWhite($data)
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

    // 验证名称
    public function nameCard($data)
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
