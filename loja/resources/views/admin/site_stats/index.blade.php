@extends('layouts.app')

@section('content')
<section class="planos-section" aria-label="Estatísticas da página inicial">
  <div class="container" style="max-width:860px;">

    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:0.5rem;">
      <a href="{{ route('admin.dashboard') }}" class="btn-modern" style="font-size:0.85rem;padding:0.35rem 0.9rem;">&larr; Voltar</a>
      <h2 style="margin:0;">Estatísticas da Página Inicial</h2>
    </div>
    <p style="color:#666;margin-bottom:2rem;">Estes são os 4 números em destaque no topo da loja. Edite-os aqui para ficarem actualizados em tempo real.</p>

    @if(session('success'))
      <div class="alert alert-success" role="alert" style="margin-bottom:1.5rem;">{{ session('success') }}</div>
    @endif

    {{-- Preview live --}}
    <div class="stat-bar" style="margin-bottom:2.5rem;border-radius:12px;overflow:hidden;">
      <div class="stat-bar__grid">
        @foreach($stats as $stat)
          <div class="stat-bar__item">
            <span class="stat-bar__num">{{ $stat->valor }}</span>
            <span class="stat-bar__lbl">{{ $stat->legenda }}</span>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Edit cards --}}
    <div style="display:grid;gap:1.5rem;">
      @foreach($stats as $stat)
        <div class="info-card" style="border-left:4px solid #f7b500;">
          <form action="{{ route('admin.site_stats.update', $stat->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
              <div>
                <label style="font-size:.85rem;font-weight:600;color:#444;display:block;margin-bottom:.3rem;">
                  Valor exibido <span style="color:#f7b500;">*</span>
                </label>
                <input type="text" name="valor" value="{{ old('valor', $stat->valor) }}" required maxlength="50"
                  style="width:100%;padding:.5rem .75rem;border:1.5px solid #e5e7eb;border-radius:8px;font-size:1.1rem;font-weight:700;color:#0d1526;">
                @error('valor')<p style="color:red;font-size:.8rem;margin-top:.2rem;">{{ $message }}</p>@enderror
              </div>
              <div>
                <label style="font-size:.85rem;font-weight:600;color:#444;display:block;margin-bottom:.3rem;">
                  Legenda <span style="color:#f7b500;">*</span>
                </label>
                <input type="text" name="legenda" value="{{ old('legenda', $stat->legenda) }}" required maxlength="100"
                  style="width:100%;padding:.5rem .75rem;border:1.5px solid #e5e7eb;border-radius:8px;font-size:.95rem;">
                @error('legenda')<p style="color:red;font-size:.8rem;margin-top:.2rem;">{{ $message }}</p>@enderror
              </div>
            </div>

            <details style="margin-bottom:1rem;">
              <summary style="font-size:.82rem;color:#888;cursor:pointer;user-select:none;">
                ⚙️ Configuração da animação de contagem
              </summary>
              <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-top:.75rem;padding:.75rem;background:#f9f9f9;border-radius:8px;">
                <div>
                  <label style="font-size:.8rem;font-weight:600;color:#444;display:block;margin-bottom:.3rem;">
                    Contar até (número)
                    <span title="Deixe em branco para desactivar a contagem animada" style="cursor:help;color:#aaa;">?</span>
                  </label>
                  <input type="number" name="count_to" value="{{ old('count_to', $stat->count_to) }}" min="0" step="0.01" placeholder="ex: 5000"
                    style="width:100%;padding:.4rem .6rem;border:1.5px solid #e5e7eb;border-radius:6px;font-size:.9rem;">
                  @error('count_to')<p style="color:red;font-size:.75rem;margin-top:.2rem;">{{ $message }}</p>@enderror
                </div>
                <div>
                  <label style="font-size:.8rem;font-weight:600;color:#444;display:block;margin-bottom:.3rem;">
                    Casas decimais
                  </label>
                  <input type="number" name="count_decimals" value="{{ old('count_decimals', $stat->count_decimals) }}" min="0" max="4"
                    style="width:100%;padding:.4rem .6rem;border:1.5px solid #e5e7eb;border-radius:6px;font-size:.9rem;">
                </div>
                <div>
                  <label style="font-size:.8rem;font-weight:600;color:#444;display:block;margin-bottom:.3rem;">
                    Sufixo após número
                    <span title="ex: + ou %" style="cursor:help;color:#aaa;">?</span>
                  </label>
                  <input type="text" name="count_suffix" value="{{ old('count_suffix', $stat->count_suffix) }}" maxlength="10" placeholder="ex: +"
                    style="width:100%;padding:.4rem .6rem;border:1.5px solid #e5e7eb;border-radius:6px;font-size:.9rem;">
                </div>
              </div>
            </details>

            <button type="submit" class="btn-modern" style="font-size:.9rem;padding:.4rem 1.2rem;">
              Guardar alterações
            </button>
          </form>
        </div>
      @endforeach
    </div>

  </div>
</section>
@endsection
