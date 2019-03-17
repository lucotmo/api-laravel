<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Arr;
use App\Traits\ApiResponser;
use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    use ApiResponser;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $this->errorResponse('No autenticado.', 401);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if( $exception instanceof ValidationException ){
            return $this->convertValidationExceptionToResponse($exception, $request);
        }
        if( $exception instanceof ModelNotFoundException ){
            $modelo = strtolower(class_basename($exception->getModel()));
            return $this->errorResponse("No existe ninguna instancia de {$modelo} en el id expecificado", 404);
        }
        if( $exception instanceof AuthenticationException ){
            return $this->unauthenticated($exception, $request);
        }
        if( $exception instanceof AuthenticationException ){
            return $this->errorResponse('No posee permisos para ejecutar esta acción', 403);
        }
        if( $exception instanceof NotFoundHttpException ){
            return $this->errorResponse('No se encontró la URL especificada', 404);
        }
        if( $exception instanceof MethodNotAllowedHttpException ){
            return $this->errorResponse('El metódo especificado en la petición no es válido', 405);
        }
        if( $exception instanceof HttpException ){
            return $this->errorResponse($exception->getMessage(), $exception->getStatusCode());
        }
        if( $exception instanceof QueryException ){
            //dd($exception);
            $codigo = $exception->errorInfo[1];
            if ( $codigo == 1451 ){
                return $this->errorResponse('no se puede eliminar de forma permanente el recurso porque esta relacionado con algun otro.', 409);
            }
        }

        if ( config('app.debug') ){
            return parent::render($request, $exception);
        }

        return $this->errorResonse('Falla inesperada. Intente luego', 500);


    }

    /**
     * Create a response object from the given validation exception.
     *
     * @param  \Illuminate\Validation\ValidationException  $e
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        return $request->expectsJson()
                    ? $this->invalidJson($request, $e)
                    : $this->invalid($request, $e);
    }

    /**
     * Convert a validation exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Validation\ValidationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function invalid($request, ValidationException $exception)
    {
        return $this->errorResponse($exception->errors(), 422);
    }
}
