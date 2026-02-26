import type { APIRoute } from 'astro';

// ─────────────────────────────────────────────────────────────────────────────
// Tipos
// ─────────────────────────────────────────────────────────────────────────────

interface SearchEntry {
    title: string;
    url: string;
    type: 'Servicio' | 'Producto';
    serviceSlug: string;
    serviceName: string;
    excerpt: string;
    keywords: string[];
}

interface ProductDef {
    name: string;
    short?: string;
    hash: string;
    keywords?: string[];
}

interface ServiceDef {
    slug: string;
    name: string;
    excerpt: string;
    keywords: string[];
    products: ProductDef[];
}

// ─────────────────────────────────────────────────────────────────────────────
// Catálogo estático de servicios y productos
// Fuente de verdad: los arrays de productos en src/pages/servicios/*.astro
// ─────────────────────────────────────────────────────────────────────────────

const SERVICES: ServiceDef[] = [
    // ── Medios Visuales & Promocionales ──────────────────────────────────────
    {
        slug: 'medios-visuales',
        name: 'Medios Visuales & Promocionales',
        excerpt: 'Herramientas visuales que comunican tu marca: rotulación, impresión, promocionales y más.',
        keywords: ['medios visuales', 'rotulación', 'publicidad', 'impresión', 'promocionales', 'señalética'],
        products: [
            {
                name: 'Letras Corpóreas en PVC, Acrílico y Metal',
                short: 'Letras de caja iluminadas',
                hash: 'letras-corporeas',
                keywords: ['letras', 'letras corpóreas', 'letras pvc', 'letras acrilico', 'letras metal', 'iluminadas', 'backlit'],
            },
            {
                name: 'Sidekick Publicitario (MDF, PVC, Coroplast 8mm)',
                short: 'Sidekick publicitario',
                hash: 'sidekick',
                keywords: ['sidekick', 'display piso', 'punto de venta', 'mdf', 'coroplast'],
            },
            {
                name: 'Exhibidor de Mostrador en PVC',
                short: 'Exhibidor de mostrador',
                hash: 'exhibidor-pvc',
                keywords: ['exhibidor', 'mostrador', 'pvc', 'display mostrador'],
            },
            {
                name: 'Stand Personalizado',
                short: 'Stand de piso a medida',
                hash: 'stand-personalizado',
                keywords: ['stand', 'stand personalizado', 'stand piso', 'exhibición'],
            },
            {
                name: 'Rótulos PVC y Señalética',
                short: 'Señalética y rótulos',
                hash: 'rotulos-pvc',
                keywords: ['rótulo', 'rotulos', 'señalética', 'pvc', 'señal', 'aviso'],
            },
            {
                name: 'Vinil Impreso y Stickers',
                short: 'Viniles y stickers personalizados',
                hash: 'vinil-stickers',
                keywords: ['vinil', 'sticker', 'adhesivo', 'impresión vinil', 'calcomanía'],
            },
            {
                name: 'Sublimación y Transfer (tazas, textiles, termos, llaveros, lapiceros, lanyards, DTF)',
                short: 'Productos promocionales sublimados',
                hash: 'sublimacion',
                keywords: ['sublimación', 'sublimacion', 'transfer', 'dtf', 'tazas', 'textiles', 'termos', 'llaveros', 'lapiceros', 'lanyards', 'promocionales'],
            },
            {
                name: 'Rotulación Vehicular y Gran Formato (banners, lonas, microperforado, vinil reflectivo)',
                short: 'Rotulación vehicular y gran formato',
                hash: 'rotulacion-vehicular',
                keywords: ['rotulación vehicular', 'banner', 'lona', 'microperforado', 'reflectivo', 'gran formato', 'vehículo'],
            },
            {
                name: 'Roll-ups y Displays Portátiles',
                short: 'Displays portátiles y roll-ups',
                hash: 'roll-ups',
                keywords: ['roll up', 'rollup', 'display portátil', 'banner portátil'],
            },
            {
                name: 'Grabado y Corte Láser (madera, acrílico, metal, cuero)',
                hash: 'grabado-laser',
                keywords: ['láser', 'laser', 'grabado', 'corte', 'madera', 'acrilico', 'metal', 'cuero', 'piezas personalizadas'],
            },
            {
                name: 'Vallas Publicitarias y Gran Formato',
                hash: 'vallas',
                keywords: ['valla', 'vallas', 'billboard', 'gran formato', 'outdoor', 'publicidad exterior'],
            },
        ],
    },

    // ── Muebles Comerciales & Hogar ───────────────────────────────────────────
    {
        slug: 'muebles-comerciales',
        name: 'Muebles Comerciales & Hogar',
        excerpt: 'Diseño y fabricación de muebles publicitarios, exhibidores, islas y mobiliario para el hogar.',
        keywords: ['muebles', 'muebles comerciales', 'mobiliario', 'fabricación muebles', 'carpintería'],
        products: [
            {
                name: 'Muebles de Piso',
                hash: 'muebles-piso',
                keywords: ['mueble piso', 'mueble de piso', 'stand piso', 'exhibición piso', 'retail'],
            },
            {
                name: 'Activación Arco',
                hash: 'activacion-arco',
                keywords: ['arco', 'activación', 'activacion arco', 'punto de venta arco'],
            },
            {
                name: 'Isla Especial',
                hash: 'isla-especial',
                keywords: ['isla', 'isla especial', 'islas comerciales', 'isla de ventas'],
            },
            {
                name: 'Mueble Tipo Cabecera',
                hash: 'cabecera',
                keywords: ['cabecera', 'mueble cabecera', 'gondola cabecera'],
            },
            {
                name: 'Mueble Punta de Góndola',
                hash: 'punta-gondola',
                keywords: ['punta de góndola', 'gondola', 'exhibidor gondola', 'punta gondola'],
            },
            {
                name: 'Mueble TV / Entretenimiento',
                hash: 'mueble-tv',
                keywords: ['mueble tv', 'televisión', 'entretenimiento', 'mueble electrónico'],
            },
            {
                name: 'Mueble Estantería',
                hash: 'estanteria',
                keywords: ['estantería', 'estante', 'repisa', 'librero'],
            },
            {
                name: 'Escritorio Modular',
                hash: 'escritorio',
                keywords: ['escritorio', 'escritorio modular', 'estación trabajo', 'desk'],
            },
            {
                name: 'Exhibidor',
                hash: 'exhibidor',
                keywords: ['exhibidor', 'exhibición', 'vitrina', 'display mueble'],
            },
            {
                name: 'Isla Comercial',
                hash: 'isla',
                keywords: ['isla', 'isla comercial'],
            },
            {
                name: 'Barra Bar',
                hash: 'barra-bar',
                keywords: ['barra', 'barra bar', 'bar', 'cantina', 'mostrador bar'],
            },
        ],
    },

    // ── Diseño Gráfico & Modelado 3D ─────────────────────────────────────────
    {
        slug: 'diseno-3d',
        name: 'Diseño Gráfico & Modelado 3D',
        excerpt: 'Identidad corporativa, diseño editorial, publicidad, packaging, modelado 3D y renderizado fotorrealista.',
        keywords: ['diseño gráfico', 'diseño', 'branding', 'modelado 3d', '3d', 'renderizado', 'identidad'],
        products: [
            {
                name: 'Identidad Corporativa',
                hash: 'identidad-corporativa',
                keywords: ['identidad', 'logo', 'logotipo', 'branding', 'manual identidad', 'marca'],
            },
            {
                name: 'Diseño Editorial',
                hash: 'diseno-editorial',
                keywords: ['editorial', 'revista', 'catálogo', 'brochure', 'libro', 'reporte anual', 'layout'],
            },
            {
                name: 'Publicidad Impresa y Digital',
                hash: 'publicidad-impresa',
                keywords: ['publicidad', 'flyer', 'afiche', 'campaña', 'pieza publicitaria', 'impresa'],
            },
            {
                name: 'Diseño para Redes Sociales',
                hash: 'redes-sociales',
                keywords: ['redes sociales', 'social media', 'post', 'stories', 'reels', 'instagram', 'facebook', 'contenido digital'],
            },
            {
                name: 'Modelado 3D',
                hash: 'modelado-3d',
                keywords: ['modelado 3d', '3d', 'modelo', 'render', 'modelado', 'diseño 3d'],
            },
            {
                name: 'Diseño, Modelado y Renderizado de Stand',
                hash: 'renderizado-stand',
                keywords: ['stand 3d', 'render stand', 'visualización stand', 'renderizado', 'fotorrealista'],
            },
            {
                name: 'Packaging y Diseño de Empaque',
                hash: 'packaging',
                keywords: ['packaging', 'empaque', 'embalaje', 'unboxing', 'etiqueta'],
            },
            {
                name: 'Paquetes Personalizados de Diseño',
                hash: 'paquetes',
                keywords: ['paquete diseño', 'paquete personalizado', 'diseño completo'],
            },
        ],
    },

    // ── Web & Programación ────────────────────────────────────────────────────
    {
        slug: 'web-programacion',
        name: 'Desarrollo Web & Programación',
        excerpt: 'Sitios web modernos, apps móviles, sistemas empresariales (ERP, CRM, POS) y automatización digital.',
        keywords: ['desarrollo web', 'web', 'programación', 'software', 'aplicaciones', 'tecnología', 'digital'],
        products: [
            {
                name: 'Diseño UI/UX',
                hash: 'ui-ux',
                keywords: ['ui', 'ux', 'interfaz', 'experiencia usuario', 'diseño web', 'wireframe', 'prototipo'],
            },
            {
                name: 'Diseño Responsivo',
                hash: 'diseno-responsivo',
                keywords: ['responsivo', 'responsive', 'móvil', 'adaptable', 'mobile first'],
            },
            {
                name: 'Landing Pages',
                hash: 'landing-pages',
                keywords: ['landing page', 'landing', 'página aterrizaje', 'conversión', 'campaña'],
            },
            {
                name: 'Sitios Corporativos',
                hash: 'sitios-corporativos',
                keywords: ['sitio corporativo', 'web corporativa', 'empresa web', 'presencia web'],
            },
            {
                name: 'E-Commerce (Tienda Online)',
                hash: 'ecommerce',
                keywords: ['ecommerce', 'e-commerce', 'tienda online', 'tienda virtual', 'pasarela pago', 'ventas online'],
            },
            {
                name: 'Aplicaciones Móviles (iOS & Android)',
                hash: 'apps-moviles',
                keywords: ['app móvil', 'aplicación móvil', 'android', 'ios', 'nativa', 'multiplataforma'],
            },
            {
                name: 'Aplicaciones Web Progresivas (PWA)',
                hash: 'pwa',
                keywords: ['pwa', 'progressive web app', 'app web', 'offline', 'web app'],
            },
            {
                name: 'Dashboards y Paneles de Control',
                hash: 'dashboards',
                keywords: ['dashboard', 'panel control', 'admin', 'visualización datos', 'reportes'],
            },
            {
                name: 'Integraciones y APIs',
                hash: 'apis',
                keywords: ['api', 'integración', 'webhook', 'rest', 'servicios externos', 'automatización api'],
            },
            {
                name: 'Sistemas de Gestión Empresarial (ERP)',
                hash: 'erp',
                keywords: ['erp', 'sistema gestión', 'recursos empresariales', 'inventario', 'finanzas sistema'],
            },
            {
                name: 'CRM (Gestión de Relaciones con Clientes)',
                hash: 'crm',
                keywords: ['crm', 'clientes', 'seguimiento ventas', 'marketing automatizado', 'contactos'],
            },
            {
                name: 'Sistemas de Punto de Venta (POS)',
                hash: 'pos',
                keywords: ['pos', 'punto de venta', 'caja', 'facturación', 'inventario pos', 'restaurante sistema'],
            },
            {
                name: 'Automatización de Procesos',
                hash: 'automatizacion',
                keywords: ['automatización', 'automatizar', 'bot', 'flujo trabajo', 'eficiencia', 'productividad'],
            },
            {
                name: 'Bases de Datos y Migración',
                hash: 'bases-datos',
                keywords: ['base de datos', 'database', 'sql', 'migración', 'optimización bd'],
            },
            {
                name: 'Hosting, Dominio & SSL',
                hash: 'hosting',
                keywords: ['hosting', 'dominio', 'ssl', 'correo corporativo', 'mantenimiento web', 'seo'],
            },
        ],
    },

    // ── Eventos Sociales ──────────────────────────────────────────────────────
    {
        slug: 'eventos-sociales',
        name: 'Eventos Sociales',
        excerpt: 'Productos personalizados para bodas, fiestas y eventos corporativos: señalética, decoración, photobooth y más.',
        keywords: ['eventos', 'eventos sociales', 'bodas', 'fiestas', 'corporativo', 'decoración evento'],
        products: [
            {
                name: 'Mesa de Bienvenida Personalizada',
                hash: 'mesa-bienvenida',
                keywords: ['mesa bienvenida', 'señalética boda', 'welcome table', 'boda'],
            },
            {
                name: 'Señalización de Eventos Corporativos',
                hash: 'senalizacion-corporativa',
                keywords: ['señalización', 'rotulación evento', 'corporativo', 'empresa evento'],
            },
            {
                name: 'Decoración de Mesas Temáticas',
                hash: 'decoracion-mesas',
                keywords: ['decoración', 'mesa temática', 'fiesta', 'cumpleaños', 'quinceañera'],
            },
            {
                name: 'Photobooth Personalizado',
                hash: 'photobooth',
                keywords: ['photobooth', 'photo booth', 'fotografía evento', 'fondo foto', 'props'],
            },
            {
                name: 'Menús y Tarjetas de Mesa',
                hash: 'menus-tarjetas',
                keywords: ['menú', 'tarjeta mesa', 'papelería evento', 'papelería boda'],
            },
            {
                name: 'Stands Promocionales para Ferias',
                hash: 'stands-feria',
                keywords: ['stand feria', 'exposición', 'exhibición evento', 'feria comercial'],
            },
            {
                name: 'Centros de Mesa Personalizados',
                hash: 'centros-mesa',
                keywords: ['centro de mesa', 'centerpiece', 'decoración central', 'flores'],
            },
            {
                name: 'Señalética Direccional para Eventos',
                hash: 'senaletica-direccional',
                keywords: ['señalética direccional', 'direccion evento', 'guía visual', 'flechas evento'],
            },
            {
                name: 'Backdrop Personalizado',
                hash: 'backdrop',
                keywords: ['backdrop', 'fondo impreso', 'background', 'lona fondo', 'ceremonia fondo'],
            },
        ],
    },
];

// ─────────────────────────────────────────────────────────────────────────────
// Generador de entradas de búsqueda
// ─────────────────────────────────────────────────────────────────────────────

function buildSearchEntries(): SearchEntry[] {
    const entries: SearchEntry[] = [];

    for (const service of SERVICES) {
        // Entrada de nivel servicio (sin hash)
        entries.push({
            title: service.name,
            url: `/servicios/${service.slug}`,
            type: 'Servicio',
            serviceSlug: service.slug,
            serviceName: service.name,
            excerpt: service.excerpt,
            keywords: service.keywords,
        });

        // Entradas de productos (con hash anchor)
        for (const product of service.products) {
            entries.push({
                title: product.short ?? product.name,
                url: `/servicios/${service.slug}#${product.hash}`,
                type: 'Producto',
                serviceSlug: service.slug,
                serviceName: service.name,
                excerpt: `${product.name} — ${service.name}`,
                keywords: [
                    ...(product.keywords ?? []),
                    ...service.keywords,
                    service.slug,
                ],
            });
        }
    }

    return entries;
}

// ─────────────────────────────────────────────────────────────────────────────
// Endpoint
// ─────────────────────────────────────────────────────────────────────────────

export const GET: APIRoute = async () => {
    const staticEntries = buildSearchEntries();

    // Contenido dinámico desde WordPress (mantenido por compatibilidad)
    const dynamicEntries: SearchEntry[] = [];

    try {
        const { getPostsByCategory } = await import('../../services/wp');

        try {
            const productosMap = await getPostsByCategory('productos');
            const productos = Object.values(productosMap);
            productos.forEach((item: any) => {
                dynamicEntries.push({
                    title: item.title.rendered,
                    url: `/productos/${item.slug}`,
                    type: 'Producto',
                    serviceSlug: 'productos',
                    serviceName: 'Productos',
                    excerpt: item.excerpt.rendered.replace(/<[^>]*>?/gm, '').slice(0, 100) + '...',
                    keywords: [item.slug],
                });
            });
        } catch {
            console.log('[search] No se encontraron productos WP.');
        }

        try {
            const portfolioMap = await getPostsByCategory('portafolio');
            if (portfolioMap) {
                const projects = Object.values(portfolioMap);
                projects.forEach((item: any) => {
                    dynamicEntries.push({
                        title: item.title.rendered,
                        url: `/portafolio/${item.slug}`,
                        type: 'Producto',
                        serviceSlug: 'portafolio',
                        serviceName: 'Portafolio',
                        excerpt: item.excerpt.rendered.replace(/<[^>]*>?/gm, '').slice(0, 100) + '...',
                        keywords: [item.slug, 'portafolio', 'proyecto'],
                    });
                });
            }
        } catch {
            console.log('[search] No se encontró categoría portafolio en WP.');
        }
    } catch {
        console.log('[search] Módulo WP no disponible.');
    }

    // Los productos estáticos van primero (mayor prioridad en el cliente)
    const payload = [...staticEntries, ...dynamicEntries];

    return new Response(JSON.stringify(payload), {
        status: 200,
        headers: { 'Content-Type': 'application/json' },
    });
};
