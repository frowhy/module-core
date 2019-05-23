<?php
/**
 * Created by PhpStorm.
 * User: guoliang
 * Date: 2019/3/11
 * Time: 上午10:17
 */

namespace Modules\Core\Traits\Repository;


trait CheckPermissionTrait
{
    /**
     * 检查权限
     *
     * @param \Illuminate\Foundation\Auth\User $user
     * @param int $id
     *
     * @return bool
     */
    public function checkPermission($user, int $id)
    {
        return $this->model->where(['id' => $id, 'user_id' => $user->id])->exists();
    }
}
