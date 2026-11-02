<?php
/* registro.php — Crear cuenta: valida los datos, guarda el usuario con contraseña cifrada e inicia sesión. */
require 'conexion.php';

// Si ya tiene sesión, no tiene caso registrarse otra vez
if (isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

$err = [];
$v = ['nombre' => '', 'usuario' => '', 'email' => ''];
$registrado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($v as $k => $_) {
        $v[$k] = trim($_POST[$k] ?? '');
    }
    $p  = $_POST['password']  ?? '';
    $p2 = $_POST['password2'] ?? '';

    if (mb_strlen($v['nombre']) < 2 || mb_strlen($v['nombre']) > 80) $err[] = 'Escribe tu nombre (2 a 80 letras).';
    if (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $v['usuario'])) $err[] = 'Usuario: 3 a 20 letras, números o guion bajo.';
    if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $err[] = 'Correo no válido.';
    if (strlen($p) < 6) $err[] = 'La contraseña debe tener al menos 6 caracteres.';
    if ($p !== $p2) $err[] = 'Las contraseñas no coinciden.';

    if (!$err) {
        try {
            $st = db()->prepare('INSERT INTO usuarios (nombre, usuario, email, password) VALUES (?,?,?,?)');
            $st->execute([$v['nombre'], $v['usuario'], $v['email'], password_hash($p, PASSWORD_DEFAULT)]);

            // Inicio de sesión automático
            session_regenerate_id(true);
            $_SESSION['id']      = db()->lastInsertId();
            $_SESSION['usuario'] = $v['usuario'];
            $_SESSION['nombre']  = $v['nombre'];
            $registrado = true;
        } catch (PDOException $ex) {
            if ($ex->getCode() == 23000) {
                $err[] = 'Ese usuario o correo ya está registrado.';
            } else {
                $err[] = 'No se pudo crear la cuenta por ahora. Inténtalo de nuevo en unos minutos.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Papoyisa | Únete</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Fredoka:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="registro.css">
</head>
<body>

  <!-- ENCABEZADO -->
  <header class="top">
    <a class="logo" href="index.php">
      <span class="mini-gafas"><i></i><i></i></span>
      Papoyisa
    </a>
    <div class="acciones">
      <button id="btnPlatano" class="icono" title="Lluvia de plátanos">🍌</button>
      <button id="btnTema" class="icono" title="Modo noche">🌙</button>
      <a class="icono" href="index.php#login" title="Iniciar sesión">👤</a>
    </div>
  </header>

  <!-- MENÚ -->
  <nav>
    <button id="btnMenu" class="hamburguesa" aria-label="Abrir menú">☰</button>
    <ul id="menu">
      <li class="mi"><a href="index.php">🏠 Inicio</a></li>

      <li class="mi">
        <a href="noticias.php?categoria=deportes">⚽ Deportes</a>
        <div class="sub">
          <a href="noticias.php?categoria=deportes&amp;sec=NFL">NFL</a>
          <a href="noticias.php?categoria=deportes&amp;sec=MLB">MLB</a>
          <a href="noticias.php?categoria=deportes&amp;sec=NBA">NBA</a>
          <a href="noticias.php?categoria=deportes&amp;sec=Apuesta">Apuesta</a>
        </div>
      </li>

      <li class="mi">
        <a href="noticias.php?categoria=politica">🏛️ Política</a>
        <div class="sub">
          <a href="noticias.php?categoria=politica&amp;sec=USA">USA</a>
          <a href="noticias.php?categoria=politica&amp;sec=Mexico">México</a>
        </div>
      </li>

      <li class="mi">
        <a href="noticias.php?categoria=conspiraciones">👽 Conspiraciones</a>
        <div class="sub">
          <a href="noticias.php?categoria=conspiraciones&amp;sec=Muertes">Muertes</a>
          <a href="noticias.php?categoria=conspiraciones&amp;sec=Reemplazos">Reemplazos</a>
          <a href="noticias.php?categoria=conspiraciones&amp;sec=Asesinatos">Asesinatos</a>
          <a href="noticias.php?categoria=conspiraciones&amp;sec=Aliens">Aliens</a>
        </div>
      </li>

      <li class="mi"><a href="noticias.php?categoria=historia">📜 #Historia</a></li>
    </ul>
  </nav>

  <!-- CONTENIDO -->
  <main class="registro">

    <section class="hero">
      <div class="gafas">
        <div class="ojo"><div class="pupila"></div></div>
        <div class="ojo"><div class="pupila"></div></div>
      </div>
      <h1>¡Únete a la Papoyisa!</h1>
      <p>y sigue manteniéndote informado 🍌</p>
    </section>

    <?php if ($registrado): ?>

      <!-- ÉXITO -->
      <section class="panel exito">
        <div class="exito-emoji">🎉</div>
        <h2>¡Felicidades!</h2>
        <p class="exito-texto">¡Te uniste a la Papoyisa, <b><?= e($v['nombre']) ?></b>!</p>
        <p>Tu sesión ya está iniciada. Te llevamos al inicio en
           <span class="cuenta" id="cuenta">6</span> segundos…</p>
        <a class="btn btn-link" href="index.php">🍌 Ir al inicio ahora</a>
      </section>

    <?php else: ?>

      <!-- FORMULARIO -->
      <section class="panel">
        <h2>📝 Crea tu cuenta</h2>

        <div id="msg" class="err" <?= $err ? '' : 'hidden' ?>>
          <?php foreach ($err as $x): ?>
            <div><?= e($x) ?></div>
          <?php endforeach; ?>
        </div>

        <form id="formRegistro" method="post" novalidate>
          <label for="nombre">Nombre completo</label>
          <input type="text" id="nombre" name="nombre" placeholder="Ej. Gru Papoi"
                 value="<?= e($v['nombre']) ?>" autocomplete="name">

          <label for="usuario">Usuario</label>
          <input type="text" id="usuario" name="usuario" placeholder="Ej. banana_lover"
                 value="<?= e($v['usuario']) ?>" autocomplete="username">

          <label for="email">Correo</label>
          <input type="email" id="email" name="email" placeholder="tucorreo@ejemplo.com"
                 value="<?= e($v['email']) ?>" autocomplete="email">

          <label for="password">Contraseña</label>
          <div class="pass">
            <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres" autocomplete="new-password">
            <button type="button" class="ojo-pass" data-ver="password" title="Mostrar contraseña">👁️</button>
          </div>
          <div class="fuerza"><div id="barra"></div></div>
          <small id="fuerzaTxt" class="fuerza-txt"></small>

          <label for="password2">Repite la contraseña</label>
          <div class="pass">
            <input type="password" id="password2" name="password2" placeholder="Otra vez" autocomplete="new-password">
            <button type="button" class="ojo-pass" data-ver="password2" title="Mostrar contraseña">👁️</button>
          </div>

          <button class="btn" type="submit">🍌 ¡Unirme a la Papoyisa!</button>
        </form>

        <p>¿Ya eres Papoi? <a href="index.php#login">Inicia sesión</a></p>
      </section>

    <?php endif; ?>
  </main>

  <footer>
    <div class="bolsillo">© <span id="anio"></span> Papoyisa · Hecho con ❤️ y muchos 🍌</div>
  </footer>

<script>
  document.getElementById('anio').textContent = new Date().getFullYear();

  // ---------- Ojos que siguen el mouse ----------
  const ojos = document.querySelectorAll('.gafas .ojo');
  function mirar(x, y) {
    ojos.forEach(ojo => {
      const r = ojo.getBoundingClientRect();
      const cx = r.left + r.width / 2;
      const cy = r.top + r.height / 2;
      const ang = Math.atan2(y - cy, x - cx);
      const dist = Math.min(r.width * 0.2, Math.hypot(x - cx, y - cy) / 12);
      ojo.firstElementChild.style.transform =
        'translate(' + Math.cos(ang) * dist + 'px,' + Math.sin(ang) * dist + 'px)';
    });
  }
  document.addEventListener('mousemove', ev => mirar(ev.clientX, ev.clientY));
  document.addEventListener('touchmove', ev => {
    const t = ev.touches[0];
    mirar(t.clientX, t.clientY);
  }, { passive: true });

  // ---------- Lluvia de plátanos ----------
  function lluviaPlatanos() {
    for (let i = 0; i < 30; i++) {
      const p = document.createElement('span');
      p.className = 'platano';
      p.textContent = '🍌';
      p.style.left = Math.random() * 100 + 'vw';
      p.style.fontSize = (20 + Math.random() * 30) + 'px';
      p.style.animationDuration = (2 + Math.random() * 3) + 's';
      p.style.animationDelay = (Math.random() * 1.5) + 's';
      document.body.appendChild(p);
      setTimeout(() => p.remove(), 7000);
    }
  }
  document.getElementById('btnPlatano').addEventListener('click', lluviaPlatanos);

  // ---------- Menú hamburguesa ----------
  const menu = document.getElementById('menu');
  document.getElementById('btnMenu').addEventListener('click', () => menu.classList.toggle('abierto'));
  document.querySelectorAll('.mi').forEach(item => {
    const link = item.querySelector('a');
    if (item.querySelector('.sub')) {
      link.addEventListener('click', ev => {
        if (window.innerWidth <= 700) {
          ev.preventDefault();
          item.classList.toggle('abierto');
        }
      });
    }
  });

  // ---------- Modo noche ----------
  const btnTema = document.getElementById('btnTema');
  function aplicarTema(noche) {
    document.body.classList.toggle('noche', noche);
    btnTema.textContent = noche ? '☀️' : '🌙';
  }
  let noche = false;
  try { noche = localStorage.getItem('tema') === 'noche'; } catch (e) {}
  aplicarTema(noche);
  btnTema.addEventListener('click', () => {
    noche = !noche;
    aplicarTema(noche);
    try { localStorage.setItem('tema', noche ? 'noche' : 'dia'); } catch (e) {}
  });

  // ---------- Pantalla de éxito: confeti y cuenta regresiva ----------
  const cuenta = document.getElementById('cuenta');
  if (cuenta) {
    lluviaPlatanos();
    try { sessionStorage.setItem('fiesta', '1'); } catch (e) {}
    let n = 6;
    const t = setInterval(() => {
      n--;
      cuenta.textContent = n;
      if (n <= 0) {
        clearInterval(t);
        window.location.href = 'index.php';
      }
    }, 1000);
  }

  // ---------- Formulario ----------
  const form = document.getElementById('formRegistro');
  if (form) {
    const msg = document.getElementById('msg');
    const pass = document.getElementById('password');

    // Mostrar / ocultar contraseñas
    document.querySelectorAll('[data-ver]').forEach(b => {
      b.addEventListener('click', () => {
        const inp = document.getElementById(b.dataset.ver);
        inp.type = inp.type === 'password' ? 'text' : 'password';
      });
    });

    // Medidor de fuerza
    const barra = document.getElementById('barra');
    const txt = document.getElementById('fuerzaTxt');
    pass.addEventListener('input', () => {
      const p = pass.value;
      let pts = 0;
      if (p.length >= 6) pts++;
      if (p.length >= 10) pts++;
      if (/[A-Z]/.test(p) && /[a-z]/.test(p)) pts++;
      if (/\d/.test(p)) pts++;
      if (/[^A-Za-z0-9]/.test(p)) pts++;
      const niveles = [
        ['0%', '', ''],
        ['25%', '#e53935', 'Débil 😬'],
        ['50%', '#fb8c00', 'Regular 🙂'],
        ['75%', '#fdd835', 'Buena 😎'],
        ['100%', '#43a047', '¡Súper Papoi! 🍌']
      ];
      const nv = p.length === 0 ? 0 : Math.min(4, Math.max(1, pts - 1));
      barra.style.width = niveles[nv][0];
      barra.style.background = niveles[nv][1];
      txt.textContent = niveles[nv][2];
    });

    // Validación antes de enviar
    form.addEventListener('submit', ev => {
      const errores = [];
      const nombre = document.getElementById('nombre').value.trim();
      const usuario = document.getElementById('usuario').value.trim();
      const email = document.getElementById('email').value.trim();
      const p2 = document.getElementById('password2').value;

      if (nombre.length < 2) errores.push('Escribe tu nombre.');
      if (!/^[A-Za-z0-9_]{3,20}$/.test(usuario)) errores.push('Usuario: 3 a 20 letras, números o guion bajo.');
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errores.push('Correo no válido.');
      if (pass.value.length < 6) errores.push('La contraseña debe tener al menos 6 caracteres.');
      if (pass.value !== p2) errores.push('Las contraseñas no coinciden.');

      if (errores.length) {
        ev.preventDefault();
        msg.innerHTML = '';
        errores.forEach(t => {
          const d = document.createElement('div');
          d.textContent = t;
          msg.appendChild(d);
        });
        msg.hidden = false;
        msg.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });
  }
</script>
</body>
</html>