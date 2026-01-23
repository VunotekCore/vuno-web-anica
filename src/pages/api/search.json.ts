import type { APIRoute } from 'astro';
import { getPostsByCategory } from '../../services/wp';

export const GET: APIRoute = async () => {
    const posts = [];

    // 1. Static Services from src/pages/servicios
    // Using import.meta.glob to dynamically get the files
    const serviceFiles = import.meta.glob('../servicios/*.astro');

    for (const path in serviceFiles) {
        const slug = path.split('/').pop()?.replace('.astro', '');
        // Format title from slug: web-programacion -> Web Programacion
        const title = slug
            ?.split('-')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');

        posts.push({
            title: title,
            url: `/servicios/${slug}`,
            type: 'Servicio',
            excerpt: `Servicio especializado de ${title} en Anica Soluciones.`
        });
    }

    // 2. Dynamic Content from WordPress
    try {
        // Fetch 'productos'
        const productosMap = await getPostsByCategory('productos');
        const productos = Object.values(productosMap);

        productos.forEach((item: any) => {
            posts.push({
                title: item.title.rendered,
                url: `/productos/${item.slug}`, // Assuming this route exists or will exist
                type: 'Producto',
                excerpt: item.excerpt.rendered.replace(/<[^>]*>?/gm, '').slice(0, 100) + '...'
            });
        });

        // Fetch 'proyectos' or 'portafolio'
        // Checking previous conversations, user has 'portafolio' mentioned in nav, but let's try 'proyectos' or 'portafolio'
        // I will try 'portafolio' as it's a common slug, if fails, it might be empty.
        // Actually, let's stick to safe bets or catch errors.
        try {
            const portfolioMap = await getPostsByCategory('portafolio');
            if (portfolioMap) {
                const projects = Object.values(portfolioMap);
                projects.forEach((item: any) => {
                    posts.push({
                        title: item.title.rendered,
                        url: `/portafolio/${item.slug}`,
                        type: 'Proyecto',
                        excerpt: item.excerpt.rendered.replace(/<[^>]*>?/gm, '').slice(0, 100) + '...'
                    });
                });
            }
        } catch (e) {
            console.log('No portfolio/projects found or category not exists');
        }

    } catch (error) {
        console.error('Error fetching WP posts for search:', error);
    }

    return new Response(JSON.stringify(posts), {
        status: 200,
        headers: {
            'Content-Type': 'application/json'
        }
    });
}
