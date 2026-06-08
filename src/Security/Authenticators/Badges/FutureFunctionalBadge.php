<?php

namespace Core\Security\Authenticators\Badges;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

class FutureFunctionalBadge implements BadgeInterface
{
    private ?array $featureActionList = null;
    private string $currentRouteName;

    public function __construct(?array $featureClassList, Request $request)
    {
        if (!is_null($featureClassList)) {
            $this->featureActionList = array_flip($featureClassList);
        }

        $this->currentRouteName = $request->attributes->get('_route');
    }

    public function isResolved(): bool
    {
        if (empty($this->featureActionList)) {
            return true;
        }

        return !array_key_exists($this->currentRouteName, $this->featureActionList);
    }
}