<?php require_once ROOT.'/app/Views/layouts/header_main.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Design System</h1>
            <p class="text-accents-5">Component library and style guide for Mivo.</p>
        </div>
        <div class="flex gap-2">
            <button onclick="document.documentElement.classList.remove('dark')" class="btn bg-gray-200 text-gray-800">Light</button>
            <button onclick="document.documentElement.classList.add('dark')" class="btn bg-gray-800 text-white">Dark</button>
        </div>
    </div>

    <!-- 1. Typography -->
    <section class="mb-12">
        <h2 class="text-xl font-semibold mb-4 border-b border-accents-2 pb-2">Typography</h2>
        <div class="card space-y-4">
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight">Heading 1 (text-4xl)</h1>
                <p class="text-sm text-accents-5">Used for landing page titles.</p>
            </div>
            <div>
                <h2 class="text-3xl font-bold tracking-tight">Heading 2 (text-3xl)</h2>
                <p class="text-sm text-accents-5">Used for page titles.</p>
            </div>
            <div>
                <h3 class="text-2xl font-bold tracking-tight">Heading 3 (text-2xl)</h3>
                <p class="text-sm text-accents-5">Used for section headers.</p>
            </div>
            <div>
                <h4 class="text-xl font-semibold tracking-tight">Heading 4 (text-xl)</h4>
                <p class="text-sm text-accents-5">Used for card titles.</p>
            </div>
            <div>
                <p class="text-base text-foreground">Body Text (text-base)</p>
                <p class="text-sm text-accents-5">The quick brown fox jumps over the lazy dog. Used for specific content.</p>
            </div>
            <div>
                <p class="text-sm text-foreground">Small Text (text-sm)</p>
                <p class="text-sm text-accents-5">The quick brown fox jumps over the lazy dog. Used for descriptions.</p>
            </div>
        </div>
    </section>

    <!-- 2. Colors -->
    <section class="mb-12">
        <h2 class="text-xl font-semibold mb-4 border-b border-accents-2 pb-2">Colors (Theming)</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-4 rounded-lg bg-background border border-accents-2">
                <div class="font-bold">Background</div>
                <div class="text-xs text-accents-5">bg-background</div>
            </div>
            <div class="p-4 rounded-lg bg-foreground text-background">
                <div class="font-bold">Foreground</div>
                <div class="text-xs opacity-80">bg-foreground</div>
            </div>
            <div class="p-4 rounded-lg bg-accents-1 border border-accents-2">
                <div class="font-bold">Accents-1</div>
                <div class="text-xs text-accents-5">bg-accents-1</div>
            </div>
            <div class="p-4 rounded-lg bg-accents-2">
                <div class="font-bold">Accents-2</div>
                <div class="text-xs text-accents-5">bg-accents-2</div>
            </div>
            <!-- Status Colors -->
            <div class="p-4 rounded-lg bg-blue-600 text-white">
                <div class="font-bold">Blue (Info)</div>
            </div>
            <div class="p-4 rounded-lg bg-green-600 text-white">
                <div class="font-bold">Green (Success)</div>
            </div>
            <div class="p-4 rounded-lg bg-yellow-500 text-white">
                <div class="font-bold">Yellow (Warning)</div>
            </div>
            <div class="p-4 rounded-lg bg-red-600 text-white">
                <div class="font-bold">Red (Danger)</div>
            </div>
        </div>
    </section>

    <!-- 3. Buttons -->
    <section class="mb-12">
        <h2 class="text-xl font-semibold mb-4 border-b border-accents-2 pb-2">Buttons</h2>
        <div class="card space-y-4">
            <div class="flex flex-wrap gap-4 items-center">
                <button class="btn btn-primary">Primary Button</button>
                <button class="btn btn-secondary">Secondary Button</button>
                <button class="btn btn-danger">Danger Button</button>
                <button class="px-4 py-2 text-sm font-medium text-accents-5 hover:text-foreground transition-colors">Ghost Button</button>
            </div>
            <div class="flex flex-wrap gap-4 items-center">
                <button class="btn btn-primary" disabled>Disabled</button>
                <button class="btn btn-primary w-full sm:w-auto">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                    With Icon
                </button>
                <button class="btn btn-secondary">
                    <i data-lucide="refresh-cw" class="w-4 h-4 mr-2 animate-spin"></i>
                    Loading
                </button>
            </div>
        </div>
    </section>

    <!-- 4. Form Elements -->
    <section class="mb-12">
        <h2 class="text-xl font-semibold mb-4 border-b border-accents-2 pb-2">Forms</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="card space-y-4">
                <h3 class="font-medium text-lg mb-2">Inputs</h3>
                
                <!-- Text Input -->
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-accents-6">Username</label>
                    <input type="text" class="form-input w-full" placeholder="Enter username">
                </div>

                <!-- Password Input -->
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-accents-6">Password</label>
                    <input type="password" class="form-input w-full" value="password123">
                </div>

                <!-- Select -->
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-accents-6">Role</label>
                    <div class="relative">
                        <select class="custom-select w-full">
                            <option>Administrator</option>
                            <option>User</option>
                            <option>Viewer</option>
                        </select>
                         <i data-lucide="chevron-down" class="absolute right-3 top-2.5 h-4 w-4 text-accents-4 pointer-events-none"></i>
                    </div>
                </div>

                <!-- Textarea -->
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-accents-6">Description</label>
                    <textarea class="form-input w-full" rows="3" placeholder="Add some details..."></textarea>
                </div>
            </div>

            <div class="card space-y-4">
                <h3 class="font-medium text-lg mb-2">States & Toggles</h3>

                <!-- Error State -->
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-red-500">Error Input</label>
                    <input type="text" class="form-input w-full border-red-500 focus:ring-red-500 focus:border-red-500" value="Invalid Value">
                    <p class="text-xs text-red-500">This field is required.</p>
                </div>

                <!-- Disabled State -->
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-accents-4">Disabled Input</label>
                    <input type="text" class="form-input w-full bg-accents-1 text-accents-4 cursor-not-allowed" value="Cannot edit me" disabled>
                </div>

                <!-- Checkbox -->
                <div class="flex items-center gap-2 mt-4">
                    <input type="checkbox" id="chk1" class="rounded border-accents-3 text-foreground focus:ring-foreground h-4 w-4">
                    <label for="chk1" class="text-sm">Enable Features</label>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. Cards & Layout -->
    <section class="mb-12">
        <h2 class="text-xl font-semibold mb-4 border-b border-accents-2 pb-2">Cards</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Simple Card -->
            <div class="card">
                <h3 class="font-semibold text-lg mb-2">Simple Card</h3>
                <p class="text-sm text-accents-5">Just a div with `card` class.</p>
            </div>

            <!-- Hover Card -->
            <div class="card hover:border-foreground transition-colors cursor-pointer group">
                <h3 class="font-semibold text-lg mb-2 group-hover:text-blue-500 transition-colors">Hoverable Card</h3>
                <p class="text-sm text-accents-5">Add `hover:border-foreground` for interactive feel.</p>
            </div>

            <!-- Icon Card -->
            <div class="card flex items-start gap-4">
                <div class="p-2 bg-accents-1 rounded-lg">
                    <i data-lucide="box" class="w-6 h-6"></i>
                </div>
                <div>
                     <h3 class="font-semibold text-base">Icon Card</h3>
                     <p class="text-xs text-accents-5">Layout with flexbox.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 5b. Nested Cards -->
    <section class="mb-12">
        <h2 class="text-xl font-semibold mb-4 border-b border-accents-2 pb-2">Nested Cards</h2>
        <div class="card space-y-6">
            <h3 class="font-semibold text-lg">Parent Glass Card</h3>
            <p class="text-sm text-accents-5">This is the main container card.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="sub-card">
                    <h4 class="font-semibold text-base mb-1">Nested Card 1</h4>
                    <p class="text-xs text-accents-5">Standard content inside a generic sub-card container.</p>
                </div>
                
                <div class="sub-card flex items-center gap-3">
                    <div class="p-2 bg-white/10 rounded-lg">
                        <i data-lucide="shield" class="w-5 h-5 text-blue-500"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-base">Nested with Icon</h4>
                        <p class="text-xs text-accents-5">Additional context here.</p>
                    </div>
                </div>
            </div>

            <div class="sub-card p-0 overflow-hidden">
                <div class="p-4 border-b border-white/10">
                    <h4 class="font-semibold">Full Width Sub-Card</h4>
                </div>
                <div class="p-4">
                    <p class="text-sm text-accents-5">This sub-card has a header and content area, simulating a mini-panel.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. Tables -->
    <section class="mb-12">
        <h2 class="text-xl font-semibold mb-4 border-b border-accents-2 pb-2">Data Table</h2>
        
        <div class="card space-y-4">
            <h3 class="font-semibold text-lg">Detailed List</h3>
            <p class="text-sm text-accents-5 mb-4">Using <code>.table-glass</code> class for a premium look.</p>
            
            <div class="table-container">
                <table class="table-glass">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Role</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="flex items-center">
                                    <div class="h-9 w-9 rounded-full bg-accents-2 flex items-center justify-center text-xs font-bold mr-3">
                                         JD
                                    </div>
                                    <div>
                                        <div class="font-medium text-foreground">Jane Cooper</div>
                                        <div class="text-xs text-accents-5">jane.cooper@example.com</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="px-2.5 py-0.5 inline-flex text-xs font-medium rounded-full bg-green-500/10 text-green-600 dark:text-green-400 border border-green-500/20">
                                    Active
                                </span>
                            </td>
                            <td class="text-accents-5">Admin</td>
                             <td class="text-right">
                                <button class="p-1 hover:bg-accents-2 rounded text-accents-5 hover:text-foreground transition-colors">
                                    <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="flex items-center">
                                    <div class="h-9 w-9 rounded-full bg-accents-2 flex items-center justify-center text-xs font-bold mr-3">
                                         CW
                                    </div>
                                    <div>
                                        <div class="font-medium text-foreground">Cody Fisher</div>
                                        <div class="text-xs text-accents-5">cody.fisher@example.com</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="px-2.5 py-0.5 inline-flex text-xs font-medium rounded-full bg-accents-2 text-accents-6 border border-accents-3/20">
                                    Offline
                                </span>
                            </td>
                            <td class="text-accents-5">User</td>
                             <td class="text-right">
                                <button class="p-1 hover:bg-accents-2 rounded text-accents-5 hover:text-foreground transition-colors">
                                    <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="flex items-center">
                                    <div class="h-9 w-9 rounded-full bg-accents-2 flex items-center justify-center text-xs font-bold mr-3">
                                         EW
                                    </div>
                                    <div>
                                        <div class="font-medium text-foreground">Esther Howard</div>
                                        <div class="text-xs text-accents-5">esther.howard@example.com</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="px-2.5 py-0.5 inline-flex text-xs font-medium rounded-full bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                                    On Leave
                                </span>
                            </td>
                            <td class="text-accents-5">Editor</td>
                             <td class="text-right">
                                <button class="p-1 hover:bg-accents-2 rounded text-accents-5 hover:text-foreground transition-colors">
                                    <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="flex items-center justify-between pt-4 border-t border-white/10">
                 <div class="text-xs text-accents-5">Showing 1 to 3 of 12 results</div>
                 <div class="flex gap-2">
                     <button class="btn btn-secondary py-1 px-3 text-xs h-8">Previous</button>
                     <button class="btn btn-secondary py-1 px-3 text-xs h-8">Next</button>
                 </div>
            </div>
        </div>
    </section>

    <!-- 7. Alerts & Confirmations -->
    <section class="mb-12">
        <h2 class="text-xl font-semibold mb-4 border-b border-accents-2 pb-2">Alerts & Confirmations (JS Helper)</h2>
        <div class="card space-y-4">
            <p class="text-sm text-accents-5 mb-4">You can trigger standardized premium alerts using the global <code>Mivo</code> helper.</p>
            
            <div class="flex flex-wrap gap-4 items-center">
                <button onclick="Mivo.alert('success', 'Operation Successful', 'Data has been saved to the database.')" class="btn btn-secondary">
                    Success Alert
                </button>
                <button onclick="Mivo.alert('error', 'Operation Failed', 'There was a connection error.')" class="btn btn-secondary text-red-500">
                    Error Alert
                </button>
                <button onclick="Mivo.alert('warning', 'Low Storage', 'Please check your disk space.')" class="btn btn-secondary text-yellow-500">
                    Warning Alert
                </button>
                <button onclick="Mivo.alert('info', 'System Update', 'New features are available.')" class="btn btn-secondary text-blue-500">
                    Info Alert
                </button>
            </div>

            <div class="pt-4 border-t border-accents-2 mt-4">
                <h3 class="font-medium text-lg mb-2">Confirmation Example</h3>
                <button onclick="Mivo.confirm('Delete Item?', 'Are you sure? This cannot be undone.', 'Yes, Delete', 'Keep it').then((result) => {
                    if (result.isConfirmed) {
                        Mivo.alert('success', 'Deleted!', 'The item has been removed.');
                    }
                })" class="btn btn-danger">
                    Trigger Confirmation
                </button>
            </div>
        </div>
    <!-- Custom Stacking Toasts -->
    <section class="mb-12">
        <h2 class="text-xl font-semibold mb-4 border-b border-accents-2 pb-2">Stacking Toasts (Custom Helper)</h2>
        <div class="card space-y-4">
            <p class="text-sm text-accents-5 mb-4">Premium non-disruptive notifications that stack from the bottom-right.</p>
            
            <div class="flex flex-wrap gap-4 items-center">
                <button onclick="Mivo.toast('success', 'Operation Successful', 'Your changes have been saved.')" class="btn btn-primary bg-emerald-500 hover:bg-emerald-600 border-none text-white">
                    Success Toast
                </button>
                <button onclick="Mivo.toast('error', 'Update Failed', 'An unexpected error occurred.')" class="btn btn-primary bg-red-500 hover:bg-red-600 border-none text-white">
                    Error Toast
                </button>
                <button onclick="Mivo.toast('warning', 'Low Resources', 'Disk space is running low.')" class="btn btn-primary bg-amber-500 hover:bg-amber-600 border-none text-white">
                    Warning Toast
                </button>
                <button onclick="Mivo.toast('info', 'System Update', 'New features are available.')" class="btn btn-primary bg-blue-500 hover:bg-blue-600 border-none text-white">
                    Info Toast
                </button>
            </div>
        </div>
    </section>

</div>

<?php require_once ROOT.'/app/Views/layouts/footer_main.php'; ?>
