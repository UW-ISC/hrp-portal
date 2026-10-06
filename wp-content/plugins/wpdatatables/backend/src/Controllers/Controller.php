<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers;

use Exception;
use RuntimeException;
use WPDataTables\Common\Exceptions\ForbiddenException;
use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WPDataTables\Common\Exceptions\NotFoundException;
use WPDataTables\Common\Exceptions\QueryExecutionException;
use WPDataTables\Common\Exceptions\ServiceUnavailableException;
use WPDataTables\Common\Exceptions\ValidationException;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Abstract REST controller.
 *
 * `__invoke()` is the entrypoint registered as a route callback; it wraps the
 * concrete `handle()` and maps typed exceptions to HTTP status codes. Copied
 * from the ivyforms Controller pattern so wpDataTables REST + admin-ajax can
 * converge on the same controllers.
 *
 * @package WPDataTables\Controllers
 */
abstract class Controller
{
    /**
     * @param WP_REST_Request $data
     * @return WP_REST_Response
     */
    public function __invoke(WP_REST_Request $data): WP_REST_Response
    {
        try {
            $response = $this->handle($data);

            if ($response->get_status() !== 200) {
                throw new RuntimeException('Unexpected HTTP status: ' . $response->get_status());
            }

            return new WP_REST_Response([
                'message' => 'ok',
                'data'    => $response->get_data(),
            ], 200);
        } catch (ForbiddenException $e) {
            return self::error($e, 403);
        } catch (NotFoundException $e) {
            return self::error($e, 404);
        } catch (InvalidArgumentException $e) {
            return self::error($e, 400);
        } catch (ValidationException $e) {
            return self::error($e, 422, 'Validation error: ');
        } catch (QueryExecutionException $e) {
            return self::error($e, 500, 'Database error: ');
        } catch (ServiceUnavailableException $e) {
            return self::error($e, 503);
        } catch (Exception $e) {
            return self::error($e, 500, 'Unexpected error occurred: ');
        }
    }

    /**
     * Build a structured error response and log it when WP_DEBUG is on.
     */
    private static function error(Exception $e, int $status, string $prefix = ''): WP_REST_Response
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('wpDataTables error ' . $status . ': ' . $e->getMessage());
        }

        return new WP_REST_Response([
            'message' => $prefix . $e->getMessage(),
            'data'    => [],
        ], $status);
    }

    /**
     * Concrete controllers implement the actual request handling here.
     *
     * @param WP_REST_Request $data
     * @return WP_REST_Response
     */
    abstract protected function handle(WP_REST_Request $data): WP_REST_Response;
}
