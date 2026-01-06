const domain = import.meta.env.WP_DOMAIN
const apiUrl = `${domain}/wp-json/wp/v2`
const apiUrlInfo = `${domain}/wp-json/custom/v1`

export const getSiteInfo = async () => {
    console.log('apiUrlInfo', apiUrlInfo)
    const rest = await fetch('https://anicasolucionesintegrales.com/wp-json/custom/v1/site-info')
    if (!rest.ok) throw new Error('Failet to fetch site information')

    return rest.json()

}
// https://anicasolucionesintegrales.com/wp-json/custom/v1/site-info