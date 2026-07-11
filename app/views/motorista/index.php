<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="theme-color" content="#0D0E12">
<title>Fokos — Motorista</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sora:wght@500;600;700;800&family=Bebas+Neue&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent}
:root{
  --bg:#0D0E12;--card:rgba(22,25,35,.55);--card2:rgba(255,255,255,.055);--border:rgba(255,255,255,.09);
  --yellow:#FFD600;--text:#F2F3F7;--text2:#9CA1B2;--text3:#5C6170;
  --green:#3DDC84;--red:#FF5C51;--blue:#5BA8FF;--orange:#FFAA33;--r:14px;
  --panel:rgba(18,20,28,.72);--border2:rgba(255,255,255,.16);--shadow:0 2px 6px rgba(0,0,0,.3),0 14px 40px rgba(0,0,0,.28);--shadow-lg:0 6px 16px rgba(0,0,0,.4),0 40px 100px rgba(0,0,0,.5);
}
body{background:var(--bg);color:var(--text);font-family:'Inter',sans-serif;font-size:13px;min-height:100vh;-webkit-font-smoothing:antialiased;overscroll-behavior:none;}
body::before{content:'';position:fixed;inset:0;z-index:-2;pointer-events:none;background:var(--bg);}
.app-bg-img{position:fixed;inset:0;z-index:-1;pointer-events:none;background:url('<?= APP_URL ?>/public/assets/img/bg-ocean.jpg?v=7') center/cover no-repeat;opacity:.2;}
body::after{content:'';position:fixed;inset:0;z-index:-1;pointer-events:none;background:radial-gradient(120% 90% at 50% 0%,transparent 55%,rgba(0,0,0,.4) 100%),radial-gradient(circle at 14% 28%,rgba(170,210,255,.07) 0 4px,transparent 20px),radial-gradient(circle at 30% 74%,rgba(150,200,255,.05) 0 8px,transparent 38px),radial-gradient(circle at 56% 84%,rgba(160,205,255,.05) 0 6px,transparent 30px),radial-gradient(circle at 68% 22%,rgba(190,225,255,.05) 0 4px,transparent 20px),radial-gradient(circle at 84% 58%,rgba(150,200,255,.04) 0 9px,transparent 44px);animation:bokehBreathe 9s ease-in-out infinite alternate;}
@keyframes bokehBreathe{from{opacity:.7}to{opacity:1}}
@media (prefers-reduced-motion:reduce){body::after{animation:none}}
@media (prefers-reduced-motion:reduce){body::before{animation:none}}

.topbar{position:fixed;top:10px;left:12px;right:12px;z-index:999;height:58px;
  background:linear-gradient(180deg,rgba(255,255,255,.045),transparent 55%),var(--panel);
  backdrop-filter:saturate(170%) blur(22px);-webkit-backdrop-filter:saturate(170%) blur(22px);
  border:1px solid var(--border2);border-radius:18px;
  box-shadow:0 1px 0 rgba(255,255,255,.06) inset,0 6px 16px rgba(0,0,0,.35),0 24px 60px rgba(0,0,0,.35);
  padding:0 7px 0 15px;display:flex;align-items:center;justify-content:space-between;}
.topbar-logo{height:22px;width:auto;display:block;}
.topbar-right{display:flex;align-items:center;gap:6px;}
.t-disp{display:flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.2);font-size:11px;font-weight:600;color:var(--green);}
.t-dot{width:6px;height:6px;border-radius:50%;background:var(--green);}
.t-disp{display:flex;align-items:center;gap:6px;height:34px;padding:0 11px;border-radius:999px;background:rgba(61,220,132,.09);border:1px solid rgba(61,220,132,.25);font-size:10.5px;font-weight:700;color:#5FE79D;letter-spacing:.04em;}
.t-dot{width:7px;height:7px;border-radius:50%;background:var(--green);box-shadow:0 0 8px rgba(61,220,132,.8);animation:dispPulse 2.2s ease-in-out infinite;}
@keyframes dispPulse{0%,100%{box-shadow:0 0 0 0 rgba(61,220,132,.4)}50%{box-shadow:0 0 0 5px rgba(61,220,132,0)}}
.t-bell{width:38px;height:38px;border-radius:13px;background:var(--card2);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text2);font-size:14.5px;position:relative;transition:all .15s;-webkit-tap-highlight-color:transparent;}
.t-bell:active{transform:scale(.94);}
.t-bell-dot{position:absolute;top:7px;right:8px;width:8px;height:8px;border-radius:50%;background:var(--yellow);border:2px solid #14161C;display:none;}
.t-av{width:38px;height:38px;border-radius:13px;background:linear-gradient(135deg,#FFE14D,var(--yellow) 55%,#E8B400);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#000;font-family:'Sora',sans-serif;box-shadow:0 1px 0 rgba(255,255,255,.4) inset,0 4px 12px rgba(255,214,0,.25);}

.main{padding-top:84px;padding-bottom:96px;min-height:100vh;}

.hero{padding:20px 16px 16px;background:linear-gradient(180deg,rgba(255,214,0,.04),transparent);border-bottom:1px solid var(--border);}
.hero-time{font-size:11px;color:var(--text2);margin-bottom:4px;}
.hero-name{font-family:'Sora',sans-serif;font-size:18px;font-weight:700;letter-spacing:-.02em;line-height:1.3;margin-bottom:14px;}
.hero-name span{color:var(--yellow);}
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;}
.stat-box{background:var(--card);backdrop-filter:saturate(150%) blur(12px);-webkit-backdrop-filter:saturate(150%) blur(12px);border:1px solid var(--border);border-radius:14px;padding:11px 6px;text-align:center;box-shadow:var(--shadow);}
.stat-num{font-family:'Sora',sans-serif;font-size:15px;font-weight:700;line-height:1;letter-spacing:-.01em;font-variant-numeric:tabular-nums;}
.stat-lbl{font-size:9px;color:var(--text2);margin-top:4px;text-transform:uppercase;letter-spacing:.04em;}

.sec-bar{display:flex;align-items:center;justify-content:space-between;padding:14px 16px 8px;}
.sec-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text2);}
.sec-btn{width:30px;height:30px;border-radius:8px;background:var(--card);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text2);font-size:12px;}

.filtros{display:flex;gap:8px;padding:0 16px 12px;}
.fsel{flex:1;height:32px;background:var(--bg);border:1px solid var(--border2);border-radius:9px;color:var(--text);font-size:12.5px;padding:0 10px;outline:none;font-family:'Inter',sans-serif;-webkit-appearance:none;appearance:none;}
.fsel:focus{border-color:var(--yellow);}

.page{display:none;}
.page.active{display:block;}
.empty{text-align:center;padding:52px 20px;color:var(--text2);}
.empty i{font-size:36px;opacity:.15;margin-bottom:12px;display:block;}
.empty h3{font-size:16px;font-weight:600;margin-bottom:6px;color:var(--text);}
.empty p{font-size:13px;}

/* Cards de demanda */
.dcard{background:var(--card);backdrop-filter:saturate(150%) blur(12px);-webkit-backdrop-filter:saturate(150%) blur(12px);border:1px solid var(--border);border-radius:14px;margin:0 16px 9px;overflow:hidden;cursor:pointer;transition:transform .15s,border-color .15s;position:relative;box-shadow:var(--shadow);}
.dcard::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;}
.dcard.s-pendente::before{background:#888;}
.dcard.s-preparacao::before{background:#3b82f6;}
.dcard.s-em_rota::before{background:var(--blue);}
.dcard.s-em_retirada::before{background:var(--orange);}
.dcard.s-entregue::before{background:var(--green);}
.dcard.s-devolvido::before{background:var(--blue);}
.dcard.s-finalizado::before{background:#555;}
.dcard:active{transform:scale(.985);}
.dcard-top{padding:14px 14px 12px 18px;display:flex;gap:10px;align-items:flex-start;}
.dcard-info{flex:1;min-width:0;}
.dcard-title{font-size:13px;font-weight:600;margin-bottom:6px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.dcard-let{font-family:'Bebas Neue',sans-serif;font-size:17px;color:var(--yellow);letter-spacing:.04em;margin-bottom:6px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.dcard-meta{font-size:11px;color:var(--text2);display:flex;align-items:center;gap:4px;margin-bottom:3px;}
.dbadge{padding:3px 9px;border-radius:8px;font-size:9.5px;font-weight:600;flex-shrink:0;align-self:flex-start;border:1px solid transparent;}
.dcard-foot{padding:10px 14px 12px 18px;display:flex;align-items:center;justify-content:space-between;border-top:1px solid var(--border);background:rgba(255,255,255,.02);}
.dcard-addr{font-size:11px;color:var(--text2);display:flex;align-items:center;gap:6px;flex:1;min-width:0;overflow:hidden;}
.dcard-addr span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.qbtns{display:flex;gap:6px;flex-shrink:0;}
.qbtn{width:32px;height:32px;border-radius:9px;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:13px;text-decoration:none;transition:transform .1s;}
.qbtn:active{transform:scale(.9);}
.qbtn-w{background:rgba(37,211,102,.12);color:#25d366;}
.qbtn-m{background:rgba(100,210,255,.1);color:var(--blue);}

/* Detail */
.det-overlay{position:fixed;inset:0;z-index:1490;background:rgba(5,6,9,.6);display:none;}
.det-overlay.open{display:block;}
.detail{position:fixed;top:0;right:0;bottom:0;width:100vw;z-index:1500;background:rgba(11,13,18,.72);backdrop-filter:saturate(180%) blur(26px);-webkit-backdrop-filter:saturate(180%) blur(26px);transform:translateX(100%);transition:transform .28s cubic-bezier(.4,0,.2,1);overflow-y:auto;padding-bottom:80px;}
.detail.open{transform:translateX(0);}
.det-top{position:sticky;top:0;z-index:20;background:rgba(14,16,22,.6);backdrop-filter:saturate(170%) blur(24px);-webkit-backdrop-filter:saturate(170%) blur(24px);border-bottom:1px solid var(--border);padding:12px 16px;display:flex;align-items:center;gap:12px;height:56px;}
.back-btn{width:36px;height:36px;border-radius:10px;background:var(--card);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text);font-size:14px;flex-shrink:0;}
.det-title{font-size:14px;font-weight:600;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.det-badge{padding:4px 10px;border-radius:20px;font-size:10px;font-weight:700;flex-shrink:0;}

.dsec{padding:16px;border-bottom:1px solid var(--border);}
.dsec:last-child{border-bottom:none;}
.dsec-lbl{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text2);margin-bottom:12px;}
.igrid{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.ibox{background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:10px 13px;}
.ibox .l{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text3);margin-bottom:3px;}
.ibox .v{font-size:12.5px;font-weight:500;}
.copy-row{background:var(--card2);border-radius:10px;padding:12px;margin-top:8px;display:flex;align-items:center;gap:8px;font-size:12px;color:#aaa;cursor:pointer;}
.copy-row:active{opacity:.7;}
.let-box{background:var(--card2);border-radius:12px;padding:14px;margin-bottom:10px;}
.let-txt{font-family:'Bebas Neue',sans-serif;font-size:28px;color:var(--yellow);letter-spacing:.06em;line-height:1;}
.let-sub{font-size:10px;color:var(--text2);margin-bottom:6px;}

.agrid{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:16px;}
.abtn{padding:16px 12px;border-radius:14px;border:none;cursor:pointer;font-size:13px;font-weight:600;font-family:'Inter',sans-serif;display:flex;flex-direction:column;align-items:center;gap:7px;transition:transform .15s;text-decoration:none;color:inherit;}
.abtn:active{transform:scale(.96);}
.abtn i{font-size:24px;}
.abtn-w{background:rgba(37,211,102,.12);color:#25d366;}
.abtn-m{background:rgba(100,210,255,.1);color:var(--blue);}
.abtn-c{background:rgba(34,197,94,.1);color:var(--green);}
.abtn-f{background:rgba(255,214,0,.1);color:var(--yellow);}

.sopts{display:flex;flex-direction:column;gap:9px;}
.back-status-btn{width:100%;height:44px;border-radius:13px;background:transparent;border:1px solid var(--border2);color:var(--text2);font-size:13px;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .15s;}
.back-status-btn:active{transform:scale(.97);}
.sopt{background:var(--card);border:1.5px solid var(--border);border-radius:14px;padding:14px 16px;display:flex;align-items:center;gap:12px;cursor:pointer;transition:all .15s;font-size:14px;font-weight:500;}
.sopt:active{transform:scale(.98);}
.sopt-ico{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;}
.sopt-info{flex:1;}
.sopt-lbl{font-size:13px;font-weight:600;}
.sopt-sub{font-size:11px;color:var(--text2);margin-top:1px;}
.edit-status{margin-top:10px;border-top:1px solid var(--border);padding-top:12px;}
.edit-lbl{font-size:10px;color:var(--text3);font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;}
.edit-btns{display:flex;flex-wrap:wrap;gap:6px;}
.edit-btn{padding:7px 12px;border-radius:20px;font-size:11px;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif;border:1px solid;transition:all .15s;}

.obs-area{width:100%;background:var(--card2);border:1px solid var(--border);border-radius:12px;color:var(--text);font-family:'Inter',sans-serif;font-size:14px;padding:12px;resize:none;min-height:90px;outline:none;}
.obs-area:focus{border-color:var(--yellow);}
.save-btn{width:100%;margin-top:10px;padding:15px;border-radius:14px;border:none;background:linear-gradient(180deg,#FFE14D,var(--yellow));color:#000;font-weight:700;font-size:13.5px;box-shadow:0 1px 0 rgba(255,255,255,.35) inset,0 8px 24px rgba(255,214,0,.22);cursor:pointer;font-family:'Inter',sans-serif;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .15s;}
.save-btn:active{transform:scale(.98);}

/* Notificações */
.notif-panel{position:fixed;top:78px;left:0;right:0;bottom:86px;background:rgba(13,14,18,.9);backdrop-filter:saturate(160%) blur(18px);-webkit-backdrop-filter:saturate(160%) blur(18px);z-index:500;transform:translateY(-110%);transition:transform .28s cubic-bezier(.4,0,.2,1);overflow-y:auto;box-shadow:0 8px 32px rgba(0,0,0,.5);}
.notif-panel.open{transform:translateY(0);}
.notif-top{padding:14px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;background:var(--bg);}
.notif-top h4{font-family:'Sora',sans-serif;font-size:16px;font-weight:700;}
.notif-lidas{font-size:12px;color:var(--yellow);font-weight:600;background:none;border:none;cursor:pointer;font-family:'Inter',sans-serif;}
.notif-item{display:flex;align-items:flex-start;gap:12px;padding:14px 16px;border-bottom:1px solid var(--border);cursor:pointer;}
.notif-item.unread{background:rgba(255,214,0,.03);}
.notif-ico{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;}
.notif-body{flex:1;min-width:0;}
.notif-ttl{font-size:13px;font-weight:600;margin-bottom:3px;}
.notif-msg{font-size:12px;color:var(--text2);line-height:1.4;}
.notif-time{font-size:10px;color:var(--text3);margin-top:4px;}
.notif-dot-u{width:7px;height:7px;border-radius:50%;background:var(--yellow);flex-shrink:0;margin-top:5px;}

/* Perfil */
.perf-hero{background:linear-gradient(180deg,rgba(255,214,0,.06),transparent);padding:28px 16px 20px;text-align:center;border-bottom:1px solid var(--border);}
.perf-av{width:68px;height:68px;border-radius:22px;background:linear-gradient(135deg,var(--yellow),#E8B400);display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-size:26px;font-weight:800;color:#000;margin:0 auto 12px;box-shadow:0 0 0 4px rgba(255,214,0,.15);}
.perf-nome{font-family:'Sora',sans-serif;font-size:17px;font-weight:700;letter-spacing:-.02em;margin-bottom:4px;}
.perf-email{font-size:13px;color:var(--text2);}
.perf-stats{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:16px;}
.pstat{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:13px;text-align:center;box-shadow:var(--shadow);}
.pstat-num{font-family:'Sora',sans-serif;font-size:17px;font-weight:700;color:var(--yellow);font-variant-numeric:tabular-nums;}
.pstat-lbl{font-size:10px;color:var(--text2);margin-top:3px;text-transform:uppercase;letter-spacing:.04em;}
.logout-btn{display:flex;align-items:center;justify-content:center;gap:8px;width:calc(100% - 32px);margin:16px;padding:15px;border-radius:14px;border:1px solid rgba(255,59,48,.2);background:rgba(255,59,48,.08);color:var(--red);font-size:14px;font-weight:600;text-decoration:none;transition:all .15s;}

/* Toast */
.toasts{position:fixed;bottom:calc(88px + env(safe-area-inset-bottom,0));left:14px;right:14px;z-index:9999;display:flex;flex-direction:column;gap:8px;pointer-events:none;}
.toast{background:rgba(16,18,25,.92);backdrop-filter:saturate(170%) blur(20px);-webkit-backdrop-filter:saturate(170%) blur(20px);border:1px solid var(--border2);border-left:3px solid var(--yellow);border-radius:14px;padding:14px 16px;display:flex;align-items:center;gap:11px;box-shadow:0 12px 40px rgba(0,0,0,.55);animation:tin .28s cubic-bezier(.34,1.2,.5,1);font-size:13.5px;font-weight:600;}
.toast.ok{border-left-color:var(--green);}
.toast.err{border-left-color:var(--red);}
@keyframes tin{from{transform:translateY(-10px);opacity:0}to{transform:translateY(0);opacity:1}}
.toast-ico{font-size:18px;flex-shrink:0;}
.toast.ok .toast-ico{color:var(--green);}
.toast.err .toast-ico{color:var(--red);}
.toast.warn .toast-ico{color:var(--yellow);}

/* Bottom nav */
.bnav{position:fixed;bottom:12px;left:12px;right:12px;z-index:998;background:var(--panel);backdrop-filter:saturate(170%) blur(22px);-webkit-backdrop-filter:saturate(170%) blur(22px);border:1px solid var(--border2);border-radius:20px;box-shadow:var(--shadow-lg);display:flex;height:62px;padding:0 6px;margin-bottom:env(safe-area-inset-bottom,0);}
.bnav-btn{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;cursor:pointer;border:none;background:none;color:var(--text3);font-size:9.5px;font-weight:600;font-family:'Inter',sans-serif;transition:color .15s;position:relative;}
.bnav-btn.active::before{content:'';position:absolute;bottom:7px;left:50%;transform:translateX(-50%);width:16px;height:3px;border-radius:3px;background:var(--yellow);box-shadow:0 0 10px rgba(255,214,0,.6);}
.bnav-btn.active i{transform:translateY(-2px);}
.bnav-btn i{transition:transform .15s;}
.bnav-btn i{font-size:18px;}
.bnav-btn.active{color:var(--yellow);}

/* Histórico (usa mesmos estilos dos dcards) */

/* ── Super up v6.1 ── */
.hero-greet{font-size:11px;color:var(--text3);font-weight:600;text-transform:uppercase;letter-spacing:.12em;margin-bottom:4px;}
.hoje-tag{display:inline-flex;align-items:center;gap:5px;background:rgba(255,214,0,.12);color:var(--yellow);border:1px solid rgba(255,214,0,.3);font-size:9px;font-weight:800;letter-spacing:.06em;padding:2px 8px;border-radius:6px;animation:hojePulse 1.8s ease-in-out infinite;}
@keyframes hojePulse{0%,100%{box-shadow:0 0 0 0 rgba(255,214,0,.25)}50%{box-shadow:0 0 0 5px rgba(255,214,0,0)}}
/* stepper do fluxo no detalhe */
.steps{display:flex;align-items:center;gap:0;padding:14px 4px 6px;}
.step{flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;position:relative;min-width:0;}
.step::before{content:'';position:absolute;top:11px;left:-50%;right:50%;height:2px;background:var(--border);z-index:0;}
.step:first-child::before{display:none;}
.step.done::before{background:var(--yellow);}
.step-dot{width:22px;height:22px;border-radius:50%;background:var(--card2);border:2px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:9px;color:var(--text3);position:relative;z-index:1;}
.step.done .step-dot{background:var(--yellow);border-color:var(--yellow);color:#000;}
.step.cur .step-dot{border-color:var(--yellow);color:var(--yellow);box-shadow:0 0 0 4px rgba(255,214,0,.15);}
.step-lbl{font-size:8px;color:var(--text3);text-align:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%;}
.step.done .step-lbl,.step.cur .step-lbl{color:var(--text2);}
/* Sheet de navegação (Waze/Maps) */
.nav-sheet{position:fixed;left:0;right:0;bottom:0;z-index:2000;background:rgba(13,14,18,.86);backdrop-filter:saturate(170%) blur(22px);-webkit-backdrop-filter:saturate(170%) blur(22px);border-top:1px solid var(--border);border-radius:22px 22px 0 0;transform:translateY(110%);transition:transform .3s cubic-bezier(.34,1.1,.5,1);box-shadow:0 -20px 60px rgba(0,0,0,.5);max-height:86vh;display:flex;flex-direction:column;}
.nav-sheet.open{transform:none;}
.nav-sheet-grab{width:40px;height:4px;border-radius:4px;background:var(--border);margin:10px auto 6px;flex-shrink:0;}
.nav-sheet-head{padding:4px 18px 12px;flex-shrink:0;}
.nav-sheet-title{font-family:'Sora',sans-serif;font-size:15px;font-weight:700;display:flex;align-items:center;gap:8px;}
.nav-sheet-addr{font-size:12px;color:var(--text2);margin-top:3px;line-height:1.5;}
.nav-map{flex-shrink:0;height:min(38vh,300px);margin:0 14px;border-radius:14px;overflow:hidden;border:1px solid var(--border);background:var(--card);}
.nav-map iframe{width:100%;height:100%;border:0;filter:saturate(.9) contrast(1.05);}
.nav-acts{padding:14px;display:grid;grid-template-columns:1fr 1fr;gap:9px;flex-shrink:0;padding-bottom:calc(14px + env(safe-area-inset-bottom,0));}
.nav-act{display:flex;align-items:center;justify-content:center;gap:9px;height:48px;border-radius:13px;font-size:13.5px;font-weight:700;text-decoration:none;border:1px solid var(--border);transition:transform .12s;font-family:'Inter',sans-serif;cursor:pointer;background:var(--card);color:var(--text);}
.nav-act:active{transform:scale(.97);}
.nav-act.waze{background:#33ccff;color:#04222e;border-color:#33ccff;grid-column:1/-1;font-size:14.5px;}
.nav-act.gmaps{color:var(--green);border-color:rgba(61,220,132,.35);}
.nav-act i{font-size:17px;}
.nav-overlay{position:fixed;inset:0;z-index:1990;background:rgba(5,6,9,.6);opacity:0;pointer-events:none;transition:opacity .25s;}
.nav-overlay.open{opacity:1;pointer-events:auto;}
.dcard-addr span{text-decoration:underline dotted rgba(255,255,255,.2);text-underline-offset:3px;}

.fk-transition{position:fixed;inset:0;z-index:99999;background:#0D0E12;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:26px;animation:fkFade .25s ease;}
@keyframes fkFade{from{opacity:0}to{opacity:1}}
.fk-transition img{width:min(46vw,210px);height:auto;animation:fkPulse 1.6s ease-in-out infinite;}
@keyframes fkPulse{0%,100%{opacity:.85;transform:scale(1)}50%{opacity:1;transform:scale(1.04)}}
.fk-transition .fk-bar{width:min(46vw,210px);height:3px;border-radius:3px;background:rgba(255,255,255,.08);overflow:hidden;position:relative;}
.fk-transition .fk-bar::after{content:'';position:absolute;top:0;bottom:0;left:-40%;width:40%;border-radius:3px;background:linear-gradient(90deg,transparent,var(--yellow),transparent);animation:fkBar 1s cubic-bezier(.4,0,.2,1) infinite;}
@keyframes fkBar{to{left:100%}}
.fk-transition .fk-txt{font-family:'Sora',sans-serif;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.22em;color:#5C6170;}
</style>
</head>
<body>
<div class="app-bg-img"></div>

<div class="toasts" id="toasts"></div>

<!-- TOPBAR -->
<header class="topbar">
  <img src="<?= APP_URL ?>/public/assets/img/logo-full.png" class="topbar-logo" alt="Fokos Eventos">
  <div class="topbar-right">
    <div class="t-disp"><div class="t-dot"></div><span>Disponível</span></div>
    <div class="t-bell" id="btn-notif">
      <i class="fa-solid fa-bell"></i>
      <div class="t-bell-dot" id="notif-dot"></div>
    </div>
    <div class="t-av" id="t-av">?</div>
  </div>
</header>

<main class="main">

  <!-- DEMANDAS -->
  <div class="page active" id="pg-dem">
    <div class="hero">
      <div class="hero-time" id="g-time">Bom dia</div>
      <div class="hero-greet" id="g-greet">Bem-vindo</div>
      <div class="hero-name"><span id="g-saud">Olá</span>, <span id="g-nome">Motorista</span> 👋</div>
      <div class="stats-grid">
        <div class="stat-box"><div class="stat-num" id="s-total" style="color:var(--yellow)">—</div><div class="stat-lbl">Total</div></div>
        <div class="stat-box"><div class="stat-num" id="s-rota" style="color:var(--blue)">—</div><div class="stat-lbl">Em rota</div></div>
        <div class="stat-box"><div class="stat-num" id="s-ok" style="color:var(--green)">—</div><div class="stat-lbl">Concluídas</div></div>
        <div class="stat-box"><div class="stat-num" id="s-pend" style="color:var(--orange)">—</div><div class="stat-lbl">Pendentes</div></div>
      </div>
    </div>
    <div class="sec-bar">
      <span class="sec-title">Minhas Demandas</span>
      <div class="sec-btn" id="btn-refresh"><i class="fa-solid fa-rotate-right"></i></div>
    </div>
    <div class="filtros">
      <select class="fsel" id="f-ano"><option value="">Todos os anos</option></select>
      <select class="fsel" id="f-mes">
        <option value="">Todos os meses</option>
        <option value="01">Janeiro</option><option value="02">Fevereiro</option><option value="03">Março</option>
        <option value="04">Abril</option><option value="05">Maio</option><option value="06">Junho</option>
        <option value="07">Julho</option><option value="08">Agosto</option><option value="09">Setembro</option>
        <option value="10">Outubro</option><option value="11">Novembro</option><option value="12">Dezembro</option>
      </select>
    </div>
    <div id="lista-dem"><div class="empty"><i class="fa-solid fa-spinner fa-spin"></i><h3>Carregando...</h3></div></div>
  </div>

  <!-- HISTÓRICO -->
  <div class="page" id="pg-hist">
    <div class="sec-bar">
      <span class="sec-title">Histórico</span>
      <div class="sec-btn" id="btn-hist-refresh"><i class="fa-solid fa-rotate-right"></i></div>
    </div>
    <div class="filtros">
      <select class="fsel" id="hf-ano"><option value="">Todos os anos</option></select>
      <select class="fsel" id="hf-mes">
        <option value="">Todos os meses</option>
        <option value="01">Janeiro</option><option value="02">Fevereiro</option><option value="03">Março</option>
        <option value="04">Abril</option><option value="05">Maio</option><option value="06">Junho</option>
        <option value="07">Julho</option><option value="08">Agosto</option><option value="09">Setembro</option>
        <option value="10">Outubro</option><option value="11">Novembro</option><option value="12">Dezembro</option>
      </select>
    </div>
    <div id="lista-hist"><div class="empty"><i class="fa-solid fa-clock-rotate-left"></i><h3>Toque em atualizar</h3><p>Carregue seu histórico de entregas.</p></div></div>
  </div>

  <!-- PERFIL -->
  <div class="page" id="pg-perf">
    <div class="perf-hero">
      <div class="perf-av" id="perf-av">?</div>
      <div class="perf-nome" id="perf-nome">—</div>
      <div class="perf-email" id="perf-email">—</div>
    </div>
    <div class="perf-stats">
      <div class="pstat"><div class="pstat-num" id="perf-ativas">0</div><div class="pstat-lbl">Ativas</div></div>
      <div class="pstat"><div class="pstat-num" id="perf-concl">0</div><div class="pstat-lbl">Concluídas</div></div>
    </div>
    <a href="<?= APP_URL ?>/logout" class="logout-btn" id="btn-logout-mot"><i class="fa-solid fa-right-from-bracket"></i> Sair do sistema</a>
  </div>

</main>

<!-- BOTTOM NAV -->
<nav class="bnav">
  <button class="bnav-btn active" id="nav-dem"><i class="fa-solid fa-clipboard-list"></i>Demandas</button>
  <button class="bnav-btn" id="nav-hist"><i class="fa-solid fa-clock-rotate-left"></i>Histórico</button>
  <button class="bnav-btn" id="nav-perf"><i class="fa-solid fa-user"></i>Perfil</button>
</nav>

<!-- NOTIFICAÇÕES -->
<div class="notif-panel" id="notif-panel">
  <div class="notif-top">
    <h4>Notificações</h4>
    <button class="notif-lidas" id="btn-lidas">Marcar lidas</button>
  </div>
  <div id="notif-lista"><div class="empty"><i class="fa-solid fa-bell"></i><h3>Sem notificações</h3></div></div>
</div>

<!-- DETALHE -->
<div class="det-overlay" id="det-overlay"></div>
<div class="nav-overlay" id="nav-overlay"></div>
<div class="nav-sheet" id="nav-sheet">
  <div class="nav-sheet-grab"></div>
  <div class="nav-sheet-head">
    <div class="nav-sheet-title"><i class="fa-solid fa-route" style="color:var(--yellow)"></i> Navegar até o destino</div>
    <div class="nav-sheet-addr" id="nav-addr">—</div>
  </div>
  <div class="nav-map" id="nav-map"></div>
  <div class="nav-acts">
    <a class="nav-act waze" id="nav-waze" target="_blank" rel="noopener"><i class="fa-brands fa-waze"></i> Abrir no Waze</a>
    <a class="nav-act gmaps" id="nav-gmaps" target="_blank" rel="noopener"><i class="fa-solid fa-diamond-turn-right"></i> Google Maps</a>
    <button class="nav-act" id="nav-copy"><i class="fa-regular fa-copy"></i> Copiar endereço</button>
  </div>
</div>

<div class="detail" id="detail">
  <div class="det-top">
    <div class="back-btn" id="btn-back"><i class="fa-solid fa-chevron-left"></i></div>
    <div class="det-title" id="det-titulo">—</div>
    <div class="det-badge" id="det-badge"></div>
  </div>
  <div id="det-body"></div>
</div>

<script>
var APP    = '<?= APP_URL ?>';
var CSRF   = '<?= csrfToken() ?>';
var USER_ID = <?= (int)($_SESSION['user_id'] ?? 0) ?>;
var NOME   = '<?= htmlspecialchars(Database::fetchOne("SELECT nome FROM usuarios WHERE id=?", [$_SESSION["user_id"] ?? 0])["nome"] ?? "Motorista") ?>';
var EMAIL  = '<?= htmlspecialchars(Database::fetchOne("SELECT email FROM usuarios WHERE id=?", [$_SESSION["user_id"] ?? 0])["email"] ?? "") ?>';

var HOJE    = new Date().toISOString().substring(0,10);
var DEMS    = [];
var HIST    = [];
var DET_ID  = null;

var SL = {pendente:'Pendente',preparacao:'Em Preparação',em_rota:'Em Rota',em_retirada:'Em Retirada',entregue:'Entregue',devolvido:'Devolvido',finalizado:'Finalizado'};
var SC = {pendente:'#888',preparacao:'#3b82f6',em_rota:'#64d2ff',em_retirada:'#f97316',entregue:'#22c55e',devolvido:'#64d2ff',finalizado:'#555'};
var SI = {pendente:'fa-clock',preparacao:'fa-screwdriver-wrench',em_rota:'fa-truck-fast',em_retirada:'fa-rotate-left',entregue:'fa-box-open',devolvido:'fa-warehouse',finalizado:'fa-circle-check'};
var PROX = {
  pendente:    [{s:'preparacao',l:'Preparar letreiro',ic:'fa-screwdriver-wrench',c:'#3b82f6'}],
  preparacao:  [{s:'em_rota',l:'Saí para entrega',ic:'fa-truck-fast',c:'#64d2ff'}],
  em_rota:     [{s:'entregue',l:'Entregue no cliente',ic:'fa-box-open',c:'#22c55e'}],
  em_retirada: [{s:'devolvido',l:'Devolvido ao depósito',ic:'fa-warehouse',c:'#64d2ff'}],
  entregue:    [{s:'finalizado',l:'Finalizar',ic:'fa-circle-check',c:'#555'}],
  devolvido:   [{s:'finalizado',l:'Finalizar',ic:'fa-circle-check',c:'#555'}],
};
var TODOS_STATUS = [
  {s:'pendente',l:'Pendente',c:'#888'},{s:'preparacao',l:'Em Preparação',c:'#3b82f6'},
  {s:'em_rota',l:'Em Rota',c:'#64d2ff'},{s:'em_retirada',l:'Em Retirada',c:'#f97316'},
  {s:'entregue',l:'Entregue',c:'#22c55e'},{s:'devolvido',l:'Devolvido',c:'#64d2ff'},
  {s:'finalizado',l:'Finalizado',c:'#555'}
];

// ── Logoff com loading ──
document.addEventListener('click', function(e){
  var a = e.target.closest && e.target.closest('#btn-logout-mot');
  if(!a) return;
  e.preventDefault();
  var ov=document.createElement('div');
  ov.className='fk-transition';
  ov.innerHTML='<img src="<?= APP_URL ?>/public/assets/img/logo-full.png" alt="Fokos Eventos">'
    +'<div class="fk-bar"></div><div class="fk-txt">Saindo</div>';
  document.body.appendChild(ov);
  setTimeout(function(){ window.location.href=a.href; }, 800);
});

// ── Saudação ──
(function(){
  var h = new Date().getHours();
  var saud = h < 12 ? 'Bom dia' : (h < 18 ? 'Boa tarde' : 'Boa noite');
  var dias = ['Domingo','Segunda-feira','Terça-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sábado'];
  var meses = ['janeiro','fevereiro','março','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro'];
  var d = new Date();
  document.addEventListener('DOMContentLoaded', function(){
    var g = document.getElementById('g-saud'); if (g) g.textContent = saud;
    var gr = document.getElementById('g-greet'); if (gr) gr.textContent = dias[d.getDay()] + ', ' + d.getDate() + ' de ' + meses[d.getMonth()];
  });
})();

// ── Pilha de painéis + histórico (voltar do celular sempre fecha o painel de cima) ──
var PANEIS = [];              // ex.: ['det','nav']
var POP_GUARD = false;        // evita reentrância

function _uiClose(nome){
  if(nome==='nav'){
    document.getElementById('nav-sheet').classList.remove('open');
    document.getElementById('nav-overlay').classList.remove('open');
  } else if(nome==='det'){
    document.getElementById('detail').classList.remove('open');
    document.getElementById('det-overlay').classList.remove('open');
    document.body.style.overflow='';
    DET_ID=null;
  } else if(nome==='notif'){
    var np=document.getElementById('notif-panel');
    if(np) np.classList.remove('open');
  }
}
function abrirPainel(nome){
  PANEIS.push(nome);
  try { history.pushState({fkPaineis:PANEIS.length}, ''); } catch(e){}
}
function fecharPainel(){           // fechar via botão da UI
  if(!PANEIS.length) return;
  try { history.back(); }          // popstate fará o fechamento visual
  catch(e){ _uiClose(PANEIS.pop()); }
  // fallback de segurança: se o popstate não chegar em 350ms, fecha na marra
  var esperado = PANEIS.length - 1;
  setTimeout(function(){
    if(PANEIS.length > esperado){ _uiClose(PANEIS.pop()); }
  }, 350);
}
window.addEventListener('popstate', function(){
  if(POP_GUARD) return;
  POP_GUARD = true;
  if(PANEIS.length){ _uiClose(PANEIS.pop()); }
  POP_GUARD = false;
});

// ── Navegação (Waze / Google Maps) ──
function abrirNav(endereco){
  if(!endereco) return;
  var q = encodeURIComponent(endereco);
  document.getElementById('nav-addr').textContent = endereco;
  document.getElementById('nav-waze').href  = 'https://waze.com/ul?q=' + q + '&navigate=yes';
  document.getElementById('nav-gmaps').href = 'https://www.google.com/maps/dir/?api=1&destination=' + q;
  document.getElementById('nav-map').innerHTML =
    '<iframe loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://maps.google.com/maps?q=' + q + '&z=15&output=embed"></iframe>';
  document.getElementById('nav-copy').onclick = function(){
    navigator.clipboard.writeText(endereco).then(function(){ toast('Endereço copiado!','ok'); });
  };
  document.getElementById('nav-sheet').classList.add('open');
  document.getElementById('nav-overlay').classList.add('open');
  abrirPainel('nav');
}

// ── Utilitários ──
function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function dBR(s){ if(!s) return '—'; var p=s.split('-'); return p[2]+'/'+p[1]+'/'+p[0]; }
function tempoRel(s){
  if(!s) return '';
  var d=new Date(s.replace(' ','T')), diff=Math.floor((Date.now()-d)/1000);
  if(diff<60) return 'agora'; if(diff<3600) return Math.floor(diff/60)+'min atrás';
  if(diff<86400) return Math.floor(diff/3600)+'h atrás'; return Math.floor(diff/86400)+'d atrás';
}
function toast(msg, tipo){
  var icons={ok:'fa-circle-check',err:'fa-circle-xmark',warn:'fa-triangle-exclamation'};
  var el=document.createElement('div');
  el.className='toast '+(tipo||'ok');
  el.innerHTML='<i class="fa-solid '+icons[tipo||'ok']+' toast-ico"></i><span>'+esc(msg)+'</span>';
  document.getElementById('toasts').appendChild(el);
  setTimeout(function(){ el.remove(); },3500);
}
function copiar(txt, msg){
  if(navigator.clipboard){ navigator.clipboard.writeText(txt).then(function(){ toast(msg||'Copiado!','ok'); }); }
  else { var el=document.createElement('textarea'); el.value=txt; document.body.appendChild(el); el.select(); document.execCommand('copy'); document.body.removeChild(el); toast(msg||'Copiado!','ok'); }
}

// ── API ──
async function api(method, path, fd){
  var opts={method:method, headers:{'X-Requested-With':'XMLHttpRequest'}};
  if(fd) opts.body=fd;
  var r=await fetch(APP+path, opts);
  var data=await r.json();
  if(data.erro) throw new Error(data.erro);
  return data;
}

// ── Navegação ──
function setPage(nome){
  document.querySelectorAll('.page').forEach(function(p){ p.classList.remove('active'); });
  document.querySelectorAll('.bnav-btn').forEach(function(b){ b.classList.remove('active'); });
  document.getElementById('pg-'+nome).classList.add('active');
  document.getElementById('nav-'+nome).classList.add('active');
}

// ── Logoff com loading ──
document.addEventListener('click', function(e){
  var a = e.target.closest && e.target.closest('#btn-logout-mot');
  if(!a) return;
  e.preventDefault();
  var ov=document.createElement('div');
  ov.className='fk-transition';
  ov.innerHTML='<img src="<?= APP_URL ?>/public/assets/img/logo-full.png" alt="Fokos Eventos">'
    +'<div class="fk-bar"></div><div class="fk-txt">Saindo</div>';
  document.body.appendChild(ov);
  setTimeout(function(){ window.location.href=a.href; }, 800);
});

// ── Saudação ──
function saudar(){
  var h=new Date().getHours();
  document.getElementById('g-time').textContent = h<12?'Bom dia ☀️':h<18?'Boa tarde 🌤':'Boa noite 🌙';
  document.getElementById('g-nome').textContent = NOME.split(' ')[0];
  var ini=NOME.split(' ').slice(0,2).map(function(n){return n[0];}).join('').toUpperCase();
  document.getElementById('t-av').textContent   = ini;
  document.getElementById('perf-av').textContent = ini;
  document.getElementById('perf-nome').textContent  = NOME;
  document.getElementById('perf-email').textContent = EMAIL;
}

// ── Stats ──
function atualizarStats(){
  document.getElementById('s-total').textContent = DEMS.length + HIST.length;
  document.getElementById('s-rota').textContent  = DEMS.filter(function(d){ return d.status==='em_rota'; }).length;
  document.getElementById('s-ok').textContent    = HIST.length;
  document.getElementById('s-pend').textContent  = DEMS.filter(function(d){ return d.status==='pendente'||d.status==='preparacao'; }).length;
  document.getElementById('perf-ativas').textContent = DEMS.length;
  document.getElementById('perf-concl').textContent  = HIST.length;
}

// ── Demandas ──
async function carregarDems(silent){
  if(!silent){ document.getElementById('btn-refresh').innerHTML='<i class="fa-solid fa-spinner fa-spin"></i>'; }
  try{
    var data=await api('GET','/api/demandas');
    DEMS=data.demandas||[];
    popularAnos('f-ano', DEMS);
    renderDems();
    atualizarStats();
  }catch(e){ toast('Erro ao carregar','err'); }
  if(!silent){ document.getElementById('btn-refresh').innerHTML='<i class="fa-solid fa-rotate-right"></i>'; }
}

function popularAnos(id, lista){
  var anos={}, sel=document.getElementById(id), cur=sel.value;
  lista.forEach(function(d){ if(d.data_evento) anos[d.data_evento.substring(0,4)]=1; });
  sel.innerHTML='<option value="">Todos os anos</option>';
  Object.keys(anos).sort().reverse().forEach(function(a){ sel.innerHTML+='<option value="'+a+'"'+(a===cur?' selected':'')+'>'+a+'</option>'; });
}

function filtrar(lista, anoId, mesId){
  var ano=document.getElementById(anoId).value, mes=document.getElementById(mesId).value;
  return lista.filter(function(d){
    if(ano && !(d.data_evento||'').startsWith(ano)) return false;
    if(mes && (d.data_evento||'').substring(5,7)!==mes) return false;
    return true;
  });
}

function renderDems(){ renderLista('lista-dem', filtrar(DEMS,'f-ano','f-mes'), false); }

function renderLista(elId, lista, isHist){
  var el=document.getElementById(elId);
  if(!lista.length){
    el.innerHTML='<div class="empty"><i class="fa-solid fa-inbox"></i><h3>Nenhuma demanda</h3><p>Nenhum resultado para o filtro selecionado.</p></div>';
    return;
  }
  el.innerHTML=lista.map(function(d){
    var cor=SC[d.status]||'#888', lbl=SL[d.status]||d.status, ico=SI[d.status]||'fa-circle';
    var tel=d.telefone?(d.telefone+'').replace(/[^0-9]/g,''):'';
    return '<div class="dcard s-'+d.status+'" data-id="'+d.id+'" data-hist="'+(isHist?1:0)+'">'
      +'<div class="dcard-top">'
      +'<div class="dcard-info">'
      +'<div class="dcard-title">'+esc(d.titulo)+(d.data_evento===HOJE?' <span class="hoje-tag"><i class="fa-solid fa-bolt" style="font-size:8px"></i>HOJE</span>':'')+'</div>'
      +(d.letreiros_texto?'<div class="dcard-let">'+esc(d.letreiros_texto)+'</div>':'')
      +(d.cliente_nome?'<div class="dcard-meta"><i class="fa-solid fa-user" style="font-size:10px"></i>'+esc(d.cliente_nome)+'</div>':'')
      +(d.data_evento?'<div class="dcard-meta"><i class="fa-solid fa-calendar" style="font-size:10px"></i>'+dBR(d.data_evento)+(d.horario?' · '+d.horario.substring(0,5):'')+'</div>':'')
      +'</div>'
      +'<span class="dbadge" style="background:'+cor+'20;color:'+cor+';border:1px solid '+cor+'44"><i class="fa-solid '+ico+'" style="margin-right:4px"></i>'+lbl+'</span>'
      +'</div>'
      +'<div class="dcard-foot">'
      +(d.endereco
        ? '<div class="dcard-addr" data-nav="'+esc(d.endereco)+'"><i class="fa-solid fa-location-dot" style="font-size:11px;color:var(--blue);flex-shrink:0"></i><span>'+esc(d.endereco)+'</span></div>'
        : '<div class="dcard-addr"><i class="fa-solid fa-location-dot" style="font-size:11px;color:var(--text3);flex-shrink:0"></i><span>Sem endereço</span></div>')
      +'<div class="qbtns">'
      +(tel?'<a href="https://wa.me/55'+tel+'" class="qbtn qbtn-w" onclick="event.stopPropagation()"><i class="fa-brands fa-whatsapp"></i></a>':'')
      +(d.endereco?'<button class="qbtn qbtn-m" data-nav="'+esc(d.endereco)+'"><i class="fa-solid fa-route"></i></button>':'')
      +'</div></div></div>';
  }).join('');
}

// ── Histórico ──
async function carregarHist(){
  document.getElementById('btn-hist-refresh').innerHTML='<i class="fa-solid fa-spinner fa-spin"></i>';
  try{
    var data=await api('GET','/api/demandas?historico=1');
    HIST=data.demandas||[];
    popularAnos('hf-ano', HIST);
    renderHist();
    atualizarStats();
  }catch(e){ toast('Erro ao carregar histórico','err'); }
  document.getElementById('btn-hist-refresh').innerHTML='<i class="fa-solid fa-rotate-right"></i>';
}

function renderHist(){ renderLista('lista-hist', filtrar(HIST,'hf-ano','hf-mes'), true); }

// ── Detalhe ──
function abrirDetalhe(id, isHist){
  var d=(isHist?HIST:DEMS).find(function(x){ return x.id==id; });
  if(!d) return;
  DET_ID=id;
  preencherDetalhe(d);
  document.getElementById('detail').classList.add('open');
  document.getElementById('det-overlay').classList.add('open');
  document.body.style.overflow='hidden';
  abrirPainel('det');
}

function preencherDetalhe(d){
  var cor=SC[d.status]||'#888', lbl=SL[d.status]||d.status;
  document.getElementById('det-titulo').textContent=d.titulo;
  document.getElementById('det-badge').textContent=lbl;
  document.getElementById('det-badge').style.cssText='background:'+cor+'20;color:'+cor+';border:1px solid '+cor+'44;padding:4px 10px;border-radius:20px;font-size:10px;font-weight:700;';

  var tel=d.telefone?(d.telefone+'').replace(/[^0-9]/g,''):'';

  // ── Fluxo conforme o papel do motorista nesta demanda ──
  var ehEntrega  = (+d.motorista_id === USER_ID);
  var ehRetirada = (+d.motorista_retirada_id === USER_ID);
  var FLUXO;
  if (ehEntrega && ehRetirada)      FLUXO=['pendente','preparacao','em_rota','entregue','em_retirada','devolvido','finalizado'];
  else if (ehEntrega)               FLUXO=['pendente','preparacao','em_rota','entregue','finalizado'];
  else if (ehRetirada)              FLUXO=['entregue','em_retirada','devolvido','finalizado'];
  else                              FLUXO=['pendente','preparacao','em_rota','entregue','em_retirada','devolvido','finalizado'];
  var FLBL={pendente:'Pendente',preparacao:'Preparo',em_rota:'Em rota',entregue:'Entregue',em_retirada:'Retirada',devolvido:'Devolvido',finalizado:'Fim'};
  var FICO={pendente:'fa-clock',preparacao:'fa-box-open',em_rota:'fa-truck-fast',entregue:'fa-circle-check',em_retirada:'fa-rotate-left',devolvido:'fa-warehouse',finalizado:'fa-flag-checkered'};
  var idx=FLUXO.indexOf(d.status);

  var stepsHtml = idx<0 ? '' : '<div class="steps">'+FLUXO.map(function(st,i){
    var cls = i<idx?'done':(i===idx?'cur done':'');
    return '<div class="step '+cls+'"><div class="step-dot">'+(i<idx?'<i class="fa-solid fa-check"></i>':(i+1))+'</div><div class="step-lbl">'+FLBL[st]+'</div></div>';
  }).join('')+'</div>';

  var prev = idx>0 ? FLUXO[idx-1] : null;
  var next = (idx>=0 && idx<FLUXO.length-1) ? FLUXO[idx+1] : null;
  var statusBtns = '';
  if (next) statusBtns += '<button class="save-btn" data-status="'+next+'" style="margin-top:0"><i class="fa-solid '+FICO[next]+'"></i> Avançar para: '+(SL[next]||FLBL[next])+'</button>';
  else statusBtns += '<p style="color:var(--green);font-size:13px;text-align:center;padding:8px 0;font-weight:600"><i class="fa-solid fa-flag-checkered"></i> Demanda concluída</p>';
  if (prev) statusBtns += '<button class="back-status-btn" data-status="'+prev+'"><i class="fa-solid fa-rotate-left"></i> Voltar para: '+(SL[prev]||FLBL[prev])+'</button>';

  document.getElementById('det-body').innerHTML=
    stepsHtml
    +'<div class="dsec"><div class="dsec-lbl">Informações</div>'
    +'<div class="igrid">'
    +'<div class="ibox"><div class="l">Cliente</div><div class="v">'+esc(d.cliente_nome||'—')+'</div></div>'
    +'<div class="ibox"><div class="l">Data</div><div class="v">'+(d.data_evento?dBR(d.data_evento):'—')+'</div></div>'
    +'<div class="ibox"><div class="l">Entrega</div><div class="v">'+(d.horario?d.horario.substring(0,5):'—')+'</div></div>'
    +'<div class="ibox"><div class="l">Retirada</div><div class="v">'+(d.horario_retirada?d.horario_retirada.substring(0,5):'—')+'</div></div>'
    +'</div>'
    +(d.telefone?'<div class="copy-row" data-copy="'+esc(d.telefone)+'" data-msg="Telefone copiado!"><i class="fa-solid fa-phone" style="color:var(--green);flex-shrink:0"></i><span style="flex:1">'+esc(d.telefone)+'</span><i class="fa-regular fa-copy" style="color:#555"></i></div>':'')
    +(d.endereco?'<div class="copy-row" data-nav="'+esc(d.endereco)+'"><i class="fa-solid fa-location-dot" style="color:var(--blue);flex-shrink:0"></i><span style="flex:1">'+esc(d.endereco)+'</span><i class="fa-solid fa-route" style="color:#33ccff"></i></div>':'')
    +'</div>'
    +(d.letreiros_texto?'<div class="dsec"><div class="dsec-lbl">Letreiro</div><div class="let-box"><div class="let-txt">'+esc(d.letreiros_texto)+'</div></div></div>':'')
    +'<div class="dsec"><div class="dsec-lbl">Contato & Navegação</div>'
    +'<div class="agrid">'
    +(tel?'<a href="https://wa.me/55'+tel+'" class="abtn abtn-w"><i class="fa-brands fa-whatsapp"></i>WhatsApp</a>':'')
    +(d.endereco?'<button class="abtn abtn-m" data-nav="'+esc(d.endereco)+'"><i class="fa-brands fa-waze"></i>Navegar</button>':'')
    +(tel?'<a href="tel:'+tel+'" class="abtn abtn-c"><i class="fa-solid fa-phone"></i>Ligar</a>':'')
    +'</div></div>'
    +'<div class="dsec"><div class="dsec-lbl">Atualizar Status</div>'
    +'<div class="sopts">'+statusBtns+'</div>'
    +'</div>'
    +'<div class="dsec"><div class="dsec-lbl">Observação</div>'
    +'<textarea class="obs-area" id="obs-txt" placeholder="Escreva uma observação...">'+esc(d.observacoes||'')+'</textarea>'
    +'<button class="save-btn" id="btn-save-obs"><i class="fa-solid fa-check"></i> Salvar observação</button>'
    +'</div>';

}

function fecharDetalhe(){ fecharPainel(); }

async function mudarStatus(status){
  if(!DET_ID) return;
  var fd=new FormData(); fd.append('_csrf',CSRF); fd.append('status',status);
  try{
    await api('POST','/api/demandas/'+DET_ID+'/status',fd);
    toast('✓ Status atualizado!','ok');
    await carregarDems(true);
    await carregarHist();
    var d = DEMS.concat(HIST).find(function(x){ return x.id==DET_ID; });
    if(d){ preencherDetalhe(d); document.getElementById('detail').scrollTop = 0; }
  }catch(e){ toast(e.message,'err'); }
}

// ── Notificações ──
async function carregarNotifs(){
  try{
    var data=await api('GET','/api/notificacoes');
    var notifs=data.notificacoes||[], naoLidas=data.nao_lidas||0;
    document.getElementById('notif-dot').style.display=naoLidas>0?'block':'none';
    var el=document.getElementById('notif-lista');
    if(!notifs.length){ el.innerHTML='<div class="empty"><i class="fa-solid fa-bell-slash"></i><h3>Sem notificações</h3></div>'; return; }
    var icMap={info:'fa-circle-info',sucesso:'fa-circle-check',aviso:'fa-triangle-exclamation',erro:'fa-circle-xmark'};
    var cMap={info:'#64d2ff',sucesso:'#22c55e',aviso:'#FFD600',erro:'#ff3b30'};
    el.innerHTML=notifs.map(function(n){
      var ico=icMap[n.tipo]||'fa-bell', cor=cMap[n.tipo]||'#888';
      return '<div class="notif-item'+(n.lida?'':' unread')+'" data-notif="'+n.id+'">'
        +(n.lida?'':'<div class="notif-dot-u"></div>')
        +'<div class="notif-ico" style="background:'+cor+'18;color:'+cor+'"><i class="fa-solid '+ico+'"></i></div>'
        +'<div class="notif-body">'
        +'<div class="notif-ttl">'+esc(n.titulo)+'</div>'
        +'<div class="notif-msg">'+esc(n.mensagem)+'</div>'
        +'<div class="notif-time">'+tempoRel(n.criado_em)+'</div>'
        +'</div></div>';
    }).join('');
  }catch(e){}
}

async function lerNotif(id){
  var fd=new FormData(); fd.append('_csrf',CSRF); fd.append('id',id);
  try{ await api('POST','/api/notificacoes/lida',fd); carregarNotifs(); }catch(e){}
}

async function marcarLidas(){
  var fd=new FormData(); fd.append('_csrf',CSRF);
  try{ await api('POST','/api/notificacoes/todas-lidas',fd); carregarNotifs(); }catch(e){}
}

// ── Init ──
function init(){
  saudar();
  carregarDems(false);
  carregarNotifs();
  setInterval(function(){ carregarDems(true); carregarNotifs(); }, 60000);
}

// ── Delegação de eventos ──
document.addEventListener('click', function(e){
  // Nav
  if(e.target.closest('#nav-dem')){ setPage('dem'); }
  if(e.target.closest('#nav-hist')){ setPage('hist'); if(!HIST.length) carregarHist(); }
  if(e.target.closest('#nav-perf')){ setPage('perf'); }

  // Topbar
  if(e.target.closest('#btn-notif')){
    var np=document.getElementById('notif-panel');
    if(np.classList.contains('open')){ fecharPainel(); }
    else { np.classList.add('open'); abrirPainel('notif'); carregarNotifs(); }
  }

  // Refresh
  if(e.target.closest('#btn-refresh')){ carregarDems(false); }
  if(e.target.closest('#btn-hist-refresh')){ carregarHist(); }

  // Fechar detalhe
  if(e.target.closest('#btn-back') || e.target.id==='det-overlay'){ fecharDetalhe(); }

  // Navegação (sheet) — prioridade sobre o card
  if(e.target.id==='nav-overlay' || e.target.closest('.nav-sheet-grab')){ fecharPainel(); return; }
  var navRow=e.target.closest('[data-nav]');
  if(navRow){ e.stopPropagation(); abrirNav(navRow.dataset.nav); return; }

  // Abrir detalhe (card clicado)
  var card=e.target.closest('.dcard');
  if(card && !e.target.closest('.qbtn')){
    abrirDetalhe(card.dataset.id, card.dataset.hist==='1');
  }

  // Avançar / voltar status
  var sbtn=e.target.closest('.save-btn[data-status], .back-status-btn[data-status]');
  if(sbtn){ mudarStatus(sbtn.dataset.status); }

  // Salvar obs
  if(e.target.closest('#btn-save-obs')){
    if(!DET_ID) return;
    var obs=document.getElementById('obs-txt').value;
    var fd=new FormData(); fd.append('_csrf',CSRF); fd.append('observacoes',obs);
    api('POST','/api/demandas/'+DET_ID,fd).then(function(){ toast('Observação salva!','ok'); }).catch(function(e){ toast(e.message,'err'); });
  }

  // Copiar
  var crow=e.target.closest('.copy-row[data-copy]');
  if(crow){ copiar(crow.dataset.copy, crow.dataset.msg); }

  // Notif item
  var ni=e.target.closest('.notif-item[data-notif]');
  if(ni){ lerNotif(ni.dataset.notif); }

  // Marcar lidas
  if(e.target.closest('#btn-lidas')){ marcarLidas(); }

  // Filtros
  if(e.target.id==='f-ano'||e.target.id==='f-mes'){ renderDems(); }
  if(e.target.id==='hf-ano'||e.target.id==='hf-mes'){ renderHist(); }
});

// Filtros via change
document.getElementById('f-ano').addEventListener('change', renderDems);
document.getElementById('f-mes').addEventListener('change', renderDems);
document.getElementById('hf-ano').addEventListener('change', renderHist);
document.getElementById('hf-mes').addEventListener('change', renderHist);

init();
</script>
</body>
</html>