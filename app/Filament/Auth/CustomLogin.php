<?php

namespace App\Filament\Auth;

use DiogoGPinto\AuthUIEnhancer\Pages\Auth\AuthUiEnhancerLogin;
use Illuminate\Contracts\Support\Htmlable;

class CustomLogin extends AuthUiEnhancerLogin
{
    protected string $view = 'filament.pages.auth.login';

    public function getHeading(): string|Htmlable
    {
        return __('Sign in');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Access your inventory management dashboard';
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }
}

