@extends('admin.layout')

@section('content')
@php($base = '/' . config('app.admin_path'))
<form method="GET" action="{{ $base }}" style="display: flex; gap: 8px; margin-bottom: 16px;">
    <input name="q" value="{{ $q }}" placeholder="Buscar por nombre, tag o categoría…"
           style="flex: 1; border: solid #111; border-width: 1.2px 3px 3px 1.2px; border-radius: 8px; padding: 8px 10px;">
    <select name="type" style="border: solid #111; border-width: 1.2px 3px 3px 1.2px; border-radius: 8px; padding: 8px;">
        <option value="">Todos los tipos</option>
        <option value="sticker" @selected($type === 'sticker')>sticker</option>
        <option value="background" @selected($type === 'background')>background</option>
    </select>
    <button class="btn">Buscar</button>
    <a class="btn naranja-bg" href="{{ $base }}/images/upload">+ Subir</a>
</form>

<table>
    <tr><th></th><th>NOMBRE</th><th>TIPO</th><th>CATEGORÍA</th><th>PACK</th><th>TAGS</th><th></th></tr>
    @forelse($images as $image)
        <tr>
            <td><img class="thumb" src="/api/image/{{ $image['name'] }}" alt=""></td>
            <td><b>{{ $image['name'] }}</b></td>
            <td>{{ $image['type'] }}</td>
            <td>{{ $image['category'] }}</td>
            <td>{{ $image['pack_name'] }}{{ (int)($image['pack_is_premium'] ?? 0) === 1 ? ' ✦' : '' }}</td>
            <td>
                @foreach(json_decode($image['tags'] ?? '[]', true) ?: [] as $tag)
                    <span class="tag">{{ $tag }}</span>
                @endforeach
            </td>
            <td style="white-space: nowrap;">
                <a href="{{ $base }}/images/{{ $image['id'] }}/edit" style="color:#111; font-weight:700;">Editar</a>
                · <form method="POST" action="{{ $base }}/images/{{ $image['id'] }}/delete" style="display:inline;"
                        onsubmit="return confirm('¿Borrar {{ $image['name'] }}?');">
                    @csrf<button style="background:none;border:none;color:#D62828;font-weight:700;cursor:pointer;font:inherit;">Borrar</button>
                  </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="7" style="text-align:center; color:#999;">Catálogo vacío</td></tr>
    @endforelse
</table>
@endsection
