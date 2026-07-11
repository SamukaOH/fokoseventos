<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Fokos Eventos</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sora:wght@600;700;800&family=Bebas+Neue&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { width: 100%; background: #0D0E12; color: #f0f0f0; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
/* Fundo do sistema — div real (confiável no mobile); visível só ≤768px */
.login-bg-sys { display:none; }
@media (max-width:768px) {
  .login-bg-sys { display:block; position:fixed; inset:0; z-index:0; pointer-events:none; }
  .login-bg-sys .b-img {
    position:absolute; inset:0;
    background:url('<?= APP_URL ?>/public/assets/img/bg-ocean.jpg?v=7') center/cover no-repeat;
    opacity:.2;
  }
  .login-bg-sys .b-fx { display:none; }
  .page { position:relative; z-index:1; }
}
@keyframes bokehLg { from { opacity:.7; } to { opacity:1; } }

:root { --yellow:#FFD600; --border:rgba(255,255,255,.07); --muted:#666; --danger:#ff3b30; --success:#4cd964; }

/* ═══════════════════════════
   DESKTOP — split layout
   ═══════════════════════════ */
.page { display:flex; min-height:100vh; }

.left {
  flex:1; display:flex; flex-direction:column;
  justify-content:space-between; padding:48px;
  position:relative; overflow:hidden;
  background:url('<?= APP_URL ?>/public/assets/img/login-bg.jpg') center/cover no-repeat;
}
.left::before {
  content:''; position:absolute; inset:0;
  background:linear-gradient(135deg,rgba(0,0,0,.82) 0%,rgba(0,0,0,.60) 50%,rgba(0,0,0,.75) 100%);
}
.left::after {
  content:''; position:absolute; bottom:0; left:0; right:0; height:200px;
  background:linear-gradient(to top,rgba(0,0,0,.7),transparent);
}
.left > * { position:relative; z-index:1; }
.left-brand img { height:80px; filter:brightness(0) invert(1); opacity:.95; }
.left-headline {
  font-family:'Sora',sans-serif;
  font-size:clamp(52px, 4.6vw, 68px);
  font-weight:800; line-height:1.02; letter-spacing:-.04em;
  margin-bottom:20px;
  color:#fff;
}
.hl-line { display:block; overflow:hidden; padding-bottom:.08em; margin-bottom:-.08em; }
.hl-in {
  display:inline-block;
  background:linear-gradient(180deg, #fff 35%, #B9BCC8 100%);
  -webkit-background-clip:text; background-clip:text;
  -webkit-text-fill-color:transparent;
  transform:translateY(110%);
  filter:blur(6px); opacity:0;
  animation:hlUp .7s cubic-bezier(.22,1,.36,1) var(--d,0s) forwards;
}
@keyframes hlUp {
  60% { filter:blur(0); }
  to  { transform:none; filter:blur(0); opacity:1; }
}
.hl-y {
  position:relative; white-space:nowrap;
  background:linear-gradient(180deg, #FFE14D, var(--yellow) 75%);
  -webkit-background-clip:text; background-clip:text;
  -webkit-text-fill-color:transparent; color:var(--yellow);
}
.hl-y::after {
  content:''; position:absolute; left:2%; right:2%; bottom:.06em;
  height:.1em; border-radius:4px;
  background:linear-gradient(90deg, var(--yellow), rgba(255,214,0,.25));
  z-index:-1;
  transform:scaleX(0); transform-origin:left center;
  animation:hlBar .5s cubic-bezier(.22,1,.36,1) .85s forwards;
}
@keyframes hlBar { to { transform:scaleX(1); } }

/* cascata: descrição, estatísticas e formulário */
.left-desc, .left-stats { opacity:0; transform:translateY(14px); animation:riseIn .6s cubic-bezier(.22,1,.36,1) forwards; }
.left-desc  { animation-delay:.6s; }
.left-stats { animation-delay:.75s; }
.form-box, .login-brand {
  width:170px; height:auto; margin-bottom:26px;
  opacity:0; transform:translateY(16px);
  animation:riseIn .6s cubic-bezier(.22,1,.36,1) .35s forwards;
}
@media (max-width: 900px) { .login-brand { display:none; } }
.mobile-logo { width:96px; height:auto; }

.fk-transition {
  position:fixed; inset:0; z-index:99999;
  background:#0D0E12;
  display:flex; flex-direction:column; align-items:center; justify-content:center; gap:26px;
  animation:fadeIn2 .25s ease;
}
@keyframes fadeIn2 { from { opacity:0 } to { opacity:1 } }

.fk-transition img { width:min(46vw, 210px); height:auto; animation:fkPulse 1.6s ease-in-out infinite; position:relative; }
@keyframes fkPulse { 0%,100% { opacity:.85; transform:scale(1) } 50% { opacity:1; transform:scale(1.04) } }
.fk-transition .fk-bar { width:min(46vw, 210px); height:3px; border-radius:3px; background:rgba(255,255,255,.08); overflow:hidden; position:relative; }
.fk-transition .fk-bar::after { content:''; position:absolute; top:0; bottom:0; left:-40%; width:40%; border-radius:3px;
  background:linear-gradient(90deg, transparent, var(--yellow), transparent); animation:fkBar 1s cubic-bezier(.4,0,.2,1) infinite; }
@keyframes fkBar { to { left:100% } }
.fk-transition .fk-txt { font-family:'Sora',sans-serif; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.22em; color:#5C6170; }

.login-copy { opacity:0; transform:translateY(16px); animation:riseIn .6s cubic-bezier(.22,1,.36,1) forwards; }
.form-box   { animation-delay:.5s; }
.login-copy { animation-delay:.75s; }
@keyframes riseIn { to { opacity:1; transform:none; } }

@media (prefers-reduced-motion: reduce) {
  .hl-in, .hl-y::after, .left-desc, .left-stats, .form-box, .login-copy {
    animation:none; opacity:1; transform:none; filter:none;
  }
}
.left-desc { font-size:15px; color:var(--muted); line-height:1.7; max-width:380px; }
.left-stats { display:flex; gap:32px; }
.stat-num { font-family:'Sora',sans-serif; font-size:28px; font-weight:800; color:var(--yellow); }
.stat-lbl { font-size:11px; color:var(--muted); margin-top:4px; }

.right {
  width:460px; flex-shrink:0;
  display:flex; flex-direction:column; align-items:center; justify-content:center;
  padding:48px 40px;
  background:rgba(16,18,24,.55);
  backdrop-filter:saturate(160%) blur(24px);
  -webkit-backdrop-filter:saturate(160%) blur(24px);
  border-left:1px solid var(--border);
}
.form-box { width:100%; max-width:360px; }

/* ── form elements ── */
.form-eyebrow { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.16em; color:var(--yellow); margin-bottom:10px; }
.form-title { font-family:'Sora',sans-serif; font-size:26px; font-weight:800; margin-bottom:6px; }
.form-sub { font-size:13px; color:var(--muted); margin-bottom:32px; line-height:1.5; }

.field { margin-bottom:16px; }
.field label { display:block; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.07em; color:var(--muted); margin-bottom:8px; }
.field-inner { position:relative; }
.field-inner .fi { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--muted); font-size:13px; pointer-events:none; }
.field-inner input { width:100%; background:rgba(255,255,255,.05); border:1px solid var(--border); border-radius:12px; -webkit-appearance:none; color:#f0f0f0; font-family:'Inter',sans-serif; font-size:14px; padding:13px 44px 13px 40px; outline:none; transition:border-color .2s; }
.field-inner input:focus { border-color:rgba(255,214,0,.55); box-shadow:0 0 0 3px rgba(255,214,0,.12); background:rgba(255,255,255,.07); }
.field-inner .toggle-pw { position:absolute; right:14px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--muted); cursor:pointer; font-size:14px; padding:0; line-height:1; }

/* Autofill do navegador: manter os campos escuros (sem o amarelo/branco do Chrome) */
.field-inner input:-webkit-autofill,
.field-inner input:-webkit-autofill:hover,
.field-inner input:-webkit-autofill:focus {
  -webkit-box-shadow: 0 0 0 1000px #1A1D26 inset !important;
  -webkit-text-fill-color:#F2F3F7 !important;
  caret-color:#F2F3F7;
  border-radius:12px;
  transition: background-color 99999s ease-in-out 0s;
}

.btn-login { width:100%; margin-top:8px; padding:15px; background:var(--yellow); color:#000; border:none; border-radius:12px; font-family:'Inter',sans-serif; font-size:14px; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; transition:all .2s; }
.btn-login:hover { background:#ffe033; transform:translateY(-1px); box-shadow:0 8px 24px rgba(255,214,0,.2); }
.btn-login:disabled { opacity:.6; cursor:not-allowed; transform:none; box-shadow:none; }

.msg { display:none; margin-top:14px; padding:12px 14px; border-radius:10px; font-size:13px; font-weight:500; }
.msg.error   { background:rgba(255,59,48,.1);  border:1px solid rgba(255,59,48,.3);  color:var(--danger); }
.msg.success { background:rgba(76,217,100,.1); border:1px solid rgba(76,217,100,.3); color:var(--success); }

.divider { display:flex; align-items:center; gap:12px; margin:24px 0; }
.divider::before,.divider::after { content:''; flex:1; height:1px; background:var(--border); }
.divider span { font-size:11px; color:var(--muted); white-space:nowrap; }

.login-copy {
  margin-top:28px; text-align:center;
  font-size:11px; color:var(--muted);
  letter-spacing:.02em;
}

/* ═══════════════════════════
   MOBILE
   ═══════════════════════════ */
@media (max-width: 768px) {
  html, body { height: 100%; }
  body {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 32px 16px;
  }
  .page { width: 100%; max-width: 400px; flex-direction: column; min-height: unset; }
  .left  { display: none; }
  .right { width: 100%; border: none; background: none; backdrop-filter: none; -webkit-backdrop-filter: none; box-shadow: none; padding: 0; display: flex; flex-direction: column; align-items: center; }
  .mobile-hero { display: flex; align-items: center; justify-content: center; width: 100%; margin-bottom: 28px; }
  .mobile-logo { height: 80px; filter: brightness(0) invert(1); opacity: .95; }
  .form-box    { width: 100%; max-width: 400px; background: rgba(20,22,30,.72); backdrop-filter: saturate(160%) blur(22px); -webkit-backdrop-filter: saturate(160%) blur(22px); border: 1px solid rgba(255,255,255,.12); border-radius: 22px; padding: 26px 20px 20px; box-shadow: 0 1px 0 rgba(255,255,255,.05) inset, 0 20px 60px rgba(0,0,0,.5); }
  .form-eyebrow { display: none; }
  .form-title  { font-size: 20px; margin-bottom: 4px; }
  .form-sub    { font-size: 12px; margin-bottom: 20px; }
  .field-inner input { font-size: 16px; }
  .btn-login   { padding: 15px; font-size: 15px; }
  .divider     { margin: 16px 0; }
  .login-copy  { display:none; }
  .mobile-copy { display: block; width: 100%; text-align: center; padding-top: 16px; font-size: 10px; color: rgba(255,255,255,.15); }
}

/* Ocultar no desktop */
@media (min-width: 769px) {
  .mobile-hero, .mobile-copy { display:none; }
}
</style>
</head>
<body>
<div class="login-bg-sys"><div class="b-img"></div><div class="b-fx"></div></div>
<div class="page">

  <!-- Esquerdo — só desktop -->
  <div class="left">
    <div class="left-brand"><img src="<?= APP_URL ?>/public/assets/img/logo.png" alt="Fokos"></div>
    <div>
      <div class="left-headline">
        <span class="hl-line"><span class="hl-in" style="--d:.1s">Gestão inteligente</span></span>
        <span class="hl-line"><span class="hl-in" style="--d:.24s">para <span class="hl-y">eventos</span></span></span>
        <span class="hl-line"><span class="hl-in" style="--d:.38s">e logística.</span></span>
      </div>
      <p class="left-desc">Controle demandas, equipe, estoque e finanças em um único sistema.</p>
    </div>
    <div class="left-stats">
      <div class="stat"><div class="stat-num">100%</div><div class="stat-lbl">Online</div></div>
      <div class="stat"><div class="stat-num">—</div><div class="stat-lbl">Demandas ativas</div></div>
    </div>
  </div>

  <!-- Direito -->
  <div class="right">

    <!-- Hero mobile -->
    <div class="mobile-hero">
      <img src="<?= APP_URL ?>/public/assets/img/logo.png" class="mobile-logo" alt="Fokos">
    </div>

    <!-- Form -->
    <div class="form-box">
      <div class="form-eyebrow">Acesso ao sistema</div>
      <div class="form-title">Bem-vindo de volta</div>
      <p class="form-sub">Entre com suas credenciais para acessar o painel.</p>
      <form id="frm" onsubmit="doLogin(event)">
        <div class="field">
          <label>E-mail</label>
          <div class="field-inner">
            <i class="fa-solid fa-envelope fi"></i>
            <input type="email" id="email" placeholder="seu@email.com" required autocomplete="username">
          </div>
        </div>
        <div class="field">
          <label>Senha</label>
          <div class="field-inner">
            <i class="fa-solid fa-lock fi"></i>
            <input type="password" id="senha" placeholder="••••••••" required autocomplete="current-password">
            <button type="button" class="toggle-pw" onclick="togglePw()"><i class="fa-solid fa-eye" id="pw-icon"></i></button>
          </div>
        </div>
        <button type="submit" class="btn-login" id="btn">
          <i class="fa-solid fa-right-to-bracket"></i><span id="btn-txt">Entrar no Sistema</span>
        </button>
      </form>
      <div class="msg" id="msg"></div>
    </div>

    <div class="login-copy">© 2026 Fokos Eventos, Inc. Todos os direitos reservados.</div>

    <!-- Copyright mobile -->
    <div class="mobile-copy">© 2026 Fokos Eventos, Inc. Todos os direitos reservados.</div>

  </div>
</div>

<script>
function togglePw(){
  var inp=document.getElementById('senha'), ico=document.getElementById('pw-icon');
  inp.type=inp.type==='password'?'text':'password';
  ico.className=inp.type==='password'?'fa-solid fa-eye':'fa-solid fa-eye-slash';
}

async function doLogin(e){
  e.preventDefault();
  var btn=document.getElementById('btn'), txt=document.getElementById('btn-txt'), msg=document.getElementById('msg');
  btn.disabled=true; txt.textContent='Autenticando...'; msg.style.display='none';
  var fd=new FormData();
  fd.append('email',document.getElementById('email').value);
  fd.append('senha',document.getElementById('senha').value);
  fd.append('_csrf','<?= csrfToken() ?>');
  try{
    var res=await fetch('<?= APP_URL ?>/auth/login',{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}});
    var data=await res.json();
    if(data.sucesso){
      var ov=document.createElement('div');
      ov.className='fk-transition';
      ov.innerHTML='<img src="<?= APP_URL ?>/public/assets/img/logo-full.png" alt="Fokos Eventos">'
        +'<div class="fk-bar"></div><div class="fk-txt">Entrando</div>';
      document.body.appendChild(ov);
      setTimeout(function(){ window.location.href=data.redirect; },1000);
    } else {
      msg.className='msg error'; msg.textContent=data.erro||'Verifique suas credenciais.'; msg.style.display='block';
      btn.disabled=false; txt.textContent='Entrar no Sistema';
    }
  } catch(err){
    msg.className='msg error'; msg.textContent='Erro de conexão.'; msg.style.display='block';
    btn.disabled=false; txt.textContent='Entrar no Sistema';
  }
}
</script>
<canvas id="hero-bg"></canvas>
<style>
#hero-bg {
  position: fixed;
  inset: 0;
  width: 100%;
  height: 100%;
  z-index: 0;
  pointer-events: none;
  display: none;
}
@media (max-width: 768px) {
  #hero-bg { display: block; }
  .page { position: relative; z-index: 1; }
}
</style>
<script>
(function(){
  if(window.innerWidth > 768) return;
  var canvas = document.getElementById('hero-bg');
  var ctx    = canvas.getContext('2d');
  var W, H, orbs = [];

  function resize(){
    W = canvas.width  = window.innerWidth;
    H = canvas.height = window.innerHeight;
  }
  resize();
  window.addEventListener('resize', resize);

  // Criar orbs
  for(var i = 0; i < 6; i++){
    orbs.push({
      x: Math.random() * W,
      y: Math.random() * H,
      r: 60 + Math.random() * 100,
      vx: (Math.random() - .5) * .4,
      vy: (Math.random() - .5) * .4,
      alpha: .04 + Math.random() * .06,
      hue: Math.random() > .5 ? 48 : 45  // dourado
    });
  }

  function draw(){
    ctx.clearRect(0, 0, W, H);
    orbs.forEach(function(o){
      // Mover
      o.x += o.vx;
      o.y += o.vy;
      // Rebater
      if(o.x < -o.r)  o.x = W + o.r;
      if(o.x > W+o.r) o.x = -o.r;
      if(o.y < -o.r)  o.y = H + o.r;
      if(o.y > H+o.r) o.y = -o.r;
      // Desenhar
      var g = ctx.createRadialGradient(o.x, o.y, 0, o.x, o.y, o.r);
      g.addColorStop(0, 'hsla('+o.hue+',100%,55%,'+o.alpha+')');
      g.addColorStop(1, 'hsla('+o.hue+',100%,55%,0)');
      ctx.beginPath();
      ctx.arc(o.x, o.y, o.r, 0, Math.PI*2);
      ctx.fillStyle = g;
      ctx.fill();
    });
    requestAnimationFrame(draw);
  }
  draw();
})();
</script>
</body>
</html>
