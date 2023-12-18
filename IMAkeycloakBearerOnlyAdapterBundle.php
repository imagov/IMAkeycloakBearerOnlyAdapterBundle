<?php

namespace IMA\Bundle\keycloakBearerOnlyAdapterBundle;

use IMA\Bundle\keycloakBearerOnlyAdapterBundle\DependencyInjection\IMAkeycloakBearerOnlyAdapterExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class IMAkeycloakBearerOnlyAdapterBundle extends Bundle
{
    /**
     * @return ExtensionInterface|null
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new IMAkeycloakBearerOnlyAdapterExtension();
        }
        return $this->extension;
    }
}
