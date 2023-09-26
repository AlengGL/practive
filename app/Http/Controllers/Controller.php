<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
/**
 * @OA\OpenApi(
 *  @OA\Info(
 *      title="Swagger Petstore - OpenAPI 3.0",
 *      version="1.0.0",
 *      description="GL practice backend API document",
 *      @OA\Contact(
 *          email="aleng@webglsoft.com"
 *      )
 *  ),
 *  @OA\Server(
 *      url="https://petstore3.swagger.io/api/v3"
 *  ),
 *  @OA\PathItem(
 *      path="/"
 *  )
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

}
