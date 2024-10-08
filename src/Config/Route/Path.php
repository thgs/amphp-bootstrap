<?php declare(strict_types=1);

namespace thgs\Bootstrap\Config\Route;

use Amp\File\FilesystemDriver;
use Amp\Http\Server\Middleware;

/** @api */
readonly class Path
{
    public function __construct(
        public string $uri,
        public string $method,
        /**
         * You may pass a full path or a relative path.
         * Relative paths will be resolved from current working directory
         * or given public dir (with this priority).
         */
        public string $path,

        /**
         * @var bool
         */
        public bool $isDir = true,

        /**
         * Only usable for directories currently.
         *
         * @var class-string<FilesystemDriver>|null
         */
        public ?string $filesystemDriver = null,

        /**
         * @var class-string<Middleware>[]
         */
        public array $middleware = []
    ) {
    }
}
