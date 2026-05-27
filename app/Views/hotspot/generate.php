<?php require_once ROOT.'/app/Views/layouts/header_main.php'; ?>
<?php require_once ROOT.'/app/Views/layouts/sidebar_session.php'; ?>

<!-- Content Inside max-w-7xl (Opened by sidebar.php) -->

<!-- Header -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-foreground" data-i18n="hotspot_generate.title">Generate Vouchers</h1>
        <p class="text-sm text-accents-5" data-i18n="hotspot_generate.form.subtitle" data-i18n-params='{"name": "<?= htmlspecialchars($session) ?>"}'>Create multiple hotspot vouchers in batch for: <span class="font-medium text-foreground"><?= htmlspecialchars($session) ?></span></p>
    </div>
    <a href="/<?= htmlspecialchars($session) ?>/hotspot/users" class="btn btn-secondary" data-i18n="common.back">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
        Back to Users
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Main Form Column -->
    <div class="lg:col-span-2">
        <div class="card p-0 overflow-hidden border-accents-2 shadow-sm">
            <div class="p-6 border-b border-accents-2 bg-accents-1/30">
                <h3 class="text-lg font-semibold flex items-center gap-2">
                    <div class="p-2 bg-primary/10 rounded-lg text-primary">
                        <i data-lucide="layers" class="w-5 h-5"></i>
                    </div>
                    <span data-i18n="hotspot_generate.form.batch_settings">Batch Generation Settings</span>
                </h3>
            </div>
            
            <form action="/<?= htmlspecialchars($session) ?>/hotspot/generate/process" method="POST" class="p-8 space-y-8">
                <input type="hidden" name="session" value="<?= htmlspecialchars($session) ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Core Settings (Full Width on Mobile, Half on MD) -->
                    <div class="space-y-6">
                         <h4 class="text-xs font-bold text-accents-5 uppercase tracking-wider border-b border-accents-2 pb-2 mb-4" data-i18n="hotspot_generate.form.core_config">Core Config</h4>
                        
                        <!-- Quantity -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-accents-6" data-i18n="hotspot_generate.form.qty">Quantity</label>
                            <div class="input-group">
                                <input type="number" name="qty" class="form-input w-full text-lg font-bold text-primary border-primary/50 focus:border-primary focus:ring-2 focus:ring-primary/20 pr-16" value="1" min="1" required>
                                <div class="input-suffix text-xs font-bold text-accents-4 uppercase" data-i18n="hotspot_users.title">Users</div>
                            </div>
                            <p class="text-xs text-accents-5" data-i18n="hotspot_generate.form.qty_help">Count of vouchers to generate.</p>
                        </div>

                        <!-- Server -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-accents-6" data-i18n="hotspot_generate.form.server">Server</label>
                            <select name="server" class="custom-select w-full" data-search="true">
                                <option value="all">all</option>
                                <?php if (isset($servers) && is_array($servers)) { ?>
                                    <?php foreach ($servers as $srv) { ?>
                                        <option value="<?= htmlspecialchars($srv['name']) ?>">
                                            <?= htmlspecialchars($srv['name']) ?>
                                        </option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                            <p class="text-xs text-accents-5" data-i18n="hotspot_generate.form.server_help">Target Hotspot Instance.</p>
                        </div>

                        <!-- User Mode -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-accents-6" data-i18n="hotspot_generate.form.user_mode">User Mode</label>
                            <select name="userModel" class="custom-select w-full">
                                <option value="up">Username & Password</option>
                                <option value="vc">Username = Password</option>
                            </select>
                            <p class="text-xs text-accents-5" data-i18n="hotspot_generate.form.user_mode_help">Login credential format.</p>
                        </div>

                        <!-- Comment -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-accents-6" data-i18n="hotspot_generate.form.comment">Comment</label>
                             <div class="input-group">
                                 <span class="input-icon">
                                    <i data-lucide="message-square" class="w-4 h-4"></i>
                                </span>
                                <input type="text" name="comment" class="form-input w-full" data-i18n-placeholder="hotspot_generate.form.comment_help" placeholder="Batch note...">
                            </div>
                             <p class="text-xs text-accents-5" data-i18n="hotspot_generate.form.comment_help">Note for this batch.</p>
                        </div>
                    </div>

                    <!-- User Format -->
                    <div class="space-y-6">
                        <h4 class="text-xs font-bold text-accents-5 uppercase tracking-wider border-b border-accents-2 pb-2 mb-4" data-i18n="hotspot_generate.form.user_format">User Format</h4>

                        <!-- Name Length -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-accents-6" data-i18n="hotspot_generate.form.user_length">Name Length</label>
                            <select name="userLength" class="custom-select w-full">
                                <?php for ($i = 3; $i <= 8; $i++) { ?>
                                <option value="<?= $i ?>" <?= $i == 4 ? 'selected' : '' ?>><?= $i ?></option>
                                <?php } ?>
                            </select>
                             <p class="text-xs text-accents-5" data-i18n="hotspot_generate.form.name_length_help">Length of username/password.</p>
                        </div>

                        <!-- Prefix -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-accents-6" data-i18n="hotspot_generate.form.prefix">Prefix</label>
                            <div class="input-group">
                                 <span class="input-icon">
                                    <i data-lucide="type" class="w-4 h-4"></i>
                                </span>
                                <input type="text" name="prefix" class="form-input w-full" data-i18n-placeholder="hotspot_generate.form.prefix_placeholder" placeholder="e.g. VIP-">
                            </div>
                             <p class="text-xs text-accents-5" data-i18n="hotspot_generate.form.prefix_help">Prefix for generated usernames.</p>
                        </div>

                        <!-- Character Set -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-accents-6" data-i18n="hotspot_generate.form.characters">Character Set</label>
                            <select name="char" class="custom-select w-full">
                                <option value="lower">abcd (Lower)</option>
                                <option value="upper">ABCD (Upper)</option>
                                <option value="uppernumber">ABCD2345 (Upper + Num)</option>
                                <option value="lowernumber">abcd2345 (Lower + Num)</option>
                                <option value="number">12345 (Numbers)</option>
                                <option value="mix">aBcD2345 (Mix)</option>
                            </select>
                             <p class="text-xs text-accents-5" data-i18n="hotspot_generate.form.characters_help">Character types to include.</p>
                        </div>
                    </div>
                </div>

                <!-- Limit Profile (Full Width) -->
                 <div class="space-y-6 pt-2">
                    <h4 class="text-xs font-bold text-accents-5 uppercase tracking-wider border-b border-accents-2 pb-2 mb-4" data-i18n="hotspot_generate.form.limits_profile">Limits & Profile</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Profile -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-accents-6" data-i18n="hotspot_generate.form.profile">Profile</label>
                            <select name="profile" class="custom-select w-full" required data-search="true">
                                <?php foreach ($profiles as $profile) { ?>
                                    <option value="<?= htmlspecialchars($profile['name']) ?>">
                                        <?= htmlspecialchars($profile['name']) ?>
                                    </option>
                                <?php } ?>
                            </select>
                             <p class="text-xs text-accents-5" data-i18n="hotspot_generate.form.profile_help">Apply speed limits from profile.</p>
                        </div>
                        
                        <!-- Empty Placeholder for Grid Alignment -->
                         <div class="hidden md:block"></div>

                        <!-- Time Limit -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-accents-6" data-i18n="hotspot_generate.form.time_limit">Time Limit</label>
                            <div class="flex w-full">
                                <!-- Day -->
                                <div class="input-group flex-1">
                                    <input type="number" name="timelimit_d" min="0" class="form-input w-full pr-8 rounded-r-none border-r-0 focus:z-10 font-mono text-center" placeholder="0">
                                    <div class="input-suffix text-xs font-bold w-8 justify-center">D</div>
                                </div>
                                <!-- Hour -->
                                <div class="input-group flex-1">
                                    <input type="number" name="timelimit_h" min="0" max="23" class="form-input w-full pr-8 rounded-none border-r-0 focus:z-10 font-mono text-center" placeholder="0">
                                    <div class="input-suffix text-xs font-bold w-8 justify-center">H</div>
                                </div>
                                <!-- Minute -->
                                <div class="input-group flex-1">
                                    <input type="number" name="timelimit_m" min="0" max="59" class="form-input w-full pr-8 rounded-l-none focus:z-10 font-mono text-center" placeholder="0">
                                    <div class="input-suffix text-xs font-bold w-8 justify-center">M</div>
                                </div>
                            </div>
                            <p class="text-xs text-accents-5" data-i18n="hotspot_generate.form.time_limit_help">Max uptime (e.g. 1h, 30m).</p>
                        </div>

                        <!-- Data Limit -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-accents-6" data-i18n="hotspot_generate.form.data_limit">Data Limit</label>
                             <div class="flex w-full">
                                <div class="input-group flex-grow z-0 focus-within:z-10">
                                    <div class="input-icon">
                                        <i data-lucide="database" class="w-4 h-4"></i>
                                    </div>
                                    <input type="number" name="datalimit_val" min="0" class="form-input w-full rounded-r-none border-r-0" placeholder="0">
                                </div>
                                <select name="datalimit_unit" class="custom-select w-32 bg-accents-1 font-medium text-accents-6 text-center rounded-l-none border-l-0 -ml-px z-0 focus:z-10">
                                    <option value="MB" selected>MB</option>
                                    <option value="GB">GB</option>
                                </select>
                            </div>
                            <p class="text-xs text-accents-5" data-i18n="hotspot_generate.form.data_limit_help">Max data transfer (MB).</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-6 border-t border-accents-2 flex justify-end gap-3">
                    <a href="/<?= htmlspecialchars($session) ?>/hotspot/users" class="btn btn-secondary" data-i18n="common.cancel">Cancel</a>
                    <button type="submit" class="btn btn-primary px-8 shadow-lg shadow-primary/20 hover:shadow-primary/40 transition-shadow">
                        <i data-lucide="zap" class="w-4 h-4 mr-2"></i>
                        <span data-i18n="hotspot_generate.form.generate">Generate Vouchers</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sticky Quick Tips Column -->
    <div class="lg:col-span-1">
        <div class="sticky top-6 space-y-6">
            <div class="card p-6 bg-accents-1/50 border-accents-2 border-dashed">
                <h3 class="font-semibold mb-4 flex items-center gap-2 text-foreground" data-i18n="hotspot_generate.form.quick_tips">
                    <i data-lucide="lightbulb" class="w-4 h-4 text-yellow-500"></i> 
                    Quick Tips
                </h3>
                <div class="space-y-6 text-sm text-accents-5">
                    <div class="space-y-2">
                        <h4 class="font-medium text-foreground" data-i18n="hotspot_generate.form.user_mode">User Mode</h4>
                        <ul class="space-y-1">
                            <li class="flex gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-primary mt-1.5 flex-shrink-0"></span>
                                <span data-i18n="hotspot_generate.form.tip_user_mode"><strong>User Mode</strong>: UP (separate), VC (same).</span>
                            </li>
                        </ul>
                    </div>
                    <div class="space-y-2">
                        <h4 class="font-medium text-foreground" data-i18n="hotspot_generate.form.user_format">User Format</h4>
                        <ul class="space-y-1">
                            <li class="flex gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-primary mt-1.5 flex-shrink-0"></span>
                                <span data-i18n="hotspot_generate.form.tip_format_examples"><strong>Format Examples</strong>: abcd (lower), 1234 (num), Mix (upper/lower/num).</span>
                            </li>
                        </ul>
                    </div>
                    <div class="space-y-2">
                        <h4 class="font-medium text-foreground" data-i18n="hotspot_profiles.form.limits_queues">Limits</h4>
                        <ul class="space-y-1">
                             <li class="flex gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-primary mt-1.5 flex-shrink-0"></span>
                                <span data-i18n="hotspot_generate.form.tip_limits"><strong>Limits</strong>: Time (e.g. 1h, 30m), Data (e.g. 100MB). Leave empty to use Profile default.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer closes the divs opened in sidebar.php -->
<?php require_once ROOT.'/app/Views/layouts/footer_main.php'; ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Initialize Custom Selects with Search
        if (typeof CustomSelect !== 'undefined') {
            document.querySelectorAll('.custom-select').forEach(select => {
                new CustomSelect(select);
            });
        }
    });
</script>
