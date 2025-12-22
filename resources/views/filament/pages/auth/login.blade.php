<x-filament-panels::page.simple>
    {{-- Demo Credentials Card with Click-to-Copy --}}
    <div class="demo-credentials-card">
        <div class="demo-header">
            <div class="demo-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                </svg>
            </div>
            <span class="demo-title">Demo Credentials</span>
            <span class="demo-hint">Click to copy</span>
        </div>
        <div class="demo-row" onclick="copyToClipboard(this, 'admin@sifco.dz')">
            <span class="demo-label">Email</span>
            <div class="demo-value-container">
                <span class="demo-value">admin@sifco.dz</span>
                <span class="demo-copied">Copied!</span>
            </div>
        </div>
        <div class="demo-row" onclick="copyToClipboard(this, 'admin')">
            <span class="demo-label">Password</span>
            <div class="demo-value-container">
                <span class="demo-value">admin</span>
                <span class="demo-copied">Copied!</span>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(element, text) {
            navigator.clipboard.writeText(text).then(() => {
                // Add copied class to show feedback
                element.classList.add('copied');
                
                // Also fill in the form fields
                if (text.includes('@')) {
                    document.querySelector('input[name=email]').value = text;
                } else {
                    document.querySelector('input[name=password]').value = text;
                }
                
                // Remove copied class after animation
                setTimeout(() => {
                    element.classList.remove('copied');
                }, 1500);
            });
        }
    </script>

    {{-- Login Form - properly rendered via content schema --}}
    {{ $this->content }}
</x-filament-panels::page.simple>

