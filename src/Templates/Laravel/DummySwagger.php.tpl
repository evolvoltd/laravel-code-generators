<?php
define("API_HOST", config('l5-swagger.constants.L5_SWAGGER_CONST_HOST'));

/**
 * @OA\Get(
 *      path="/[#'uri'#]",
 *      tags={"[#'uri'#]"},
 *      @OA\Response(response=200, description="Success"),
 * )
 */
/**
 * @OA\Get(
 *      path="/[#'uri'#]/{[#'id'#]}",
 *      tags={"[#'uri'#]"},
 *      @OA\Response(response=200, description="Success"),
 * )
 */
/**
 * @OA\Post(
 *      path="/[#'uri'#]",
 *      tags={"[#'uri'#]"},
 *
 *       [#'post_parameters'#]
 *
 *      @OA\Response(response=200, description="Success"),
 *      @OA\Response(response=422, description="Validation failed"),
 * )
 */
/**
 * @OA\Put(
 *      path="/[#'uri'#]/{[#'id'#]}",
 *      tags={"[#'uri'#]"},
 *
 *       [#'put_parameters'#]
 *
 *      @OA\Response(response=200, description="Success"),
 *      @OA\Response(response=422, description="Validation failed"),
 * )
 */
/**
 * @OA\Delete(
 *      path="/[#'uri'#]/{[#'id'#]}",
 *      tags={"[#'uri'#]"},
 *      @OA\Response(response=200, description="Success"),
 * )
 */
