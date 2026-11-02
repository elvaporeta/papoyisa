<?php
/* noticias.php — Lista de noticias publicadas de una categoría (?categoria=) y subcategoría (?sec=). */
require_once __DIR__.'/noticias_comun.php';
$cats=papoyisa_categorias(); $cat=$_GET['categoria']??'deportes'; if(!isset($cats[$cat])) $cat='deportes';
$sec=$_GET['sec']??''; if($sec!=='' && !isset($cats[$cat]['subs'][$sec])) $sec='';
$c=$cats[$cat]; $titulo=$sec!==''?$c['subs'][$sec]:$c['titulo'];
$sql="SELECT id,titulo,resumen,imagen,imagen_alt,fecha_publicacion,categoria,subcategoria,destacada,plantilla FROM noticias WHERE estado='publicada' AND categoria=?"; $args=[$cat];
if($sec!==''){ $sql.=' AND subcategoria=?'; $args[]=$sec; } $sql.=' ORDER BY fecha_publicacion DESC, id DESC';
try{$st=db()->prepare($sql);$st->execute($args);$lista=$st->fetchAll();}catch(PDOException $e){$lista=[];$errorDb=true;}
$admin=isset($_SESSION['usuario']) && !empty($_SESSION['es_admin']);
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Papoyisa | <?=h($titulo)?></title><link rel="stylesheet" href="style.css"><link rel="stylesheet" href="noticias.css"><link rel="stylesheet" href="admin/admin.css"><?=papoyisa_estilo()?></head><body>
<!-- ENCABEZADO -->
<header class="top"><a class="logo" href="index.php">🍌 <?=h(papoyisa_config()['nombre_sitio']??'Papoyisa')?></a><div class="acciones"><?php if($admin):?><a class="icono" href="admin/">🛠️ Panel</a><?php endif;?><a class="icono" href="index.php#login">👤</a></div></header>
<!-- MENÚ -->
<nav><button id="btnMenu" class="hamburguesa">☰</button><ul id="menu"><li class="mi"><a href="index.php">🏠 Inicio</a></li>
<?php foreach($cats as $key=>$cc):?><li class="mi"><a class="<?= $key===$cat?'activo':''?>" href="noticias.php?categoria=<?=h($key)?>"><?=h($cc['emoji'].' '.$cc['titulo'])?></a><?php if($cc['subs']):?><div class="sub"><?php foreach($cc['subs'] as $k=>$v):?><a href="noticias.php?categoria=<?=h($key)?>&amp;sec=<?=h($k)?>"><?=h($v)?></a><?php endforeach;?></div><?php endif;?></li><?php endforeach;?></ul></nav>
<!-- CONTENIDO -->
<main class="pagina-noticias"><div class="migas"><a href="index.php">Inicio</a> › <?=h($titulo)?></div><section class="noticias-hero"><div><span class="sobre-titulo">EL MUNDO, EN MODO PAPOI</span><h1><?=h($c['emoji'].' '.$titulo)?></h1><p><?=h($c['descripcion'])?></p></div><div class="hero-sello">🍌<span>PAPOYISA</span></div></section>
<!-- FILTRO POR SUBCATEGORÍA -->
<?php if($c['subs']):?><section class="filtro-secciones"><h2>Explora la sección</h2><div class="filtro-botones"><a class="filtro <?=$sec===''?'seleccionado':''?>" href="noticias.php?categoria=<?=h($cat)?>">Todo</a><?php foreach($c['subs'] as $k=>$v):?><a class="filtro <?=$sec===$k?'seleccionado':''?>" href="noticias.php?categoria=<?=h($cat)?>&amp;sec=<?=h($k)?>"><?=h($v)?></a><?php endforeach;?></div></section><?php endif;?>
<!-- LISTA DE NOTICIAS -->
<?php if(!empty($errorDb)):?><p class="alerta">No se pudieron cargar las noticias en este momento. Inténtalo de nuevo más tarde.</p><?php elseif(!$lista):?><section class="sin-noticias"><h2>🍌 Todavía no hay noticias publicadas</h2><p>Pronto habrá noticias en esta sección.</p><?php if($admin):?><a class="btn" href="admin/nueva.php">＋ Crear primera noticia</a><?php endif;?></section><?php else:?><section class="noticias-grid"><?php foreach($lista as $n):?><a class="enlace-tarjeta" href="articulo.php?id=<?=(int)$n['id']?>"><article class="tarjeta-noticia tarjeta-<?=h($n['plantilla']??'clasica')?>"><div class="noticia-imagen"><?php if($n['imagen']):?><img src="<?=h($n['imagen'])?>" alt=""><?php else:?><span>🍌</span><?php endif;?></div><div class="noticia-cuerpo"><?php if(!empty($n['destacada'])):?><div class="badge-destacada">★ Destacada</div><?php endif;?><div class="noticia-meta"><span><?=h($titulo)?></span><time><?=h(date('d/m/Y',strtotime($n['fecha_publicacion'])))?></time></div><h2><?=h($n['titulo'])?></h2><p><?=h($n['resumen'])?></p><span class="noticia-pendiente">Leer artículo completo →</span></div></article></a><?php endforeach;?></section><?php endif;?></main>
<!-- PIE DE PÁGINA -->
<footer>🍌 <?=h(papoyisa_config()['texto_pie']??'Papoyisa · Noticias para estar al día')?></footer>
<script>document.getElementById('btnMenu')?.addEventListener('click',()=>document.getElementById('menu').classList.toggle('abierto'));</script></body></html>