@extends('admin.layout')

@section('content')
@php($base = '/' . config('app.admin_path'))
<div class="sb" style="padding: 20px;">
    <div class="display" style="font-size: 20px; margin-bottom: 14px;">SUBIR AL CATÁLOGO</div>
    <form method="POST" action="{{ $base }}/images/upload" enctype="multipart/form-data"
          style="display: flex; gap: 20px; flex-wrap: wrap;">
        @csrf
        <div style="flex: 1; min-width: 260px;">
            <div id="drop" style="border: 2px dashed #111; border-radius: 12px; padding: 30px 10px; text-align: center; color: #777; cursor: pointer;">
                ⬆️ Arrastrá PNGs acá o hacé click<br>
                <span style="font-size: 11px;">hasta 10 archivos · máx 1MB c/u · solo PNG</span>
                <div id="names" style="font-size: 12px; color: #111; margin-top: 8px; font-weight: 700;"></div>
            </div>
            <input id="files" type="file" name="files[]" accept=".png,image/png" multiple required style="display: none;">
        </div>
        <div style="flex: 1; min-width: 260px;">
            <div class="field">
                <label>TIPO</label>
                <select name="type"><option value="sticker">sticker</option><option value="background">background</option></select>
            </div>
            <div class="field"><label>CATEGORÍA</label><input name="category" placeholder="amor, fiesta, gotico…"></div>
            <div class="field"><label>TAGS (separados por coma)</label><input name="tags" placeholder="fuego, hype"></div>
            <div class="field">
                <label>PACK</label>
                <select name="pack_id">
                    <option value="">— sin pack —</option>
                    @foreach($packs as $pack)
                        <option value="{{ $pack['id'] }}">{{ $pack['name'] }}{{ (int)$pack['is_premium'] === 1 ? ' ✦' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn naranja-bg" style="width: 100%;">SUBIR AL CATÁLOGO</button>
            <div style="font-size: 10px; color: #999; margin-top: 8px;">
                Se valida el PNG real (magic bytes), se re-encodea la imagen y se normaliza el nombre.
            </div>
        </div>
    </form>
</div>
<script>
    const drop = document.getElementById('drop');
    const input = document.getElementById('files');
    const names = document.getElementById('names');
    const show = () => { names.textContent = [...input.files].map(f => f.name).join(' · '); };
    drop.addEventListener('click', () => input.click());
    input.addEventListener('change', show);
    drop.addEventListener('dragover', e => { e.preventDefault(); drop.style.background = '#fff'; });
    drop.addEventListener('dragleave', () => { drop.style.background = ''; });
    drop.addEventListener('drop', e => {
        e.preventDefault();
        drop.style.background = '';
        input.files = e.dataTransfer.files;
        show();
    });
</script>
@endsection
