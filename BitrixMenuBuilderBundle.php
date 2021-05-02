<?php

namespace Prokl\BitrixMenuBuilderBundle;

use Prokl\BitrixMenuBuilderBundle\DependencyInjection\BitrixMenuBuilderExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class BitrixMenuBuilderBundle
 * @package Prokl\BitrixMenuBuilderBundle
 *
 * @since 02.05.2021
 */
class BitrixMenuBuilderBundle extends Bundle
{
   /**
   * @inheritDoc
   */
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new BitrixMenuBuilderExtension();
        }

        return $this->extension;
    }
}
