<?php
/**
 * Created by PhpStorm.
 * User: guoliang
 * Date: 2019/3/11
 * Time: 上午10:17.
 */

namespace Modules\Core\Traits\Service;

use Modules\Core\Supports\Response;

/**
 * @property \Modules\Core\Contracts\Repository\CheckPermission repository
 */
trait CheckPermissionTrait
{
    /**
     * 检查权限.
     *
     * @param \Illuminate\Foundation\Auth\User $user
     * @param int                              $id
     *
     * @return \Modules\Core\Supports\Response
     */
    public function checkPermission($user, int $id)
    {
        $result = $this->repository->checkPermission($user, $id);
        if (!$result) {
            return Response::handleForbidden((string) __('core::default.permission_denied'));
        }

        return Response::handleOk();
    }
}
