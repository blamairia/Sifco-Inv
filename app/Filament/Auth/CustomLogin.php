<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;

class CustomLogin extends Login
{
    /**
     * Boot the component and register render hooks for demo credentials and styling
     */
    public function boot(): void
    {
        // Inject custom CSS styles for dark theme
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => '
                <style>
                    /* Dark Login Page Theme */
                    .fi-simple-layout {
                        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%) !important;
                        min-height: 100vh;
                        position: relative;
                    }
                    
                    .fi-simple-layout::before {
                        content: "";
                        position: absolute;
                        inset: 0;
                        background-image: 
                            radial-gradient(circle at 20% 50%, rgba(38, 93, 166, 0.2) 0%, transparent 50%),
                            radial-gradient(circle at 80% 20%, rgba(35, 70, 140, 0.15) 0%, transparent 40%),
                            radial-gradient(circle at 40% 80%, rgba(242, 53, 69, 0.1) 0%, transparent 40%);
                        animation: bgFloat 20s ease-in-out infinite;
                        pointer-events: none;
                    }
                    
                    @keyframes bgFloat {
                        0%, 100% { transform: translateY(0) scale(1); }
                        50% { transform: translateY(-20px) scale(1.02); }
                    }
                    
                    /* Login Card */
                    .fi-simple-main-ctn {
                        background: rgba(255, 255, 255, 0.03) !important;
                        backdrop-filter: blur(20px);
                        -webkit-backdrop-filter: blur(20px);
                        border: 1px solid rgba(255, 255, 255, 0.08) !important;
                        border-radius: 20px !important;
                        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
                        position: relative;
                        z-index: 10;
                    }
                    
                    /* Logo */
                    .fi-simple-header .fi-logo {
                        padding: 16px 20px !important;
                        background: rgba(255, 255, 255, 0.08) !important;
                        border-radius: 12px !important;
                    }
                    
                    /* Headings */
                    .fi-simple-header-heading {
                        color: #ffffff !important;
                        font-weight: 700 !important;
                    }
                    
                    .fi-simple-header-subheading {
                        color: rgba(255, 255, 255, 0.6) !important;
                    }
                    
                    /* Form Labels */
                    .fi-simple-page .fi-fo-field-wrp label,
                    .fi-simple-page [class*="field-wrapper"] label {
                        color: rgba(255, 255, 255, 0.8) !important;
                    }
                    
                    /* Input Fields */
                    .fi-simple-page .fi-input-wrp {
                        background: rgba(255, 255, 255, 0.05) !important;
                        border: 1px solid rgba(255, 255, 255, 0.1) !important;
                        border-radius: 10px !important;
                    }
                    
                    .fi-simple-page .fi-input-wrp:focus-within {
                        background: rgba(255, 255, 255, 0.08) !important;
                        border-color: rgba(38, 93, 166, 0.5) !important;
                        box-shadow: 0 0 0 3px rgba(38, 93, 166, 0.2) !important;
                    }
                    
                    .fi-simple-page .fi-input {
                        background: transparent !important;
                        color: #ffffff !important;
                    }
                    
                    .fi-simple-page .fi-input::placeholder {
                        color: rgba(255, 255, 255, 0.4) !important;
                    }
                    
                    /* Password reveal button */
                    .fi-simple-page .fi-input-wrp button {
                        color: rgba(255, 255, 255, 0.5) !important;
                    }
                    
                    .fi-simple-page .fi-input-wrp button:hover {
                        color: rgba(255, 255, 255, 0.8) !important;
                    }
                    
                    /* Checkbox */
                    .fi-simple-page .fi-checkbox-label {
                        color: rgba(255, 255, 255, 0.7) !important;
                    }
                    
                    .fi-simple-page .fi-checkbox-input {
                        border-color: rgba(255, 255, 255, 0.2) !important;
                        background: transparent !important;
                    }
                    
                    .fi-simple-page .fi-checkbox-input:checked {
                        background-color: #265DA6 !important;
                        border-color: #265DA6 !important;
                    }
                    
                    /* Submit Button */
                    .fi-simple-page .fi-btn-primary {
                        background: linear-gradient(135deg, #265DA6, #23468C) !important;
                        border: none !important;
                        border-radius: 10px !important;
                        box-shadow: 0 4px 15px rgba(38, 93, 166, 0.4) !important;
                        transition: all 0.3s ease !important;
                    }
                    
                    .fi-simple-page .fi-btn-primary:hover {
                        transform: translateY(-2px) !important;
                        box-shadow: 0 6px 20px rgba(38, 93, 166, 0.5) !important;
                    }
                </style>
            ',
            scopes: static::class,
        );

        // Register demo credentials display before the form
        FilamentView::registerRenderHook(
            PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
            fn (): string => '
                <div style="
                    background: linear-gradient(135deg, rgba(38, 93, 166, 0.2), rgba(35, 70, 140, 0.15));
                    border: 1px solid rgba(38, 93, 166, 0.3);
                    border-radius: 12px;
                    padding: 16px;
                    margin-bottom: 20px;
                ">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                        <div style="
                            width: 28px;
                            height: 28px;
                            background: linear-gradient(135deg, #265DA6, #23468C);
                            border-radius: 6px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        ">
                            <svg style="width: 16px; height: 16px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span style="font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.9); letter-spacing: 0.05em; text-transform: uppercase;">Demo Credentials</span>
                    </div>
                    <div style="
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: 8px 12px;
                        background: rgba(0, 0, 0, 0.2);
                        border-radius: 8px;
                        margin-bottom: 6px;
                        cursor: pointer;
                        transition: background 0.2s;
                    " onclick="navigator.clipboard.writeText(\'admin@demo.com\'); this.querySelector(\'.val\').textContent=\'Copied!\'; setTimeout(() => this.querySelector(\'.val\').textContent=\'admin@demo.com\', 1000)">
                        <span style="font-size: 12px; color: rgba(255, 255, 255, 0.5); font-weight: 500;">Email</span>
                        <span class="val" style="font-family: ui-monospace, monospace; font-size: 13px; color: #60a5fa; font-weight: 500;">admin@demo.com</span>
                    </div>
                    <div style="
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: 8px 12px;
                        background: rgba(0, 0, 0, 0.2);
                        border-radius: 8px;
                        cursor: pointer;
                        transition: background 0.2s;
                    " onclick="navigator.clipboard.writeText(\'admin\'); this.querySelector(\'.val\').textContent=\'Copied!\'; setTimeout(() => this.querySelector(\'.val\').textContent=\'admin\', 1000)">
                        <span style="font-size: 12px; color: rgba(255, 255, 255, 0.5); font-weight: 500;">Password</span>
                        <span class="val" style="font-family: ui-monospace, monospace; font-size: 13px; color: #60a5fa; font-weight: 500;">admin</span>
                    </div>
                </div>
            ',
            scopes: static::class,
        );
    }
}
