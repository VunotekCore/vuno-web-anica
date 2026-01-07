import type { APIRoute } from 'astro';

/**
 * PROXY ENDPOINT PARA WORDPRESS
 * 
 * Este proxy es necesario porque WordPress/Hostinger tiene protección anti-bot
 * que bloquea peticiones que no vienen de navegadores reales.
 */

export const GET: APIRoute = async () => {
    try {
        const wpDomain = import.meta.env.WP_DOMAIN;

        if (!wpDomain) {
            console.error('WP_DOMAIN no está configurado en .env');
            return new Response(JSON.stringify({
                error: 'WordPress domain not configured'
            }), {
                status: 500,
                headers: { 'Content-Type': 'application/json' }
            });
        }

        const url = `${wpDomain}/wp-json/custom/v1/site-info`;
        console.log('[PROXY] Fetching from WordPress:', url);

        const response = await fetch(url, {
            headers: {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept': 'application/json, text/plain, */*',
                'Accept-Language': 'es-ES,es;q=0.9,en;q=0.8',
            }
        });

        console.log('[PROXY] WordPress response status:', response.status);

        // Si WordPress está bloqueado por anti-bot, usar datos de fallback
        if (!response.ok) {
            const errorText = await response.text();

            if (response.status === 403 && errorText.includes('jschallenge')) {
                console.warn('[PROXY] WordPress bloqueado por anti-bot. Usando datos de fallback.');

                const fallbackData = {
                    logo_url: null,
                    site: {
                        name: "ANICA Soluciones Integrales",
                        description: "",
                        url: "https://anicasolucionesintegrales.com",
                        home: "https://anicasolucionesintegrales.com",
                        language: "es",
                        charset: "UTF-8"
                    }
                };

                return new Response(JSON.stringify(fallbackData), {
                    status: 200,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Fallback-Data': 'true',
                        'Cache-Control': 'no-cache'
                    }
                });
            }

            return new Response(JSON.stringify({
                error: 'Failed to fetch from WordPress',
                status: response.status,
                details: errorText.substring(0, 500)
            }), {
                status: response.status,
                headers: { 'Content-Type': 'application/json' }
            });
        }

        const data = await response.json();
        console.log('[PROXY] Data received from WordPress');

        return new Response(JSON.stringify(data), {
            status: 200,
            headers: {
                'Content-Type': 'application/json',
                'Cache-Control': 'public, max-age=300'
            }
        });

    } catch (error) {
        console.error('[PROXY] Error:', error);

        return new Response(JSON.stringify({
            error: 'Internal server error',
            message: error instanceof Error ? error.message : 'Unknown error'
        }), {
            status: 500,
            headers: { 'Content-Type': 'application/json' }
        });
    }
};
