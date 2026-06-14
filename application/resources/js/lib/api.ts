function getCsrfToken(): string {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

export async function apiFetch(
    url: string,
    init: RequestInit = {},
): Promise<Response> {
    const headers = new Headers(init.headers);
    if (!headers.has('Accept')) headers.set('Accept', 'application/json');

    const method = (init.method ?? 'GET').toUpperCase();
    if (method !== 'GET' && method !== 'HEAD' && !headers.has('X-XSRF-TOKEN')) {
        headers.set('X-XSRF-TOKEN', getCsrfToken());
    }

    return fetch(url, {
        credentials: 'same-origin',
        ...init,
        headers,
    });
}
