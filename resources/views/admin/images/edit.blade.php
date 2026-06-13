@extends('admin.layout')

@section('content')
@php($base = '/' . config('app.admin_path'))
<div class="sb" style="padding: 20px; max-width: 520px;">
    <div style="display: flex; gap: 14px; align-items: center; margin-bottom: 14px;">
        <img class="thumb" style="width: 64px; height: 64px;" src="/api/image/{{ $image['name'] }}" alt="">
        <div class="display" style="font-size: 20px;">{{ $image['name'] }}</div>
    </div>
    <form method="POST" action="{{ $base }}/images/{{ $image['id'] }}/edit">
        @csrf
        <div class="field">
            <label>TIPO</label>
            <select name="type">
                <option value="sticker" @selected($image['type'] === 'sticker')>sticker</option>
                <option value="background" @selected($image['type'] === 'background')>background</option>
            </select>
        </div>
        <div class="field"><label>CATEGORÍA</label><input name="category" value="{{ $image['category'] }}"></div>
        <div class="field">
            <label>TAGS (separados por coma)</label>
            <input name="tags" value="{{ implode(', ', json_decode($image['tags'] ?? '[]', true) ?: []) }}">
        </div>
        <div class="field">
            <label>PACK</label>
            <select name="pack_id">
                <option value="">— sin pack —</option>
                @foreach($packs as $pack)
                    <option value="{{ $pack['id'] }}" @selected((int)($image['pack_id'] ?? 0) === (int)$pack['id'])>{{ $pack['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="field"><label>ORDEN</label><input type="number" name="sort" value="{{ $image['sort'] }}"></div>
        <button class="btn">GUARDAR</button>
    </form>
    <form method="POST" action="{{ $base }}/images/{{ $image['id'] }}/delete" style="margin-top: 14px;"
          onsubmit="return confirm('¿Borrar {{ $image['name'] }}? Los buzones que la usen dejarán de mostrar este sticker.');">
        @csrf
        <button class="btn peligro">BORRAR DEL CATÁLOGO</button>
    </form>
</div>
@endsection
