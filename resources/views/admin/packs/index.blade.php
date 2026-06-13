@extends('admin.layout')

@section('content')
@php($base = '/' . config('app.admin_path'))
<div style="display: flex; justify-content: space-between; margin-bottom: 14px;">
    <div class="display" style="font-size: 20px;">PACKS</div>
    <a class="btn naranja-bg" href="{{ $base }}/packs/create">+ Nuevo pack</a>
</div>
<table>
    <tr><th></th><th>NOMBRE</th><th>SLUG</th><th>PREMIUM</th><th>PRECIO</th><th>ORDEN</th><th></th></tr>
    @forelse($packs as $pack)
        <tr>
            <td>@if($pack['cover'])<img class="thumb" src="/api/image/{{ $pack['cover'] }}" alt="">@endif</td>
            <td><b>{{ $pack['name'] }}</b></td>
            <td>{{ $pack['slug'] }}</td>
            <td>{{ (int)$pack['is_premium'] === 1 ? '✦ sí' : 'no' }}</td>
            <td>{{ $pack['price'] }} hype</td>
            <td>{{ $pack['sort'] }}</td>
            <td style="white-space: nowrap;">
                <a href="{{ $base }}/packs/{{ $pack['id'] }}/edit" style="color:#111; font-weight:700;">Editar</a>
                · <form method="POST" action="{{ $base }}/packs/{{ $pack['id'] }}/delete" style="display:inline;"
                        onsubmit="return confirm('¿Borrar {{ $pack['name'] }}? Sus imágenes quedarán sin pack.');">
                    @csrf<button style="background:none;border:none;color:#D62828;font-weight:700;cursor:pointer;font:inherit;">Borrar</button>
                  </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="7" style="text-align:center; color:#999;">Sin packs</td></tr>
    @endforelse
</table>
@endsection
