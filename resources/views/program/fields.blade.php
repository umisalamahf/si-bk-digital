<form method="POST" action="{{ $action }}" class="max-w-3xl space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-rose-100">
    @csrf
    @method('PUT')
    <div>
        <label for="capaian_layanan" class="mb-1 block text-sm font-medium text-slate-700">Capaian Layanan BK</label>
        <textarea id="capaian_layanan" name="capaian_layanan" rows="4" class="w-full border border-slate-300 px-3 py-2">{{ old('capaian_layanan', $program->capaian_layanan) }}</textarea>
    </div>
    <div>
        <label for="alur_tujuan" class="mb-1 block text-sm font-medium text-slate-700">Alur Tujuan Layanan</label>
        <textarea id="alur_tujuan" name="alur_tujuan" rows="4" class="w-full border border-slate-300 px-3 py-2">{{ old('alur_tujuan', $program->alur_tujuan) }}</textarea>
    </div>
    <div>
        <label for="action_plan" class="mb-1 block text-sm font-medium text-slate-700">Action Plan</label>
        <textarea id="action_plan" name="action_plan" rows="4" class="w-full border border-slate-300 px-3 py-2">{{ old('action_plan', $program->action_plan) }}</textarea>
    </div>
    <button class="rounded-xl bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">Simpan Program</button>
</form>
