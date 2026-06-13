@extends('admin.layout')

@section('content')
@php($base = '/' . config('app.admin_path'))
@php($isEdit = $pack !== null)
<div class="sb" style="padding: 20px; max-width: 480px;">
    <div class="display" style="font-size: 20px; margin-bottom: 14px;">{{ $isEdit ? 'EDITAR PACK' : 'NUEVO PACK' }}</div>
    <form method="POST" action="{{ $isEdit ? "$base/packs/{$pack['id']}/edit" : "$base/packs/create" }}">
        @csrf
        <div class="field"><label>NOMBRE</label><input name="name" value="{{ $pack['name'] ?? '' }}" required></div>
        @if($isEdit)
            <div class="field"><label>SLUG (fijo)</label><input value="{{ $pack['slug'] }}" disabled></div>
        @endif
        <div class="field">
            <label>PREMIUM</label>
            <select name="is_premium">
                <option value="0" @selected(!(int)($pack['is_premium'] ?? 0))>no</option>
                <option value="1" @selected((int)($pack['is_premium'] ?? 0) === 1)>sí ✦</option>
            </select>
        </div>
        <div class="field"><label>PRECIO (hype)</label><input type="number" name="price" min="0" value="{{ $pack['price'] ?? 0 }}"></div>
        <div class="field">
            <label>PORTADA (imagen del catálogo)</label>
            <select name="cover">
                <option value="">— sin portada —</option>
                @foreach($images as $image)
                    <option value="{{ $image['name'] }}" @selected(($pack['cover'] ?? '') === $image['name'])>{{ $image['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="field"><label>ORDEN</label><input type="number" name="sort" value="{{ $pack['sort'] ?? 0 }}"></div>
        <button class="btn">GUARDAR</button>
    </form>
</div>
@endsection
