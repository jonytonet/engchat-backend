<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="EngChat API",
 *     version="1.0.0",
 *     description="API documentation for EngChat - Customer Service Platform",
 *     @OA\Contact(
 *         email="dev@engchat.com",
 *         name="EngChat Development Team"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="EngChat API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Use Bearer token for authentication"
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="Authentication endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Conversations",
 *     description="Operations about conversations"
 * )
 *
 * @OA\Tag(
 *     name="Contacts",
 *     description="Operations about contacts"
 * )
 *
 * @OA\Tag(
 *     name="Messages",
 *     description="Operations about messages"
 * )
 *
 * @OA\Tag(
 *     name="Users",
 *     description="Operations about users"
 * )
 *
 * @OA\Tag(
 *     name="Channels",
 *     description="Operations about channels"
 * )
 *
 * @OA\Tag(
 *     name="Categories",
 *     description="Operations about categories"
 * )
 */
abstract class Controller
{
    //
}
