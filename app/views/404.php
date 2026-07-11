<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>404 — Fokos Eventos</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Sora:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{
    background:#0D0E12;color:#F2F3F7;
    font-family:'Inter',sans-serif;
    min-height:100vh;display:flex;align-items:center;justify-content:center;
    -webkit-font-smoothing:antialiased;
}
.container{text-align:center;padding:44px 32px;max-width:460px;background:#14161C;border:1px solid #303542;border-radius:24px;box-shadow:0 6px 16px rgba(0,0,0,.4),0 40px 100px rgba(0,0,0,.5)}
.code{
    font-family:'Sora',sans-serif;font-size:100px;font-weight:800;
    color:#FFD600;line-height:1;letter-spacing:-4px;
    text-shadow:0 0 60px rgba(255,214,0,0.25);
    margin-bottom:8px;
}
.divider{
    width:60px;height:3px;border-radius:2px;
    background:linear-gradient(90deg,#FFD600,transparent);
    margin:16px auto;
}
h2{font-size:22px;font-weight:600;margin-bottom:12px}
p{font-size:15px;color:#9CA1B2;line-height:1.7;margin-bottom:32px}
.actions{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
.btn{
    padding:12px 24px;border-radius:12px;font-size:14px;font-weight:600;
    text-decoration:none;display:inline-flex;align-items:center;gap:8px;
    transition:all 0.2s;
}
.btn-primary{background:#FFD600;color:#000}
.btn-primary:hover{background:#ffe033;transform:translateY(-1px)}
.btn-ghost{
    background:#1D2029;border:1px solid #303542 !important;color:#F2F3F7;
    border:1px solid rgba(255,255,255,0.1);
}
.btn-ghost:hover{background:rgba(255,255,255,0.08);transform:translateY(-1px)}
.icon-404{font-size:48px;margin-bottom:16px;opacity:0.3}
</style>
</head>
<body>
<div class="container">
    <div class="icon-404"><i class="fa-solid fa-map-pin"></i></div>
    <div class="code">404</div>
    <div class="divider"></div>
    <h2>Página não encontrada</h2>
    <p>A rota que você tentou acessar não existe ou foi movida. Verifique o endereço ou volte ao início.</p>
    <div class="actions">
        <a href="<?= defined('APP_URL') ? APP_URL : '/' ?>/dashboard" class="btn btn-primary">
            <i class="fa-solid fa-house"></i> Ir ao Dashboard
        </a>
        <a href="javascript:history.back()" class="btn btn-ghost">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>
</body>
</html>
