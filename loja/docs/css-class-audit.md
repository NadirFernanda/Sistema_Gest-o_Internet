# CSS Class Audit — All Blade Templates
Generated from thorough audit of all 50 `.blade.php` files in `resources/views/`.

---

## 1. `layouts/app.blade.php`
- **CSS Classes**: NONE (shell layout only)
- **Inline Styles**: NONE

---

## 2. `partials/header.blade.php`
**CSS Classes:**
`store-header`, `store-header-inner`, `store-brand`, `store-logo`, `store-title`, `store-right`, `store-nav`, `store-link`, `store-link active`, `store-dropdown`, `store-dropdown-menu`, `dropdown-search-wrapper`, `search-input`, `store-dropdown-item`, `store-dropdown-item__icon`, `store-dropdown-item__body`, `store-dropdown-item__title`, `store-dropdown-item__desc`, `store-link--muted`, `store-actions`, `store-cta`, `mobile-menu-toggle`, `mobile-menu-icon`, `store-mobile-menu`, `mobile-search-wrapper`, `store-mobile-link`, `store-mobile-cta`, `store-mobile-submenu`, `search-result-item`, `res-title`, `res-sub`, `search-wrapper`

**Inline Styles:**
- `style="padding: 12px 16px 8px 16px;"` (dropdown-search-wrapper)
- `style="width: 100%; max-width: 260px;"` (search inputs)

---

## 3. `partials/footer.blade.php`
**CSS Classes:**
`site-footer`, `footer-top-accent`, `footer-inner`, `container`, `footer-col`, `footer-brand`, `brand-link`, `footer-brand-text`, `footer-tagline`, `footer-social`, `social-link`, `footer-links`, `footer-contact`, `contact-list`, `contact-icon`, `contact-info`, `contact-label`, `contact-link`, `contact-text`, `footer-legal`, `footer-legal-inner`, `muted`, `dev-credit`, `legal-nav`

**Inline Styles:** NONE

---

## 4. `welcome.blade.php` (Default Laravel page — Tailwind utilities)
**CSS Classes (Tailwind — extensive list):**
`bg-[#FDFDFC]`, `dark:bg-[#0a0a0a]`, `text-[#1b1b18]`, `flex`, `p-6`, `lg:p-8`, `items-center`, `lg:justify-center`, `min-h-screen`, `flex-col`, `w-full`, `lg:max-w-4xl`, `max-w-[335px]`, `text-sm`, `mb-6`, `not-has-[nav]:hidden`, `inline-block`, `px-5`, `py-1.5`, `dark:text-[#EDEDEC]`, `border-[#19140035]`, `hover:border-[#1915014a]`, `border`, `dark:border-[#3E3E3A]`, `dark:hover:border-[#62605b]`, `rounded-sm`, `leading-normal`, `border-transparent`, `hover:border-[#19140035]`, `dark:hover:border-[#3E3E3A]`, `transition-opacity`, `opacity-100`, `duration-750`, `lg:grow`, `starting:opacity-0`, `flex-col-reverse`, `lg:flex-row`, `text-[13px]`, `leading-[20px]`, `flex-1`, `pb-12`, `lg:p-20`, `bg-white`, `dark:bg-[#161615]`, `shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)]`, `dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]`, `rounded-bl-lg`, `rounded-br-lg`, `lg:rounded-tl-lg`, `lg:rounded-br-none`, `mb-1`, `font-medium`, `mb-2`, `text-[#706f6c]`, `dark:text-[#A1A09A]`, `mb-4`, `lg:mb-6`, `gap-4`, `py-2`, `relative`, `before:border-l`, `before:border-[#e3e3e0]`, `dark:before:border-[#3E3E3A]`, `before:top-1/2`, `before:bottom-0`, `before:left-[0.4rem]`, `before:absolute`, `py-1`, `rounded-full`, `shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)]`, `w-3.5`, `h-3.5`, `border-[#e3e3e0]`, `bg-[#dbdbd7]`, `dark:bg-[#3E3E3A]`, `w-1.5`, `h-1.5`, `space-x-1`, `underline`, `underline-offset-4`, `text-[#f53003]`, `dark:text-[#FF4433]`, `ml-1`, `w-2.5`, `h-2.5`, `before:bottom-1/2`, `before:top-0`, `gap-3`, `dark:bg-[#eeeeec]`, `dark:border-[#eeeeec]`, `dark:text-[#1C1C1A]`, `dark:hover:bg-white`, `dark:hover:border-white`, `hover:bg-black`, `hover:border-black`, `bg-[#1b1b18]`, `border-black`, `text-white`, `bg-[#fff2f2]`, `dark:bg-[#1D0002]`, `lg:-ml-px`, `-mb-px`, `lg:mb-0`, `rounded-t-lg`, `lg:rounded-t-none`, `lg:rounded-r-lg`, `aspect-[335/376]`, `lg:aspect-auto`, `lg:w-[438px]`, `shrink-0`, `overflow-hidden`, `text-[#F53003]`, `dark:text-[#F61500]`, `transition-all`, `translate-y-0`, `max-w-none`, `starting:translate-y-6`, `w-[448px]`, `-mt-[4.9rem]`, `-ml-8`, `lg:ml-0`, `lg:-mt-[6.6rem]`, `dark:hidden`, `delay-300`, `starting:translate-y-4`, `hidden`, `dark:block`, `absolute`, `inset-0`, `h-14.5`, `lg:block`

**Inline Styles:** Embedded full Tailwind CSS reset in `<style>` tag

---

## 5. `store/index.blade.php`
**CSS Classes:**
`hero`, `hero__track`, `hero__slide`, `hero__card`, `hero__eyebrow`, `hero__title`, `hero__desc`, `hero__actions`, `btn-primary`, `btn-ghost`, `hero__arrow`, `hero__arrow--prev`, `hero__arrow--next`, `hero__dots`, `hero__dot`, `hero__dot is-active`, `stat-bar`, `stat-bar__grid`, `stat-bar__item`, `stat-bar__num`, `stat-bar__lbl`, `planos-section`, `planos-section--individual`, `container`, `section-header`, `section-header__rule`, `plans-grid`, `plans-grid--individual`, `plan-card-modern`, `plan-card--individual`, `plan-card--{id}`, `plan-card--featured`, `plan-card-modern-inner`, `plan-badge`, `plan-card-modern-header`, `plan-emoji`, `plan-title`, `plan-card-modern-body`, `plan-price-row`, `plan-price`, `plan-currency`, `plan-features`, `plan-feature`, `plan-feature--active`, `plan-desc`, `plan-card-modern-footer`, `btn-modern`, `plan-card-modern empty`, `planos-section--family`, `family-loading`, `family-loading__dot`, `planos-section--company`, `planos-section--institutional`, `family-empty-state`

**Inline Styles:**
- `style="background-image:url('/img/carrossel1.webp')"` (hero slide)
- Pushed `<style>` block: `.planos-section--individual .plan-features { flex-direction: column; align-items: flex-start; }`

---

## 6. `store/show.blade.php`
**CSS Classes (Tailwind):**
`text-xl`, `font-semibold`, `mb-4`, `p-4`, `bg-white`, `rounded`, `shadow`, `mt-4`, `px-3`, `py-2`, `bg-green-600`, `text-white`

**Inline Styles:** NONE

---

## 7. `store/checkout.blade.php`
**CSS Classes:**
`container--720`, `checkout-page`, `checkout-title`, `checkout-subtitle`, `checkout-layout`, `checkout-summary-card`, `label`, `total`, `checkout-form-card`, `checkout-errors`, `checkout-form`, `checkout-note`, `checkout-payment`, `checkout-payment-title`, `checkout-payment-options`, `checkout-actions`, `btn-primary`

**Inline Styles:** NONE

---

## 8. `store/checkout-confirmation.blade.php`
**CSS Classes:**
`container--720`, `checkout-page`, `checkout-success-header`, `checkout-success-icon`, `checkout-title`, `checkout-subtitle`, `checkout-layout`, `checkout-summary-card`, `wifi-code-box`, `wifi-code-label`, `wifi-code-value`, `btn-copy`, `checkout-note`, `checkout-divider`, `label`, `total`, `checkout-form-card`, `checkout-instructions`, `checkout-actions`, `btn-primary`

**Inline Styles:**
- `style="margin-top:1rem;"`
- `style="margin-top:0.5rem;"`
- `style="margin-top:1.5rem;"`

---

## 9. `store/reseller-apply.blade.php`
**CSS Classes:**
`container--880`, `reseller-page`, `reseller-layout`, `reseller-intro`, `reseller-badge`, `reseller-title`, `reseller-lead`, `reseller-points`, `reseller-form-card`, `reseller-form-title`, `reseller-form-subtitle`, `reseller-errors`, `reseller-form`, `reseller-form-row`, `reseller-radio-group`, `reseller-radio-option`, `reseller-field-error`, `reseller-fixed-message`, `reseller-fixed-title`, `reseller-fixed-body`, `reseller-form-actions`, `btn-primary`

**Inline Styles:**
- `style="font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;color:var(--text-muted);margin:0 0 0.6rem;"`
- `style="margin:1.1rem 0 0.35rem;font-size:0.88rem;color:var(--text-muted);line-height:1.65;"`
- `style="margin:0;font-size:0.82rem;font-weight:700;color:var(--text-dark);"`

---

## 10. `store/reseller-apply-thankyou.blade.php`
**CSS Classes (defined in @push styles):**
`ty-overlay`, `ty-modal`, `ty-icon`, `ty-title`, `ty-sub`, `ty-steps`, `ty-btn`, `ty-progress`, `ty-progress-bar`, `ty-countdown`

**Inline Styles:** All via embedded `<style>` block

---

## 11. `store/payment-callback-simulated.blade.php`
**CSS Classes (Tailwind):**
`text-xl`, `font-semibold`, `mb-4`, `p-4`, `bg-white`, `rounded`, `shadow`, `mb-2`, `text-sm`, `text-gray-700`, `inline-block`, `px-4`, `py-2`, `bg-blue-600`, `text-white`

**Inline Styles:** NONE

---

## 12. `reseller/panel.blade.php`
**CSS Classes (defined in @push styles + HTML):**
`rv-page`, `rv-login-wrap`, `rv-login-card`, `rv-brand`, `rv-brand-text`, `rv-field`, `rv-login-note`, `rv-btn-login`, `rv-dash`, `rv-topbar`, `rv-topbar-left`, `rv-avatar`, `rv-topbar-name`, `rv-topbar-email`, `rv-mode-badge`, `rv-mode-badge own`, `rv-mode-badge wifi`, `rv-logout-btn`, `rv-sub`, `rv-alert`, `rv-alert danger`, `rv-alert warning`, `rv-alert-icon`, `rv-stats`, `rv-stat-card`, `rv-stat-card green`, `rv-stat-card blue`, `rv-stat-card amber`, `rv-stat-card purple`, `rv-stat-icon`, `rv-stat-label`, `rv-stat-value`, `rv-stat-value green`, `rv-stat-value blue`, `rv-stat-sub`, `rv-progress-bar`, `rv-progress-fill`, `rv-panel`, `rv-panel-title`, `rv-panel-icon`, `rv-disc-table`, `rv-disc-current-chip`, `rv-purchase-form`, `rv-btn-buy`, `rv-field-error`, `rv-plan-grid`, `rv-plan-card`, `rv-plan-card--out`, `rv-plan-card-header`, `rv-plan-name`, `rv-plan-badges`, `rv-badge`, `rv-badge-validity`, `rv-badge-speed`, `rv-plan-prices`, `rv-plan-price-row`, `rv-price-label`, `rv-price-public`, `rv-price-reseller`, `rv-price-profit`, `rv-plan-price-row--reseller`, `rv-plan-price-row--profit`, `rv-plan-stock`, `rv-plan-stock ok`, `rv-plan-stock out`, `rv-plan-add-form`, `rv-qty-input`, `rv-btn-add`, `rv-cart-panel`, `rv-cart-footer`, `rv-cart-totals`, `rv-cart-actions`, `rv-hist-wrap`, `rv-hist-table`, `rv-csv-btn`, `rv-empty`, `rv-pagination`

**Inline Styles:** Numerous inline styles on structural/layout divs

---

## 13. `reseller/stock.blade.php`
**CSS Classes (defined in @push styles + HTML):**
`sp`, `sp-wrap`, `sp-topbar`, `sp-sub`, `sp-back`, `sp-ok`, `sp-err`, `sp-summary`, `sp-card`, `sp-card-val`, `sp-card-lbl`, `sp-filters`, `sp-fg`, `sp-fg grow`, `sp-label`, `sp-ctrl`, `sp-btn`, `sp-btn-primary`, `sp-btn-outline`, `sp-btn-sm`, `sp-btn-yellow`, `sp-btn-green`, `sp-btn-ghost`, `sp-tcard`, `sp-table`, `mono`, `dim`, `badge`, `badge-stock`, `badge-sold`, `badge-plan-diario`, `badge-plan-semanal`, `badge-plan-mensal`, `sp-dist-form`, `sp-dist-input`, `sp-copy-btn`, `sp-dl-bar`, `sp-dl-title`, `sp-empty`, `sp-empty-title`, `sp-info`

**Inline Styles:**
- `style="color:var(--s-green)"`, `style="color:var(--s-muted)"`
- `style="font-size:.85rem;color:#374151;"`
- `style="display:flex;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap;"`
- `style="background:#25d366;color:#fff;border:none;cursor:pointer;"`

---

## 14. `reseller/checkout.blade.php`
**CSS Classes (defined in @push styles + HTML):**
`rv-page`, `rv-pay-wrap`, `rv-back-link`, `rv-pay-header`, `rv-panel`, `rv-panel-title`, `rv-sum-table`, `r`, `rv-sum-plan-badge`, `rv-profit-col`, `rv-sum-tfoot`, `rv-methods-grid`, `rv-method-card`, `rv-method-card active`, `rv-method-icon`, `rv-method-name`, `rv-method-desc`, `rv-detail-box`, `rv-detail-box hidden`, `rv-detail-row`, `rv-detail-label`, `rv-detail-value`, `rv-detail-value big`, `rv-detail-note`, `rv-sim-notice`, `rv-btn-confirm`, `rv-btn-cancel`, `rv-total-bar`, `lbl`, `amount`, `profit`

**Inline Styles:** Multiple inline styles on form elements, table cells

---

## 15. `admin/dashboard.blade.php`
**CSS Classes (defined in `<style>` + HTML):**
`ap`, `ap-wrap`, `ap-topbar`, `ap-sub`, `ap-btn-logout`, `ap-nav`, `here`, `ap-sec`, `ap-kpis`, `ap-kpi`, `k-blue`, `k-green`, `k-amber`, `k-purple`, `k-rose`, `k-teal`, `ap-kpi-val`, `ap-kpi-lbl`, `ap-kpi-sub`, `ap-banner-warn`, `ap-cards`, `ap-card`, `ap-card-actions`, `plan-bars`, `plan-bar`, `plan-bar-name`, `plan-bar-track`, `plan-bar-fill`, `fill-blue`, `fill-purple`, `fill-amber`, `plan-bar-count`, `c-ok`, `c-low`, `c-out`, `badge`, `bg-green`, `bg-amber`, `bg-gray`, `ap-tcard`, `ap-tcard-head`, `ap-table`, `dim`

**Inline Styles:** Several (dynamic color, padding, border, display)

---

## 16. `admin/login.blade.php`
**CSS Classes:**
`adm-login-page`, `adm-login-card`, `adm-login-header`, `adm-login-logo`, `adm-login-body`, `adm-login-error`, `adm-login-label`, `adm-login-input`, `adm-login-submit`, `adm-login-foot`

**Inline Styles:** NONE

---

## 17. `admin/orders/index.blade.php`
**CSS Classes:**
`ap`, `ap-wrap`, `ap-topbar`, `ap-sub`, `ap-back`, `ap-ok`, `ap-err`, `ap-stats`, `ap-stat`, `ap-stat-val`, `ap-stat-lbl`, `ap-label`, `ap-ctrl`, `ap-btn`, `ap-btn-primary`, `ap-btn-outline`, `ap-btn-sm`, `ap-filters`, `ap-fg`, `ap-fg grow`, `ap-tcard`, `ap-table`, `dim`, `badge`, `bg-amber`, `bg-orange`, `bg-green`, `bg-gray`, `bg-red`, `ap-pager`, `ap-empty`, `ap-empty-t`, `ap-empty-s`, `ap-note`

**Inline Styles:** `style="min-width:160px;"`, `style="min-width:180px;"`, `style="font-weight:700;color:var(--a-amber);"`, `style="font-weight:600;"`

---

## 18. `admin/wifi_codes/index.blade.php`
**CSS Classes:**
`ap`, `ap-wrap`, `ap-topbar`, `ap-sub`, `ap-back`, `ap-ok`, `ap-err`, `ap-stats`, `ap-stat`, `ap-stat-val`, `ap-stat-lbl`, `ap-plans`, `ap-plan`, `ap-plan-diario`, `ap-plan-semanal`, `ap-plan-mensal`, `ap-plan-name`, `ap-plan-val`, `ap-plan-note`, `c-ok`, `c-low`, `c-out`, `c-dim`, `ap-sec`, `ap-import-grid`, `ap-card`, `ap-card-title`, `ap-card-sub`, `ap-card-body`, `ap-field`, `ap-label`, `ap-ctrl`, `ap-ctrl-mono`, `ap-file-zone`, `ap-file-label`, `ap-file-hint`, `ap-file-chosen`, `ap-foot`, `ap-btn`, `ap-btn-primary`, `ap-btn-outline`, `ap-btn-sm`, `ap-btn-del`, `ap-btn-danger`, `ap-btn-danger-outline`, `ap-bulk-bar`, `ap-bulk-bar show`, `ap-filters`, `ap-fg`, `ap-fg grow`, `ap-tcard`, `ap-table`, `mono`, `dim`, `badge`, `bg-blue`, `bg-purple`, `bg-amber`, `bg-green`, `bg-gray`, `bg-orange`, `ap-empty`, `ap-empty-title`, `ap-empty-sub`, `ap-pager`, `ap-note`, `ready`, `row-checkbox`

**Inline Styles:** Multiple (overflow-x:auto, width, cursor, min-width, etc.)

---

## 19. `admin/voucher_plans/index.blade.php`
**CSS Classes:**
`ap`, `ap-wrap`, `ap-topbar`, `ap-sub`, `ap-back`, `ap-nav`, `here`, `ap-ok`, `ap-err`, `ap-sec`, `vp-grid`, `vp-card`, `vp-card-head`, `vp-card-title`, `vp-badge-active`, `vp-badge-inactive`, `vp-card-body`, `vp-row`, `vp-row-label`, `vp-row-value`, `vp-profit`, `vp-card-foot`, `ap-card`, `ap-card-title`, `ap-grid-2`, `ap-field`, `ap-label`, `ap-ctrl`, `ap-hint`, `ap-btn`, `ap-btn-primary`, `ap-btn-outline`, `ap-btn-sm`, `ap-btn-warning`, `vp-modal-overlay`, `vp-modal-overlay open`, `vp-modal`, `vp-modal-close`

**Inline Styles:** Inline form layout styles, monospace font on slug

---

## 20. `admin/site_stats/index.blade.php`
**CSS Classes:**
`planos-section`, `container`, `btn-modern`, `alert alert-success`, `stat-bar`, `stat-bar__grid`, `stat-bar__item`, `stat-bar__num`, `stat-bar__lbl`, `info-card`

**Inline Styles:** Heavy inline styles on all form inputs, labels, grid layout, details/summary

---

## 21. `admin/installation_appointments/index.blade.php`
**CSS Classes:**
`ap`, `ap-wrap`, `ap-topbar`, `ap-sub`, `ap-back`, `ap-ok`, `ap-err`, `ap-stats`, `ap-stat`, `ap-stat-val`, `ap-stat-lbl`, `ap-stat active`, `ap-tcard`, `ap-table`, `dim`, `badge`, `bg-amber`, `bg-green`, `bg-gray`, `bg-red`, `bg-blue`, `bg-purple`, `bg-teal`, `ap-pager`, `ap-empty`, `ap-empty-t`, `ap-empty-s`, `ap-detail-row`, `ap-detail-inner`, `ap-ctrl`, `ap-btn`, `ap-btn-primary`, `ap-btn-sm`, `ap-toggle`, `ap-note`

**Inline Styles:** Several inline styles on table cells and layout

---

## 22. `admin/resellers/index.blade.php`
**CSS Classes:**
`ap`, `ap-wrap`, `ap-topbar`, `ap-sub`, `ap-back`, `ap-ok`, `ap-label`, `ap-ctrl`, `ap-btn`, `ap-btn-primary`, `ap-btn-outline`, `ap-btn-sm`, `ap-filters`, `ap-fg`, `ap-fg grow`, `ap-tcard`, `ap-table`, `dim`, `badge`, `bg-amber`, `bg-green`, `bg-gray`, `bg-red`, `ap-pager`, `ap-empty`, `ap-empty-t`, `ap-empty-s`, `ap-note`

**Inline Styles:** Inline styles on font-size, text-decoration, color

---

## 23. `admin/resellers/show.blade.php`
**CSS Classes:**
`ap`, `ap-wrap`, `ap-topbar`, `ap-sub`, `ap-back`, `ap-ok`, `ap-err`, `ap-stats`, `ap-stat`, `ap-stat-val`, `ap-stat-lbl`, `ap-stat-sub`, `ap-card`, `ap-card-title`, `ap-grid-2`, `ap-label`, `ap-ctrl`, `ap-hint`, `ap-err-inline`, `ap-btn`, `ap-btn-primary`, `ap-btn-sm`, `badge`, `bg-amber`, `bg-green`, `bg-gray`, `bg-red`, `bg-blue`, `ap-dl`, `ap-bar`, `ap-bar-fill`, `ap-tcard`, `ap-tcard-head`, `ap-tcard-head-title`, `ap-table`, `dim`, `r`, `ap-pager`, `ap-note`

**Inline Styles:** Multiple (dynamic color, font-size, font-weight, border, padding, width)

---

## 24. `admin/resellers/purchases.blade.php`
**CSS Classes:**
`ap`, `ap-wrap`, `ap-topbar`, `ap-sub`, `ap-back`, `ap-stats`, `ap-stat`, `ap-stat-val`, `ap-stat-lbl`, `ap-label`, `ap-ctrl`, `ap-btn`, `ap-btn-primary`, `ap-btn-outline`, `ap-btn-sm`, `ap-filters`, `ap-fg`, `ap-fg grow`, `ap-tcard`, `ap-table`, `dim`, `ap-pager`, `ap-empty`, `ap-empty-t`, `ap-empty-s`, `ap-rank-bar`, `ap-rank-fill`, `ap-note`

**Inline Styles:** Inline styles for font-weight, color, text-decoration, min-width

---

## 25. `admin/reports.blade.php`
**CSS Classes:**
`ap`, `ap-wrap`, `ap-topbar`, `ap-sub`, `ap-back`, `ap-cards`, `ap-card`, `ap-card-title`, `ap-list`, `badge`, `bg-amber`, `bg-green`, `bg-gray`, `bg-red`, `ap-note`

**Inline Styles:** `style="color:var(--a-faint);font-size:.88rem;"`, and font-weight/color on spans

---

## 26. `admin/family_requests/index.blade.php`
**CSS Classes:**
`ap`, `ap-wrap`, `ap-topbar`, `ap-sub`, `ap-back`, `ap-ok`, `ap-err`, `ap-note`, `ap-ref`, `bg-sky`, `bg-orange`, `ap-stats`, `ap-stat`, `ap-stat-val`, `ap-stat-lbl`, `ap-stat active`, `ap-btn`, `ap-btn-primary`, `ap-btn-danger`, `ap-btn-outline`, `ap-btn-sm`, `ap-label`, `ap-ctrl`, `ap-filters`, `ap-fg`, `ap-fg grow`, `ap-tcard`, `ap-table`, `dim`, `badge`, `bg-amber`, `bg-green`, `bg-gray`, `bg-red`, `bg-blue`, `ap-pager`, `ap-empty`, `ap-empty-t`, `ap-empty-s`

**Inline Styles:** Inline on badges, prices, refs, controls

---

## 27. `admin/equipment/products/index.blade.php`
**CSS Classes:**
`ap`, `ap-wrap`, `ap-topbar`, `ap-sub`, `ap-back`, `ap-ok`, `ap-btn`, `ap-btn-primary`, `ap-btn-danger`, `ap-btn-outline`, `ap-btn-sm`, `ap-label`, `ap-ctrl`, `ap-filters`, `ap-fg`, `ap-fg grow`, `ap-pager`, `ap-tcard`, `ap-table`, `dim`, `badge`, `bg-green`, `bg-gray`, `bg-red`, `r`, `c`, `ap-empty`, `ap-empty-t`, `ap-empty-s`, `ap-note`

**Inline Styles:** Multiple (gap, color, font-weight, display)

---

## 28. `admin/equipment/products/form.blade.php`
**CSS Classes:**
`ap`, `ap-wrap`, `ap-topbar`, `ap-sub`, `ap-back`, `ap-errs`, `ap-card`, `ap-grid`, `ap-full`, `ap-label`, `ap-ctrl`, `ap-hint`, `ap-err-inline`, `ap-check-row`, `ap-actions`, `ap-btn`, `ap-btn-primary`, `ap-btn-cancel`

**Inline Styles:** `style="resize:vertical;"`

---

## 29. `admin/equipment/orders/index.blade.php`
**CSS Classes:**
`ap`, `ap-wrap`, `ap-topbar`, `ap-sub`, `ap-back`, `ap-ok`, `ap-label`, `ap-ctrl`, `ap-btn-primary`, `ap-btn-outline`, `ap-btn-sm`, `ap-filters`, `ap-fg`, `ap-fg grow`, `ap-tcard`, `ap-table`, `dim`, `badge`, `bg-amber`, `bg-green`, `bg-gray`, `bg-red`, `bg-blue`, `bg-orange`, `ap-btn`, `r`, `ap-pager`, `ap-empty`, `ap-empty-t`, `ap-empty-s`, `ap-note`

**Inline Styles:** Various (display, gap, font-weight, white-space)

---

## 30. `admin/equipment/orders/show.blade.php`
**CSS Classes:**
`ap`, `ap-wrap`, `ap-topbar`, `ap-sub`, `ap-back`, `ap-ok`, `ap-grid-2`, `ap-card`, `ap-card-title`, `ap-kv`, `badge`, `bg-amber`, `bg-green`, `bg-gray`, `bg-red`, `bg-blue`, `bg-orange`, `ap-label`, `ap-ctrl`, `ap-btn-primary`, `ap-item`, `ap-item-total`

**Inline Styles:** Various (gap, flex, font-size, font-weight, color)

---

## 31. `emails/reseller-status.blade.php`
**CSS Classes (defined in `<style>`):**
`wrap`, `header`, `header-title`, `body`, `status-badge`, `badge-approved`, `badge-rejected`, `badge-icon`, `info-box`, `steps`, `cta`, `footer`

**Inline Styles:** Embedded full CSS in `<style>` tag

---

## 32. `emails/reseller-application-applicant.blade.php`
**CSS Classes:** NONE (plain HTML)
**Inline Styles:** NONE

---

## 33. `emails/reseller-application-admin.blade.php`
**CSS Classes:** NONE (plain HTML)
**Inline Styles:** NONE

---

## 34. `emails/reseller-alert.blade.php`
**CSS Classes (defined in `<style>`):**
`container`, `alert-box`, `alert-maintenance`, `alert-target`, `amount`, `footer`

**Inline Styles:** Embedded CSS in `<style>` tag

---

## 35. `emails/autovenda-wifi-code.blade.php`
**CSS Classes:** NONE (plain HTML)
**Inline Styles:** NONE

---

## 36. `emails/account-otp.blade.php`
**CSS Classes (defined in `<style>`):**
`wrap`, `header`, `header-title`, `body`, `otp-box`, `otp-code`, `otp-note`, `footer`

**Inline Styles:** Embedded CSS in `<style>` tag

---

## 37. `equipment/index.blade.php`
**CSS Classes:**
`page-hero`, `container`, `page-hero__eyebrow`, `page-hero__title`, `page-hero__desc`, `page-body`, `alert alert-success`, `alert alert-error`, `category-filter`, `is-active`, `equip-empty`, `equipment-grid`, `equipment-card`, `equipment-card__visual`, `equipment-card__img`, `equipment-card__placeholder`, `equipment-card__overlay`, `equipment-card__badges`, `equipment-card__cat-badge`, `equipment-card__stock-badge`, `equipment-card__stock-badge--in`, `equipment-card__stock-badge--out`, `equipment-card__stock-dot`, `equipment-card__body`, `equipment-card__name`, `equipment-card__footer`, `equipment-card__price-wrap`, `equipment-card__price`, `equipment-card__kz`, `equipment-card__cta`

**Inline Styles:** NONE

---

## 38. `equipment/show.blade.php`
**CSS Classes:**
`page-hero`, `page-hero--compact`, `container`, `breadcrumb`, `breadcrumb__sep`, `breadcrumb__current`, `page-hero__title`, `page-body`, `product-detail`, `product-detail__gallery`, `product-detail__image-wrap`, `product-detail__img`, `product-detail__placeholder`, `product-detail__cat-badge`, `product-detail__info`, `product-detail__price-block`, `product-detail__price`, `product-detail__currency`, `product-detail__desc`, `product-detail__stock`, `product-detail__stock--in`, `product-detail__stock--out`, `product-detail__form`, `product-detail__qty`, `product-detail__qty-label`, `product-detail__qty-input`, `btn-buy-now`, `btn-order`, `product-detail__cta`, `alert alert-error`, `product-detail__back`

**Inline Styles:** NONE

---

## 39. `equipment/cart.blade.php`
**CSS Classes:**
`page-hero`, `container`, `page-hero__eyebrow`, `page-hero__title`, `page-body`, `alert alert-success`, `card`, `card-body`, `data-table`, `btn-modern`, `btn-ghost`, `btn-primary`

**Inline Styles:** Multiple inline styles (text-align, font-weight, padding, object-fit, border-radius, color, font-size, display, gap, flex-wrap)

---

## 40. `equipment/checkout.blade.php`
**CSS Classes:**
`page-hero`, `container`, `page-hero__eyebrow`, `page-hero__title`, `page-body`, `alert alert-error`, `card`, `card-body`, `total`, `field`, `btn-ghost`, `btn-primary`

**Inline Styles:** Multiple inline styles (max-width, margin, display, grid-template-columns, gap, padding, border-bottom, font-size, font-weight)

---

## 41. `equipment/confirmation.blade.php`
**CSS Classes:**
`planos-section`, `container`, `plan-card-modern`, `btn-modern`

**Inline Styles:** All layout via inline styles (font-size, text-align, color, background, border-radius, padding, display, flex)

---

## 42. `pages/solicitar-plano.blade.php`
**CSS Classes:**
`container--720`, `checkout-page`, `checkout-title`, `checkout-subtitle`, `checkout-layout`, `checkout-summary-card`, `label`, `total`, `checkout-form-card`, `checkout-errors`, `checkout-form`, `checkout-form-row`, `checkout-phone-group`, `checkout-lookup-btn`, `checkout-lookup-feedback`, `checkout-lookup-feedback--ok`, `checkout-lookup-feedback--info`, `checkout-lookup-feedback--warn`, `checkout-autofilled`, `checkout-payment`, `checkout-payment-title`, `checkout-payment-options`, `checkout-note`, `checkout-actions`, `btn-primary`

**Inline Styles:** Minor inline styles + pushed `<style>` block for phone-group, lookup-btn, lookup-feedback, autofilled

---

## 43. `pages/solicitar-plano-confirmacao.blade.php`
**CSS Classes:**
`container--720`, `checkout-page`, `solicitar-confirmacao`, `solicitar-confirmacao-icon`, `solicitar-confirmacao-titulo`, `solicitar-confirmacao-subtitulo`, `solicitar-confirmacao-card`, `solicitar-confirmacao-row`, `solicitar-confirmacao-label`, `solicitar-confirmacao-value`, `solicitar-confirmacao-info`, `btn-primary`

**Inline Styles:** `style="display:inline-block;margin-top:2rem;text-decoration:none;"`

---

## 44. `pages/pagar-plano.blade.php`
**CSS Classes:**
`container--720`, `checkout-page`, `solicitar-confirmacao`, `solicitar-confirmacao-icon`, `solicitar-confirmacao-titulo`, `solicitar-confirmacao-subtitulo`, `pagar-plano-detalhe`, `label`, `pagar-plano-valor`, `pagar-plano-ref`, `pagar-plano-ref-inline`, `pagar-plano-instrucoes`, `pagar-plano-steps`, `pagar-plano-aviso`, `pagar-plano-contacto`, `checkout-btn`

**Inline Styles:** Pushed `<style>` block defining pagar-plano-* classes + minor inline styles

---

## 45. `pages/how-to-buy.blade.php`
**CSS Classes:**
`page-hero`, `container`, `page-hero__eyebrow`, `page-hero__title`, `page-hero__desc`, `page-body`, `howto-steps`, `howto-step`, `step-badge`, `step-body`, `steps-list`

**Inline Styles:** NONE

---

## 46. `pages/agendar-instalacao.blade.php`
**CSS Classes (defined in `<style>` + HTML):**
`ai-page`, `ai-wrap`, `ai-card`, `ai-sub`, `ai-ok`, `ai-err`, `ai-row`, `ai-types`, `ai-type`, `ai-type-icon`, `ai-btn`, `ai-back`

**Inline Styles:** NONE (all via `<style>` block)

---

## 47. `pages/account.blade.php`
**CSS Classes:**
`page-hero`, `container`, `page-hero__eyebrow`, `page-hero__title`, `page-hero__desc`, `page-body`, `auth-grid`, `auth-card`, `field`, `btn-primary`, `btn-ghost`, `auth-footer-note`, `card`, `card-body`, `data-table`

**Inline Styles:** Several inline styles for auth/otp styling (font-size, letter-spacing, text-align, background, border, color)

---

## 48. `pages/about.blade.php`
**CSS Classes:**
`page-hero`, `container`, `page-hero__eyebrow`, `page-hero__title`, `page-hero__desc`, `page-body`, `info-grid`, `info-card`, `info-card__icon`

**Inline Styles:** NONE

---

## 49. `pdf/vouchers-revendedor.blade.php`
**CSS Classes (defined in `<style>`):**
`header`, `header-left`, `header-right`, `brand`, `brand-sub`, `doc-title`, `doc-sub`, `info-grid`, `info-cell`, `info-label`, `info-val`, `info-val green`, `stats-bar`, `stat-box`, `stat-box green`, `stat-box blue`, `sv`, `sl`, `notice`, `voucher-table`, `code-cell`, `badge-stock`, `badge-sold`, `num-cell`, `customer-cell`, `date-cell`, `footer`, `footer-left`, `footer-right`

**Inline Styles:** Embedded CSS in `<style>` tag (all via classes)

---

## 50. `pdf/contrato-revendedor.blade.php`
**CSS Classes (defined in `<style>`):**
`section`, `label`, `assinatura`

**Inline Styles:** Inline styles on signature float layout

---

---

# DEDUPLICATED MASTER CLASS LIST (by category)

## Layout / Container
`container`, `container--720`, `container--880`

## Store Header
`store-header`, `store-header-inner`, `store-brand`, `store-logo`, `store-title`, `store-right`, `store-nav`, `store-link`, `store-link--muted`, `store-dropdown`, `store-dropdown-menu`, `dropdown-search-wrapper`, `search-input`, `search-wrapper`, `search-result-item`, `res-title`, `res-sub`, `store-dropdown-item`, `store-dropdown-item__icon`, `store-dropdown-item__body`, `store-dropdown-item__title`, `store-dropdown-item__desc`, `store-actions`, `store-cta`, `mobile-menu-toggle`, `mobile-menu-icon`, `store-mobile-menu`, `mobile-search-wrapper`, `store-mobile-link`, `store-mobile-cta`, `store-mobile-submenu`

## Store Footer
`site-footer`, `footer-top-accent`, `footer-inner`, `footer-col`, `footer-brand`, `brand-link`, `footer-brand-text`, `footer-tagline`, `footer-social`, `social-link`, `footer-links`, `footer-contact`, `contact-list`, `contact-icon`, `contact-info`, `contact-label`, `contact-link`, `contact-text`, `footer-legal`, `footer-legal-inner`, `muted`, `dev-credit`, `legal-nav`

## Hero / Carousel
`hero`, `hero__track`, `hero__slide`, `hero__card`, `hero__eyebrow`, `hero__title`, `hero__desc`, `hero__actions`, `hero__arrow`, `hero__arrow--prev`, `hero__arrow--next`, `hero__dots`, `hero__dot`

## Page Hero
`page-hero`, `page-hero--compact`, `page-hero__eyebrow`, `page-hero__title`, `page-hero__desc`

## Stat Bar
`stat-bar`, `stat-bar__grid`, `stat-bar__item`, `stat-bar__num`, `stat-bar__lbl`

## Plans / Cards
`planos-section`, `planos-section--individual`, `planos-section--family`, `planos-section--company`, `planos-section--institutional`, `section-header`, `section-header__rule`, `plans-grid`, `plans-grid--individual`, `plan-card-modern`, `plan-card--individual`, `plan-card--featured`, `plan-card-modern-inner`, `plan-card-modern-header`, `plan-card-modern-body`, `plan-card-modern-footer`, `plan-badge`, `plan-emoji`, `plan-title`, `plan-price-row`, `plan-price`, `plan-currency`, `plan-features`, `plan-feature`, `plan-feature--active`, `plan-desc`, `family-loading`, `family-loading__dot`, `family-empty-state`

## Buttons
`btn-primary`, `btn-ghost`, `btn-modern`, `btn-copy`, `btn-buy-now`, `btn-order`

## Checkout (Store)
`checkout-page`, `checkout-title`, `checkout-subtitle`, `checkout-layout`, `checkout-summary-card`, `checkout-form-card`, `checkout-errors`, `checkout-form`, `checkout-form-row`, `checkout-note`, `checkout-payment`, `checkout-payment-title`, `checkout-payment-options`, `checkout-actions`, `checkout-success-header`, `checkout-success-icon`, `checkout-divider`, `checkout-instructions`, `checkout-btn`, `checkout-phone-group`, `checkout-lookup-btn`, `checkout-lookup-feedback`, `checkout-autofilled`

## WiFi Code
`wifi-code-box`, `wifi-code-label`, `wifi-code-value`

## Reseller Apply Form
`reseller-page`, `reseller-layout`, `reseller-intro`, `reseller-badge`, `reseller-title`, `reseller-lead`, `reseller-points`, `reseller-form-card`, `reseller-form-title`, `reseller-form-subtitle`, `reseller-errors`, `reseller-form`, `reseller-form-row`, `reseller-radio-group`, `reseller-radio-option`, `reseller-field-error`, `reseller-fixed-message`, `reseller-fixed-title`, `reseller-fixed-body`, `reseller-form-actions`

## Reseller Thank-you Modal
`ty-overlay`, `ty-modal`, `ty-icon`, `ty-title`, `ty-sub`, `ty-steps`, `ty-btn`, `ty-progress`, `ty-progress-bar`, `ty-countdown`

## Reseller Panel (rv-*)
`rv-page`, `rv-login-wrap`, `rv-login-card`, `rv-brand`, `rv-brand-text`, `rv-field`, `rv-login-note`, `rv-btn-login`, `rv-dash`, `rv-topbar`, `rv-topbar-left`, `rv-avatar`, `rv-topbar-name`, `rv-topbar-email`, `rv-mode-badge`, `rv-logout-btn`, `rv-sub`, `rv-alert`, `rv-alert-icon`, `rv-stats`, `rv-stat-card`, `rv-stat-icon`, `rv-stat-label`, `rv-stat-value`, `rv-stat-sub`, `rv-progress-bar`, `rv-progress-fill`, `rv-panel`, `rv-panel-title`, `rv-panel-icon`, `rv-disc-table`, `rv-disc-current-chip`, `rv-purchase-form`, `rv-btn-buy`, `rv-field-error`, `rv-plan-grid`, `rv-plan-card`, `rv-plan-card--out`, `rv-plan-card-header`, `rv-plan-name`, `rv-plan-badges`, `rv-badge`, `rv-badge-validity`, `rv-badge-speed`, `rv-plan-prices`, `rv-plan-price-row`, `rv-plan-price-row--reseller`, `rv-plan-price-row--profit`, `rv-price-label`, `rv-price-public`, `rv-price-reseller`, `rv-price-profit`, `rv-plan-stock`, `rv-plan-add-form`, `rv-qty-input`, `rv-btn-add`, `rv-cart-panel`, `rv-cart-footer`, `rv-cart-totals`, `rv-cart-actions`, `rv-hist-wrap`, `rv-hist-table`, `rv-csv-btn`, `rv-empty`, `rv-pagination`

## Reseller Checkout (rv-pay-*)
`rv-pay-wrap`, `rv-back-link`, `rv-pay-header`, `rv-sum-table`, `rv-sum-plan-badge`, `rv-profit-col`, `rv-sum-tfoot`, `rv-methods-grid`, `rv-method-card`, `rv-method-icon`, `rv-method-name`, `rv-method-desc`, `rv-detail-box`, `rv-detail-row`, `rv-detail-label`, `rv-detail-value`, `rv-detail-note`, `rv-sim-notice`, `rv-btn-confirm`, `rv-btn-cancel`, `rv-total-bar`

## Reseller Stock (sp-*)
`sp`, `sp-wrap`, `sp-topbar`, `sp-sub`, `sp-back`, `sp-ok`, `sp-err`, `sp-summary`, `sp-card`, `sp-card-val`, `sp-card-lbl`, `sp-filters`, `sp-fg`, `sp-label`, `sp-ctrl`, `sp-btn`, `sp-btn-primary`, `sp-btn-outline`, `sp-btn-sm`, `sp-btn-yellow`, `sp-btn-green`, `sp-btn-ghost`, `sp-tcard`, `sp-table`, `sp-dist-form`, `sp-dist-input`, `sp-copy-btn`, `sp-dl-bar`, `sp-dl-title`, `sp-empty`, `sp-empty-title`, `sp-info`

## Admin Panel (ap-*)
`ap`, `ap-wrap`, `ap-topbar`, `ap-sub`, `ap-back`, `ap-ok`, `ap-err`, `ap-errs`, `ap-stats`, `ap-stat`, `ap-stat-val`, `ap-stat-lbl`, `ap-stat-sub`, `ap-label`, `ap-ctrl`, `ap-ctrl-mono`, `ap-btn`, `ap-btn-primary`, `ap-btn-outline`, `ap-btn-sm`, `ap-btn-del`, `ap-btn-danger`, `ap-btn-danger-outline`, `ap-btn-warning`, `ap-btn-cancel`, `ap-btn-logout`, `ap-filters`, `ap-fg`, `ap-tcard`, `ap-tcard-head`, `ap-tcard-head-title`, `ap-table`, `ap-pager`, `ap-empty`, `ap-empty-t`, `ap-empty-s`, `ap-empty-title`, `ap-empty-sub`, `ap-note`, `ap-sec`, `ap-nav`, `ap-kpis`, `ap-kpi`, `ap-kpi-val`, `ap-kpi-lbl`, `ap-kpi-sub`, `ap-banner-warn`, `ap-cards`, `ap-card`, `ap-card-title`, `ap-card-sub`, `ap-card-body`, `ap-card-actions`, `ap-actions`, `ap-check-row`, `ap-grid`, `ap-grid-2`, `ap-full`, `ap-field`, `ap-hint`, `ap-err-inline`, `ap-foot`, `ap-bar`, `ap-bar-fill`, `ap-dl`, `ap-list`, `ap-import-grid`, `ap-bulk-bar`, `ap-plans`, `ap-plan`, `ap-plan-diario`, `ap-plan-semanal`, `ap-plan-mensal`, `ap-plan-name`, `ap-plan-val`, `ap-plan-note`, `ap-rank-bar`, `ap-rank-fill`, `ap-ref`, `ap-detail-row`, `ap-detail-inner`, `ap-toggle`, `ap-file-zone`, `ap-file-label`, `ap-file-hint`, `ap-file-chosen`, `ap-item`, `ap-item-total`, `ap-kv`

## Admin KPI color helpers
`k-blue`, `k-green`, `k-amber`, `k-purple`, `k-rose`, `k-teal`

## Admin Login
`adm-login-page`, `adm-login-card`, `adm-login-header`, `adm-login-logo`, `adm-login-body`, `adm-login-error`, `adm-login-label`, `adm-login-input`, `adm-login-submit`, `adm-login-foot`

## Voucher Plans Admin (vp-*)
`vp-grid`, `vp-card`, `vp-card-head`, `vp-card-title`, `vp-badge-active`, `vp-badge-inactive`, `vp-card-body`, `vp-row`, `vp-row-label`, `vp-row-value`, `vp-profit`, `vp-card-foot`, `vp-modal-overlay`, `vp-modal`, `vp-modal-close`

## Badges (shared)
`badge`, `bg-amber`, `bg-orange`, `bg-green`, `bg-gray`, `bg-red`, `bg-blue`, `bg-purple`, `bg-teal`, `bg-sky`, `badge-stock`, `badge-sold`, `badge-plan-diario`, `badge-plan-semanal`, `badge-plan-mensal`

## Plan bar charts (dashboard)
`plan-bars`, `plan-bar`, `plan-bar-name`, `plan-bar-track`, `plan-bar-fill`, `fill-blue`, `fill-purple`, `fill-amber`, `plan-bar-count`

## Stock color helpers
`c-ok`, `c-low`, `c-out`, `c-dim`

## Utility/shared
`dim`, `mono`, `r`, `c`, `here`, `label`, `total`, `field`, `amount`, `lbl`, `profit`, `active`, `ready`, `show`, `hidden`, `row-checkbox`, `grow`

## Generic UI
`card`, `card-body`, `data-table`, `alert`, `alert-success`, `alert-error`, `info-card`, `info-card__icon`, `info-grid`, `info-banner`

## How-to-buy
`howto-steps`, `howto-step`, `step-badge`, `step-body`, `steps-list`

## Appointment Scheduling (ai-*)
`ai-page`, `ai-wrap`, `ai-card`, `ai-sub`, `ai-ok`, `ai-err`, `ai-row`, `ai-types`, `ai-type`, `ai-type-icon`, `ai-btn`, `ai-back`

## Account / Auth
`auth-grid`, `auth-card`, `auth-footer-note`

## Breadcrumb
`breadcrumb`, `breadcrumb__sep`, `breadcrumb__current`

## Category Filter
`category-filter`, `is-active`

## Equipment Cards
`equipment-grid`, `equipment-card`, `equipment-card__visual`, `equipment-card__img`, `equipment-card__placeholder`, `equipment-card__overlay`, `equipment-card__badges`, `equipment-card__cat-badge`, `equipment-card__stock-badge`, `equipment-card__stock-badge--in`, `equipment-card__stock-badge--out`, `equipment-card__stock-dot`, `equipment-card__body`, `equipment-card__name`, `equipment-card__footer`, `equipment-card__price-wrap`, `equipment-card__price`, `equipment-card__kz`, `equipment-card__cta`, `equip-empty`

## Product Detail
`product-detail`, `product-detail__gallery`, `product-detail__image-wrap`, `product-detail__img`, `product-detail__placeholder`, `product-detail__cat-badge`, `product-detail__info`, `product-detail__price-block`, `product-detail__price`, `product-detail__currency`, `product-detail__desc`, `product-detail__stock`, `product-detail__stock--in`, `product-detail__stock--out`, `product-detail__form`, `product-detail__qty`, `product-detail__qty-label`, `product-detail__qty-input`, `product-detail__cta`, `product-detail__back`

## Solicitar/Pagar Plano Pages
`solicitar-confirmacao`, `solicitar-confirmacao-icon`, `solicitar-confirmacao-titulo`, `solicitar-confirmacao-subtitulo`, `solicitar-confirmacao-card`, `solicitar-confirmacao-row`, `solicitar-confirmacao-label`, `solicitar-confirmacao-value`, `solicitar-confirmacao-info`, `pagar-plano-detalhe`, `pagar-plano-valor`, `pagar-plano-ref`, `pagar-plano-ref-inline`, `pagar-plano-instrucoes`, `pagar-plano-steps`, `pagar-plano-aviso`, `pagar-plano-contacto`

## Email Templates (self-contained `<style>` classes)
- **reseller-status**: `wrap`, `header`, `header-title`, `body`, `status-badge`, `badge-approved`, `badge-rejected`, `badge-icon`, `info-box`, `steps`, `cta`, `footer`
- **reseller-alert**: `container`, `alert-box`, `alert-maintenance`, `alert-target`, `amount`, `footer`
- **account-otp**: `wrap`, `header`, `header-title`, `body`, `otp-box`, `otp-code`, `otp-note`, `footer`

## PDF Templates (self-contained `<style>` classes)
- **vouchers-revendedor**: `header`, `header-left`, `header-right`, `brand`, `brand-sub`, `doc-title`, `doc-sub`, `info-grid`, `info-cell`, `info-label`, `info-val`, `stats-bar`, `stat-box`, `sv`, `sl`, `notice`, `voucher-table`, `code-cell`, `badge-stock`, `badge-sold`, `num-cell`, `customer-cell`, `date-cell`, `footer`, `footer-left`, `footer-right`
- **contrato-revendedor**: `section`, `label`, `assinatura`
