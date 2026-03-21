@extends('layouts.app')

@section('title', 'Pedido enviado — AngolaWiFi')

@push('styles')
<style>
.ty-overlay{position:fixed;inset:0;background:rgba(15,23,42,.55);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;z-index:9999;padding:1rem;}
.ty-modal{background:#fff;border-radius:20px;padding:2.5rem 2rem;max-width:480px;width:100%;text-align:center;box-shadow:0 25px 60px rgba(0,0,0,.18);animation:ty-pop .35s cubic-bezier(.34,1.56,.64,1) both;}
@keyframes ty-pop{from{opacity:0;transform:scale(.88) translateY(16px)}to{opacity:1;transform:scale(1) translateY(0)}}
.ty-icon{width:72px;height:72px;background:linear-gradient(135deg,#f7b500,#ff9500);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.4rem;box-shadow:0 8px 20px rgba(247,181,0,.35);}
.ty-icon svg{width:36px;height:36px;stroke:#fff;stroke-width:2.5;fill:none;stroke-linecap:round;stroke-linejoin:round;}
.ty-title{font-size:1.45rem;font-weight:800;color:#1a202c;margin:0 0 .6rem;letter-spacing:-.02em;}
.ty-sub{font-size:.95rem;color:#64748b;line-height:1.65;margin:0 0 .5rem;}
.ty-steps{background:#f8fafc;border-radius:12px;padding:1rem 1.2rem;margin:1.2rem 0 1.5rem;text-align:left;}
.ty-steps p{font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin:0 0 .6rem;}
.ty-steps ol{margin:0;padding-left:1.3rem;color:#374151;font-size:.88rem;line-height:1.8;}
.ty-btn{display:inline-block;padding:.7rem 2rem;background:linear-gradient(135deg,#f7b500,#ff9500);color:#1a202c;font-weight:800;font-size:.95rem;border-radius:999px;text-decoration:none;box-shadow:0 4px 14px rgba(247,181,0,.35);transition:filter .15s,transform .15s;}
.ty-btn:hover{filter:brightness(.95);transform:translateY(-1px);}
.ty-progress{height:4px;background:#e2e8f0;border-radius:9999px;margin-top:1.5rem;overflow:hidden;}
.ty-progress-bar{height:4px;background:linear-gradient(90deg,#f7b500,#ff9500);border-radius:9999px;width:0;animation:ty-fill 5s linear forwards;}
@keyframes ty-fill{to{width:100%}}
.ty-countdown{font-size:.78rem;color:#94a3b8;margin-top:.4rem;}
</style>
@endpush

@section('content')
<div class="ty-overlay" id="ty-overlay">
  <div class="ty-modal" role="dialog" aria-modal="true" aria-labelledby="ty-title">

    <div class="ty-icon">
      <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    </div>

    <h1 class="ty-title" id="ty-title">Pedido enviado com sucesso!</h1>
    <p class="ty-sub">Recebemos a sua candidatura para ser agente revendedor AngolaWiFi. Enviámos uma confirmação para o seu e-mail.</p>

    <div class="ty-steps">
      <p>O que acontece a seguir</p>
      <ol>
        <li>A nossa equipa analisa os seus dados</li>
        <li>Entramos em contacto em até <strong>48&nbsp;horas</strong></li>
        <li>Receberá um e-mail com a decisão e os próximos passos</li>
      </ol>
    </div>

    <a href="{{ url('/') }}" class="ty-btn" id="ty-btn">Voltar à página inicial</a>

    <div class="ty-progress"><div class="ty-progress-bar" id="ty-bar"></div></div>
    <p class="ty-countdown" id="ty-cd">A redirecionar em <strong>5</strong>s…</p>
  </div>
</div>

@push('scripts')
<script>
(function(){
  var s=5, cd=document.getElementById('ty-cd'), bar=document.getElementById('ty-bar');
  var iv=setInterval(function(){
    s--;
    cd.innerHTML='A redirecionar em <strong>'+s+'</strong>s\u2026';
    if(s<=0){clearInterval(iv);window.location.href='{{ url('/') }}';}
  },1000);
  document.getElementById('ty-btn').addEventListener('click',function(){clearInterval(iv);});
})();
</script>
@endpush
@endsection
