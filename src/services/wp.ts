const domain = import.meta.env.WP_DOMAIN || 'https://anicasolucionesintegrales.com'
const apiUrl = `${domain}/wp-json/wp/v2`
const apiUrlInfo = `${domain}/wp-json/custom/v1`

export const getSiteInfo = async () => {
    let response: Response

    try {
        console.log(domain)
        console.log(apiUrlInfo)
        response = await fetch(`${apiUrlInfo}/site-info`)
    } catch (error) {
        console.error('getSiteInfo error:', error)
        throw error
    }

    if (!response.ok) {
        throw new Error('Failed to fetch site information')
    }

    return response.json()
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