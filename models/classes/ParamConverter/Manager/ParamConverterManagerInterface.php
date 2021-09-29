<?php

declare(strict_types=1);

namespace oat\tao\model\ParamConverter\Manager;

use Symfony\Component\HttpFoundation\Request;
use oat\tao\model\ParamConverter\Configuration\ParamConverter;
use oat\tao\model\ParamConverter\Request\ParamConverterInterface;

interface ParamConverterManagerInterface
{
    /**
     * Applies all converters to the passed configurations and stops when a
     * converter is applied it will move on to the next configuration and so on.
     *
     * @param ParamConverter[] $configurations
     */
    public function apply(Request $request, array $configurations): void;

    /**
     * Adds a parameter converter.
     *
     * Converters match either explicitly via $name or by iteration over all
     * converters with a $priority. If you pass a $priority = null then the
     * added converter will not be part of the iteration chain and can only
     * be invoked explicitly.
     *
     * @param int|null $priority the priority (between -10 and 10)
     */
    public function add(ParamConverterInterface $converter, ?int $priority = 0, string $name = null): void;

    /**
     * Returns all registered param converters.
     *
     * @return ParamConverterInterface[]
     */
    public function all(): array;
}
