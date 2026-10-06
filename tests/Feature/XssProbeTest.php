<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;

/**
 * El POS serializa los productos dentro de un <script> como
 * `const PRODUCTS_DATA = <json>;`.
 *
 * El riesgo teorico es que un nombre de producto rompa el bloque <script> y
 * ejecute JS. Isso NO ocurre porque json_encode() escapa "/" por defecto
 * (\/), de modo que "</script>" nunca puede cerrar el bloque. Los substrings
 * "<script>", "onerror=", etc. si aparecen en el texto, pero quedan siempre
 * dentro de un literal de cadena JS y por tanto son inertes.
 *
 * Este test fija esa garantia para que nadie la rompa por accidente, por
 * ejemplo anadiendo JSON_UNESCAPED_SLASHES o JSON_UNESCAPED_UNICODE.
 */
test('un nombre de producto malicioso no puede escapar del bloque script del POS', function () {
    $caja = User::factory()->create(['role' => User::ROLE_CAJA]);
    $category = Category::create(['name' => 'Abarrotes']);

    $payloads = [
        '900001' => '</script><img src=x onerror=alert(1)>',
        '900002' => '"; alert(document.cookie); //',
        '900003' => '\\</script><script>alert(2)</script>',
        '900004' => '<img src=x onerror=alert(3)>',
        '900005' => '&lt;/script&gt;<svg onload=alert(4)>',
        '900006' => "'); alert(5); //",
        '900007' => '</SCRIPT ><script>alert(6)</script>',
        '900008' => '</script\n><script>alert(7)</script>',
    ];

    foreach ($payloads as $code => $name) {
        Product::create([
            'code' => $code,
            'name' => $name,
            'barcode' => null,
            'category_id' => $category->id,
            'sale_type' => Product::SALE_TYPE_UNIT,
            'weight_unit' => null,
            'price' => 1.00,
            'stock' => 10,
            'is_active' => true,
        ]);
    }

    $response = $this->actingAs($caja)->get(route('sales.create'));

    $response->assertOk();
    $html = $response->getContent();

    preg_match('/<script>\s*const PRODUCTS_DATA = (.*?);\s*<\/script>/s', $html, $matches);
    $json = $matches[1] ?? '';

    expect($json)->not->toBe('', 'no se encontro el payload PRODUCTS_DATA');

    // 1. El bloque emitted sigue siendo JSON valido: la app no se rompe.
    $decoded = json_decode($json, true);
    expect($decoded)->toBeArray('PRODUCTS_DATA debe seguir siendo JSON valido');

    // 2. Todos los payloads llegaron intactos: nadie manipulo el literal.
    $names = array_column($decoded, 'name');
    foreach ($payloads as $code => $name) {
        expect($names)->toContain($name);
    }

    // 3. GUARDIA CLAVE: "</script" (en cualquier variante de mayusculas)
    //    no puede aparecer en el payload, porque cerraria el bloque <script>
    //    y permitiria inyectar HTML/JS. Este es el invariante que hay que
    //    mantener; se apoya en el escape de "/" que hace json_encode().
    expect(strtolower($json))->not->toContain('</script');

    // 4. El literal emitido debe usar escapes JSON, no HTML. Si el nombre
    //    aparece tal cual en la pagina, es texto vivo y no dato escapado.
    //    Ojo: tras json_decode() el payload vuelve a contenerse
    //    necesariamente; por eso la comprobacion va sobre el HTML emitido.
    foreach ($payloads as $name) {
        expect($html)->not->toContain($name);
    }

    // 5. Ningun manejador de evento ejecutable fuera del literal JSON.
    //    Nota: la pagina legitamente trae un <script> de Vite, asi que solo
    //    se comprueban los atributos on* y "javascript:".
    $outsideJson = str_replace($json, '', $html);
    foreach (['onerror=', 'onload=', 'onclick=', 'javascript:'] as $needle) {
        expect(strtolower($outsideJson))->not->toContain($needle);
    }
});
