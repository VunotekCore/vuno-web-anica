import { bufferHeadContent } from 'astro/runtime/server/render/astro/render.js'

const domain = import.meta.env.WP_DOMAIN || 'https://wp.anicasolucionesintegrales.com'
const apiUrl = `${domain}/wp-json/wp/v2`
const apiUrlInfo = `${domain}/wp-json/custom/v1`
const apiUrlPostsCategories = `${domain}/wp-json/categories/v1`

export const getSiteInfo = async () => {
  let response: Response

  try {
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

  if (!response.ok) {
    throw new Error('Failed to fetch page info')
  }
  //   const data = await response.json()
  const [data] = await response.json() // desestructuracion para acceder al primer
  //   console.log(data)
  //elemento equivalente hacer data[0]
  if (!data) {
    throw new Error('Page not found')
  }

  const {
    title: { rendered: title },
    content_plain
  } = data

  return { title, content_plain }
}

export const getPostInfo = async (slug: string) => {
  const response = await fetch(`${apiUrl}/posts?slug=${slug}`)
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

// obtener listado de posts por categoria
export const getPostsByCategory = async (category: string) => {
  const response = await fetch(`${apiUrlPostsCategories}/posts-by-category/${category}`)
  if (!response.ok) {
    throw new Error('Failed to fetch posts by category')
  }

  const data = await response.json()
  // convertir data en un mapa por slug
  const institucionalBySlug = Object.fromEntries(data.map((item: any) => [item.slug, item]))
  return institucionalBySlug
}

//  obtener fotos de imagekit.io
export const getSliderFromImageKit = async () => {
  const urlImagekit = import.meta.env.URL_IMAGEKIT
  const privateKey = import.meta.env.PRIVATE_KEY
  const auth = Buffer.from(`${privateKey}:`).toString('base64')

  const options = {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      Authorization: `Basic ${auth}`
    }
  }

  try {
    const response = await fetch(urlImagekit, options)

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`)
    }

    const data = await response.json()
    return data
  } catch (error) {
    console.error(error)
    return []
  }
}
