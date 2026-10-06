<?php

namespace WPDataTables\Vendor\Laravel\SerializableClosure;

use Closure;
use WPDataTables\Vendor\Laravel\SerializableClosure\Exceptions\InvalidSignatureException;
use WPDataTables\Vendor\Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException;
use WPDataTables\Vendor\Laravel\SerializableClosure\Serializers\Signed;
use WPDataTables\Vendor\Laravel\SerializableClosure\Signers\Hmac;
class SerializableClosure
{
    /**
     * The closure's serializable.
     *
     * @var \WPDataTables\Vendor\Laravel\SerializableClosure\Contracts\Serializable
     */
    protected $serializable;
    /**
     * Creates a new serializable closure instance.
     *
     * @param  \Closure  $closure
     * @return void
     */
    public function __construct(Closure $closure)
    {
        if (\PHP_VERSION_ID < 70400) {
            throw new PhpVersionNotSupportedException();
        }
        $this->serializable = \WPDataTables\Vendor\Laravel\SerializableClosure\Serializers\Signed::$signer ? new Serializers\Signed($closure) : new Serializers\Native($closure);
    }
    /**
     * Resolve the closure with the given arguments.
     *
     * @return mixed
     */
    public function __invoke()
    {
        if (\PHP_VERSION_ID < 70400) {
            throw new PhpVersionNotSupportedException();
        }
        return call_user_func_array($this->serializable, func_get_args());
    }
    /**
     * Gets the closure.
     *
     * @return \Closure
     */
    public function getClosure()
    {
        if (\PHP_VERSION_ID < 70400) {
            throw new PhpVersionNotSupportedException();
        }
        return $this->serializable->getClosure();
    }
    /**
     * Create a new unsigned serializable closure instance.
     *
     * @param  Closure  $closure
     * @return \WPDataTables\Vendor\Laravel\SerializableClosure\UnsignedSerializableClosure
     */
    public static function unsigned(Closure $closure)
    {
        return new UnsignedSerializableClosure($closure);
    }
    /**
     * Sets the serializable closure secret key.
     *
     * @param  string|null  $secret
     * @return void
     */
    public static function setSecretKey($secret)
    {
        \WPDataTables\Vendor\Laravel\SerializableClosure\Serializers\Signed::$signer = $secret ? new Hmac($secret) : null;
    }
    /**
     * Sets the serializable closure secret key.
     *
     * @param  \Closure|null  $transformer
     * @return void
     */
    public static function transformUseVariablesUsing($transformer)
    {
        \WPDataTables\Vendor\Laravel\SerializableClosure\Serializers\Native::$transformUseVariables = $transformer;
    }
    /**
     * Sets the serializable closure secret key.
     *
     * @param  \Closure|null  $resolver
     * @return void
     */
    public static function resolveUseVariablesUsing($resolver)
    {
        \WPDataTables\Vendor\Laravel\SerializableClosure\Serializers\Native::$resolveUseVariables = $resolver;
    }
    /**
     * Get the serializable representation of the closure.
     *
     * @return array
     */
    public function __serialize()
    {
        return ['serializable' => $this->serializable];
    }
    /**
     * Restore the closure after serialization.
     *
     * @param  array  $data
     * @return void
     *
     * @throws \WPDataTables\Vendor\Laravel\SerializableClosure\Exceptions\InvalidSignatureException
     */
    public function __unserialize($data)
    {
        if (Signed::$signer && !$data['serializable'] instanceof Signed) {
            throw new InvalidSignatureException();
        }
        $this->serializable = $data['serializable'];
    }
}