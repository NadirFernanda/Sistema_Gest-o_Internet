@extends('layouts.app')

@section('title', 'Produtos / Equipamentos &mdash; Admin')

@section('content')
<style>
:root{--a-bg:#f4f6f9;--a-surf:#fff;--a-border:#dde2ea;--a-text:#1a202c;--a-muted:#64748b;--a-faint:#9aa5b4;--a-brand:#f7b500;--a-green:#16a34a;--a-amber:#d97706;--a-red:#dc2626;}
.ap{font-family:Inter,system-ui,sans-serif;background:var(--a-bg);min-height:60vh;padding:2rem 0 4rem;color:var(--a-text);}
.ap-wrap{max-width:1140px;margin:0 auto;padding:0 1.5rem;}
.ap-topbar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:1.75rem;}
.ap-topbar h1{font-size:1.35rem;font-weight:800;margin:0 0 .15rem;letter-spacing:-.02em;}
.ap-topbar .ap-sub{font-size:.78rem;color:var(--a-faint);}
.ap-back{font-size:.82rem;font-weight:600;color:var(--a-muted);text-decoration:none;padding:.4rem .85rem;border:1px solid var(--a-border);border-radius:7px;background:var(--a-surf);transition:background .15s;}
.ap-back:hover{background:var(--a-border);}
.ap-ok{background:#f0fdf4;border:1px solid #86efac;border-left:4px solid var(--a-green);color:#166534;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1rem;}
.ap-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.45rem 1rem;border-radius:8px;font-size:.82rem;font-weight:700;border:none;cursor:pointer;font-family:inherit;text-decoration:none;white-space:nowrap;transition:filter .15s;}
.ap-btn-primary{background:#f7b500;color:#1a202c;}.ap-btn-primary:hover{filter:brightness(.95);}
.ap-btn-danger{background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;}.ap-btn-danger:hover{background:#fecaca;}
.ap-btn-outline{background:var(--a-surf);color:var(--a-muted);border:1.5px solid var(--a-border);}.ap-btn-outline:hover{background:var(--a-border);}
.ap-btn-sm{padding:.32rem .75rem;font-size:.78rem;}
.ap-label{display:block;font-size:.77rem;font-weight:600;color:#374151;margin-bottom:.3rem;}
.ap-ctrl{width:100%;box-sizing:border-box;padding:.55rem .75rem;border:1.5px solid var(--a-border);border-radius:8px;font-size:.875rem;color:var(--a-text);background:#f8fafc;font-family:inherit;outline:none;transition:border-color .15s;}
.ap-ctrl:focus{border-color:var(--a-brand);background:#fff;}
.ap-filters{background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;padding:.9rem 1.1rem;display:flex;flex-wrap:wrap;gap:.65rem;align-items:flex-end;margin-bottom:1.25rem;}
.ap-fg{display:flex;flex-direction:column;gap:.25rem;}
.ap-fg.grow{flex:1;min-width:170px;}
.ap-pager{padding:.7rem 1rem;border-top:1px solid var(--a-border);background:#f8fafc;}
.ap-tcard{background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;overflow:hidden;}
.ap-table{width:100%;border-collapse:collapse;font-size:.845rem;}
.ap-table thead{background:#f8fafc;}
.ap-table th{text-align:left;padding:.65rem 1rem;font-size:.69rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--a-faint);border-bottom:1px solid var(--a-border);white-space:nowrap;}
.ap-table th.r,.ap-table td.r{text-align:right;}
.ap-table th.c,.ap-table td.c{text-align:center;}
.ap-table td{padding:.6rem 1rem;border-bottom:1px solid #f4f6f9;vertical-align:middle;color:#374151;}
.ap-table tbody tr:last-child td{border-bottom:none;}
.ap-table tbody tr:hover td{background:#fafbff;}
.ap-table .dim{color:var(--a-faint);font-size:.82rem;}
.badge{display:inline-block;padding:.2rem .6rem;border-radius:999px;font-size:.73rem;font-weight:700;white-space:nowrap;}
.bg-green{background:#dcfce7;color:#15803d;}.bg-gray{background:#f1f5f9;color:#475569;}.bg-red{background:#fee2e2;color:#b91c1c;}
.ap-empty{padding:3rem 1rem;text-align:center;color:var(--a-faint);}
.ap-empty-t{font-size:.95rem;font-weight:700;color:var(--a-muted);margin:0 0 .3rem;}
.ap-empty-s{font-size:.82rem;margin:0;}
</style>

<div class="ap"><div class="ap-wrap">

  <div class="ap-topbar">
    <div>
      <h1>Produtos / Equipamentos</h1>
      <p class="ap-sub">Admin &rsaquo; Cat&aacute;logo de produtos dispon&iacute;veis na loja</p>
    </div>
    <div style="display:flex;gap:.5rem;align-items:center;">
      <a href="{{ route('admin.equipment.products.create') }}" class="ap-btn ap-btn-primary">+ Novo Produto</a>
      <a href="{{ route('admin.dashboard') }}" class="ap-back">&larr; Dashboard</a>
    </div>
  </div>

  @if(session('success'))
    <div class="ap-ok">{{ session('success') }}</div>
  @endif

  <form method="get" class="ap-filters">
    <div class="ap-fg grow">
      <label class="ap-label">Pesquisa</label>
      <input name="q" value="{{ request('q') }}" class="ap-ctrl" placeholder="Nome ou categoria...">
    </div>
    <div class="ap-fg">
      <label class="ap-label">Categoria</label>
      <select name="category" class="ap-ctrl" style="min-width:160px;">
        <option value="">Todas</option>
        @foreach($categories as $cat)
          <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
        @endforeach
      </select>
    </div>
    <div class="ap-fg">
      <label class="ap-label">Estado</label>
      <select name="active" class="ap-ctrl" style="min-width:130px;">
        <option value="">Todos</option>
        <option value="1" @selected(request('active') === '1')>Activo</option>
        <option value="0" @selected(request('active') === '0')>Inactivo</option>
      </select>
    </div>
    <div class="ap-fg">
      <label class="ap-label">Stock</label>
      <select name="stock" class="ap-ctrl" style="min-width:130px;">
        <option value="">Todos</option>
        <option value="in"  @selected(request('stock') === 'in')>Com stock</option>
        <option value="out" @selected(request('stock') === 'out')>Esgotado</option>
      </select>
    </div>
    <button type="submit" class="ap-btn ap-btn-primary">Filtrar</button>
    @if(request()->hasAny(['q','category','active','stock']))
      <a href="{{ route('admin.equipment.products.index') }}" class="ap-btn ap-btn-outline ap-btn-sm">Limpar</a>
    @endif
  </form>

  <div class="ap-tcard">
    <table class="ap-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Nome</th>
          <th>Categoria</th>
          <th class="r">Pre&ccedil;o (Kz)</th>
          <th class="c">Stock</th>
          <th class="c">Activo</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($products as $product)
          <tr>
            <td class="dim">{{ $product->id }}</td>
            <td style="font-weight:600;">{{ $product->name }}</td>
            <td>{{ $product->category ?? '&mdash;' }}</td>
            <td class="r">{{ number_format($product->price_aoa, 0, ',', '.') }}</td>
            <td class="c">
              <span style="font-weight:700;color:{{ $product->stock > 0 ? 'var(--a-green)' : 'var(--a-red)' }};">{{ $product->stock }}</span>
            </td>
            <td class="c">
              @if($product->active)
                <span class="badge bg-green">Sim</span>
              @else
                <span class="badge bg-gray">N&atilde;o</span>
              @endif
            </td>
            <td>
              <div style="display:flex;gap:.4rem;">
                <a href="{{ route('admin.equipment.products.edit', $product->id) }}" class="ap-btn ap-btn-sm" style="background:#f1f5f9;color:#374151;">Editar</a>
                <form method="POST" action="{{ route('admin.equipment.products.destroy', $product->id) }}"
                      onsubmit="return confirm('Eliminar o produto &quot;{{ addslashes($product->name) }}&quot;?');"
                      style="display:inline;">
                  @csrf @method('DELETE')
                  <button type="submit" class="ap-btn ap-btn-danger ap-btn-sm">Eliminar</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7">
              <div class="ap-empty">
                <p class="ap-empty-t">Nenhum produto criado</p>
                <p class="ap-empty-s">Clique em &ldquo;+ Novo Produto&rdquo; para adicionar o primeiro.</p>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
    <div class="ap-pager">{{ $products->links() }}</div>
  </div>

</div></div>
@endsection
