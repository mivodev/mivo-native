<?php
$title = 'Check Voucher Status';
include ROOT.'/app/Views/layouts/header_public.php';
?>

    <!-- Main Container -->
    <main class="flex-grow flex items-center justify-center w-full">
    <div class="w-full max-w-lg z-10 p-4 md:p-6 animate-fade-in-up">
        
        <div class="flex flex-col space-y-8 text-center">
            
            <!-- Brand -->
            <div class="flex justify-center">
                <div class="relative group">
                     <img src="/assets/img/logo-m.svg" alt="MIVO Logo" class="relative h-12 w-auto block dark:hidden transform transition-transform duration-300 group-hover:scale-105">
                     <img src="/assets/img/logo-m-dark.svg" alt="MIVO Logo" class="relative h-12 w-auto hidden dark:block transform transition-transform duration-300 group-hover:scale-105">
                </div>
            </div>

            <!-- Text -->
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight mb-3 text-foreground" data-i18n="status.check_title">Check Voucher Status</h1>
                <p class="text-accents-5 text-sm md:text-base leading-relaxed max-w-sm mx-auto" data-i18n="status.check_desc">
                    Monitor your data usage and voucher validity in real-time without needing to re-login.
                </p>
            </div>

            <!-- Check Form -->
            <div class="card p-6 sm:p-8 relative overflow-hidden w-full text-left">
                <form onsubmit="checkStatus(event)" class="relative z-10">
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs font-semibold text-accents-5 uppercase tracking-wider ml-1 mb-1 block" data-i18n="status.voucher_code_label">Voucher Code</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                    <i data-lucide="ticket" class="h-4 w-4 text-accents-5"></i>
                                </div>
                                <input type="text" id="voucher-code" class="form-input pl-10 h-11 text-lg font-mono tracking-wide" placeholder="Ex: QWASZX" data-i18n="status.code_placeholder" required autofocus autocomplete="off">
                            </div>
                        </div>
                        
                        <button type="submit" id="chk-btn" class="w-full btn btn-primary h-11 text-base font-bold shadow-lg hover:shadow-primary/20">
                            <span id="btn-text" data-i18n="status.check_now">Check Now</span>
                            <i id="btn-loader" data-lucide="loader-2" class="w-4 h-4 animate-spin hidden"></i>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
    </main>

    <?php include ROOT.'/app/Views/layouts/footer_public.php'; ?>

    <!-- Logic Script -->
    <script>

        async function checkStatus(e) {
            e.preventDefault();
            const code = document.getElementById('voucher-code').value.trim();
            if (!code) return;

            const btn = document.getElementById('chk-btn');
            const btnText = document.getElementById('btn-text');
            const loader = document.getElementById('btn-loader');
            
            // Set Loading
            btn.disabled = true;
            btnText.classList.add('hidden');
            loader.classList.remove('hidden');

            try {
                const pathParts = window.location.pathname.split('/');
                const session = pathParts[1]; 

                const response = await fetch('/api/status/check', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ session: session, code: code })
                });

                const json = await response.json();

                if (json.success) {
                    const d = json.data;
                    
                    // Build HTML for SweetAlert
                    // Status Badge Logic
                    // Status Badge Logic (Glassmorphism)
                    let statusColor = 'bg-blue-500/10 text-blue-600 border-blue-200 dark:border-blue-800 dark:text-blue-400';
                    if (d.status === 'active') statusColor = 'bg-emerald-500/10 text-emerald-600 border-emerald-200 dark:border-emerald-800 dark:text-emerald-400';
                    if (d.status === 'expired') statusColor = 'bg-slate-500/10 text-slate-600 border-slate-200 dark:border-slate-800 dark:text-slate-400';
                    if (d.status === 'limited') statusColor = 'bg-orange-500/10 text-orange-600 border-orange-200 dark:border-orange-800 dark:text-orange-400';
                    if (d.status === 'locked') statusColor = 'bg-red-500/10 text-red-600 border-red-200 dark:border-red-800 dark:text-red-400';

                    const htmlContent = `
                        <div class="text-left mt-6 relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md shadow-2xl ring-1 ring-black/5 dark:ring-white/5">
                            <!-- Background Decoration -->
                            <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
                            <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>
                            
                            <!-- Header -->
                            <div class="relative p-5 md:p-6 border-b border-white/10 flex justify-between items-center bg-white/10 dark:bg-black/20">
                                <div>
                                    <span class="text-[10px] text-accents-5 font-bold uppercase tracking-widest block mb-0.5">${window.i18n.t('status.code')}</span>
                                    <span class="font-mono text-xl md:text-2xl font-black tracking-tighter text-foreground">${d.username}</span>
                                </div>
                                <div class="px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-[0.15em] border ${statusColor} shadow-sm backdrop-blur-sm bg-opacity-80">
                                    ${d.status}
                                </div>
                            </div>

                            <!-- Data Usage Bar -->
                            <div class="relative p-5 md:p-6 pb-2">
                                <div class="flex justify-between items-end mb-2">
                                    <span class="text-xs font-bold text-accents-5 uppercase tracking-wide">${window.i18n.t('status.data_remaining')}</span>
                                    <span class="text-lg font-black text-blue-600 dark:text-blue-400 font-mono tracking-tight">${d.data_left}</span>
                                </div>
                                <div class="w-full h-2.5 bg-accents-2 rounded-full overflow-hidden shadow-inner ring-1 ring-black/5 dark:ring-white/5">
                                    <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-600 shadow-lg relative overflow-hidden" style="width: 100%">
                                        <div class="absolute inset-0 bg-white/20 animate-[shimmer_2s_infinite]"></div>
                                    </div> 
                                </div>
                                <div class="text-right mt-1.5">
                                     <span class="text-[10px] font-semibold text-accents-4 uppercase tracking-wider">${window.i18n.t('status.used')}: <span class="text-foreground">${d.data_used}</span></span>
                                </div>
                            </div>

                            <!-- Details Table -->
                            <div class="p-5 md:p-6 pt-2">
                                <table class="w-full text-sm text-left">
                                    <tbody class="divide-y divide-white/10">
                                        <tr>
                                            <td class="py-3 text-accents-5 font-bold uppercase tracking-wide text-[10px]">${window.i18n.t('status.package')}</td>
                                            <td class="py-3 text-right font-bold text-foreground font-mono">${d.profile}</td>
                                        </tr>
                                        <tr>
                                            <td class="py-3 text-accents-5 font-bold uppercase tracking-wide text-[10px]">${window.i18n.t('status.validity')}</td>
                                            <td class="py-3 text-right font-bold text-foreground font-mono">${d.validity}</td>
                                        </tr>
                                        <tr>
                                            <td class="py-3 text-accents-5 font-bold uppercase tracking-wide text-[10px]">${window.i18n.t('status.uptime')}</td>
                                            <td class="py-3 text-right font-medium text-foreground font-mono">${d.uptime_used}</td>
                                        </tr>
                                        <tr>
                                            <td class="py-3 text-accents-5 font-bold uppercase tracking-wide text-[10px]">${window.i18n.t('status.expires')}</td>
                                            <td class="py-3 text-right font-medium text-foreground font-mono">${d.expiration}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;

                    Mivo.alert('success', window.i18n.t('status.details_title'), htmlContent, {
                        customClass: { popup: 'w-full max-w-md' } // Override width only, others merged
                    });

                } else {
                    Mivo.alert('error', 
                        window.i18n.t('status.not_found_title'), 
                        json.message && json.message !== 'Voucher Not Found' ? json.message : window.i18n.t('status.not_found_desc'),
                        {
                            confirmButtonText: window.i18n.t('status.try_again'),
                            didClose: () => {
                                 setTimeout(() => {
                                     const el = document.getElementById('voucher-code');
                                     if(el) { el.focus(); el.select(); }
                                 }, 100);
                            }
                        }
                    );
                }

            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: window.i18n.t('errors.500_title'),
                    text: window.i18n.t('errors.500_desc'),
                    confirmButtonText: 'Close',
                    customClass: {
                        popup: 'swal2-premium-card',
                        confirmButton: 'btn btn-secondary',
                    },
                    buttonsStyling: false
                });
            } finally {
                btn.disabled = false;
                btnText.classList.remove('hidden');
                loader.classList.add('hidden');
            }
        }
    </script>

