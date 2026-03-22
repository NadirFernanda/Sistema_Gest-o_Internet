<footer class="site-footer" role="contentinfo" aria-label="Rodapé da loja">

  {{-- Linha de accent no topo do rodapé --}}
  <div class="footer-top-accent" aria-hidden="true"></div>

  <div class="footer-inner container">

    {{-- Coluna da marca --}}
    <div class="footer-col footer-brand">
      <a href="/" class="brand-link" aria-label="Voltar à página inicial">
        <span class="footer-brand-text">AngolaWiFi</span>
      </a>
      <p class="footer-tagline">Conectando vidas, lares<br>e negócios em Angola.</p>
      <div class="footer-social" aria-label="Redes sociais">
        <a href="#" aria-label="WhatsApp" class="social-link">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        </a>
        <a href="#" aria-label="Facebook" class="social-link">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
        </a>
        <a href="#" aria-label="Instagram" class="social-link">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
        </a>
      </div>
    </div>

    {{-- Coluna: Catálogo --}}
    <nav class="footer-col" aria-label="Catálogo">
      <h4>Catálogo</h4>
      <ul class="footer-links">
        <li><a href="/">Início</a></li>
        <li><a href="{{ route('equipment.index') }}">Equipamentos</a></li>
        <li><a href="/#planos">Planos Individuais</a></li>
        <li><a href="{{ route('family.request.show') }}">Planos Familiares</a></li>
      </ul>
    </nav>

    {{-- Coluna: Empresa --}}
    <nav class="footer-col" aria-label="Empresa">
      <h4>Empresa</h4>
      <ul class="footer-links">
        <li><a href="/quem-somos">Quem Somos</a></li>
        <li><a href="/como-comprar">Como Comprar</a></li>
        <li><a href="{{ route('reseller.apply') }}">Torne-se Revendedor</a></li>
        <li><a href="{{ route('account.index') }}">Área do Cliente</a></li>
      </ul>
    </nav>

    {{-- Coluna: Contacto --}}
    <aside class="footer-col footer-contact" aria-label="Fale connosco">
      <h4>Fale connosco</h4>
      <ul class="contact-list">
        <li>
          <span class="contact-icon" aria-hidden="true">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
          </span>
          <span class="contact-info">
            <span class="contact-label">Telefone</span>
            <a class="contact-link" href="tel:+244949364505">+244 949 364 505</a>
          </span>
        </li>
        <li>
          <span class="contact-icon" aria-hidden="true">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          </span>
          <span class="contact-info">
            <span class="contact-label">E-mail</span>
            <a class="contact-link" href="mailto:suporte@angolawifi.ao">suporte@angolawifi.ao</a>
          </span>
        </li>
        <li>
          <span class="contact-icon" aria-hidden="true">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          </span>
          <span class="contact-info">
            <span class="contact-label">Localização</span>
            <span class="contact-text">Luanda, Angola</span>
          </span>
        </li>
      </ul>
    </aside>

  </div>

  <div class="footer-legal">
    <div class="container footer-legal-inner">
      <small class="muted">&copy; {{ date('Y') }} AngolaWiFi. Todos os direitos reservados.</small>
      <div class="dev-credit">Desenvolvido por Fernanda Gonçalves</div>
      <nav class="legal-nav" aria-label="Links legais">
        <a href="/privacy">Privacidade</a>
        <a href="/terms">Termos</a>
      </nav>
    </div>
  </div>

</footer>
