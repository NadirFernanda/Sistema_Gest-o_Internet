<div id="aw-consent" role="dialog" aria-modal="true" aria-labelledby="aw-consent-title" aria-live="polite" style="display:none;">
  <div id="aw-consent-inner">
    <div id="aw-consent-icon" aria-hidden="true">🍪</div>
    <div id="aw-consent-body">
      <p id="aw-consent-title">Este site utiliza cookies</p>
      <p id="aw-consent-text">
        Utilizamos cookies estritamente necessários para o funcionamento do serviço. Ao continuar a navegar, aceita os nossos
        <a href="{{ route('legal.terms') }}">Termos e Condições</a>
        e a nossa
        <a href="{{ route('legal.privacy') }}">Política de Privacidade</a>,
        em conformidade com a <strong>Lei n.º&nbsp;22/11</strong> de Angola.
      </p>
    </div>
    <div id="aw-consent-actions">
      <button id="aw-consent-accept" onclick="awConsentAccept()">Aceitar e continuar</button>
      <a href="{{ route('legal.privacy') }}" id="aw-consent-more">Saber mais</a>
    </div>
    <button id="aw-consent-close" onclick="awConsentAccept()" aria-label="Fechar aviso de cookies">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>
</div>

<style>
#aw-consent{
  position:fixed;bottom:1.25rem;left:50%;transform:translateX(-50%);
  width:calc(100% - 2rem);max-width:680px;
  background:#1a202c;color:#e2e8f0;
  border-radius:14px;border:1px solid #2d3748;
  box-shadow:0 8px 32px rgba(0,0,0,.45);
  z-index:9999;font-family:Inter,system-ui,sans-serif;
  animation:aw-slide-up .3s ease;
}
@keyframes aw-slide-up{from{opacity:0;transform:translateX(-50%) translateY(1rem)}to{opacity:1;transform:translateX(-50%) translateY(0)}}
#aw-consent-inner{display:flex;align-items:flex-start;gap:1rem;padding:1.1rem 1.25rem;}
#aw-consent-icon{font-size:1.4rem;flex-shrink:0;margin-top:.1rem;}
#aw-consent-body{flex:1;min-width:0;}
#aw-consent-title{font-size:.9rem;font-weight:700;color:#fff;margin:0 0 .3rem;}
#aw-consent-text{font-size:.8rem;line-height:1.6;color:#9ca3af;margin:0;}
#aw-consent-text a{color:#f7b500;text-decoration:none;font-weight:600;}
#aw-consent-text a:hover{text-decoration:underline;}
#aw-consent-text strong{color:#e2e8f0;}
#aw-consent-actions{display:flex;flex-direction:column;gap:.45rem;flex-shrink:0;align-items:stretch;}
#aw-consent-accept{
  background:#f7b500;color:#1a202c;border:none;border-radius:8px;
  padding:.5rem 1.1rem;font-size:.82rem;font-weight:700;
  cursor:pointer;white-space:nowrap;font-family:inherit;
  transition:filter .15s;
}
#aw-consent-accept:hover{filter:brightness(.92);}
#aw-consent-more{
  font-size:.76rem;color:#6b7280;text-decoration:none;
  text-align:center;font-weight:500;
}
#aw-consent-more:hover{color:#f7b500;}
#aw-consent-close{
  background:none;border:none;color:#4b5563;cursor:pointer;
  padding:.2rem;flex-shrink:0;line-height:1;margin-top:.1rem;
  transition:color .15s;
}
#aw-consent-close:hover{color:#9ca3af;}
@media(max-width:520px){
  #aw-consent-inner{flex-wrap:wrap;}
  #aw-consent-actions{flex-direction:row;width:100%;}
  #aw-consent-accept{flex:1;}
}
</style>

<script>
(function(){
  if(localStorage.getItem('aw_consent')==='1') return;
  var el=document.getElementById('aw-consent');
  if(el) el.style.display='block';
})();
function awConsentAccept(){
  localStorage.setItem('aw_consent','1');
  var el=document.getElementById('aw-consent');
  if(!el) return;
  el.style.animation='aw-slide-up .25s ease reverse';
  setTimeout(function(){el.style.display='none';},220);
}
</script>
