/**
 * Action1 API Tester
 * Flexible API testing interface for Action1 API
 */
export class Action1Api {
    constructor() {
        this.authenticated = false;
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ||
                         document.querySelector('input[name="_token"]')?.value;

        this.init();
    }

    static getInstance() {
        if (!Action1Api.instance) {
            Action1Api.instance = new Action1Api();
        }
        return Action1Api.instance;
    }

    init() {
        this.bindElements();
        if (this.statusIndicator) {
            this.bindEvents();
            this.connect();
        }
    }

    bindElements() {
        // Status elements
        this.statusIndicator = document.getElementById('status-indicator');
        this.statusText = document.getElementById('status-text');
        this.reconnectBtn = document.getElementById('reconnect-btn');

        // Request builder elements
        this.requestBuilder = document.getElementById('request-builder');
        this.methodSelect = document.getElementById('method-select');
        this.endpointInput = document.getElementById('endpoint-input');
        this.sendBtn = document.getElementById('send-request');

        // Path parameters
        this.pathParamsSection = document.getElementById('path-params-section');
        this.pathParamsContainer = document.getElementById('path-params-container');

        // Query parameters
        this.addQueryParamBtn = document.getElementById('add-query-param');
        this.queryParamsContainer = document.getElementById('query-params-container');

        // Body
        this.bodySection = document.getElementById('body-section');
        this.requestBody = document.getElementById('request-body');

        // Response
        this.responseCard = document.getElementById('response-card');
        this.loadingOverlay = document.getElementById('loading-overlay');
        this.responseStatus = document.getElementById('response-status');
        this.responseTime = document.getElementById('response-time');
        this.responseContent = document.getElementById('response-content');
        this.copyResponseBtn = document.getElementById('copy-response');
        this.clearResponseBtn = document.getElementById('clear-response');
    }

    bindEvents() {
        // Reconnect
        this.reconnectBtn?.addEventListener('click', () => this.connect());

        // Method change - show/hide body section
        this.methodSelect?.addEventListener('change', () => this.onMethodChange());

        // Endpoint change - detect path parameters
        this.endpointInput?.addEventListener('input', () => this.onEndpointChange());
        this.endpointInput?.addEventListener('change', () => this.onEndpointChange());

        // Add query parameter
        this.addQueryParamBtn?.addEventListener('click', () => this.addQueryParam());

        // Send request
        this.sendBtn?.addEventListener('click', () => this.sendRequest());

        // Copy response
        this.copyResponseBtn?.addEventListener('click', () => this.copyResponse());

        // Clear response
        this.clearResponseBtn?.addEventListener('click', () => this.clearResponse());

        // Send on Enter in endpoint input
        this.endpointInput?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.sendRequest();
        });
    }

    async connect() {
        this.setStatus('connecting', 'Connecting to Action1...');
        this.reconnectBtn?.classList.add('hidden');

        try {
            const response = await this.apiCall('/api/action1/auth', {});

            if (response.success) {
                this.authenticated = true;
                this.setStatus('connected', 'Connected to Action1');
                this.requestBuilder?.classList.remove('hidden');
                this.onMethodChange();
            } else {
                this.setStatus('error', response.message || 'Connection failed');
                this.reconnectBtn?.classList.remove('hidden');
            }
        } catch (error) {
            this.setStatus('error', 'Connection error: ' + error.message);
            this.reconnectBtn?.classList.remove('hidden');
        }
    }

    setStatus(status, message) {
        if (this.statusText) {
            this.statusText.textContent = message;
        }

        if (this.statusIndicator) {
            this.statusIndicator.classList.remove('bg-gray-400', 'bg-green-500', 'bg-red-500', 'bg-yellow-500');

            switch (status) {
                case 'connected':
                    this.statusIndicator.classList.add('bg-green-500');
                    break;
                case 'error':
                    this.statusIndicator.classList.add('bg-red-500');
                    break;
                case 'connecting':
                    this.statusIndicator.classList.add('bg-yellow-500');
                    break;
                default:
                    this.statusIndicator.classList.add('bg-gray-400');
            }
        }
    }

    onMethodChange() {
        const method = this.methodSelect?.value;
        const showBody = method === 'POST' || method === 'PATCH';

        if (this.bodySection) {
            this.bodySection.classList.toggle('hidden', !showBody);
        }
    }

    onEndpointChange() {
        const endpoint = this.endpointInput?.value || '';
        const pathParams = endpoint.match(/\{([^}]+)\}/g) || [];

        if (pathParams.length > 0) {
            this.pathParamsSection?.classList.remove('hidden');
            this.renderPathParams(pathParams);
        } else {
            this.pathParamsSection?.classList.add('hidden');
            if (this.pathParamsContainer) {
                this.pathParamsContainer.innerHTML = '';
            }
        }
    }

    renderPathParams(params) {
        if (!this.pathParamsContainer) return;

        // Get existing values
        const existingValues = {};
        this.pathParamsContainer.querySelectorAll('input').forEach(input => {
            existingValues[input.dataset.param] = input.value;
        });

        this.pathParamsContainer.innerHTML = '';

        params.forEach(param => {
            const paramName = param.replace(/[{}]/g, '');
            const row = document.createElement('div');
            row.className = 'flex gap-2 items-center';
            row.innerHTML = `
                <label class="w-32 text-sm text-gray-600 dark:text-gray-400 font-mono">${paramName}</label>
                <input
                    type="text"
                    data-param="${paramName}"
                    placeholder="Enter ${paramName}"
                    value="${existingValues[paramName] || ''}"
                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm"
                />
            `;
            this.pathParamsContainer.appendChild(row);
        });
    }

    addQueryParam(key = '', value = '') {
        if (!this.queryParamsContainer) return;

        const row = document.createElement('div');
        row.className = 'flex gap-2 items-center';
        row.innerHTML = `
            <input
                type="text"
                placeholder="Key"
                value="${key}"
                class="query-key w-1/3 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm"
            />
            <input
                type="text"
                placeholder="Value"
                value="${value}"
                class="query-value flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm"
            />
            <button type="button" class="remove-param text-red-500 hover:text-red-700 px-2 py-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;

        row.querySelector('.remove-param').addEventListener('click', () => row.remove());
        this.queryParamsContainer.appendChild(row);
    }

    buildEndpoint() {
        let endpoint = this.endpointInput?.value || '';

        // Replace path parameters
        this.pathParamsContainer?.querySelectorAll('input').forEach(input => {
            const paramName = input.dataset.param;
            const value = input.value.trim();
            if (value) {
                endpoint = endpoint.replace(`{${paramName}}`, encodeURIComponent(value));
            }
        });

        return endpoint;
    }

    buildQueryParams() {
        const params = {};
        this.queryParamsContainer?.querySelectorAll('.flex').forEach(row => {
            const key = row.querySelector('.query-key')?.value.trim();
            const value = row.querySelector('.query-value')?.value.trim();
            if (key) {
                params[key] = value;
            }
        });
        return params;
    }

    buildBody() {
        try {
            const bodyText = this.requestBody?.value.trim() || '{}';
            return JSON.parse(bodyText);
        } catch (e) {
            return null;
        }
    }

    async sendRequest() {
        if (!this.authenticated) {
            this.showResponse({ error: 'Not authenticated' }, 401);
            return;
        }

        const method = this.methodSelect?.value || 'GET';
        const endpoint = this.buildEndpoint();
        const query = this.buildQueryParams();
        const body = this.buildBody();

        // Validate endpoint
        if (!endpoint || endpoint.includes('{')) {
            this.showResponse({ error: 'Please fill in all path parameters' }, 400);
            return;
        }

        // Validate body for POST/PATCH
        if ((method === 'POST' || method === 'PATCH') && body === null) {
            this.showResponse({ error: 'Invalid JSON in request body' }, 400);
            return;
        }

        this.showLoading(true);
        const startTime = performance.now();

        try {
            const response = await this.apiCall('/api/action1/request', {
                method,
                endpoint,
                query,
                body: body || {},
            });

            const duration = Math.round(performance.now() - startTime);
            this.showResponse(response.data || response, response.status || 200, duration);
        } catch (error) {
            const duration = Math.round(performance.now() - startTime);
            this.showResponse({ error: error.message }, 500, duration);
        } finally {
            this.showLoading(false);
        }
    }

    async apiCall(endpoint, data) {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
            },
            body: JSON.stringify(data),
        });

        return await response.json();
    }

    showLoading(show) {
        this.responseCard?.classList.remove('hidden');
        this.loadingOverlay?.classList.toggle('hidden', !show);
    }

    showResponse(data, status = 200, duration = null) {
        this.responseCard?.classList.remove('hidden');

        // Status badge
        if (this.responseStatus) {
            this.responseStatus.textContent = status;
            this.responseStatus.className = 'text-sm font-mono px-2 py-0.5 rounded ';
            if (status >= 200 && status < 300) {
                this.responseStatus.className += 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
            } else if (status >= 400) {
                this.responseStatus.className += 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
            } else {
                this.responseStatus.className += 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
            }
        }

        // Duration
        if (this.responseTime && duration !== null) {
            this.responseTime.textContent = `${duration}ms`;
        }

        // Content
        if (this.responseContent) {
            this.responseContent.textContent = JSON.stringify(data, null, 2);
        }
    }

    copyResponse() {
        const content = this.responseContent?.textContent;
        if (content) {
            navigator.clipboard.writeText(content).then(() => {
                const btn = this.copyResponseBtn;
                const originalText = btn.textContent;
                btn.textContent = 'Copied!';
                setTimeout(() => btn.textContent = originalText, 1500);
            });
        }
    }

    clearResponse() {
        if (this.responseContent) {
            this.responseContent.textContent = '';
        }
        if (this.responseStatus) {
            this.responseStatus.textContent = '';
        }
        if (this.responseTime) {
            this.responseTime.textContent = '';
        }
        this.responseCard?.classList.add('hidden');
    }
}
