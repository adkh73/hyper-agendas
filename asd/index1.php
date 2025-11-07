<?php
require_once 'config.php';
require_once 'admin/includes/db.php';
require_once 'admin/includes/functions.php';

$productos = getProductos(true);
$promocion = getPromocionActiva();

$mensaje_exito = '';
$mensaje_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pedido_submit'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $producto_id = intval($_POST['producto_id'] ?? 0);

    if ($nombre && $email && $usuario && $producto_id) {
        if (crearPedido($nombre, $email, $usuario, $producto_id)) {
            $mensaje_exito = "✅ ¡Tu pedido fue registrado correctamente! Te contactaremos pronto.";
        } else {
            $mensaje_error = "❌ Error al registrar tu pedido, intenta nuevamente.";
        }
    } else {
        $mensaje_error = "Por favor completá todos los campos.";
    }
}

// Helper para imagen placeholder si no existe
function producto_imagen($img) {
    if ($img && file_exists(__DIR__ . '/uploads/' . $img)) return 'uploads/' . $img;
    return 'https://via.placeholder.com/600x600?text=Agenda';
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars(SITE_NAME) ?></title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

  <!-- Tailwind (CDN) -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Alpine.js -->
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  <style>
    body { font-family: 'Inter', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; }
    .accent { background: linear-gradient(90deg,#ff7a59,#ff3d81); }
  </style>
</head>
<body class="bg-gray-50">

<!-- HERO / PROMO -->
<header class="accent text-white">
  <div class="max-w-7xl mx-auto px-6 py-8">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">Agendas 2026</h1>
        <p class="mt-2 text-sm md:text-base opacity-90">Diseños profesionales listos para vender — personalizá y ganá.</p>
      </div>
      <div class="hidden md:flex items-center space-x-4">
        <a href="#packs" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded font-semibold">Ver packs</a>
        <a href="#productos" class="bg-white text-indigo-600 font-bold px-4 py-2 rounded shadow">Comprar ahora</a>
      </div>
    </div>

    <?php if ($promocion): 
        // fecha_fin para contador (formato YYYY-MM-DD)
        $fecha_fin = $promocion['fecha_fin'];
        $texto_prom = htmlspecialchars($promocion['texto']);
        $descuento = floatval($promocion['descuento']);
    ?>
    <div class="mt-8 md:mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="md:col-span-2 bg-white/10 rounded p-6 shadow">
        <h2 class="text-xl md:text-2xl font-bold"><?= $texto_prom ?></h2>
        <p class="mt-2 text-sm md:text-base opacity-90">Apurate: descuento del <span class="font-extrabold"><?= $descuento ?>%</span> sobre todos los modelos hasta <?= htmlspecialchars($fecha_fin) ?>.</p>

        <!-- Contador -->
        <div x-data="countdown('<?= $fecha_fin ?>')" class="mt-6 flex space-x-4 text-center">
          <div class="bg-white/10 rounded p-3 w-20">
            <div class="text-2xl font-bold" x-text="days">0</div>
            <div class="text-xs opacity-80">Días</div>
          </div>
          <div class="bg-white/10 rounded p-3 w-20">
            <div class="text-2xl font-bold" x-text="hours">0</div>
            <div class="text-xs opacity-80">Horas</div>
          </div>
          <div class="bg-white/10 rounded p-3 w-20">
            <div class="text-2xl font-bold" x-text="minutes">0</div>
            <div class="text-xs opacity-80">Min</div>
          </div>
          <div class="bg-white/10 rounded p-3 w-20">
            <div class="text-2xl font-bold" x-text="seconds">0</div>
            <div class="text-xs opacity-80">Seg</div>
          </div>
        </div>
      </div>

      <div class="bg-white rounded p-6 text-indigo-700 shadow">
        <div class="text-sm font-semibold">OFERTA FLASH</div>
        <div class="text-3xl md:text-4xl font-extrabold mt-3">$<?= number_format(0,2) /** placeholder */ ?></div>
        <p class="mt-2 text-sm opacity-80">Precio especial aplicado automáticamente al checkout.</p>
        <a href="#productos" class="mt-4 inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded font-bold">Comprar ahora</a>
      </div>
    </div>
    <?php endif; ?>
  </div>
</header>

<main class="max-w-7xl mx-auto px-6 py-10">

  <!-- Mensajes -->
  <?php if ($mensaje_exito): ?>
    <div class="mb-6 p-4 rounded bg-green-50 border border-green-200 text-green-800"><?= $mensaje_exito ?></div>
  <?php endif; ?>
  <?php if ($mensaje_error): ?>
    <div class="mb-6 p-4 rounded bg-red-50 border border-red-200 text-red-800"><?= $mensaje_error ?></div>
  <?php endif; ?>

  <!-- BENEFICIOS / SELL POINTS -->
  <section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
    <div class="bg-white rounded shadow p-6">
      <h3 class="font-bold">Diseños 100% editables</h3>
      <p class="mt-2 text-sm text-gray-600">Archivos listos para personalizar en PowerPoint y Canva.</p>
    </div>
    <div class="bg-white rounded shadow p-6">
      <h3 class="font-bold">Actualizaciones incluidas</h3>
      <p class="mt-2 text-sm text-gray-600">Acceso a updates hasta diciembre 2026.</p>
    </div>
    <div class="bg-white rounded shadow p-6">
      <h3 class="font-bold">Uso comercial ilimitado</h3>
      <p class="mt-2 text-sm text-gray-600">Vendé cientos de agendas sin restricciones.</p>
    </div>
  </section>

  <!-- PACKS DESTACADOS -->
  <section id="packs" class="mb-12">
    <h2 class="text-2xl font-bold mb-4">Packs destacados</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Ejemplo de pack — podés reemplazar con productos si querés -->
      <div class="bg-white rounded shadow p-6">
        <div class="text-sm text-indigo-600 font-semibold">MEGACOLECCIÓN</div>
        <h3 class="text-xl font-bold mt-2">10 PACK EN 1</h3>
        <p class="mt-2 text-sm text-gray-600">+2.500 diseños, mockups y bonos.</p>
        <div class="mt-4 flex items-baseline gap-3">
          <div class="text-3xl font-extrabold">$15</div>
          <div class="text-sm line-through text-gray-400">$45</div>
        </div>
        <a href="#productos" class="mt-4 inline-block bg-indigo-600 text-white px-4 py-2 rounded font-semibold">Comprar</a>
      </div>

      <div class="bg-white rounded shadow p-6">
        <div class="text-sm text-indigo-600 font-semibold">PACK 2025/26</div>
        <h3 class="text-xl font-bold mt-2">Pack normal</h3>
        <p class="mt-2 text-sm text-gray-600">Diseños con calendario y extras.</p>
        <div class="mt-4 flex items-baseline gap-3">
          <div class="text-3xl font-extrabold">$5</div>
          <div class="text-sm line-through text-gray-400">$25</div>
        </div>
        <a href="#productos" class="mt-4 inline-block bg-indigo-600 text-white px-4 py-2 rounded font-semibold">Comprar</a>
      </div>

      <div class="bg-white rounded shadow p-6">
        <div class="text-sm text-indigo-600 font-semibold">OFERTA FLASH</div>
        <h3 class="text-xl font-bold mt-2">Solo por hoy</h3>
        <p class="mt-2 text-sm text-gray-600">Bonos y regalos por compra inmediata.</p>
        <div class="mt-4 flex items-baseline gap-3">
          <div class="text-3xl font-extrabold">$6</div>
          <div class="text-sm line-through text-gray-400">$25</div>
        </div>
        <a href="#productos" class="mt-4 inline-block bg-indigo-600 text-white px-4 py-2 rounded font-semibold">Comprar</a>
      </div>
    </div>
  </section>

  <!-- GALERÍA / MUESTRAS -->
  <section class="mb-12">
    <h2 class="text-2xl font-bold mb-4">Algunas muestras</h2>
    <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
      <?php for($i=1;$i<=12;$i++): ?>
        <div class="rounded overflow-hidden bg-white shadow">
          <img src="https://via.placeholder.com/400x400?text=Muestra+<?= $i ?>" alt="Muestra <?= $i ?>" class="w-full h-36 object-cover">
        </div>
      <?php endfor; ?>
    </div>
  </section>

  <!-- LISTADO DE PRODUCTOS -->
  <section id="productos" class="mb-12">
    <h2 class="text-2xl font-bold mb-4">Modelos disponibles</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <?php foreach($productos as $p):
          $precio_final = calcularPrecioFinal($p['precio'], $promocion);
      ?>
      <article class="bg-white rounded shadow hover:shadow-lg transition p-4 flex flex-col">
        <img src="<?= producto_imagen($p['imagen']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>" class="w-full h-56 object-cover rounded mb-4">
        <h3 class="font-bold text-lg"><?= htmlspecialchars($p['nombre']) ?></h3>
        <p class="text-sm text-gray-600 mt-2 flex-1"><?= htmlspecialchars($p['descripcion']) ?></p>
        <div class="mt-4 flex items-center justify-between">
          <div>
            <div class="text-xl font-extrabold">$<?= number_format($precio_final,2) ?></div>
            <?php if($promocion): ?>
              <div class="text-sm line-through text-gray-400">$<?= number_format($p['precio'],2) ?></div>
            <?php endif; ?>
          </div>
          <button @click="open=true; producto_id=<?= $p['id'] ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded">Encargar</button>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- TESTIMONIOS (mock) -->
  <section class="mb-12">
    <h2 class="text-2xl font-bold mb-4">Lo que dicen nuestros clientes</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white rounded shadow p-6">
        <div class="font-semibold">María G.</div>
        <p class="mt-2 text-sm text-gray-600">"Excelente calidad y fácil personalización. Recomendado."</p>
      </div>
      <div class="bg-white rounded shadow p-6">
        <div class="font-semibold">Carlos P.</div>
        <p class="mt-2 text-sm text-gray-600">"Ventas rápidas desde el primer día usando estos diseños."</p>
      </div>
      <div class="bg-white rounded shadow p-6">
        <div class="font-semibold">Lucía R.</div>
        <p class="mt-2 text-sm text-gray-600">"Los bonos incluidos fueron un diferencial para mis clientes."</p>
      </div>
    </div>
  </section>

</main>

<!-- MODAL DE PEDIDO (ALPINE) -->
<div x-data="{ open:false, producto_id:0 }" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
  <div @click.away="open=false" class="bg-white rounded-lg w-full max-w-lg shadow-lg overflow-hidden">
    <div class="p-6">
      <div class="flex justify-between items-center">
        <h3 class="text-lg font-bold">Realizar pedido</h3>
        <button @click="open=false" class="text-gray-600 hover:text-gray-900">&times;</button>
      </div>

      <form method="POST" class="mt-4 space-y-3">
        <input type="hidden" name="producto_id" :value="producto_id">
        <label class="block text-sm">Nombre</label>
        <input name="nombre" required class="w-full border rounded px-3 py-2">

        <label class="block text-sm">Email</label>
        <input name="email" type="email" required class="w-full border rounded px-3 py-2">

        <label class="block text-sm">Usuario (Instagram / Telegram)</label>
        <input name="usuario" required class="w-full border rounded px-3 py-2">

        <button type="submit" name="pedido_submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 rounded">Enviar pedido</button>
      </form>
    </div>
  </div>
</div>

<!-- FOOTER -->
<footer class="mt-12 bg-white border-t">
  <div class="max-w-7xl mx-auto px-6 py-8 text-sm text-gray-600">
    <div class="flex flex-col md:flex-row md:justify-between">
      <div>© <?= date('Y') ?> <?= htmlspecialchars(SITE_NAME) ?> — Todos los derechos reservados.</div>
      <div class="mt-4 md:mt-0">Contacto: <a href="mailto:info@tusitio.com" class="text-indigo-600">info@tusitio.com</a></div>
    </div>
  </div>
</footer>

<!-- SCRIPTS: countdown -->
<script>
function countdown(dateStr) {
  return {
    days: '0', hours: '0', minutes: '0', seconds: '0',
    init() {
      const target = new Date(dateStr + 'T23:59:59').getTime();
      const tick = () => {
        const now = new Date().getTime();
        let diff = target - now;
        if (diff < 0) { this.days='0'; this.hours='0'; this.minutes='0'; this.seconds='0'; return; }
        const d = Math.floor(diff / (1000*60*60*24));
        diff -= d*(1000*60*60*24);
        const h = Math.floor(diff / (1000*60*60));
        diff -= h*(1000*60*60);
        const m = Math.floor(diff / (1000*60));
        diff -= m*(1000);
        const s = Math.floor(diff / 1000);
        this.days = d; this.hours = String(h).padStart(2,'0'); this.minutes = String(m).padStart(2,'0'); this.seconds = String(s).padStart(2,'0');
      };
      tick();
      setInterval(tick, 1000);
    }
  }
}
</script>

</body>
</html>
