/**
 * Declarative modules catalog — SaaS assigns modules; client chooses visibility in dashboard.
 */

export const MODULES_CATALOG_API = '/api/dashboard/modules/catalog';
export const MODULES_VISIBILITY_WEB = '/dashboard/modules/visibility';

/**
 * @param {Record<string, unknown>|null|undefined} raw
 */
export function normalizeModulesCatalogPayload(raw) {
  const msg = typeof raw?.service_contact_message === 'string' ? raw.service_contact_message : '';
  return {
    producers: Array.isArray(raw?.producers) ? raw.producers : [],
    subscribers: Array.isArray(raw?.subscribers) ? raw.subscribers : [],
    available_producers: Array.isArray(raw?.available_producers) ? raw.available_producers : (Array.isArray(raw?.producers) ? raw.producers : []),
    available_subscribers: Array.isArray(raw?.available_subscribers) ? raw.available_subscribers : (Array.isArray(raw?.subscribers) ? raw.subscribers : []),
    visible_producer_ids: Array.isArray(raw?.visible_producer_ids) ? raw.visible_producer_ids : [],
    visible_subscriber_ids: Array.isArray(raw?.visible_subscriber_ids) ? raw.visible_subscriber_ids : [],
    middleware: raw?.middleware && typeof raw.middleware === 'object' ? raw.middleware : {},
    service_contact_message: msg,
  };
}

/**
 * @param {import('axios').AxiosStatic} axios
 */
export async function fetchModulesCatalog(axios) {
  const { data } = await axios.get(MODULES_CATALOG_API);
  return normalizeModulesCatalogPayload(data);
}

/**
 * @param {import('axios').AxiosStatic} axios
 * @param {{ producers?: string[], subscribers?: string[] }} payload
 */
export async function saveModulesVisibility(axios, payload) {
  const { data } = await axios.patch(MODULES_VISIBILITY_WEB, payload);
  return normalizeModulesCatalogPayload(data.catalog ?? data);
}
