<?php

declare(strict_types=1);

namespace PhilipRehberger\DtoMapper\Exceptions;

/**
 * Exception thrown when DTO mapping fails.
 */
class MappingException extends \RuntimeException
{
    /**
     * @param  array<string>  $errors
     */
    public function __construct(
        public readonly array $errors,
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        if ($message === '') {
            $message = sprintf(
                'Mapping failed with %d error(s): %s',
                count($errors),
                implode('; ', $errors),
            );
        }

        parent::__construct($message, $code, $previous);
    }
}
