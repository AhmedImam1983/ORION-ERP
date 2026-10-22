<x-filament-panels::page.simple>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap');

* { box-sizing: border-box; }
body { font-family: 'Cairo', sans-serif !important; }

/* ── Hide Filament defaults ── */
.fi-simple-header { display: none !important; }

.fi-simple-page {
  min-height: 100vh !important;
  padding: 0 !important;
  background:
    radial-gradient(ellipse at 20% 50%, rgba(109,40,217,.22) 0%, transparent 45%),
    radial-gradient(ellipse at 85% 80%, rgba(76,29,149,.18) 0%, transparent 40%),
    linear-gradient(135deg, #07091a 0%, #0a0c20 50%, #080a1c 100%) !important;
}

.fi-simple-main {
  width: 100% !important;
  max-width: none !important;
  padding: 0 !important;
}

/* ═══════════════════════════════════
   LAYOUT
═══════════════════════════════════ */
.ol-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 32px;
  direction: rtl;
}

.ol-card {
  width: min(1160px, 96vw);
  min-height: 700px;
  display: grid;
  grid-template-columns: 1.1fr .9fr;
  border-radius: 24px;
  overflow: hidden;
  border: 1px solid rgba(124,58,237,.3);
  box-shadow:
    0 40px 100px rgba(0,0,0,.6),
    0 0 80px rgba(91,33,182,.15),
    inset 0 1px 0 rgba(255,255,255,.05);
  position: relative;
}

/* ═══════════════════════════════════
   LEFT — BRAND
═══════════════════════════════════ */
.ol-brand {
  position: relative;
  overflow: hidden;
  padding: 48px 44px 40px;
  display: flex;
  flex-direction: column;
  background:
    radial-gradient(ellipse at 30% 25%, rgba(124,58,237,.22) 0%, transparent 40%),
    radial-gradient(ellipse at 70% 75%, rgba(76,29,149,.15) 0%, transparent 40%),
    linear-gradient(155deg, #14103a 0%, #0f1132 45%, #090b24 100%);
}

/* Dot grid pattern */
.ol-brand::after {
  content: '';
  position: absolute;
  inset: 0;
  background-image: radial-gradient(circle, rgba(255,255,255,.55) 1px, transparent 1px);
  background-size: 88px 88px;
  opacity: .28;
  pointer-events: none;
}

/* Glow blob */
.ol-brand::before {
  content: '';
  position: absolute;
  width: 500px; height: 200px;
  left: -150px; bottom: 120px;
  border-radius: 50%;
  background: rgba(124,58,237,.2);
  filter: blur(50px);
  transform: rotate(-15deg);
  pointer-events: none;
}

.ol-brand-inner {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  height: 100%;
}

/* Logo */
.ol-logo {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 36px;
  direction: ltr;
}

.ol-star {
  width: 32px; height: 32px;
  background: linear-gradient(135deg, #8b5cf6, #6d28d9);
  border-radius: 9px;
  display: flex; align-items: center; justify-content: center;
  font-size: 18px; color: #fff;
  box-shadow: 0 0 20px rgba(139,92,246,.5), 0 0 40px rgba(124,58,237,.25);
}

.ol-logo-text {
  font-size: 22px; font-weight: 900;
  color: #fff; letter-spacing: -.3px;
}

.ol-logo-erp { color: #9b5cff; }

/* Headline */
.ol-headline {
  font-size: clamp(32px, 3.5vw, 50px);
  font-weight: 900;
  color: #fff;
  line-height: 1.2;
  letter-spacing: -1.5px;
  margin-bottom: 18px;
}

.ol-headline .pu { color: #9b5cff; text-shadow: 0 0 30px rgba(155,92,255,.3); }

.ol-desc {
  font-size: 14px;
  color: #8892b0;
  line-height: 2;
  font-weight: 400;
  margin-bottom: 36px;
}

.ol-desc strong { color: #9b5cff; font-weight: 700; }

/* ── Dashboard Mockup ── */
.ol-mockup-wrap {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 -10px;
}

.ol-mockup {
  width: 92%;
  background: linear-gradient(145deg, #131936, #070b1c);
  border: 1px solid rgba(139,92,246,.5);
  border-radius: 20px;
  padding: 14px;
  transform: rotate(-6deg) translateY(-8px);
  box-shadow:
    0 30px 70px rgba(0,0,0,.6),
    0 0 50px rgba(124,58,237,.2);
}

.ol-mockup-inner {
  background: #090e23;
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,.04);
  padding: 12px;
  display: grid;
  grid-template-columns: 48px 1fr;
  gap: 10px;
  min-height: 260px;
}

/* Sidebar */
.ol-mock-sidebar {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding-top: 4px;
}

.ol-mock-icon {
  width: 28px; height: 28px;
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  color: #4a5568;
  border: 1px solid rgba(255,255,255,.05);
  font-size: 12px;
}

.ol-mock-icon.act {
  background: linear-gradient(135deg, #7c3aed, #5b21b6);
  color: #fff;
  border-color: transparent;
  box-shadow: 0 0 16px rgba(124,58,237,.4);
}

/* Main content */
.ol-mock-main {
  display: flex;
  flex-direction: column;
  gap: 9px;
}

/* Welcome bar */
.ol-mock-welcome {
  background: linear-gradient(135deg, rgba(124,58,237,.25), rgba(91,33,182,.15));
  border: 1px solid rgba(139,92,246,.2);
  border-radius: 8px;
  padding: 8px 12px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.ol-mock-welcome-text { font-size: 9px; color: #fff; font-weight: 700; }
.ol-mock-welcome-sub  { font-size: 7px; color: #6d7898; margin-top: 1px; }

.ol-mock-av {
  width: 22px; height: 22px;
  border-radius: 50%;
  background: linear-gradient(135deg, #7c3aed, #4c1d95);
  display: flex; align-items: center; justify-content: center;
  font-size: 9px; color: #fff; font-weight: 700;
}

/* KPI row */
.ol-mock-kpis {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 7px;
}

.ol-mock-kpi {
  border-radius: 7px;
  padding: 7px 8px;
  position: relative;
  overflow: hidden;
}

.ol-mock-kpi-n { font-size: 11px; font-weight: 800; color: #fff; }
.ol-mock-kpi-l { font-size: 7px; color: rgba(255,255,255,.55); margin-top: 1px; }

/* Chart row */
.ol-mock-charts {
  display: grid;
  grid-template-columns: 1.1fr .9fr;
  gap: 8px;
  flex: 1;
}

.ol-mock-chart {
  background: linear-gradient(180deg, #10162e, #0b1025);
  border: 1px solid rgba(255,255,255,.05);
  border-radius: 9px;
  overflow: hidden;
  position: relative;
  min-height: 120px;
}

.ol-mock-bars {
  height: 100%;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  gap: 7px;
  padding: 20px 16px 12px;
}

.ol-mock-bar {
  width: 12px;
  border-radius: 4px 4px 2px 2px;
  background: linear-gradient(180deg, #a855f7, #6d28d9);
  box-shadow: 0 0 12px rgba(139,92,246,.3);
}

.ol-mock-line {
  position: absolute;
  width: 130%;
  height: 2px;
  left: -15%;
  bottom: 35px;
  background: linear-gradient(90deg, transparent, #8b5cf6, #c084fc, transparent);
  transform: rotate(-20deg);
  box-shadow: 0 0 12px #8b5cf6;
}

/* Mini colored KPIs row */
.ol-mock-mini {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 6px;
}

.ol-mock-mini-card {
  border-radius: 7px;
  padding: 6px 8px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.ol-mock-mini-n { font-size: 12px; font-weight: 800; color: #fff; }
.ol-mock-mini-l { font-size: 6px; color: rgba(255,255,255,.55); margin-top: 1px; }
.ol-mock-mini-ic { width: 16px; height: 16px; border-radius: 5px; background: rgba(255,255,255,.15); display: flex; align-items: center; justify-content: center; font-size: 8px; }

/* ── Feature cards ── */
.ol-features {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  margin-top: 30px;
  position: relative;
  z-index: 2;
}

.ol-feature {
  background: rgba(10,15,38,.65);
  border: 1px solid rgba(139,92,246,.2);
  border-radius: 14px;
  padding: 16px 12px;
  text-align: center;
  backdrop-filter: blur(8px);
  transition: border-color .2s;
}

.ol-feature:hover { border-color: rgba(139,92,246,.4); }

.ol-feature-ic {
  width: 32px; height: 32px;
  margin: 0 auto 9px;
  border: 1px solid rgba(139,92,246,.5);
  border-radius: 9px;
  display: flex; align-items: center; justify-content: center;
  color: #a78bfa;
  font-size: 14px;
}

.ol-feature-t { color: #fff; font-weight: 800; font-size: 13px; }
.ol-feature-s { color: #5a6180; font-size: 10px; margin-top: 3px; font-weight: 400; }

/* ═══════════════════════════════════
   RIGHT — LOGIN FORM
═══════════════════════════════════ */
.ol-login {
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 64px 60px 44px;
  background: linear-gradient(160deg, rgba(9,13,32,.99), rgba(6,9,24,.99));
  direction: rtl;
  position: relative;
}

/* subtle border left */
.ol-login::before {
  content: '';
  position: absolute;
  right: 0; top: 15%; bottom: 15%;
  width: 1px;
  background: linear-gradient(180deg, transparent, rgba(139,92,246,.3), transparent);
}

.ol-login-inner {
  width: 100%;
  max-width: 420px;
  margin: auto;
}

.ol-login-head { margin-bottom: 36px; }

.ol-login-head h1 {
  font-size: 30px;
  font-weight: 900;
  color: #fff;
  margin: 0 0 8px;
  line-height: 1.3;
}

.ol-login-head p {
  font-size: 13px;
  color: #64748b;
  font-weight: 400;
}

/* ── Filament form overrides ── */
* { font-family: 'Cairo', sans-serif !important; }

.fi-fo-field-wrp { margin-bottom: 20px !important; }

label, .fi-fo-field-wrp-label {
  color: #e2e8f0 !important;
  font-weight: 600 !important;
  font-size: 14px !important;
  margin-bottom: 8px !important;
}

.fi-input-wrp {
  min-height: 52px !important;
  border-radius: 12px !important;
  background: rgba(255,255,255,.04) !important;
  border: 1px solid rgba(255,255,255,.1) !important;
  box-shadow: inset 0 1px 0 rgba(255,255,255,.025) !important;
  transition: border-color .2s, box-shadow .2s !important;
}

.fi-input-wrp:focus-within {
  border-color: #8b5cf6 !important;
  box-shadow: 0 0 0 3px rgba(139,92,246,.15), 0 0 20px rgba(124,58,237,.1) !important;
}

input[type="email"],
input[type="password"],
input[type="text"] {
  color: #fff !important;
  font-size: 14px !important;
  background: transparent !important;
}

input::placeholder { color: #4a5568 !important; }

/* Login button */
.fi-sc-actions { margin-top: 28px !important; }

.fi-btn {
  width: 100% !important;
  min-height: 54px !important;
  border-radius: 12px !important;
  background: linear-gradient(100deg, #6d28d9, #8b5cf6, #7c3aed) !important;
  border: none !important;
  color: #fff !important;
  font-size: 16px !important;
  font-weight: 800 !important;
  box-shadow: 0 12px 30px rgba(124,58,237,.3) !important;
  transition: transform .2s, box-shadow .2s !important;
}

.fi-btn:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 16px 40px rgba(124,58,237,.45) !important;
}

/* Forgot password & checkbox */
.fi-fo-checkbox-label { color: #cbd5e1 !important; font-size: 13px !important; }
a { color: #9b5cff !important; }
a:hover { color: #c084fc !important; }

/* Footer */
.ol-footer {
  margin-top: 44px;
  text-align: center;
  color: #374151;
  font-size: 11px;
}

.ol-footer strong { color: #7c3aed; }

/* Responsive */
@media (max-width: 900px) {
  .ol-card { grid-template-columns: 1fr; }
  .ol-brand { padding: 38px 24px 28px; }
  .ol-login { padding: 44px 28px 36px; }
  .ol-login::before { display: none; }
}

@media (max-width: 520px) {
  .ol-page { padding: 10px; }
  .ol-card { border-radius: 18px; }
  .ol-headline { font-size: 30px; }
  .ol-features { grid-template-columns: 1fr 1fr; }
  .ol-login { padding: 36px 20px 28px; }
}
</style>

<div class="ol-page">
  <div class="ol-card">

    {{-- ══ LEFT — BRAND ══ --}}
    <section class="ol-brand">
      <div class="ol-brand-inner">

        {{-- Logo --}}
        <div class="ol-logo">
          <div class="ol-star">✦</div>
          <span class="ol-logo-text">Orion <span class="ol-logo-erp">ERP</span></span>
        </div>

        {{-- Headline --}}
        <h2 class="ol-headline">
          نظام متكامل<br>
          لإدارة أعمالك<br>
          <span class="pu">بذكاء</span>
        </h2>

        <p class="ol-desc">
          أدِر عملياتك، تابع بياناتك، واتخذ قراراتك بثقة<br>
          من خلال نظام <strong>Orion ERP</strong> المتكامل.
        </p>

        {{-- Dashboard Mockup --}}
        <div class="ol-mockup-wrap">
          <div class="ol-mockup">
            <div class="ol-mockup-inner">

              {{-- Sidebar --}}
              <div class="ol-mock-sidebar">
                <div class="ol-mock-icon act">⌂</div>
                <div class="ol-mock-icon">🛒</div>
                <div class="ol-mock-icon">♙</div>
                <div class="ol-mock-icon">▣</div>
                <div class="ol-mock-icon">◇</div>
                <div class="ol-mock-icon">⚙</div>
              </div>

              {{-- Main --}}
              <div class="ol-mock-main">

                {{-- Welcome bar --}}
                <div class="ol-mock-welcome">
                  <div>
                    <div class="ol-mock-welcome-text">Welcome back, Super Admin</div>
                    <div class="ol-mock-welcome-sub">إليك ملخص نشاط النظام اليوم</div>
                  </div>
                  <div class="ol-mock-av">A</div>
                </div>

                {{-- KPI cards --}}
                <div class="ol-mock-kpis">
                  <div class="ol-mock-kpi" style="background:linear-gradient(135deg,rgba(124,58,237,.7),rgba(91,33,182,.5))">
                    <div class="ol-mock-kpi-n">462,177</div>
                    <div class="ol-mock-kpi-l">المبيعات</div>
                  </div>
                  <div class="ol-mock-kpi" style="background:linear-gradient(135deg,rgba(29,78,216,.7),rgba(30,58,138,.5))">
                    <div class="ol-mock-kpi-n">69</div>
                    <div class="ol-mock-kpi-l">الفواتير</div>
                  </div>
                  <div class="ol-mock-kpi" style="background:linear-gradient(135deg,rgba(190,24,93,.7),rgba(157,23,77,.5))">
                    <div class="ol-mock-kpi-n">10</div>
                    <div class="ol-mock-kpi-l">العملاء</div>
                  </div>
                </div>

                {{-- Charts --}}
                <div class="ol-mock-charts">
                  <div class="ol-mock-chart">
                    <div class="ol-mock-bars">
                      <div class="ol-mock-bar" style="height:38%"></div>
                      <div class="ol-mock-bar" style="height:62%"></div>
                      <div class="ol-mock-bar" style="height:48%"></div>
                      <div class="ol-mock-bar" style="height:85%"></div>
                      <div class="ol-mock-bar" style="height:55%"></div>
                      <div class="ol-mock-bar" style="height:72%"></div>
                    </div>
                  </div>
                  <div class="ol-mock-chart">
                    <div class="ol-mock-line"></div>
                  </div>
                </div>

                {{-- Mini cards --}}
                <div class="ol-mock-mini">
                  <div class="ol-mock-mini-card" style="background:linear-gradient(135deg,rgba(14,165,233,.4),rgba(2,132,199,.25))">
                    <div><div class="ol-mock-mini-n">21</div><div class="ol-mock-mini-l">الأصناف</div></div>
                    <div class="ol-mock-mini-ic">📦</div>
                  </div>
                  <div class="ol-mock-mini-card" style="background:linear-gradient(135deg,rgba(20,184,166,.4),rgba(13,148,136,.25))">
                    <div><div class="ol-mock-mini-n">7</div><div class="ol-mock-mini-l">موظفين</div></div>
                    <div class="ol-mock-mini-ic">👥</div>
                  </div>
                  <div class="ol-mock-mini-card" style="background:linear-gradient(135deg,rgba(245,158,11,.4),rgba(217,119,6,.25))">
                    <div><div class="ol-mock-mini-n">81</div><div class="ol-mock-mini-l">حسابات</div></div>
                    <div class="ol-mock-mini-ic">📊</div>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>

        {{-- Feature cards --}}
        <div class="ol-features">
          <div class="ol-feature">
            <div class="ol-feature-ic">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            </div>
            <div class="ol-feature-t">تقارير لحظية</div>
            <div class="ol-feature-s">تقارير دقيقة في الوقت الحقيقي</div>
          </div>
          <div class="ol-feature">
            <div class="ol-feature-ic">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <div class="ol-feature-t">أمان عالي</div>
            <div class="ol-feature-s">حماية بياناتك بأعلى المعايير</div>
          </div>
          <div class="ol-feature">
            <div class="ol-feature-ic">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.39-1 1.73V7h1a7 7 0 0 1 7 7h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h1a7 7 0 0 1 7-7h1V5.73c-.6-.34-1-.99-1-1.73a2 2 0 0 1 2-2z"/></svg>
            </div>
            <div class="ol-feature-t">واجهة ذكية</div>
            <div class="ol-feature-s">تصميم عصري وسهل الاستخدام</div>
          </div>
        </div>

      </div>
    </section>

    {{-- ══ RIGHT — LOGIN ══ --}}
    <section class="ol-login">
      <div class="ol-login-inner">

        <div class="ol-login-head">
          <h1>مرحباً بعودتك 👋</h1>
          <p>سجّل الدخول للوصول إلى حسابك</p>
        </div>

        {{ $this->content }}

        <div class="ol-footer">
          جميع الحقوق محفوظة © <strong>Orion ERP</strong> 2026
        </div>

      </div>
    </section>

  </div>
</div>

</x-filament-panels::page.simple>