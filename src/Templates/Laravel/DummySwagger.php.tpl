<?php
define("API_HOST", config('l5-swagger.constants.L5_SWAGGER_CONST_HOST'));

/**
 * @OA\Get(
 *      path="/[#'uri'#]",
 *      tags={"[#'tag'#]"},
 *      @OA\Response(response=200, description="Success"),
 * )
 */
/**
 * @OA\Get(
 *      path="/[#'uri'#]/{[#'id'#]}",
 *      tags={"[#'tag'#]"},
 *      [#'id_parameter'#]
 *      @OA\Response(response=200, description="Success"),
 * )
 */
/**
 * @OA\Post(
 *      path="/[#'uri'#]",
 *      tags={"[#'tag'#]"},
 *      [#'post_parameters'#]
 *      @OA\Response(response=200, description="Success"),
 *      @OA\Response(response=422, description="Validation failed"),
 * )
 */
/**
 * @OA\Put(
 *      path="/[#'uri'#]/{[#'id'#]}",
 *      tags={"[#'tag'#]"},
 *      [#'id_parameter'#]
 *      [#'put_parameters'#]
 *      @OA\Response(response=200, description="Success"),
 *      @OA\Response(response=422, description="Validation failed"),
 * )
 */
/**
 * @OA\Delete(
 *      path="/[#'uri'#]/{[#'id'#]}",
 *      tags={"[#'tag'#]"},
 *      [#'id_parameter'#]
 *      @OA\Response(response=200, description="Success"),
 * )
 */
