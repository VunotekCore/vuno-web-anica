const domain = import.meta.env.WP_DOMAIN
const apiUrl = `${domain}/wp-json/wp/v2`
const apiUrlInfo = `${domain}/wp-json/custom/v1`

export const getSiteInfo = async () => {
    try {
        // Detectar si estamos en el servidor o en el cliente
        const isServer = typeof window === 'undefined'

        let proxyUrl: string
        if (isServer) {
            // En el servidor, usar URL absoluta
            const baseUrl = import.meta.env.SITE || 'http://localhost:4321'
            proxyUrl = `${baseUrl}/api/site-info`
        } else {
            // En el cliente, usar URL relativa
            proxyUrl = '/api/site-info'
        }

        const response = await fetch(proxyUrl)

        if (!response.ok) {
            throw new Error(`Failed to fetch site info: ${response.status} ${response.statusText}`)
        }

        const data = await response.json()
        return data

    } catch (error) {
        console.error('getSiteInfo error:', error)
        throw error
    }
}

// // https://anicasolucionesintegrales.com/wp-json/custom/v1/site-info

export const getPageInfo = async (slug: string) => {
    const response = await fetch(`${apiUrl}/pages?slug=${slug}`)
    console.log(domain)
    console.log(apiUrl)
    if (!response.ok) {
        throw new Error('Failed to fetch page info')
    }
    //   const data = await response.json()
    const [data] = await response.json() // desestructuracion para acceder al primer elemento equivalente hacer data[0]

    //   const { title, content } = data
    //   console.log(title.rendered)
    //   console.log(content.rendered)

    const {
        title: { rendered: title },
        content: { rendered: content }
    } = data

    console.log('title', title)
    console.log('content', content)

    return { title, content }
}