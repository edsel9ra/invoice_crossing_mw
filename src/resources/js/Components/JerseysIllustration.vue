<template>
  <div class="flex flex-col items-center justify-center gap-3 w-full">
    <div
      class="relative w-full rounded-2xl bg-gradient-to-br from-slate-50 to-slate-100/50 p-4 sm:p-6 overflow-hidden border border-slate-200/60"
    >
      <div class="absolute inset-0 opacity-[0.03] pointer-events-none"
        style="background-image: radial-gradient(circle at 25% 25%, #fcd116 0%, transparent 50%), radial-gradient(circle at 75% 75%, #003893 0%, transparent 50%), radial-gradient(circle at 50% 50%, #ce1126 0%, transparent 50%)">
      </div>

      <div class="flex items-center justify-center gap-2 sm:gap-4 lg:gap-6 relative z-10">
        <div class="flex flex-col items-center gap-1.5 flex-1 max-w-[160px] sm:max-w-[180px]">
          <div class="relative w-full aspect-[3/4] rounded-xl overflow-hidden bg-white shadow-[0_4px_16px_-4px_rgba(0,0,0,0.1)]">
            <img
              src="https://www.unisportsoccer.com/cdn/shop/files/4VCOdlME_JL6973_1_APPAREL_Photography_Front-Center-View_transparent.png?v=1773950235"
              alt="Colombia 2026 Away Jersey - Navy Blue"
              class="w-full h-full object-contain"
              loading="lazy"
            />
          </div>
          <span class="inline-flex items-center gap-1 rounded-full bg-slate-800 px-2.5 py-0.5 text-[10px] sm:text-xs font-semibold text-white border border-slate-700/60">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
            Visita
          </span>
        </div>
      </div>

      <div class="mt-3 text-center">
        <p class="text-[10px] sm:text-xs font-medium text-slate-400 uppercase tracking-widest">
          Camisetas Oficiales · Mundial 2026
        </p>
      </div>
    </div>
  </div>
</template>
