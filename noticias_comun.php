<?php
/* noticias_comun.php — Funciones compartidas por las páginas públicas: categorías, escape de texto y apariencia. */
require_once __DIR__ . '/conexion.php';
// Categorías y subcategorías del sitio (menú, filtros y editor)
function papoyisa_categorias(): array { return [
 'deportes'=>['titulo'=>'Deportes','emoji'=>'🏀','descripcion'=>'Toda la actualidad deportiva.','subs'=>['NBA'=>'Básquetbol / NBA','NFL'=>'NFL','MLB'=>'MLB','Apuesta'=>'Análisis de apuestas']],
 'politica'=>['titulo'=>'Política','emoji'=>'🏛️','descripcion'=>'Información política de México y el mundo.','subs'=>['Mexico'=>'México','USA'=>'Estados Unidos']],
 'conspiraciones'=>['titulo'=>'Conspiraciones','emoji'=>'👽','descripcion'=>'Misterios, teorías y casos para investigar.','subs'=>['Muertes'=>'Muertes','Reemplazos'=>'Reemplazos','Asesinatos'=>'Asesinatos','Aliens'=>'Aliens']],
 'historia'=>['titulo'=>'#Historia','emoji'=>'📜','descripcion'=>'Personajes, civilizaciones y acontecimientos históricos.','subs'=>[]]
 ]; }
// Escapa texto para mostrarlo de forma segura en HTML
function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
// Lee los ajustes de apariencia guardados en la tabla papoyisa_config
function papoyisa_config(): array {
 static $cfg=null; if($cfg!==null)return $cfg; $cfg=[];
 try{$rows=db()->query('SELECT clave,valor FROM papoyisa_config')->fetchAll();foreach($rows as $r)$cfg[$r['clave']]=$r['valor'];}catch(Throwable $e){}
 return $cfg;
}
// Genera el <style> con los colores y la tipografía elegidos en Admin > Personalizar
function papoyisa_estilo(): string {
 $c=papoyisa_config();$primary=preg_match('/^#[0-9a-fA-F]{6}$/',$c['color_primario']??'')?$c['color_primario']:'#ffd43b';$secondary=preg_match('/^#[0-9a-fA-F]{6}$/',$c['color_secundario']??'')?$c['color_secundario']:'#174c83';$bg=preg_match('/^#[0-9a-fA-F]{6}$/',$c['color_fondo']??'')?$c['color_fondo']:'#fffdf5';$font=in_array($c['tipografia']??'system',['system','arial','georgia'],true)?$c['tipografia']:'system';$fontCss=['system'=>'system-ui, sans-serif','arial'=>'Arial, sans-serif','georgia'=>'Georgia, serif'][$font];
 return '<style>:root{--papoy-acento:'.$primary.';--papoy-secundario:'.$secondary.';--papoy-fondo:'.$bg.';--papoy-tipografia:'.$fontCss.'}body{font-family:var(--papoy-tipografia)}.top,.noticias-hero{border-color:var(--papoy-acento)}.btn,.filtro.seleccionado,.badge-destacada{background:var(--papoy-secundario)!important}.pagina-noticias{background:var(--papoy-fondo)}.logo,.noticia-meta span{color:var(--papoy-secundario)}</style>';
}
