<?php
// Template Editor (Shared for Add/Edit)
$isEdit = isset($template);
$title = $isEdit ? 'Edit Template' : 'New Template';
$initialContent = $template['content'] ?? '<div style="border: 1px solid #000; padding: 10px; width: 300px; background-color: #fff;">
    <h3>{{dns_name}}</h3>
    <p>User: {{username}}</p>
    <p>Pass: {{password}}</p>
    <p>Price: {{price}}</p>
    <p>Valid: {{validity}}</p>
</div>';
require_once ROOT.'/app/Views/layouts/header_main.php';
?>

<style>
    /* Make CodeMirror fill the entire container height */
    #editorContainer .cm-editor {
        height: 100%;
    }
</style>

<div class="flex flex-col lg:h-[calc(100vh-8rem)] gap-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 flex-shrink-0">
        <div class="flex items-center gap-4">
            <a href="/settings/voucher-templates" class="text-accents-5 hover:text-foreground transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <h1 class="text-xl font-bold tracking-tight text-foreground"><?= $title ?></h1>
        </div>
        
        <form id="templateForm" action="<?= $isEdit ? '/settings/voucher-templates/update' : '/settings/voucher-templates/store' ?>" method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto">
            <?php if ($isEdit) { ?>
                <input type="hidden" name="id" value="<?= $template['id'] ?>">
            <?php } ?>
            
            <input type="text" name="name" value="<?= htmlspecialchars($template['name'] ?? 'New Template') ?>" required class="form-input w-full lg:w-64" placeholder="Template Name" data-i18n-placeholder="settings.template_name">
            
            <button type="submit" class="btn btn-primary h-9 justify-center">
                <i data-lucide="save" class="w-4 h-4 mr-2"></i> <span data-i18n="common.save">Save</span>
            </button>
        </form>
    </div>

    <!-- Editor Layout -->
    <div class="flex-1 flex flex-col lg:flex-row gap-6 overflow-hidden min-h-0">
        
        <!-- Left: Code Editor -->
        <div class="flex-1 flex flex-col bg-background border border-accents-2 rounded-lg overflow-hidden min-w-0 min-h-0 h-[400px] sm:h-[500px] lg:h-auto shrink-0">
            <div class="bg-accents-1 px-4 py-3 border-b border-accents-2 flex items-center justify-between gap-4">
                <span class="text-xs font-mono font-medium text-accents-5 whitespace-nowrap" data-i18n="settings.html_source">HTML Source</span>
                
                <!-- Scrollable Toolbar -->
                <div class="flex-1 flex gap-2 overflow-x-auto no-scrollbar mask-fade-right py-1 px-1">
                     <div class="flex gap-2 whitespace-nowrap">
                         <!-- Help Button -->
                         <button type="button" onclick="toggleDocs()" class="text-xs px-2 py-1 bg-accents-2 hover:bg-accents-3 text-accents-8 rounded transition-colors flex items-center gap-1">
                            <i data-lucide="help-circle" class="w-3 h-3"></i> <span data-i18n="settings.docs">Docs</span>
                         </button>
                         
                         <button type="button" onclick="insertVar('{{username}}')" class="text-xs px-2 py-1 bg-background border border-accents-2 rounded hover:bg-accents-2 transition-colors">{{username}}</button>
                         <button type="button" onclick="insertVar('{{password}}')" class="text-xs px-2 py-1 bg-background border border-accents-2 rounded hover:bg-accents-2 transition-colors">{{password}}</button>
                         <button type="button" onclick="insertVar('{{price}}')" class="text-xs px-2 py-1 bg-background border border-accents-2 rounded hover:bg-accents-2 transition-colors">{{price}}</button>
                         <button type="button" onclick="insertVar('{{validity}}')" class="text-xs px-2 py-1 bg-background border border-accents-2 rounded hover:bg-accents-2 transition-colors">{{validity}}</button>
                         <button type="button" onclick="insertVar('{{timelimit}}')" class="text-xs px-2 py-1 bg-background border border-accents-2 rounded hover:bg-accents-2 transition-colors">{{timelimit}}</button>
                         <button type="button" onclick="insertVar('{{datalimit}}')" class="text-xs px-2 py-1 bg-background border border-accents-2 rounded hover:bg-accents-2 transition-colors">{{datalimit}}</button>
                         <button type="button" onclick="insertVar('{{profile}}')" class="text-xs px-2 py-1 bg-background border border-accents-2 rounded hover:bg-accents-2 transition-colors">{{profile}}</button>
                         <button type="button" onclick="insertVar('{{dns_name}}')" class="text-xs px-2 py-1 bg-background border border-accents-2 rounded hover:bg-accents-2 transition-colors">{{dns_name}}</button>
                         <button type="button" onclick="insertVar('{{login_url}}')" class="text-xs px-2 py-1 bg-background border border-accents-2 rounded hover:bg-accents-2 transition-colors">{{login_url}}</button>
                         <button type="button" onclick="insertVar('{{qrcode}}')" class="text-xs px-2 py-1 bg-background border border-accents-2 rounded hover:bg-accents-2 transition-colors" title="Insert QR Code">{{qrcode}}</button>
                     </div>
                </div>
            </div>
<div id="editorContainer" class="flex-1 w-full font-mono text-sm h-full min-h-0 border-none outline-none overflow-hidden bg-background"></div>
            <!-- Hidden textarea for form submission -->
            <textarea id="codeEditor" name="content" form="templateForm" class="hidden"><?= htmlspecialchars($initialContent) ?></textarea>
        </div>

        <!-- Right: Preview -->
        <div class="flex-1 flex flex-col border border-accents-2 rounded-lg bg-accents-1 relative overflow-hidden min-h-[500px] shrink-0 lg:h-auto lg:min-h-0">
            <div class="bg-background px-4 py-2 border-b border-accents-2 flex items-center justify-between">
                <span class="text-xs font-mono font-medium text-accents-5" data-i18n="settings.live_preview">Live Preview</span>
                <i data-lucide="refresh-cw" class="w-4 h-4 text-accents-5 cursor-pointer hover:text-foreground" onclick="updatePreview()"></i>
            </div>
            <!-- Scaled Preview Container - White Paper Simulation -->
             <div class="flex-1 overflow-auto flex items-center justify-center p-8 bg-zinc-900/50">
                 <div id="previewContainer" class="bg-white text-black shadow-xl p-4 min-w-[300px] min-h-[300px] flex items-center justify-center rounded-sm">
                     <!-- Content Injected Here -->
                 </div>
             </div>
        </div>
    </div>
</div>

<script src="/assets/js/qrious.min.js"></script>
<script src="/assets/js/vendor/editor.bundle.js"></script>
</div>

<!-- Documentation Modal -->
<div id="docsModal" class="fixed inset-0 z-50 hidden transition-all duration-200">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-background/80 backdrop-blur-sm opacity-0 transition-opacity duration-200" onclick="toggleDocs()"></div>
    
    <!-- Modal Content -->
    <div class="absolute inset-x-0 top-[10%] mx-auto max-w-2xl bg-background border border-accents-2 shadow-2xl rounded-xl overflow-hidden flex flex-col max-h-[80vh] opacity-0 scale-95 transition-all duration-200 origin-top">
        <div class="px-6 py-4 border-b border-accents-2 flex items-center justify-between">
            <h2 class="text-lg font-bold" data-i18n="settings.template_variables">Template Variables</h2>
            <button onclick="toggleDocs()" class="text-accents-5 hover:text-foreground">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <div class="p-6 overflow-y-auto custom-scrollbar">
            <div class="prose dark:prose-invert max-w-none">
                <p class="text-sm text-accents-5 mb-4" data-i18n="settings.variables_desc">Use these variables in your HTML source. They will be replaced with actual user data during printing.</p>

                <!-- NEW: Editor Shortcuts & Emmet -->
                <h3 class="text-sm font-bold uppercase text-accents-5 mb-2" data-i18n="settings.editor_shortcuts">Editor Shortcuts & Emmet</h3>
                <div class="p-4 rounded bg-accents-1 border border-accents-2 mb-6">
                    <p class="text-sm text-accents-6 mb-4" data-i18n="settings.emmet_desc">Use Emmet abbreviations for fast coding. Look for the dotted underline, then press Tab.</p>
                    <ul class="text-sm space-y-4 list-disc list-inside text-accents-6">
                        <li data-i18n="settings.tip_emmet_html"><strong>HTML Boilerplate</strong>: Type <code>!</code> then <code>Tab</code>.</li>
                        <li data-i18n="settings.tip_emmet_tag"><strong>Auto-Tag</strong>: Type <code>.container</code> then <code>Tab</code> for <code>&lt;div class="container"&gt;</code>.</li>
                        <li data-i18n="settings.tip_color_picker"><strong>Color Picker</strong>: Click the color box next to hex codes (e.g., #ff0000) to open the picker.</li>
                        <li data-i18n="settings.tip_syntax_error"><strong>Syntax Error</strong>: Red squiggles (and dots in the gutter) show structure errors like mismatched tags.</li>
                    </ul>
                </div>
                
                <h3 class="text-sm font-bold uppercase text-accents-5 mb-2">Basic Variables</h3>
                <div class="grid grid-cols-1 gap-2 mb-6">
                    <div class="flex items-center justify-between p-2 rounded bg-accents-1 border border-accents-2">
                        <code class="text-sm font-mono text-primary">{{username}}</code>
                        <span class="text-sm text-accents-6">Username</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded bg-accents-1 border border-accents-2">
                        <code class="text-sm font-mono text-primary">{{password}}</code>
                        <span class="text-sm text-accents-6">Password</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded bg-accents-1 border border-accents-2">
                        <code class="text-sm font-mono text-primary">{{price}}</code>
                        <span class="text-sm text-accents-6">Price (formatted)</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded bg-accents-1 border border-accents-2">
                        <code class="text-sm font-mono text-primary">{{validity}}</code>
                        <span class="text-sm text-accents-6">Validity (Raw)</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded bg-accents-1 border border-accents-2">
                        <code class="text-sm font-mono text-primary">{{timelimit}}</code>
                        <span class="text-sm text-accents-6">Time Limit (Formatted)</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded bg-accents-1 border border-accents-2">
                        <code class="text-sm font-mono text-primary">{{datalimit}}</code>
                        <span class="text-sm text-accents-6">Data Limit (Formatted)</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded bg-accents-1 border border-accents-2">
                        <code class="text-sm font-mono text-primary">{{profile}}</code>
                        <span class="text-sm text-accents-6">User Profile Name</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded bg-accents-1 border border-accents-2">
                        <code class="text-sm font-mono text-primary">{{dns_name}}</code>
                        <span class="text-sm text-accents-6">DNS Name / Hotspot Name</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded bg-accents-1 border border-accents-2">
                        <code class="text-sm font-mono text-primary">{{login_url}}</code>
                        <span class="text-sm text-accents-6">Login URL (http://dnsname/login)</span>
                    </div>
                </div>

                <h3 class="text-sm font-bold uppercase text-accents-5 mb-2" data-i18n="settings.qr_code">QR Code</h3>
                <div class="p-4 rounded bg-accents-1 border border-accents-2">
                    <p class="mb-2"><code class="text-sm font-mono text-primary">{{qrcode}}</code></p>
                    <p class="text-sm text-accents-6 mb-4" data-i18n="settings.qr_desc">Generates a QR Code containing the Login URL with username and password.</p>
                    
                    <h4 class="text-xs font-bold uppercase text-accents-5 mb-2" data-i18n="settings.custom_attributes">Custom Attributes</h4>
                    <ul class="text-sm space-y-2 list-disc list-inside text-accents-6 mb-4">
                        <li><strong class="text-foreground">fg</strong>: Foreground color (name or hex)</li>
                        <li><strong class="text-foreground">bg</strong>: Background color (name or hex)</li>
                        <li><strong class="text-foreground">size</strong>: Size in pixels (default 100)</li>
                        <li><strong class="text-foreground">padding</strong>: Padding around QR code (pixels)</li>
                        <li><strong class="text-foreground">rounded</strong>: Corner radius (pixels)</li>
                    </ul>
                    
                    <h4 class="text-xs font-bold uppercase text-accents-5 mb-1" data-i18n="settings.examples">Examples:</h4>
                    <div class="bg-background p-2 rounded border border-accents-2 space-y-1 font-mono text-xs">
                        <p>{{qrcode fg=red bg=yellow}}</p>
                        <p>{{qrcode size=200 padding=10 rounded=15}}</p>
                        <p>{{qrcode fg=#000 bg=#fff}}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="px-6 py-4 border-t border-accents-2 bg-accents-1 flex justify-end">
            <button onclick="toggleDocs()" class="btn btn-secondary" data-i18n="common.cancel">Close</button>
        </div>
    </div>
</div>

<script>
    // --- Documentation Modal Animation ---
    function toggleDocs() {
        const modal = document.getElementById('docsModal');
        const content = modal.querySelector('div.bg-background'); // The modal card
        
        if (modal.classList.contains('hidden')) {
            // Open
            modal.classList.remove('hidden');
            // Small delay to allow display:block to apply before opacity transition
            setTimeout(() => {
                modal.firstElementChild.classList.remove('opacity-0'); // Backdrop
                content.classList.remove('opacity-0', 'scale-95');
                content.classList.add('opacity-100', 'scale-100');
            }, 10);
        } else {
            // Close
            modal.firstElementChild.classList.add('opacity-0');
            content.classList.remove('opacity-100', 'scale-100');
            content.classList.add('opacity-0', 'scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200); // Match duration
        }
    }

    // --- Editor Logic (CodeMirror 6) ---
    const textarea = document.getElementById('codeEditor');
    const container = document.getElementById('editorContainer');
    const preview = document.getElementById('previewContainer');
    const isDark = document.documentElement.classList.contains('dark');
    
    let cmView = null;

    function initEditor() {
        if (typeof MivoEditor === 'undefined') {
            console.error('CodeMirror bundle not loaded yet.');
            return;
        }

        cmView = MivoEditor.init({
            parent: container,
            initialValue: textarea.value,
            dark: isDark,
            onChange: (val) => {
                textarea.value = val;
                updatePreview();
            }
        });

        // Set focus
        cmView.focus();
    }

    function insertVar(text) {
        if (!cmView) return;
        
        const selection = cmView.state.selection.main;
        cmView.dispatch({
            changes: { from: selection.from, to: selection.to, insert: text },
            selection: { anchor: selection.from + text.length }
        });
        cmView.focus();
    }
    
    // Live Preview Logic

    // Inject Logo Map from PHP
    const logoMap = <?= json_encode($logoMap ?? []) ?>;

    // Sample Data for Preview
    const sampleData = {
        '{{username}}': 'user123',
        '{{password}}': 'pass789',
        '{{price}}': 'Rp 5.000',
        '{{validity}}': ' 3 Hours',
        '{{timelimit}}': ' 3 Hours',
        '{{datalimit}}': '500 MB',
        '{{profile}}': 'General',
        '{{comment}}': 'mivo',
        '{{hotspotname}}': 'Mivo Hotspot',
        '{{num}}': '1',
        '{{logo}}': '<img src="/assets/img/logo.png" style="height:30px;border:0;">', // Default placeholder
        '{{dns_name}}': 'hotspot.mivo', 
        '{{login_url}}': 'http://hotspot.mivo/login',
    };

    function updatePreview() {
        let content = textarea.value;
        
        // 1. Handle {{logo id=...}}
        content = content.replace(/\{\{logo\s+id=['"]?([^'"\s]+)['"]?\}\}/gi, (match, id) => {
            if (logoMap[id]) {
                return `<img src="${logoMap[id]}" style="height:50px; width:auto;">`;
            }
            return '';
        });

        // 2. Simple Replace for other variables
        for (const [key, value] of Object.entries(sampleData)) {
            content = content.replaceAll(key, value);
        }
        
        // 3. Handle QR Code - Local Generation with Attributes
        content = content.replace(/\{\{qrcode(?:\s+(.*?))?\}\}/gi, (match, attrs) => {
            const qrValue = sampleData['{{login_url}}'] + '?user=' + sampleData['{{username}}'] + '&password=' + sampleData['{{password}}'];
            
            let opts = {
                value: qrValue,
                size: 100,
                foreground: 'black',
            };
            
            let roundedStyle = '';
            
            // Default styling options
            let styleOpts = {
                padding: 0,
                background: 'white',
                logo: null
            };
            
            opts.backgroundAlpha = 0;

            if (attrs) {
                const fgMatch = attrs.match(/fg\s*=\s*['"]?([^'"\s]+)['"]?/i);
                if (fgMatch) opts.foreground = fgMatch[1];

                const bgMatch = attrs.match(/bg\s*=\s*['"]?([^'"\s]+)['"]?/i);
                if (bgMatch) styleOpts.background = bgMatch[1];
                
                const sizeMatch = attrs.match(/size\s*=\s*['"]?(\d+)['"]?/i);
                if (sizeMatch) opts.size = parseInt(sizeMatch[1]);
                
                const paddingMatch = attrs.match(/padding\s*=\s*['"]?(\d+)['"]?/i);
                if (paddingMatch) styleOpts.padding = parseInt(paddingMatch[1]);
                
                const roundedMatch = attrs.match(/rounded\s*=\s*['"]?(\d+)['"]?/i);
                if (roundedMatch) roundedStyle = `border-radius: ${roundedMatch[1]}px;`;

                const logoMatch = attrs.match(/logo\s*=\s*['"]?([^'"\s]+)['"]?/i);
                if (logoMatch) styleOpts.logo = logoMatch[1];
            }

            const qr = new QRious(opts);
            const qrDataUrl = qr.toDataURL();
            
            // Construct compound style
            const cssBg = `background-color: ${styleOpts.background};`;
            const cssPadding = styleOpts.padding ? `padding: ${styleOpts.padding}px;` : '';
            const baseStyle = `display: inline-block; vertical-align: middle; ${cssBg} ${cssPadding} ${roundedStyle}`;
            
            // If Logo requested, we need Canvas manipulation.
            if (styleOpts.logo && logoMap[styleOpts.logo]) {
                 // Create a canvas (not added to DOM) to draw composite
                 const canvas = document.createElement('canvas');
                 const ctx = canvas.getContext('2d');
                 const size = opts.size;
                 canvas.width = size;
                 canvas.height = size;
                 
                 // Since QRious gives dataURL, we need to load it back
                 // But wait, this is synchronous preview. Loading image is async.
                 // We can return a placeholder or handle async?
                 // Simple hack: Return an IMG with a unique class, script loads it? 
                 // Or better: Just render the QR + Logo overlay using CSS absolute positioning?
                 // Print view uses Canvas. Live Preview uses innerHTML.
                 // CSS Overlay is easiest for preview, but Print View logic uses Canvas-drawing.
                 // Let's stick to Canvas drawing for 1:1 fidelity, BUT we need async handling.
                 // We can use a unique ID + script injection like print view? 
                 // Yes, let's replicate print view logic.
                 
                 const uniqueId = 'preview-qr-' + Math.random().toString(36).substr(2, 9);
                 const logoPath = logoMap[styleOpts.logo];
                 
                 // Generate Script to execute after insertion
                 // We need to delay execution until element exists.
                 // Note: innerHTML scripts don't run automatically in all contexts, but updatePreview sets innerHTML.
                 // Scripts inserted via innerHTML do NOT execute.
                 // We need another way or just CSS overlay for preview.
                 
                 // CSS Overlay Approach for Preview (Simpler/Faster)
                 // <div style="position:relative; ..."> 
                 //    <img src="QR">
                 //    <img src="LOGO" style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); width:20%;">
                 // </div>
                 
                 return `<div style="position:relative; ${baseStyle}">
                            <img src="${qrDataUrl}" style="display:block;">
                            <img src="${logoPath}" style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); width:20%; height:auto;">
                         </div>`;
            }

            return '<img src="' + qrDataUrl + '" alt="QR Code" style="' + baseStyle + '">';
        });
        
        preview.innerHTML = content;
    }

    // Init
    document.addEventListener('DOMContentLoaded', () => {
        initEditor();
        updatePreview();
    });

    // Theme Switch Recognition
    window.addEventListener('languageChanged', () => {
        // Not language, but theme toggle button often triggers layout shifts. 
        // We might need a MutationObserver if we want to live-toggle CM theme.
        // For now, reload or manual re-init on theme toggle could work.
    });

    // Watch for theme changes globally
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === 'class' && mutation.target === document.documentElement) {
                // Theme changed
                // CodeMirror 6 themes are extensions, changing them requires re-configuring the state.
                // For simplicity, let's just re-init everything if theme changes.
                const newIsDark = document.documentElement.classList.contains('dark');
                if (cmView) {
                    const content = cmView.state.doc.toString();
                    container.innerHTML = '';
                    cmView = null;
                    textarea.value = content;
                    initEditor();
                }
            }
        });
    });
    observer.observe(document.documentElement, { attributes: true });

</script>

<?php require_once ROOT.'/app/Views/layouts/footer_main.php'; ?>
