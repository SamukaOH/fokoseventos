// ============================================================
// FOKOS EVENTOS — app.js
// ============================================================

// APP_URL e CSRF lidos das metas
var APP_URL = document.querySelector('meta[name="app-url"]')?.content || '';
var CSRF    = document.querySelector('meta[name="csrf"]')?.content || '';

// ---- API ----
var Api = {
  async get(url) {
    try {
      var res = await fetch(APP_URL + url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      if (!res.ok) return null;
      var ct = res.headers.get('content-type') || '';
      if (!ct.includes('json')) { window.location.href = APP_URL + '/'; return null; }
      return await res.json();
    } catch(e) { return null; }
  },
  async post(url, data) {
    try {
      var res = await fetch(APP_URL + url, {
        method: 'POST',
        body: data,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      var json = await res.json();
      if (json.erro) throw new Error(json.erro);
      return json;
    } catch(e) { throw e; }
  },
  // alias para compatibilidade
  async postForm(url, fd) {
    return this.post(url, fd);
  }
};

// ---- Toast ----
var _toastContainer = null;
function _getToastContainer() {
  if (!_toastContainer) {
    _toastContainer = document.createElement('div');
    _toastContainer.id = 'toast-container';
    _toastContainer.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:8px;';
    document.body.appendChild(_toastContainer);
  }
  return _toastContainer;
}

var Toast = {
  show(type, title, msg) {
    var colors = { success:'#4cd964', error:'#ff3b30', warning:'#FFD600', info:'#64d2ff' };
    var icons  = { success:'fa-circle-check', error:'fa-circle-xmark', warning:'fa-triangle-exclamation', info:'fa-circle-info' };
    var el = document.createElement('div');
    el.style.cssText = 'background:#1c1c1f;border:1px solid rgba(255,255,255,0.1);border-left:3px solid '+colors[type]+';border-radius:12px;padding:14px 16px;min-width:280px;max-width:360px;display:flex;align-items:flex-start;gap:12px;box-shadow:0 8px 32px rgba(0,0,0,0.4);animation:slideIn .2s ease;';
    el.innerHTML = '<i class="fa-solid '+icons[type]+'" style="color:'+colors[type]+';font-size:16px;flex-shrink:0;margin-top:1px"></i>'
      + '<div><div style="font-size:13px;font-weight:600;color:#f0f0f0;margin-bottom:2px">'+escHtml(title)+'</div>'
      + (msg ? '<div style="font-size:12px;color:#888">'+escHtml(msg)+'</div>' : '')
      + '</div>';
    _getToastContainer().appendChild(el);
    setTimeout(function() { el.remove(); }, 4000);
  },
  success(t, m) { this.show('success', t, m); },
  error(t, m)   { this.show('error',   t, m); },
  warning(t, m) { this.show('warning', t, m); },
  info(t, m)    { this.show('info',    t, m); }
};

// ---- Modal ----
var Modal = {
  open(id) {
    var el = document.getElementById(id);
    if (!el) return;
    // Mover para o <body> — escapa de qualquer contexto de empilhamento
    // (main-content com transform prendia o modal atrás do dock)
    if (el.parentNode !== document.body) document.body.appendChild(el);
    el.classList.add('open'); el.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    document.body.classList.add('modal-open');
  },
  close(id) {
    var el = document.getElementById(id);
    if (el) { el.classList.remove('open'); }
    document.body.style.overflow = '';
    document.body.classList.remove('modal-open');
  },
  confirm(title, msg, cb) {
    var id = 'modal-confirm-'+Date.now();
    var el = document.createElement('div');
    el.className = 'modal-overlay open';
    el.id = id;
    el.innerHTML = '<div class="modal" style="max-width:400px">'
      + '<div class="modal-header"><h3>'+escHtml(title)+'</h3></div>'
      + '<div class="modal-body"><p style="color:var(--text2)">'+escHtml(msg)+'</p></div>'
      + '<div class="modal-footer">'
      + '<button class="btn btn-ghost" onclick="Modal.close(\''+id+'\')">Cancelar</button>'
      + '<button class="btn btn-danger" id="confirm-ok-'+id+'">Confirmar</button>'
      + '</div></div>';
    document.body.appendChild(el);
    document.getElementById('confirm-ok-'+id).onclick = function() { Modal.close(id); setTimeout(cb, 100); };
  }
};

// Fechar modal clicando no overlay ou no botão X
document.addEventListener('click', function(e) {
  // Overlay
  if (e.target.classList.contains('modal-overlay')) Modal.close(e.target.id);
  // Botão X ou qualquer elemento com data-close-modal (incluindo ícones filhos)
  var closer = e.target.closest('[data-close-modal]');
  if (closer) Modal.close(closer.dataset.closeModal);
});

// ---- Notificações ----
var Notifs = {
  toggle() {
    var dd = document.getElementById('notif-dropdown');
    if (dd) {
      if (dd.classList.contains('open')) {
        dd.classList.remove('open');
      } else {
        posicionarDropdown();
        dd.classList.add('open');
        this.load();
      }
    }
  },
  async load() {
    var data = await Api.get('/api/notificacoes');
    if (!data) return;
    var badge = document.getElementById('notif-badge');
    var list  = document.getElementById('notif-list');
    if (badge) {
      if (data.nao_lidas > 0) { badge.textContent = data.nao_lidas > 9 ? '9+' : data.nao_lidas; badge.style.display = 'flex'; }
      else badge.style.display = 'none';
    }
    if (!list) return;
    if (!data.notificacoes.length) {
      list.innerHTML = '<div class="notif-empty"><i class="fa-solid fa-bell-slash"></i>Nenhuma notificação</div>';
      return;
    }
    var iconMap = { info:'fa-circle-info', sucesso:'fa-circle-check', aviso:'fa-triangle-exclamation', erro:'fa-circle-xmark' };
    list.innerHTML = data.notificacoes.map(function(n) {
      var ico  = iconMap[n.tipo] || 'fa-bell';
      var tipo = n.tipo || 'info';
      var tempo = tempoRelativo(n.criado_em);
      return '<div class="notif-item'+(n.lida ? '' : ' unread')+'" onclick="lerNotif('+n.id+')">'
        + '<div class="notif-dot'+(n.lida?' read':'')+'"></div>'
        + '<div class="notif-icon '+tipo+'"><i class="fa-solid '+ico+'"></i></div>'
        + '<div class="notif-body">'
        + '<div class="notif-title">'+escHtml(n.titulo)+'</div>'
        + '<div class="notif-msg">'+escHtml(n.mensagem)+'</div>'
        + '<div class="notif-time">'+tempo+'</div>'
        + '</div></div>';
    }).join('');
  }
};
setInterval(function() { Notifs.load(); }, 30000);

function tempoRelativo(dateStr) {
  if (!dateStr) return '';
  var d    = new Date(dateStr.replace(' ','T'));
  var diff = Math.floor((Date.now() - d.getTime()) / 1000);
  if (diff < 60)   return 'agora mesmo';
  if (diff < 3600) return Math.floor(diff/60) + ' min atrás';
  if (diff < 86400)return Math.floor(diff/3600) + 'h atrás';
  return Math.floor(diff/86400) + 'd atrás';
}

async function lerNotif(id) {
  var fd = new FormData();
  fd.append('_csrf', CSRF);
  fd.append('id', id);
  await Api.post('/api/notificacoes/lida', fd);
  Notifs.load();
}

// ---- Utilitários ----
function escHtml(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function moeda(v) {
  return 'R$ ' + parseFloat(v||0).toLocaleString('pt-BR', {minimumFractionDigits:2});
}
function dataBR(str) {
  if (!str) return '-';
  var p = str.split('-');
  return p[2]+'/'+p[1]+'/'+p[0];
}
function statusLabel(s) {
  var map = {pendente:'Pendente',preparacao:'Em Preparação',em_rota:'Em Rota',entregue:'Entregue',montado:'Montado',finalizado:'Finalizado',cancelado:'Cancelado'};
  return map[s] || s;
}
function prioLabel(p) {
  var map = {baixa:'Baixa',media:'Média',alta:'Alta',urgente:'Urgente'};
  return map[p] || p;
}
function badgeStatus(s) {
  return '<span class="badge badge-'+s+'">'+statusLabel(s)+'</span>';
}
function badgePrio(p) {
  return '<span class="badge badge-'+p+'">'+prioLabel(p)+'</span>';
}
function setLoadingBtn(btn, loading, text) {
  if (loading) {
    btn._orig = btn.innerHTML;
    btn.innerHTML = '<span class="spinner"></span> '+(text||'Aguarde...');
    btn.disabled = true;
  } else {
    btn.innerHTML = btn._orig || (text||'');
    btn.disabled = false;
  }
}
function getCsrf() { return CSRF; }
function togglePassword(id) {
  var inp = document.getElementById(id);
  if (!inp) return;
  inp.type = inp.type === 'password' ? 'text' : 'password';
}

// ---- Init ----
document.addEventListener('DOMContentLoaded', function() {
  // Injetar CSS de animação do toast
  var style = document.createElement('style');
  style.textContent = '@keyframes slideIn{from{transform:translateX(20px);opacity:0}to{transform:translateX(0);opacity:1}}';
  document.head.appendChild(style);

  Notifs.load();
});
// ═══════════ FkSelect — dropdown customizado do design system ═══════════
// Substitui o POPUP nativo dos <select> (a lógica/valor continua no select real).
// Funciona com selects dinâmicos: a lista é construída na hora da abertura.
var FkSelect = (function() {
  var panel = null, current = null;

  function close() {
    if (panel) { panel.remove(); panel = null; }
    if (current) { current.classList.remove('fk-select-open'); current = null; }
  }

  function open(sel) {
    close();
    current = sel;
    sel.classList.add('fk-select-open');
    var rect = sel.getBoundingClientRect();

    panel = document.createElement('div');
    panel.className = 'fk-select-panel';
    panel.style.left  = rect.left + 'px';
    panel.style.width = rect.width + 'px';

    var html = '';
    for (var i = 0; i < sel.options.length; i++) {
      var o = sel.options[i];
      if (o.hidden) continue;
      html += '<div class="fk-opt'+(o.disabled?' dis':'')+(i===sel.selectedIndex?' sel':'')+'" data-i="'+i+'" title="'+o.text.replace(/"/g,'&quot;')+'">'
        + '<span class="fk-opt-txt"></span>'
        + (i===sel.selectedIndex ? '<i class="fa-solid fa-check"></i>' : '')
        + '</div>';
    }
    panel.innerHTML = html;
    // textContent seguro
    var opts = panel.querySelectorAll('.fk-opt');
    for (var j = 0; j < opts.length; j++) {
      opts[j].querySelector('.fk-opt-txt').textContent = sel.options[+opts[j].dataset.i].text;
    }
    document.body.appendChild(panel);

    // posição: abaixo; vira para cima se não couber
    var ph = Math.min(panel.scrollHeight, 260);
    var below = window.innerHeight - rect.bottom;
    if (below < ph + 12 && rect.top > ph + 12) {
      panel.style.top = (rect.top - ph - 6) + 'px';
    } else {
      panel.style.top = (rect.bottom + 6) + 'px';
    }
    panel.style.maxHeight = '260px';

    var selEl = panel.querySelector('.fk-opt.sel');
    if (selEl) selEl.scrollIntoView({ block:'nearest' });

    panel.addEventListener('click', function(e) {
      var opt = e.target.closest('.fk-opt');
      if (!opt || opt.classList.contains('dis')) return;
      sel.selectedIndex = +opt.dataset.i;
      sel.dispatchEvent(new Event('change', { bubbles:true }));
      close();
    });
  }

  // Intercepta a abertura nativa
  document.addEventListener('mousedown', function(e) {
    var sel = e.target.closest && e.target.closest('select.form-control, select.estoque-select, .estoque-select');
    if (sel && sel.tagName === 'SELECT' && !sel.multiple && !sel.disabled) {
      e.preventDefault();
      sel.focus({ preventScroll:true });
      if (current === sel) { close(); } else { open(sel); }
      return;
    }
    if (panel && !e.target.closest('.fk-select-panel')) close();
  }, true);
  document.addEventListener('keydown', function(e) { if (e.key === 'Escape') close(); });
  window.addEventListener('resize', close);
  window.addEventListener('scroll', close, true);

  return { close: close };
})();

// ═══════════ Transição de entrada/saída (loading com a marca) ═══════════
function fkTransition(txt) {
  var el = document.createElement('div');
  el.className = 'fk-transition';
  el.innerHTML = '<img src="' + APP_URL + '/public/assets/img/logo-full.png" alt="Fokos Eventos">'
    + '<div class="fk-bar"></div>'
    + '<div class="fk-txt">' + (txt || 'Carregando') + '</div>';
  document.body.appendChild(el);
  return el;
}

// Logout: confirmação + transição
document.addEventListener('click', function(e) {
  var a = e.target.closest && e.target.closest('a[href$="/logout"], a[href*="auth/logout"]');
  if (!a) return;
  e.preventDefault();
  var url = a.href;
  var id = 'modal-logoff';
  var el = document.getElementById(id);
  if (!el) {
    el = document.createElement('div');
    el.className = 'modal-overlay'; el.id = id;
    el.innerHTML = '<div class="modal" style="max-width:380px">'
      + '<div class="modal-header"><h3><i class="fa-solid fa-right-from-bracket" style="color:var(--yellow)"></i> Sair do sistema</h3>'
      + '<button class="modal-close" data-close-modal="'+id+'"><i class="fa-solid fa-xmark"></i></button></div>'
      + '<div class="modal-body"><p style="color:var(--text2);font-size:13.5px">Tem certeza que deseja encerrar a sessão?</p></div>'
      + '<div class="modal-footer">'
      + '<button class="btn btn-ghost" data-close-modal="'+id+'">Cancelar</button>'
      + '<button class="btn btn-primary" id="btn-logoff-ok"><i class="fa-solid fa-check"></i> Sair</button>'
      + '</div></div>';
    document.body.appendChild(el);
  }
  document.getElementById('btn-logoff-ok').onclick = function() {
    Modal.close(id);
    fkTransition('Saindo');
    setTimeout(function() { window.location.href = url; }, 800);
  };
  Modal.open(id);
});


// ═══════════ Transição Apple entre páginas ═══════════
(function(){
  // Standalone: adicionar classe pro CSS detectar
  if (window.navigator.standalone || window.matchMedia('(display-mode: standalone)').matches) {
    document.documentElement.classList.add('standalone');
  }

  document.addEventListener('click', function(e){
    var a = e.target.closest && e.target.closest('a[href]');
    if (!a) return;
    var href = a.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('javascript') || a.target === '_blank') return;
    // Interceptar navegação interna (sidebar, bottom-nav, links do sistema)
    var isInternal = a.closest('.nav-item, .bottom-nav, .bnav-item') ||
                     (href.indexOf(APP_URL) === 0 && !href.match(/\.(php|jpg|png|css|js)/));
    if (!isInternal) return;
    if (href === window.location.href) return;
    e.preventDefault();
    var mc = document.querySelector('.main-content');
    if (!mc) { window.location.href = href; return; }
    mc.classList.add('fk-page-out');
    setTimeout(function(){ window.location.href = href; }, 160);
  });

  // Entrada suave
  var mc = document.querySelector('.main-content');
  if (mc) mc.classList.add('fk-page-in');
})();
