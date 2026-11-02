<?php
/* index.php — Página de inicio: inicio de sesión, menú de categorías, juegos y saludo. */
require 'conexion.php';

$error = '';
$u = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // ----- Cambiar de cuenta (cerrar sesión) -----
    if ($accion === 'salir') {
        $_SESSION = [];
        session_destroy();
        header('Location: index.php#login');
        exit;
    }

    // ----- Iniciar sesión -----
    if ($accion === 'login') {
        $u = trim($_POST['usuario'] ?? '');
        $p = $_POST['password'] ?? '';

        if (($_SESSION['bloqueo'] ?? 0) > time()) {
            $error = 'Demasiados intentos. Espera 30 segundos.';
        } elseif ($u === '' || $p === '') {
            $error = 'Llena todos los campos.';
        } else {
            try {
                $st = db()->prepare('SELECT * FROM usuarios WHERE usuario = ? OR email = ? LIMIT 1');
                $st->execute([$u, $u]);
                $r = $st->fetch();

                if ($r && password_verify($p, $r['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['id']       = $r['id'];
                    $_SESSION['usuario']  = $r['usuario'];
                    $_SESSION['nombre']   = $r['nombre'];
                    $_SESSION['es_admin'] = (int)($r['es_admin'] ?? 0);
                    $_SESSION['intentos'] = 0;
                    header('Location: index.php');
                    exit;
                }

                $_SESSION['intentos'] = ($_SESSION['intentos'] ?? 0) + 1;
                if ($_SESSION['intentos'] >= 5) {
                    $_SESSION['bloqueo']  = time() + 30;
                    $_SESSION['intentos'] = 0;
                }
                $error = 'Usuario o contraseña incorrectos.';
            } catch (PDOException $ex) {
                $error = 'No se pudo iniciar sesión por ahora. Inténtalo de nuevo en unos minutos.';
            }
        }
    }
}

$logueado = isset($_SESSION['usuario']);
$nombre   = $_SESSION['nombre']  ?? '';
$usuario  = $_SESSION['usuario'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Papoyisa | Inicio</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Fredoka:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- ENCABEZADO -->
  <header class="top">
    <a class="logo" href="index.php">
      <span class="mini-gafas"><i></i><i></i></span>
      Papoyisa
    </a>
    <div class="acciones">
      <?php if ($logueado && !empty($_SESSION['es_admin'])): ?><a class="icono" href="admin/">🛠️</a><?php endif; ?>
      <button id="btnPlatano" class="icono" title="Lluvia de plátanos">🍌</button>
      <button id="btnTema" class="icono" title="Modo noche">🌙</button>
      <a class="icono" href="#login" title="Mi cuenta">👤<?= $logueado ? ' ' . e($usuario) : '' ?></a>
    </div>
  </header>

  <!-- MENÚ -->
  <nav>
    <button id="btnMenu" class="hamburguesa" aria-label="Abrir menú">☰</button>
    <ul id="menu">
      <li class="mi"><a href="index.php" class="activo">🏠 Inicio</a></li>

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
  <main>

    <!-- HERO con gafas -->
    <section class="hero">
      <div class="gafas">
        <div class="ojo"><div class="pupila"></div></div>
        <div class="ojo"><div class="pupila"></div></div>
      </div>
      <h1 id="saludo" data-nombre="<?= e($nombre) ?>">¡Bienvenido!</h1>
      <?php if ($logueado): ?>
        <p>Las noticias más <b>bananas</b> de todo el mundo 🍌</p>
      <?php else: ?>
        <p><b>Inicia sesión</b> para entrar a la Papoyisa 🍌</p>
      <?php endif; ?>
      <p class="reloj" id="reloj"></p>
    </section>

    <div class="grid">

      <section class="panel">
        <h2>🍌 Explora</h2>
        <p>Selecciona una categoría del menú o entra directo:</p>
        <div class="tabs">
          <a href="noticias.php?categoria=deportes">⚽ Deportes</a>
          <a href="noticias.php?categoria=politica">🏛️ Política</a>
          <a href="noticias.php?categoria=conspiraciones">👽 Conspiraciones</a>
          <a href="noticias.php?categoria=historia">📜 #Historia</a>
        </div>
      </section>

      <?php if ($logueado): ?>

        <!-- YA ES PAPOI -->
        <section class="panel papoi" id="login">
          <div class="papoi-banana">🍌</div>
          <h2 class="papoi-titulo">¡Ya eres un Papoi!</h2>
          <p class="papoi-nombre"><?= e($nombre) ?></p>
          <p class="papoi-user">@<?= e($usuario) ?></p>
          <p>Bienvenido a la Papoyisa. Sigue manteniéndote informado. 🎉</p>
          <form method="post" class="form-salir">
            <input type="hidden" name="accion" value="salir">
            <button class="btn btn-sec" type="submit">🔄 Cambiar de cuenta</button>
          </form>
        </section>

      <?php else: ?>

        <!-- INICIAR SESIÓN -->
        <section class="panel" id="login">
          <h2>🔐 Inicia sesión</h2>
          <p class="aviso">👉 Entra para ser parte de la Papoyisa</p>
          <div id="msg" class="err" <?= $error ? '' : 'hidden' ?>><?= e($error) ?></div>
          <form id="formLogin" method="post" novalidate>
            <input type="hidden" name="accion" value="login">
            <input type="text" id="usuario" name="usuario" placeholder="Usuario o correo"
                   value="<?= e($u) ?>" autocomplete="username">
            <div class="pass">
              <input type="password" id="password" name="password" placeholder="Contraseña" autocomplete="current-password">
              <button type="button" id="verPass" class="ojo-pass" title="Mostrar contraseña">👁️</button>
            </div>
            <button class="btn" type="submit">¡Entrar!</button>
          </form>
          <p>¿Aún no eres Papoi? <a href="registro.php">¡Únete a la Papoyisa!</a></p>
        </section>

      <?php endif; ?>

    </div>

    <section class="panel juego">
  <h2>🎮 Juegos</h2>

  <!-- Elegir juego -->
  <div class="juegos-grid" id="listaJuegos">
    <button class="juego-card" type="button" data-src="juego/bananarun/juego.html" data-nombre="Banana Run">
      <span class="emoji">🍌</span>
      <strong>Banana Run</strong>
      <small>Corre y recoge plátanos</small>
    </button>
    <button class="juego-card" type="button" data-src="juego/bananacaos/index.html" data-nombre="Minion Arena">
      <span class="emoji">🤪</span>
      <strong>Minion Arena</strong>
      <small>Sobrevive a las oleadas</small>
    </button>
  </div>

  <!-- Juego abierto -->
  <div id="zonaJuego" hidden>
    <div class="juego-cab">
      <h3 id="tituloJuego"></h3>
      <div>
        <button id="btnPantalla" class="btn" type="button">⛶ Pantalla completa</button>
        <button id="btnCerrar" class="btn btn-sec" type="button">⬅ Elegir otro juego</button>
      </div>
    </div>
    <div class="juego-box" id="juegoBox">
      <iframe id="juegoFrame" title="Juego" allow="fullscreen" allowfullscreen></iframe>
    </div>
  </div>
</section>

<script>
 // ---------- Elegir juego + pantalla completa ----------
const listaJuegos = document.getElementById('listaJuegos');
const zonaJuego   = document.getElementById('zonaJuego');
const juegoBox    = document.getElementById('juegoBox');
const juegoFrame  = document.getElementById('juegoFrame');
const btnPantalla = document.getElementById('btnPantalla');

const enPantallaCompleta = () =>
  document.fullscreenElement || document.webkitFullscreenElement ||
  juegoBox.classList.contains('pantalla-falsa');

const actualizarBoton = () =>
  btnPantalla.textContent = enPantallaCompleta() ? '✖ Salir' : '⛶ Pantalla completa';

async function salirPantalla() {
  if (document.fullscreenElement && document.exitFullscreen) await document.exitFullscreen();
  else if (document.webkitFullscreenElement && document.webkitExitFullscreen) document.webkitExitFullscreen();
  juegoBox.classList.remove('pantalla-falsa');
  actualizarBoton();
}

// Click en una tarjeta: abre ese juego
listaJuegos.querySelectorAll('.juego-card').forEach(card => {
  card.addEventListener('click', () => {
    juegoFrame.src = card.dataset.src;
    document.getElementById('tituloJuego').textContent = card.dataset.nombre;
    listaJuegos.hidden = true;
    zonaJuego.hidden = false;
  });
});

// Volver a la lista
document.getElementById('btnCerrar').addEventListener('click', async () => {
  await salirPantalla();
  juegoFrame.src = 'about:blank';
  zonaJuego.hidden = true;
  listaJuegos.hidden = false;
});

// Botón de pantalla completa
btnPantalla.addEventListener('click', async () => {
  if (enPantallaCompleta()) { await salirPantalla(); return; }
  const pedir = juegoBox.requestFullscreen || juegoBox.webkitRequestFullscreen;
  if (pedir) {
    try { await pedir.call(juegoBox); } catch (e) { juegoBox.classList.add('pantalla-falsa'); }
  } else {
    juegoBox.classList.add('pantalla-falsa'); // iPhone
  }
  juegoFrame.focus();
  actualizarBoton();
});

document.addEventListener('fullscreenchange', actualizarBoton);
document.addEventListener('webkitfullscreenchange', actualizarBoton);
document.addEventListener('keydown', e => {
  if (e.key === 'Escape' && juegoBox.classList.contains('pantalla-falsa')) salirPantalla();
});
  // ---------- Saludo según la hora ----------
  const hora = new Date().getHours();
  const saludo = hora < 12 ? '¡Buenos días' : hora < 19 ? '¡Buenas tardes' : '¡Buenas noches';
  const elSaludo = document.getElementById('saludo');
  const nombre = elSaludo.dataset.nombre;
  elSaludo.textContent = saludo + (nombre ? ', ' + nombre : '') + '!';

  // ---------- Reloj en vivo ----------
  function actualizarReloj() {
    const ahora = new Date();
    document.getElementById('reloj').textContent =
      ahora.toLocaleDateString('es-MX', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) +
      ' · ' + ahora.toLocaleTimeString('es-MX');
  }
  actualizarReloj();
  setInterval(actualizarReloj, 1000);

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
    for (let i = 0; i < 25; i++) {
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
  <?php if ($logueado): ?>
  // Si ya es Papoi, celebramos
  if (sessionStorage.getItem('fiesta') !== '1') {
    lluviaPlatanos();
    try { sessionStorage.setItem('fiesta', '1'); } catch (e) {}
  }
  <?php else: ?>
  try { sessionStorage.removeItem('fiesta'); } catch (e) {}
  <?php endif; ?>

  // ---------- Menú hamburguesa (celular) ----------
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

  // ---------- Modo noche (se recuerda) ----------
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

  // ---------- Login: mostrar contraseña y validar ----------
  const formLogin = document.getElementById('formLogin');
  if (formLogin) {
    const inputPass = document.getElementById('password');
    document.getElementById('verPass').addEventListener('click', () => {
      inputPass.type = inputPass.type === 'password' ? 'text' : 'password';
    });

    formLogin.addEventListener('submit', ev => {
      const u = document.getElementById('usuario').value.trim();
      const p = inputPass.value;
      const msg = document.getElementById('msg');

      if (u === '' || p === '') {
        ev.preventDefault();
        msg.textContent = 'Llena todos los campos.';
        msg.hidden = false;
      } else if (p.length < 6) {
        ev.preventDefault();
        msg.textContent = 'La contraseña debe tener al menos 6 caracteres.';
        msg.hidden = false;
      }
    });
  }
</script>
</body>
</html>